<?php
/*
Plugin Name: Google Calendar List View
Plugin URI: 
Description: The plugin is to create a shortcode for displaying the list view of a public Google Calendar.
Version: 7.2.3
Author: Kimiya Kitani
Author URI: https://profiles.wordpress.org/kimipooh/
Text Domain: list-view-google-calendar
Domain Path: /lang
*/

require_once( plugin_dir_path(__FILE__) . '/includes/hash_tags.php');
require_once( plugin_dir_path(__FILE__) . '/includes/getAPIDataCurl.php');

class gclv extends gclv_hash_tags{
	var $set_op = 'list-view-google-calendar_array';	// Save setting name in DB
	var $plugin_name = 'list-view-google-calendar';
	var $plugin_title = 'Google Calendar List View';
	var $plugin_shortcode = 'gc_list_view';
	var $default_maxResults = 10;  
	var $default_noEventMessage = "There are no events.";
	var $default_fix_timezone_offset = ""; // Corrected values for time zone deviations
	var $html_tags = array('li'=>'li', 'p'=>'p', 'dd'=>'dd', 'lip'=>'lip', 'li2'=>'li2', 'li-month'=>'li-month', 'li-title'=>'li-title','li-notitle'=>'li-notitle' ); 
	var $default_html_tag = 'li'; 
	var $google_calendar = array( 
		'api-key'		=> '',
		'id'			=> '',
		'api-url'		=> 'https://www.googleapis.com/calendar/v3/calendars/',
		'start-date'	=> '',					// Default events are from today to the future.
		'end-date'		=> '',
		'orderby'		=> 'startTime',			// startTime, updated (only ascending).
		'orderbysort'	=> 'descending',		// ascending or descending.
		'maxResults'	=> '',					// Get items <= 2500 (https://developers.google.com/google-apps/calendar/v3/reference/events/list)
		'html_tag'		=> '',	
		'noEventMessage' =>'',
		'fix-timezone-offset' => '',  // Corrected values for time zone deviations
	);
	var $lang_dir = 'lang';	// Language folder name
	var $settings;
	
	function __construct(){
		$this->settings = get_option($this->set_op);
		$this->init_settings();
		register_activation_hook(__FILE__, array(&$this, 'installer'));
		register_deactivation_hook(__FILE__, array(&$this, 'uninstaller'));
		// Add Setting to WordPress 'Settings' menu. 
		add_action('admin_menu', array(&$this, 'add_to_settings_menu'));
		add_action('plugins_loaded', array(&$this,'enable_language_translation'));
		
		add_shortcode($this->plugin_shortcode, array(&$this, 'shortcodes'));
	}
	public function enable_language_translation(){
		load_plugin_textdomain($this->plugin_name)
		or load_plugin_textdomain($this->plugin_name, false, dirname( plugin_basename( __FILE__ ) ) . '/' . $this->lang_dir . '/');
	}
	public function init_settings(){
		$this->settings = $this->google_calendar; // Save to default settings.
		$this->settings['version'] = 720;
		$this->settings['db_version'] = 100;
	}
	public function installer(){
		update_option($this->set_op , $this->settings);
	}
	public function uninstaller(){
		// Remove Save data.
		delete_option($this->set_op);
	}
	
	/* WordPress Timezone are 2 types (Strings (ex. "Asia/Tokyo") or Offset (ex. +9,-1, or etc.) ).
	* "get_date_from_gmt" function requires "String" timezone set (ex. "Asia/Tokyo"), so if WordPress timezone is set by Offset, the date gotten by this function is incorrect. Therefore, if a plugin/theme needs to handle a date with timezone on WordPress, the special handling for not only the timezone set (String) but also the timezone (Offset) is required.
	* The library can handle the date with both of 2 types Timezone on WordPress!
	*/
	public  function  wp_datetime_converter_init(){
		$timezone_set = '';
		// WordPress Timezone are 2 types (Strings or Offset).
		$timezone_set = get_option('timezone_string');
		if (! $timezone_set ):
			$gmt_offset  = get_option( 'gmt_offset' );
			$gmt_hours   = (int) $gmt_offset;
			$gmt_minutes = ( $gmt_offset - floor( $gmt_offset ) ) * 60;
			$timezone_set  = sprintf( '%+03d:%02d', $gmt_hours, $gmt_minutes );
		endif;
		return $timezone_set;
	}
	// Get current time with Timezone.
	public function wp_datetime_converter_current_time($format="c"){
		$timezone_set = $this->wp_datetime_converter_init(); 
		$date_obj = new DateTime('', new DateTimeZone($timezone_set)); // get UTC time.
		$settings = get_option($this->set_op);
		if(isset($settings['google_calendar']) && is_array($settings['google_calendar'])):
			$this->google_calendar = $settings['google_calendar'];
		endif;
		if(isset($this->google_calendar['fix-timezone-offset']) && !empty($this->google_calendar['fix-timezone-offset'])):
			if(@DateInterval::createFromDateString($this->google_calendar['fix-timezone-offset'])):
				$date_obj->modify($this->google_calendar['fix-timezone-offset']);
			endif;
		endif;		
		return $date_obj->format($format);
	}		
		
