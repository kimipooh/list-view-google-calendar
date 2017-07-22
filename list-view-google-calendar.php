<?php
/*
Plugin Name: Google Calendar List View
Plugin URI: 
Description: The plugin is to create a shortcode for displaying the list view of a public Google Calendar.
Version: 2.11
Author: Kimiya Kitani
Author URI: https://profiles.wordpress.org/kimipooh/
Text Domain: list-view-google-calendar
Domain Path: /lang
*/

$wm = new gclv();

class gclv{
	var $set_op = 'list-view-google-calendar_array';	// Save setting name in DB
	var $plugin_name = 'list-view-google-calendar';
	var $plugin_title = 'Google Calendar List View';
	var $plugin_shortcode = 'gc_list_view';
	var $default_maxResults = 10;  
	var $html_tags = array('li'=>'li', 'p'=>'p', 'dd'=>'dd'); 
	var $default_html_tag = 'li'; 
	var $google_calendar = array( 
		'api-key'		=> '',
		'id'			=> '',
		'api-url'		=> 'https://www.googleapis.com/calendar/v3/calendars/',
		'start-date'	=> '',					// Default events are from today to the future.
		'end-date'		=> '',
		'orderby'		=> 'startTime',			// startTime, updated (only ascending).
		'orderbysort'	=> 'ascending',			// ascending or descending.
		'maxResults'	=> '',  // <= 2500 (https://developers.google.com/google-apps/calendar/v3/reference/events/list)
		'html-tag'		=> '',
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
		load_plugin_textdomain($this->plugin_name, false, dirname( plugin_basename( __FILE__ ) ) . '/' . $this->lang_dir . '/');
	}
	public function init_settings(){
		$this->settings['version'] = 211;
		$this->settings['db_version'] = 100;
	}
	public function installer(){
		update_option($this->set_op , $this->settings);
	}
	public function uninstaller(){
		// Remove Save data.
		delete_option($this->set_op);
	}
	public function shortcodes($atts){
		$atts = $this->security_check_array($atts);
		extract($atts = shortcode_atts(array(
			'id'			=> '',
    	    'start_date' 	=> '',
    	    'end_date'		=> '',
    	    'date_format'	=> 'Y.m.d', 
			'orderbysort'	=> '',			// ascending or descending.
			'g_api_key'		=> '',			// Google Calendar API KEY
			'g_id'			=> '',			// Google Calendar ID
			'max_view'		=> '',			// Maximum number of view
			'html_tag'		=> '',
			'html_tag_class'	=> $this->plugin_name,		// adding a class to html tag (default: $this->plugin_name) 
			'html_tag_date_class'	=> $this->plugin_name . "_date",		// setting up a class to date in html tag
			'html_tag_title_class'	=> $this->plugin_name . "_title",	// setting up a class to title in html tag
			'hook_secret_key' => '',  // If you use a hook, please set the secret key because of preventing an overwrite from any other plugins.
			'lang'			=> '', // List only specific languages. #lang [value] on the comment of Google Calendar. version 2.1 
		), $atts));
		$settings = get_option($this->set_op);
		$gc_data = $this->get_google_calendar_contents($atts);
		// get lang data.
		$gc_data = $this->get_select_lang_data($gc_data, $atts);
 		// Security check for the hook (clean up ALL html tag).
 		$gc_data = $this->security_check_array($gc_data);

 		if(isset($html_tag) && !empty($html_tag)): 
 			$settings['google_calendar']['html-tag'] = wp_strip_all_tags($html_tag);
 			if(!isset($this->html_tags[$settings['google_calendar']['html-tag']])) $settings['google_calendar']['html-tag'] = $this->html_tags[$default_html_tag];
 		endif;
 		$atts['html_tag'] = $settings['google_calendar']['html-tag'] ? $settings['google_calendar']['html-tag'] : $this->default_html_tag;
		$html_tag = $atts['html_tag'];
		
		// Remove security reason.
 		//$gc_data = apply_filters('lvgc_gc_data', $gc_data, $atts);
		$out = ''; 
		$match = array();
		if($gc_data['items']): 
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
	 			
	 			$start_date_num = get_date_from_gmt($dateTime, "Ymd");
	 			$start_date_value = get_date_from_gmt($dateTime, $date_format);
	 			$end_date_num = get_date_from_gmt($end_dateTime, "Ymd");
	 			$today_date_num = current_time("Ymd");
	 			$holding_flag = false;
	 			if($today_date_num >= $start_date_num && $today_date_num <= $end_date_num) $holding_flag = true;
	 			$gc_link = esc_url($gc_value['htmlLink']);
	 			$gc_title = esc_html($gc_value['summary']);
				$plugin_name = $this->plugin_name;
				$html_tag_class_c = $holding_flag ? $html_tag_class . '_holding' : $html_tag_class; 

	 			// for a hook "lvgc_output_data".
	 			$out_atts = array(
	 				'start_date_num'	=> $start_date_num,
	 				'start_date_value'	=> $start_date_value,
	 				'end_date_num'		=> $end_date_num,
	 				'today_date_num'	=> $today_date_num,
	 				'holding_flag'		=> $holding_flag,
	 				'gc_link'			=> $gc_link,
	 				'gc_title'			=> $gc_title, 
	 				'plugin_name'		=> $plugin_name,
	 				'html_tag_class_c'	=> $html_tag_class_c,
	 				'id'				=> $id,
	 				'lang'				=> $lang,
	 			);

				$out_temp = '';
				if(!empty($html_tag) && file_exists (dirname( __FILE__ ) . '/library/tags/' . $html_tag . '.php')):
					include(dirname( __FILE__ ) . '/library/tags/' . $html_tag . '.php');
				else:
					$out_temp = <<< ___EOF___
 <li class='$html_tag_class_c'>$start_date_value <a href='$gc_link'>$gc_title</a></li>
___EOF___;
				endif;
				if(!empty($hook_secret_key)):
					$out_t = wp_kses_post(apply_filters( 'lvgc_each_output_data', $out_temp, $out_atts ));
					if(isset($out_t['hook_secret_key']) && $hook_secret_key === $out_t['hook_secret_key']):
						$out .= $out_t['data'];
					else:
						$out .= $out_temp;
					endif;
				else:
					$out .= $out_temp;
					
				endif;
	  		endforeach;
		endif;

		return $out;
	}
	public function get_select_lang_data($gc_data, $atts){
		if (empty($gc_data)) return $gc_data;
		if($atts) extract($atts);

		if($gc_data['items']): 
			foreach($gc_data['items'] as $gc_key=>$gc_value):
				if(!empty($lang)):
					if(isset($gc_value['description'])):
						if(preg_match('/#lang(\s+)(\w+)/', $gc_value['description'], $match)):
							if(isset($match[2]) && $match[2] !== $lang):
								unset($gc_data['items'][$gc_key]); 
								continue;
							endif;
						else:
								unset($gc_data['items'][$gc_key]); 
								continue;							 
						endif;
					endif;
				endif; 
			endforeach;
		endif;
		return $gc_data;
	}
	public function security_check_array($array){
		if (empty($array)) return $array;
		if(is_array($array)):
				foreach($array as $k => $v):
					$array[$k] = $this->security_check_array($v);
				endforeach;
		else:
			$array = esc_html(wp_strip_all_tags($array)); 
		endif;
		
		return $array;
	}
	public function get_google_calendar_contents($atts){
		if($atts) extract($atts);
		$settings = get_option($this->set_op);
		$gc = array();
		if(isset($settings['google_calendar']))
			$gc = $settings['google_calendar'];

		// Priority of the attribution value in the shortcode.
 		if(isset($start_date) && !empty($start_date)) $gc['start-date'] = wp_strip_all_tags($start_date);
 		if(isset($end_date) && !empty($end_date)) $gc['end-date'] = wp_strip_all_tags($end_date);
 		if(isset($orderbysort) && !empty($orderbysort)) $gc['orderbysort'] = wp_strip_all_tags($orderbysort);
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

 		$g_url = esc_url($gc['api-url']) . wp_strip_all_tags($gc['id']) . '/events?key=' . wp_strip_all_tags($gc['api-key']) . '&singleEvents=true';

		$params = array();
		$params[] = 'orderBy=' . wp_strip_all_tags($this->google_calendar['orderby']);
		$params[] = 'maxResults=' . (int)(isset($gc['maxResults']) ? wp_strip_all_tags($gc['maxResults']) : $this->default_maxResults);
		if(!empty($gc['start-date'])):
			if(strtolower($gc['start-date']) !== 'all'):
				$params[] = 'timeMin='.urlencode(get_date_from_gmt(strtotime($gc['start-date']), 'c'));
			endif;
		else:
			$params[] = 'timeMin='.urlencode(date_i18n('c'));			
		endif;
		if(!empty($gc['end-date']))
			$params[] = 'timeMax='.urlencode(get_date_from_gmt(strtotime($gc['end-date']), 'c'));

		$url = $g_url .'&'.implode('&', $params);

		$results = file_get_contents($url);
 
 		$json = $results ? json_decode($results, true) : '';
 		
 		// Instead of odersort (like Google Calendar API v2)
 		if(strtolower($gc['orderbysort']) === 'descending'):
	 		if($json['items']):
 		 		$s_date = array(); 
 	 			foreach($json['items'] as $item):
 	 				if($this->google_calendar['orderby'] === strtolower('updated')):
	 		 			$s_date[] = $item['updated'];
	 		 		else:
	 		 			if(isset($item['start']['dateTime'])):
		 		 			$s_date[] = $item['start']['dateTime'];
		 		 		else:
		 		 			$s_date[] = $item['start']['date'];
						endif;
	 		 		endif;
 	 			endforeach; 
 	 			array_multisort($s_date, SORT_DESC, $json['items']);
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

		$google_calendar_flag = false;
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
		if(isset($_POST['google-calendar-html-tag'])):
			$this->google_calendar['html-tag'] =  wp_strip_all_tags($_POST['google-calendar-html-tag'] ? $_POST['google-calendar-html-tag'] : $this->default_html_tag);
			$google_calendar_flag = true;
		endif;		

		$settings['google_calendar'] = $this->google_calendar;

		if($google_calendar_flag):
			update_option($this->set_op , $settings);
		endif;
?>
<div id="add_mime_media_admin_menu">
  <h2><?php _e($this->plugin_title . ' Settings', $this->plugin_name); ?></h2>
  
  <form method="post" action="">
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
		<div style="overflow:noscroll; height: 400px;">
		<br/>
		<table>
			<tr><td><strong>1. <?php _e('Google Calendar API Key: ', $this->plugin_name); ?></strong></td><td><input name="google-calendar-api-key" type="text" value="<?php print esc_attr($this->google_calendar['api-key']);?>" size="60" maxlength="100"/> </td></tr>
			<tr><td><strong>2. <?php _e('Google Calendar ID: ', $this->plugin_name); ?></strong></td><td><input name="google-calendar-id" type="text" value="<?php print esc_attr($this->google_calendar['id']);?>" size="60" maxlength="100"/></td></tr>
			<tr><td><strong>3. <?php _e('Start Date (YYYY-MM-DD/ALL): ', $this->plugin_name); ?></strong></td><td><input name="google-calendar-start-date" type="text" value="<?php print esc_attr($this->google_calendar['start-date']);?>" size="30" maxlength="100"/> <?php _e('(<a href="http://php.net/manual/en/function.strtotime.php" target="_blank">strtotime</a> date format is supported.)', $this->plugin_name); ?></td></tr>
			<tr><td><strong>4. <?php _e('End Date (YYYY-MM-DD): ', $this->plugin_name); ?></strong></td><td><input name="google-calendar-end-date" type="text" value="<?php print esc_attr($this->google_calendar['end-date']);?>" size="30" maxlength="100"/> <?php _e('(<a href="http://php.net/manual/en/function.strtotime.php" target="_blank">strtotime</a> date format is supported.)', $this->plugin_name); ?></td></tr>
			<tr><td><strong>5. <?php _e('maxResults (Default value is 10): ', $this->plugin_name); ?></strong></td><td><input name="google-calendar-maxResults" type="text" value="<?php print esc_attr($this->google_calendar['maxResults'] ? $this->google_calendar['maxResults'] : $this->default_maxResults);?>" size="30" maxlength="100"/> <?php _e('(0 > maxResults <= 2500 | <a href="https://developers.google.com/google-apps/calendar/v3/reference/events/list" target="_blank">Events: list</a>)', $this->plugin_name); ?></td></tr>
			<tr><td colspan="2">
				<ol>
					<li><?php _e('Get Google Calendar API Key from <a href="https://console.developers.google.com/" target="_blank">Google Developer Console</a> (Reference: <a href="https://docs.simplecalendar.io/google-api-key/?utm_source=inside-plugin&utm_medium=link&utm_campaign=core-plugin&utm_content=settings-link" target="_blank">Creating Google API Key</a> by Simple Calendar Documentation)', $this->plugin_name); ?></li>
					<li><?php _e('Get Google Calendar ID from a public Google Calendar setting (Reference: <a href="https://docs.simplecalendar.io/find-google-calendar-id/" target="_blank">Finding Your Google Calendar ID</a> by Simple Calendar Documentation', $this->plugin_name); ?></li>
					<li><?php _e('If "Start Date" or "End Date" are setting up, get Google Calendar events from "Start Date" to "End Date".', $this->plugin_name); ?> <?php _e('Default value is empty (=current date). If "ALL" value is setting up, start_date value is unlimited.', $this->plugin_name); ?></li>
					<li><?php _e('If both of "Start Date" and "End Date" are empty, get Google Calendar events without date limitation.', $this->plugin_name); ?></li>
					<li><?php _e('maxResults is maximum number of events returned on one result page.', $this->plugin_name); ?></li>
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
		<div style="overflow:noscroll; height: 180px;">
		<br/>
		<table>
			<tr><td><strong>1. <?php _e('Order by Sort: ', $this->plugin_name); ?></strong></td><td><input name="google-calendar-orderbysort" type="radio" value="ascending" <?php if(strtolower($this->google_calendar['orderbysort']) !== 'descending') print 'checked';?>/>Ascending <input name="google-calendar-orderbysort" type="radio" value="descending" <?php if(strtolower($this->google_calendar['orderbysort']) === 'descending') print 'checked';?>/>Descending</td></tr>
			<tr><td><strong>2. <?php _e('HTML tag: ', $this->plugin_name); ?></strong></td><td>
			<?php foreach($this->html_tags as $html_tag): ?>
			<input name="google-calendar-html-tag" type="radio" value="<?php print esc_attr($html_tag); ?>" <?php if($this->google_calendar['html-tag'] === $html_tag) print 'checked'; elseif(empty($this->google_calendar['html-tag']) && $this->default_html_tag === $html_tag) print 'checked'; ?>/><?php print esc_html('<' . $html_tag . '>');?> 
			<?php endforeach; ?></td></tr>

			<tr><td colspan="2">
				<ol>
					<li><?php _e('Order by Sort behaves like "ordersort" by Google Calendar API v2.', $this->plugin_name); ?></li>
					<li><?php printf(__('HTML tag is used by the output Google Calendar events. The tag class is "%s".', $this->plugin_name), $this->plugin_name); ?></li>
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