	// Get the date time with WordPress timezone.
	public function wp_datetime_converter_get_date_from_gmt($format="c", $dateTime="", $timezone_set="", $flag="start"){
		$timezone_set = $this->wp_datetime_converter_init();
		if(empty($dateTime)) return $date;
		if(empty($timezone_set)) return $timezone_set;
		$date_obj = new DateTime($dateTime); // UTC timezone
		// In case of all day,  the timezone is not set. ex. 30/03/2022(all day) sets "start: 30/03/2022, end: 01/04/2022)". Thus, fixed to subtract one second from the end date to make it 23:59:59.
		if(!preg_match('/T/', $dateTime)):
			if($flag === "end"):
				$date_obj->modify('-1 seconds');
			endif;
			return $date_obj->format($format);
		endif; 
		$date_obj->setTimezone(new DateTimeZone($timezone_set)); // Set timezone.
		$settings = get_option($this->set_op);
		if(isset($settings['google_calendar']) && is_array($settings['google_calendar'])):
			$this->google_calendar = $settings['google_calendar'];
		endif;
		if(isset($this->google_calendar['fix-timezone-offset']) && !empty($this->google_calendar['fix-timezone-offset'])):
			if(@DateInterval::createFromDateString($this->google_calendar['fix-timezone-offset'])):
				$date_obj->modify($this->google_calendar['fix-timezone-offset']);
			endif;
		endif;

		return $date_obj->format($format);
	}

	// Set the date time with WordPress timezone.
	public function wp_datetime_converter_setTimeZone($format="c", $dateTime="", $timezone_set=""){
		$timezone_set = $this->wp_datetime_converter_init();
		if(empty($dateTime)) return $date;
		if(empty($timezone_set)) return $timezone_set;
		$date_obj = new DateTime($dateTime, new DateTimeZone($timezone_set)); // Set timezone.
		$settings = get_option($this->set_op);
		if(isset($settings['google_calendar']) && is_array($settings['google_calendar'])):
			$this->google_calendar = $settings['google_calendar'];
		endif;
		if(isset($this->google_calendar['fix-timezone-offset']) && !empty($this->google_calendar['fix-timezone-offset'])):
			if(@DateInterval::createFromDateString($this->google_calendar['fix-timezone-offset'])):
				$date_obj->modify($this->google_calendar['fix-timezone-offset']);
			endif;
		endif;		
		return $date_obj->format($format);
	}
	
	// Convert time to the beginning of the day or the end of the day with WordPress timezone.
	// Default: convert to the beginning of the day.
	public function wp_datetime_converter_setDayTime($format="c", $dateTime="",  $flag="start", $timezone_set=""){
		$timezone_set = $this->wp_datetime_converter_init();
		if(empty($dateTime)) return $dateTime;
		if(empty($timezone_set)) return $timezone_set;

		if($dateTime == strtolower("today")):
			$date_obj = new DateTime('', new DateTimeZone($timezone_set)); // Set timezone.
		else:
			$date_obj = new DateTime($dateTime, new DateTimeZone($timezone_set)); // Set timezone.
		endif;
		if($flag == strtolower("start")):
			$date_num = mktime(0,0,0,$date_obj->format("m"), $date_obj->format("d"), $date_obj->format("Y"));
		else:
			$date_num = mktime(23,59,59,$date_obj->format("m"), $date_obj->format("d"), $date_obj->format("Y"));
		endif;
		$date_obj = new DateTime(date('Y-m-d H:i:s', $date_num), new DateTimeZone($timezone_set));
		
		$settings = get_option($this->set_op);
		if(isset($settings['google_calendar']) && is_array($settings['google_calendar'])):
			$this->google_calendar = $settings['google_calendar'];
		endif;
		if(isset($this->google_calendar['fix-timezone-offset']) && !empty($this->google_calendar['fix-timezone-offset'])):
			if(@DateInterval::createFromDateString($this->google_calendar['fix-timezone-offset'])):
				$date_obj->modify($this->google_calendar['fix-timezone-offset']);
			endif;
		endif;		
		return $date_obj->format($format);
	}

	// Mapping month names with WordPress Core's translation feature 
	public function convert_language_of_month_name(){
		  $convert_month = array(
		  	"January" => __("January"),
  			"February"	=> __("February"),
  			"March" => __("March"),
  			"April" => __("April"),
  			"May" => __("May"),
  			"June" => __("June"),
  			"July" => __("July"),
  			"August" => __("August"),
  			"September" => __("September"),
  			"October" => __("October"),
  			"November" => __("November"),
  			"December" => __("December")
  		);
  		  		
 		return $convert_month;
	}

	public function shortcodes($atts){
		$atts = $this->security_check_array($atts);
		// If there are not any options, $atts will be initialized by array().
		if(empty($atts) || !is_array($atts)):
			$atts = array();
		endif;
		// Allow g_id_*** and g_api_key_*** version 4.0
		$atts_special_allow_options = array();
		foreach($atts as $key=>$value):
			if(preg_match('/^g_id_/', $key) || preg_match('/^g_api_key_/', $key)):
				$atts_special_allow_options[$key] = $value;
			endif;
		endforeach;
		$atts_options = array(
			'id'			=> '',
			'start_date' 	=> '',
			'end_date'		=> '',
			'date_format'	=> 'Y.m.d', 
			'orderbysort'	=> '',			// ascending or descending.
			'g_api_key'		=> '',			// Google Calendar API KEY
			'g_id'			=> '',			// Google Calendar ID
			'max_view'		=> '',			// Maximum number of view
			'max_display'		=> '',		// Maximum number of display
			'html_tag'		=> '',			// Allow $this->html_tags value.
			'html_tag_class'	=> '',		// adding a class to html tag (default: $this->plugin_name) 
			'hook_secret_key' => '',		// If you use a hook, please set the secret key because of preventing an overwrite from any other plugins.
			'lang'			=> '',			// List only specific languages. #lang [value] on the comment of Google Calendar. version 2.1
			'enable_view_category'	=> '',	// If you want to display the category (#type and #organizer), please set this value to "true" or not empty value. version 3.0
			'view_location'	=> '',	// If the value is not empty, the location data is displayed with title.
			'no_event_message' => '', // When there are no events, this message is displayed priority.
			'view_location_name' =>'', // If the view_location option is enabled, this value is set as the title of the item.
			'no_event_link' => '', // If the no_event_link value isn't empty, the event link is removed.
			'view_end_date' => '', // If the view_end_date value isn't empty, the end date is displayed, using the value of view_end_date as the delimiter string after the start date.
		);
		if(!empty($atts_special_allow_options)):
			$atts_options = array_merge($atts_options, $atts_special_allow_options);  // Overwrite the same options.
		endif;
		extract($atts = shortcode_atts($atts_options, $atts));

		$html_tag_class = $html_tag_class ?: $this->plugin_name;

		$settings = get_option($this->set_op);	
		$gc_data = $this->get_google_calendar_contents($atts); 
		// get lang data.
		$gc_data = $this->get_select_lang_data($gc_data, $atts); 
		// Security check for the hook (clean up ALL html tag except description).
		$gc_data = $this->security_check_array($gc_data);

		if(!isset($settings['google_calendar'])):
			$settings['google_calendar'] = array();
		endif;
		if(!isset($settings['google_calendar']['html_tag'])):
			$settings['google_calendar']['html_tag'] = "";
		endif;
		if(isset($html_tag) && !empty($html_tag)): 
			$settings['google_calendar']['html_tag'] = wp_strip_all_tags($html_tag);
			if(!isset($this->html_tags[$settings['google_calendar']['html_tag']])) $settings['google_calendar']['html_tag'] = $this->html_tags[$this->default_html_tag];
		endif;
		$atts['html_tag'] = $settings['google_calendar']['html_tag'] ? $settings['google_calendar']['html_tag'] : $this->default_html_tag;
		$html_tag = $atts['html_tag'];
		if(isset($orderbysort) && !empty($orderbysort)):
			$settings['google_calendar']['orderbysort'] = wp_strip_all_tags($orderbysort);
		endif;
		if(isset($no_event_message) && !empty($no_event_message)): 
				$settings['google_calendar']['noEventMessage'] = wp_strip_all_tags($no_event_message);
		endif;
		if(!isset($settings['google_calendar']['noEventMessage'])) $settings['google_calendar']['noEventMessage'] = $this->default_noEventMessage;
		else if($settings['google_calendar']['noEventMessage'] === "none") $settings['google_calendar']['noEventMessage']  = "";
		if(isset($fix_timezone_offset) && !empty($fix_timezone_offset)): 
				$settings['google_calendar']['fix-timezone-offset'] = wp_strip_all_tags($fix_timezone_offset);
		endif;
		if(!isset($settings['google_calendar']['fix-timezone-offset'])):
			$settings['google_calendar']['fix-timezone-offset'] = $this->default_fix_timezone_offset;
		endif;

		$out  = ''; 
		$element_count = 0; 
		$match = array();
		if( isset($gc_data['items']) ): 
			foreach($gc_data['items'] as $gc_key=>$gc_value):
				if(isset($gc_value['start']['dateTime'])):
					$dateTime = $gc_value['start']['dateTime'];
				else:
					$dateTime = $gc_value['start']['date'];
				endif;
				if(isset($gc_value['end']['dateTime'])):
					$end_dateTime = $gc_value['end']['dateTime'];
				else:
					$end_dateTime = $gc_value['end']['date'];
				endif;

				$today_date_num = $this->wp_datetime_converter_current_time("Ymd");
				$start_date_num = $this->wp_datetime_converter_get_date_from_gmt("Ymd", $dateTime);
				$start_date_value = $this->wp_datetime_converter_get_date_from_gmt($date_format, $dateTime);
				$end_date_num = $this->wp_datetime_converter_get_date_from_gmt("Ymd", $end_dateTime, "", "end");
				$end_date_value = $this->wp_datetime_converter_get_date_from_gmt($date_format, $end_dateTime, "", "end");
		
				$holding_flag = false;
				if($today_date_num >= $start_date_num && $today_date_num <= $end_date_num) $holding_flag = true;
				$gc_link = "";
				if(isset($gc_value['htmlLink'])):
					$gc_link = esc_url($gc_value['htmlLink']);
					$gc_value['htmlLink'] = $gc_link;
				endif;
				$gc_title = "";
				if(isset($gc_value['summary'])):
					$gc_title = esc_html($gc_value['summary']);
					$gc_value['summary'] = $gc_title;
				endif;
				$gc_description = "";
				if(isset($gc_value['description'])):
					$gc_description = wp_kses_post($gc_value['description']);
					$gc_value['description'] = $gc_description;
				endif;
				$gc_location = "";
				if(isset($gc_value['location'])):
					$gc_location = esc_html($gc_value['location']);
					if(isset($atts['view_location']) && !empty($gc_location)):
						// in case of view_location = "yes|link" in the shortcode opton,
						if($atts['view_location'] === 'yes|link'):
							$url_preg='/http(s)?:\/\/[0-9a-z_,.:;&=+*%$#!?@()~\'\/-]+/i';
							$gc_location  = preg_replace($url_preg, '<a target="_blank" href="$0">$0</a>', $gc_location);
						elseif($atts['view_location'] === 'yes|map'):
							$gc_location  = '<a target="_blank" href="https://www.google.com/maps/search/' . $gc_location . '"/>' . $gc_location . '</a>';
						elseif($atts['view_location'] === 'yes|link|map'):
							$url_preg='/http(s)?:\/\/[0-9a-z_,.:;&=+*%$#!?@()~\'\/-]+/i';
							if(preg_match($url_preg, $gc_location)):
								$gc_location  = preg_replace($url_preg, '<a target="_blank" href="$0">$0</a>', $gc_location);
							else:
								$gc_location  = '<a target="_blank" href="https://www.google.com/maps/search/' . $gc_location . '"/>' . $gc_location . '</a>';
							endif;
						endif;
					endif;
					
					
					$gc_value['location'] = $gc_location;
				endif;
				$plugin_name = $this->plugin_name;
				$html_tag_class_c = $holding_flag ? $html_tag_class . '_holding' : $html_tag_class;

				// for a hook.
				$hash_tags = $this->get_hash_tags($gc_value, $atts);
				$hash_tags_type_title = "";
				if(isset($hash_tags['type']['title'])) $hash_tags_type_title = $hash_tags['type']['title'];
				$hash_tags_organizer_value = "";
				if(isset($hash_tags['organizer']['value'])) $hash_tags_organizer_value = $hash_tags['organizer']['value'];
				if(isset($hash_tags['organizer']['title'])) $hash_tags_organizer_value .= ' ' . $hash_tags['organizer']['title'];
				$hash_tags_display_value = "";
				if(isset($hash_tags['display']['value'])) $hash_tags_display_value = $hash_tags['display']['value'];
				$output_category_temp = '';
				if(!empty($enable_view_category)):
					if(!empty($hash_tags_type_title)):
						$output_category_temp .= " <span class='{$html_tag_class}_category'>$hash_tags_type_title</span> ";
					endif;
					if(!empty($hash_tags_organizer_value)):
						$output_category_temp .= " <span class='{$html_tag_class}_organizer'>$hash_tags_organizer_value</span> ";
					endif;
				endif;
				$start_date_month_value = $this->wp_datetime_converter_get_date_from_gmt("Ym", $dateTime);
				if( isset($pre_start_dateTime) ):
					$pre_start_date_month_value = $this->wp_datetime_converter_get_date_from_gmt("Ym", $pre_start_dateTime);
				else:
					$pre_start_date_month_value = '';
				endif;
				$month_value = $this->wp_datetime_converter_get_date_from_gmt("F", $dateTime);
				
				$translate_month_values = $this->convert_language_of_month_name();

				$out_atts = array(
					'start_date_num'	=> $start_date_num,
					'start_date_value'	=> $start_date_value,
					'end_date_num'		=> $end_date_num,
					'end_date_value'	=> $end_date_value,
					'today_date_num'	=> $today_date_num,
					'holding_flag'		=> $holding_flag,
					'gc_link'			=> $gc_link,
					'gc_title'			=> $gc_title, 
					'gc_description'	=> $gc_description, 
					'gc_location'		=> $gc_location, 
					'plugin_name'		=> $plugin_name,
					'html_tag_class'	=> $html_tag_class,
					'html_tag_class_c'	=> $html_tag_class_c,
					'id'				=> $id,
					'lang'				=> $lang,
					'hash_tags'			=> $hash_tags,
					'hash_tags_type_title'	=> $hash_tags_type_title,
					'hash_tags_organizer_value'	=> $hash_tags_organizer_value,
					'output_category_temp'	=> $output_category_temp,
					'hash_tags_display_value'	=> $hash_tags_display_value, 
					'element_count' => $element_count,
					'gc_description_title' => '',
					'view_location_name'=>$view_location_name,
					'no_event_link'=>$no_event_link,
					'start_date_month_value'=> $start_date_month_value,
					'pre_start_date_month_value'=> $pre_start_date_month_value,
					'month_value'	=> $month_value,
					'translate_month_values' => $translate_month_values,
					'view_end_date'=>$view_end_date,
				);
				// When  $hash_tags_display_value = "none" or "off"  (#display none or #display off   in Description of Google Calendar Event), the event isn't displayed.
				if($hash_tags_display_value === "none" || $hash_tags_display_value === "off"):
					continue;
				endif;

				// For title attribution
				$gc_description_title = "";
				if( isset($gc_description) && !empty($gc_description) ): 
					// &#13;&#10;  is the HTML-encodeing CR+LF (line feed).
					$gc_description_title = str_replace(array("\r\n", "\r", "\n"), "<br />", $gc_description);
					$gc_description_title = str_replace(
						array("<br/>","<br />", "<br>", "<p>", "</p>"),
						 '&#13;&#10;', $gc_description_title);
					$gc_description_title = strip_tags($gc_description_title);
					$gc_description_title = str_replace('&#13;&#10;&#13;&#10;&#13;&#10;', '&#13;&#10;', $gc_description_title);
					// Limit the output to the title attribute to 1024 bytes.
					if( function_exists("mb_strcut") ):
						$gc_description_title = mb_strcut($gc_description_title, 0, 1024);
					else:
						$gc_description_title = substr($gc_description_title, 0, 1024);
					endif;	
				endif;
				$out_atts['gc_description_title'] = $gc_description_title;
				$out_temp = '';
				$filter_out_temp = '';
				$start_end_date_value = $start_date_value;
				if(!empty($view_end_date) && !empty($end_date_value)):
					if( $start_date_num !== $end_date_num ):
						$start_end_date_value .=  ' ' . $view_end_date . ' ' . $end_date_value;
					else:
						$split_time = explode(" ",$end_date_value);
						array_shift($split_time);
						if(!empty($split_time)):
							$start_end_date_value .=  ' ' . $view_end_date;
							foreach($split_time as $st):
								if($st != $start_date_num):
									$start_end_date_value .=  ' ' . $st;
								endif;
							endforeach;
						endif;
					endif;
				endif;
				if(!empty($html_tag) && file_exists (dirname( __FILE__ ) . '/library/tags/' . $html_tag . '.php')):
					include(dirname( __FILE__ ) . '/library/tags/' . $html_tag . '.php');
				endif;
				if(!empty($hook_secret_key)): 
					// $gc_value components is referred in https://developers.google.com/calendar/v3/reference/events#resource.
					$out_t = apply_filters( 'lvgc_each_output_data', $out_temp, $out_atts, $gc_value);
					if(isset($out_t['hook_secret_key']) && $hook_secret_key === $out_t['hook_secret_key']):
						$filter_out_temp = wp_kses_post($out_t['data']);
						if(!empty($filter_out_temp)):
							$element_count++;
							$out .= $filter_out_temp;
						endif;
					else:
						$out .= $out_temp;
					endif;
				else:
					$out .= $out_temp;
				endif;
				$pre_start_date_value = $start_date_value;
				$pre_start_dateTime = $dateTime;
	  		endforeach;
		endif;

		if(empty($out)):
			$out = __($settings['google_calendar']['noEventMessage'], $this->plugin_name);
		endif;

		if ( preg_match('/-month$/', $html_tag) ):
			$out .= '</ul>';
		endif;
		
		return $out;
	}
	
	// Remove all tag except "description" on Google Calendar
	public function security_check_array($array){
		static $exception = "";
		if (empty($array)) return $array;
		if(is_array($array)):
				foreach($array as $k => $v):
					if($k === "description") $exception = "description";
					else $exception = "";
					$array[$k] = $this->security_check_array($v);
				endforeach;
		else:
			if($exception === "description")
				$array = wp_kses_post($array); 
			else
				$array = esc_html(wp_strip_all_tags($array)); 
		endif;
		return $array;
	}
	public function get_google_calendar_contents($atts){
		if($atts) extract($atts = $this->security_check_array($atts));

		// Getting the settings from the setting menu.
		$settings = get_option($this->set_op);

		$gc = array();
		$gc['api-url'] = $this->google_calendar['api-url'];
		if(isset($settings['google_calendar']))
			$gc = $settings['google_calendar'];
		// Priority of the attribution value in the shortcode.
		if(isset($start_date) && !empty($start_date)):
			$gc['start-date'] = wp_strip_all_tags($start_date);
		else:
			$gc['start-date'] = '';
		endif;
		if(isset($end_date) && !empty($end_date)):
			$gc['end-date'] = wp_strip_all_tags($end_date);
		else:
			$gc['end-date'] = '';
		endif;
		if(isset($orderbysort) && !empty($orderbysort)):
			$gc['orderbysort'] = wp_strip_all_tags($orderbysort);
		else:
			if(isset($gc['orderbysort']) && !empty($gc['orderbysort'])):
			else:
				$gc['orderbysort'] = $this->google_calendar['orderbysort'];
			endif;
		endif;
		if(isset($g_api_key) && !empty($g_api_key)) $gc['api-key'] = wp_strip_all_tags($g_api_key);
		if(isset($g_id) && !empty($g_id)) $gc['id'] = wp_strip_all_tags($g_id);
		if(isset($max_view) && !empty($max_view)):
			if((int)$max_view > 0 && (int)($max_view) <= 2500):
			 $gc['maxResults'] = (int)wp_strip_all_tags($max_view);
			else:
			 $gc['maxResults'] = $this->default_maxResults;
			endif;
		endif;
		if(isset($lang) && !empty($lang)) $gc['lang'] = wp_strip_all_tags($lang);

		// Additional Calendars (g_id_*** and g_api_key_*)
		$g_urls = array(); 
		foreach($atts as $key=>$value):
			$matches = array();
			if(preg_match('/^(g_id_)(.+)$/', $key, $matches)):
				if(isset($matches[2]) && !empty($matches[2])):
					if(isset($atts['g_api_key_' . $matches[2]]) && !empty($atts['g_api_key_' . $matches[2]])):
						$g_urls[$key] = esc_url($gc['api-url']) . wp_strip_all_tags($value) . '/events?key=' . wp_strip_all_tags($atts['g_api_key_' . $matches[2]]) . '&singleEvents=true';
					else:
						$g_urls[$key] = esc_url($gc['api-url']) . wp_strip_all_tags($value) . '/events?key=' . wp_strip_all_tags($gc['api-key']) . '&singleEvents=true';
					endif;
				endif;
			endif;
		endforeach;

		$g_url = esc_url($gc['api-url']) . wp_strip_all_tags($gc['id']) . '/events?key=' . wp_strip_all_tags($gc['api-key']) . '&singleEvents=true';

		$today_date = $this->wp_datetime_converter_current_time("c");
		$today_start_date = $this->wp_datetime_converter_setDayTime("c", "today", "start");
		$today_end_date = $this->wp_datetime_converter_setDayTime("c", "today", "end");

		$params = array();
		$params[] = 'orderBy=' . wp_strip_all_tags($this->google_calendar['orderby']);
		$params[] = 'maxResults=' . (int)(isset($gc['maxResults']) ? wp_strip_all_tags($gc['maxResults']) : $this->default_maxResults);
		/* No limitation : Start Date = all, End Date = empty/all
		 * Start Date = now : Start Date = empty
		 * Start Date = value : Start Date != empty/all
		 * End Date = value : End Date != empty/all
		*/
		if(!empty($gc['start-date'])):
			if(strtolower($gc['start-date']) != "all"):
				if(strtolower($gc['start-date']) === "now"):
					$params[] = 'timeMin='.urlencode($today_start_date);
				else:
					$params[] = 'timeMin='.urlencode($this->wp_datetime_converter_setDayTime("c", $gc['start-date']));
				endif;
			endif;
		else:
			$params[] = 'timeMin='.urlencode($today_start_date);
		endif;
		if(!empty($gc['end-date'])):
			if(strtolower($gc['end-date']) != "all"):
				if(strtolower($gc['end-date']) == "now"):
					$params[] = 'timeMax='.urlencode($today_end_date);
				else:
					$params[] = 'timeMax='.urlencode($this->wp_datetime_converter_setDayTime("c", $gc['end-date']));
				endif;
			endif;
		endif;

		$urls = array();
		if(!empty($g_urls)):
			foreach($g_urls as $key=>$value):
				$urls[$key] = $value .'&'.implode('&', $params);
			endforeach;
		endif;
		$url = $g_url .'&'.implode('&', $params);
 
		// Fixed the warning : Ref. https://qiita.com/kawaguchi_011/items/29cc3811b2bc2ce2d85e
		$fgc_context = stream_context_create(array(
			 'http' => array('ignore_errors' => true),
		));
//		var_dump($url);
		$urls_json = array();
		$urls_results = array();
		foreach($urls as $key=>$value):
//			$urls_results = file_get_contents($value, false, $fgc_context);
			$urls_results = k_getAPIDataCurl($value);
			$urls_json[$key] = $urls_results ? json_decode($urls_results, true) : '';
		endforeach;
		$results  = array();
		if(isset($gc['id']) && isset($gc['api-key'])):
			$results = k_getAPIDataCurl($url);
//			$results = file_get_contents($url, false, $fgc_context);
		endif;
		$json = $results ? json_decode($results, true) : '';
		// Merge Events of Multi Galendar
		if($urls_json && isset($json['items'])):
			foreach($urls_json as $key=>$value):
				if(isset($value['items'])):
					foreach((array)$value['items'] as $s_key=>$s_value):
						array_push($json['items'], $s_value);
					endforeach;
				endif;
			endforeach;
		endif;

		// Instead of ordersort (like Google Calendar API v2)
		// Sorting at every time for Multi Calendar 
		if(isset($json['items']) && !empty($json['items'])):
			$s_date = array(); 
			foreach($json['items'] as $key=>$item):
				if($this->google_calendar['orderby'] === strtolower('updated')):
					$s_date[] = $item['updated'];
					$json['items'][$key][$this->google_calendar['orderby']] = $item['updated'];
				else:
					if(isset($item['start']['dateTime'])):
						$s_date[] = $item['start']['dateTime'];
						$json['items'][$key][$this->google_calendar['orderby']] = $item['start']['dateTime'];
					else:
						$s_date[] = $item['start']['date'];
						$json['items'][$key][$this->google_calendar['orderby']] = $item['start']['date'];
					endif;
				endif;
			endforeach; 
			if(strtolower($gc['orderbysort']) !== "descending"):
				array_multisort($s_date, SORT_ASC,$json['items']);
			else:
				array_multisort($s_date, SORT_DESC,$json['items']);
			endif;
		endif;
		
		/* Pick up $max_display array from the head of $json (data).
		*/
		if(isset($json['items'])):
			if(!empty($max_display) && $max_display > 0):
				$json['items'] = array_slice($json['items'], 0, (int)$max_display);
			elseif(isset($gc['maxResults']) && !empty($gc['maxResults'])):
				$json['items'] = array_slice($json['items'], 0, (int)$gc['maxResults']);
			endif;
		endif;
		return $json;
	}
	public function add_to_settings_menu(){
		add_options_page(sprintf(__('%s Settings', $this->plugin_title), $this->plugin_title), sprintf(__('%s Settings', $this->plugin_title), $this->plugin_title), 'manage_options', __FILE__,array(&$this,'admin_settings_page'));
	}

	// Processing Setting menu for the plugin.
	public function admin_settings_page(){
		$settings = get_option($this->set_op);

		if(isset($settings['google_calendar']) && is_array($settings['google_calendar'])):
			$this->google_calendar = $settings['google_calendar'];
		endif;
		if(!isset($this->google_calendar['noEventMessage'])):
			$this->google_calendar['noEventMessage'] = $this->default_noEventMessage;
		endif;
		if(!isset($this->google_calendar['fix-timezone-offset'])):
			$this->google_calendar['fix-timezone-offset'] = $this->default_fix_timezone_offset;
		endif;
		$google_calendar_flag = false;

		if(isset($_POST["gclv-form"]) && $_POST["gclv-form"]):
			if(check_admin_referer("gclv-nonce-key", "gclv-form")):
				// GET setting data in Settings.
				if(isset($_POST['google-calendar-api-key'])):
					$this->google_calendar['api-key'] =  wp_strip_all_tags($_POST['google-calendar-api-key']);
					$google_calendar_flag = true;
				endif;
				if(isset($_POST['google-calendar-id'])):
					$this->google_calendar['id'] =  wp_strip_all_tags($_POST['google-calendar-id']);
					$google_calendar_flag = true;
				endif;
				if(isset($_POST['google-calendar-start-date'])):
					$this->google_calendar['start-date'] =  wp_strip_all_tags($_POST['google-calendar-start-date']);
					$google_calendar_flag = true;
				endif;
				if(isset($_POST['google-calendar-end-date'])):
					$this->google_calendar['end-date'] =  wp_strip_all_tags($_POST['google-calendar-end-date']);
					$google_calendar_flag = true;
				endif;
				if(isset($_POST['google-calendar-maxResults'])): 
					// maxResults 
					if((int)$_POST['google-calendar-maxResults'] > 0 && (int)($_POST['google-calendar-maxResults'] <= 2500)):
						$this->google_calendar['maxResults'] = (int) wp_strip_all_tags($_POST['google-calendar-maxResults']);
					else:
						$this->google_calendar['maxResults'] = $this->default_maxResults;
					endif;
					$google_calendar_flag = true;
				endif;
				if(isset($_POST['google-calendar-orderbysort'])):
					$this->google_calendar['orderbysort'] =  wp_strip_all_tags($_POST['google-calendar-orderbysort']);
					$google_calendar_flag = true;
				endif;
				if(isset($_POST['google-calendar-html_tag'])):
					$this->google_calendar['html_tag'] =  wp_strip_all_tags($_POST['google-calendar-html_tag'] ? $_POST['google-calendar-html_tag'] : $this->default_html_tag);
					$google_calendar_flag = true;
				endif;
				if(isset($_POST['google-calendar-no-event-message'])):
					$this->google_calendar['noEventMessage'] =  wp_strip_all_tags($_POST['google-calendar-no-event-message'] ? $_POST['google-calendar-no-event-message'] : $this->default_noEventMessage);
					$google_calendar_flag = true;
				endif;
				if(isset($_POST['google-calendar-fix-timezone-offset'])):
					$this->google_calendar['fix-timezone-offset'] =  wp_strip_all_tags($_POST['google-calendar-fix-timezone-offset'] ? $_POST['google-calendar-fix-timezone-offset'] : $this->default_fix_timezone_offset);
					$google_calendar_flag = true;
				endif;
			endif;
		endif;

		$settings['google_calendar'] = $this->google_calendar;

		if($google_calendar_flag):
			update_option($this->set_op , $settings);
		endif;
?>
<div id="add_mime_media_admin_menu">
  <h2><?php _e($this->plugin_title . ' Settings', $this->plugin_name); ?></h2>
  
  <form method="post" action="">
	<?php // for CSRF (Cross-Site Request Forgery): https://propansystem.net/blog/2018/02/20/post-6279/
		wp_nonce_field("gclv-nonce-key", "gclv-form"); ?>
     <fieldset style="border:1px solid #777777; width: 800px; padding-left: 6px;">
       <legend><h3><?php _e('How to use it.', $this->plugin_name); ?></h3></legend>
       <div style="overflow:noscroll; height: 70px;">
         <p><?php _e('Shortcode: ', $this->plugin_name); ?><strong><?php print '[' . $this->plugin_shortcode .']'; ?></strong> <?php _e('(Put the shortcode on a post or page.)', $this->plugin_name); ?></p>
         <p><?php _e('The handling manual in detail is <a href="https://info.cseas.kyoto-u.ac.jp/en/links-en/plugin-en/wordpress-dev-info-en/google-calendar-list-view" target="_blank">here</a>.', $this->plugin_name); ?>
       </div>
     </fieldset>
     <br/>
     <fieldset style="border:1px solid #777777; width: 800px; padding-left: 6px;">
        <legend><h3><?php _e('Google Calendar API Settings', $this->plugin_name); ?></h3></legend>
        <div style="overflow:noscroll; height: 550px;">
         <br/>
         <table>
            <tr><td><strong>1. <?php _e('Google Calendar API Key: ', $this->plugin_name); ?></strong></td><td><input name="google-calendar-api-key" type="text" value="<?php print esc_attr($this->google_calendar['api-key']);?>" size="60" maxlength="100"/> </td></tr>
            <tr><td><strong>2. <?php _e('Google Calendar ID: ', $this->plugin_name); ?></strong></td><td><input name="google-calendar-id" type="text" value="<?php print esc_attr($this->google_calendar['id']);?>" size="60" maxlength="100"/></td></tr>
            <tr><td><strong>3. <?php _e('Start Date (YYYY-MM-DD/ALL): ', $this->plugin_name); ?></strong></td><td><input name="google-calendar-start-date" type="text" value="<?php print esc_attr($this->google_calendar['start-date']);?>" size="30" maxlength="100"/> <?php _e('(<a href="https://www.php.net/manual/en/datetime.format.php" target="_blank">datetime</a> date format is supported.)', $this->plugin_name); ?></td></tr>
            <tr><td><strong>4. <?php _e('End Date (YYYY-MM-DD): ', $this->plugin_name); ?></strong></td><td><input name="google-calendar-end-date" type="text" value="<?php print esc_attr($this->google_calendar['end-date']);?>" size="30" maxlength="100"/> <?php _e('(<a href="https://www.php.net/manual/en/datetime.format.php" target="_blank">datetime</a> date format is supported.)', $this->plugin_name); ?></td></tr>
            <tr><td><strong>5. <?php _e('maxResults (Default value is 10): ', $this->plugin_name); ?></strong></td><td><input name="google-calendar-maxResults" type="text" value="<?php print esc_attr($this->google_calendar['maxResults'] ? $this->google_calendar['maxResults'] : $this->default_maxResults);?>" size="30" maxlength="100"/> <?php _e('(0 > maxResults <= 2500 | <a href="https://developers.google.com/google-apps/calendar/v3/reference/events/list" target="_blank">Events: list</a>)', $this->plugin_name); ?></td></tr>
            <tr><td colspan="2">
             <ol>
               <li><?php _e('Get Google Calendar API Key from <a href="https://console.developers.google.com/" target="_blank">Google Developer Console</a> (Reference: <a href="https://docs.simplecalendar.io/google-api-key/?utm_source=inside-plugin&utm_medium=link&utm_campaign=core-plugin&utm_content=settings-link" target="_blank">Creating Google API Key</a> by Simple Calendar Documentation)', $this->plugin_name); ?></li>
               <li><?php _e('Get Google Calendar ID from a public Google Calendar setting (Reference: <a href="https://docs.simplecalendar.io/find-google-calendar-id/" target="_blank">Finding Your Google Calendar ID</a> by Simple Calendar Documentation', $this->plugin_name); ?></li>
               <li><?php _e('If "Start Date" or "End Date" are setting up, get Google Calendar events from "Start Date" to "End Date".', $this->plugin_name); ?> <?php _e('Default value is empty (start_date value = current date).', $this->plugin_name); ?></li>
               <li><?php _e('"Start Date" and "End Date" can use the value of "now" and "ALL". "now" means current date. "ALL" means unlimited.', $this->plugin_name); ?> <?php _e('"Start Date" and "End Date" can use <a href="https://www.php.net/manual/en/datetime.format.php" target="_blank">datetime</a> data format. "-2 days" means 2 days ago from current time. "+1 days" means 1 day later from current time. In detail, please see <a href="https://www.php.net/manual/en/datetime.format.php" target="_blank">datetime</a> help.', $this->plugin_name); ?></li>
               <li><?php _e('maxResults is maximum number of events returned on one result page. If multiple calendars are specified, this plugin gets maxResults number of events from each calendars and sort by order into them. And then, it picks up maxResults number of latest events from sorted events. (ex. maxResults = 10, Calendar A/B. It gets total 20 events from Calendar A (10 events) and Calendar B(10 events) and 20 events are sorted by order, and then picked up latest 10 events).', $this->plugin_name); ?></li>
             </ol>
            </td></tr>
         </table>
         <input type="submit" value="<?php _e('Save', $this->plugin_name);  ?>" />
         <br/>
        </div>
     </fieldset>
     <br/>
     <fieldset style="border:1px solid #777777; width: 800px; padding-left: 6px;">
        <legend><h3><?php _e('General Settings', $this->plugin_name); ?></h3></legend>
        <div style="overflow:noscroll; height: 450px;">
         <br/>
         <table>
           <tr><td><strong>1. <?php _e('Order by Sort: ', $this->plugin_name); ?></strong></td><td><input name="google-calendar-orderbysort" type="radio" value="ascending" <?php if(strtolower($this->google_calendar['orderbysort']) !== 'descending') print 'checked';?>/>Ascending <input name="google-calendar-orderbysort" type="radio" value="descending" <?php if(strtolower($this->google_calendar['orderbysort']) === 'descending') print 'checked';?>/>Descending</td></tr>
           <tr><td><strong>2. <?php _e('HTML tag: ', $this->plugin_name); ?></strong></td><td>
           <?php foreach($this->html_tags as $html_tag): ?>
           <input name="google-calendar-html_tag" type="radio" value="<?php print esc_attr($html_tag); ?>" <?php if($this->google_calendar['html_tag'] === $html_tag) print 'checked'; elseif(empty($this->google_calendar['html_tag']) && $this->default_html_tag === $html_tag) print 'checked'; ?>/><?php print esc_html('<' . $html_tag . '>');?> 
           <?php endforeach; ?></td></tr>
           <tr><td><strong>3. <?php _e('No Event Message: ', $this->plugin_name); ?></strong></td><td><input name="google-calendar-no-event-message" type="text" value="<?php print  esc_attr($this->google_calendar['noEventMessage']);?>" size="60" maxlength="100"/></td></tr>
           <tr><td><strong>4. <?php _e('Fix Timezone Offset: ', $this->plugin_name); ?></strong></td><td><input name="google-calendar-fix-timezone-offset" type="text" value="<?php print  esc_attr($this->google_calendar['fix-timezone-offset']);?>" size="60" maxlength="100"/ placeholder="ex. +3 hours +10 minutes -30 seconds(default is empty value)"></td></tr>
           <tr><td colspan="2">
              <ol>
               <li><?php _e('Order by Sort behaves like "ordersort" by Google Calendar API v2.', $this->plugin_name); ?></li>
               <li><?php printf(__('HTML tag is used by the output Google Calendar events. The tag class is "%s".', $this->plugin_name), $this->plugin_name); ?></li>
               <li><?php _e('Change the message when there are no events. If the value is empty, "'. $this->default_noEventMessage .'" is set. Else if the value is "none", the message is hidden. If "no_event_message" shortcode option is set, the message is overwritten by the shortcode message', $this->plugin_name); ?></li>
               <li><?php _e('If you cannot solve the timezone issue, you can manually shift the hours, minutes, and seconds by setting the value of "Fix Timezone Offset".', $this->plugin_name); ?>
               <?php _e('<br/>For example<br/>
<ul>
<li>+3 hours = 3 hours later</li>
<li>+10 minutes = 10 minutes later</li>
<li>-30 seconds = 30 seconds before</li>
</ul>
Multiple settings can be made by separating them with spaces. hours/minutes/seconds can be singular or plural. In detail, please see <a href="https://www.php.net/manual/en/datetime.modify.php" target="_blank">date_modity</a>.', $this->plugin_name); ?></li>
              </ol>
           </td></tr>
         </table>
         <input type="submit" value="<?php _e('Save', $this->plugin_name);  ?>" />
         <br/>
        </div>
     </fieldset>
     <br/>
     <fieldset style="border:1px solid #777777; width: 800px; padding-left: 6px;">
        <legend><h3><?php _e('Feature Expansion &amp; Other notice', $this->plugin_name); ?></h3></legend>
        <div style="overflow:noscroll; height: 200px;">
         <br/>
         <?php _e('The plugin is the following hooks', $this->plugin_name); ?>
         <ol>
           <li><strong>lvgc_each_output_data</strong> <?php _e('can handled each output data.', $this->plugin_name); ?></li>
         </ol>

         <ul>
           <li><?php _e('If you use above hooks, you must set "hook_secret_key" option in the shortcode. And you need to return "hook_secrey_key" value in the hook. Please see the <a href="https://info.cseas.kyoto-u.ac.jp/en/links-en/plugin-en/wordpress-dev-info-en/google-calendar-list-view" target="_blank">document</a>.', $this->plugin_name); ?></li>
           <li><?php printf(__('If you emphasize a holding event, set class="%s" in the html tag.', $this->plugin_name), $this->plugin_name.'_holding'); ?></li>
           <li><?php _e('If you want to customize the value using a hook each a shortcode, id can use a unique key.', $this->plugin_name); ?></li>
         </ul>

        </div>
     </fieldset>
      
   </form>
<?php 
		if($google_calendar_flag):
?>
<div class="<?php print $this->plugin_name;?>_updated"><p><strong><?php _e('Updated', $this->plugin_name); ?></strong></p></div>

<?php 
		endif;
	} // close admin_settings_page function
} // close class

$wm = new gclv();