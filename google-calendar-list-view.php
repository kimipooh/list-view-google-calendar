<?php
/*
Plugin Name: Google Calendar List View
Plugin URI: 
Description: The plugin is to create a shortcode for displaying the list view of a public Google Calendar.
Version: 1.0
Author: Kimiya Kitani
Author URI: https://profiles.wordpress.org/kimipooh/
Text Domain: google-calendar-list-view
Domain Path: /lang
*/

$wm = new gclv();

class gclv{
	var $set_op = 'google-calendar-list-view_array';	// Save setting name in DB
	var $plugin_name = 'google-calendar-list-view';
	var $plugin_title = 'Google Calendar List View';
	var $plugin_shortcode = 'gc_list_view';
	var $default_maxResults = 10;  
	var $html_tags = array('li', 'p', 'dd'); 
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
		'html_tag'		=> '',
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
		$this->settings['version'] = 100;
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
		extract($atts = shortcode_atts(array(
    	    'start_date' 	=> '',
    	    'end_date'		=> '',
    	    'date_format'	=> 'Y.m.d', 
			'orderbysort'	=> '',			// ascending or descending.
	    ), $atts));
		$settings = get_option($this->set_op);
		$gc_data = $this->get_google_calendar_contents($atts);

		$out = ''; 
		if($gc_data['items']): 
			foreach($gc_data['items'] as $gc_key=>$gc_value):
				$out .= '<' . esc_html($settings['google_calendar']['html-tag']  ? $settings['google_calendar']['html-tag'] : $this->default_html_tag) . ' class="' . $this->plugin_name . '">' . ($date_format ? esc_html(date($date_format, strtotime($gc_value['start']['dateTime']))) : '') . ' <a href="' . esc_url($gc_value['htmlLink']) . '" target="_blank">' . esc_html($gc_value['summary']) .'</a>' . '</' . esc_html($settings['google_calendar']['html-tag']  ? $settings['google_calendar']['html-tag'] : $this->default_html_tag) . '>' . "\n";
	  		endforeach;		
		endif;
 
    	return $out;
	}
	public function get_google_calendar_contents($atts){
		if($atts) extract($atts);
		$settings = get_option($this->set_op);
		$gc = '';
		if(isset($settings['google_calendar']))
			$gc = $settings['google_calendar'];

 		$g_url = esc_url($gc['api-url']) . wp_strip_all_tags($gc['id']) . '/events?key=' . wp_strip_all_tags($gc['api-key']) . '&singleEvents=true';

		// Priority of the attribution value in the shortcode.
 		if(isset($start_date) && !empty($start_date)) $gc['start-date'] = wp_strip_all_tags($start_date);
 		if(isset($end_date) && !empty($end_date)) $gc['end-date'] = wp_strip_all_tags($end_date);
 		if(isset($orderbysort) && !empty($orderbysort)) $gc['orderbysort'] = wp_strip_all_tags($orderbysort);

		$params = array();
		$params[] = 'orderBy=' . wp_strip_all_tags($this->google_calendar['orderby']);
		$params[] = 'maxResults=' . (int)(isset($gc['maxResults']) ? wp_strip_all_tags($gc['maxResults']) : $this->default_maxResults);
		if(!empty($gc['start-date'])):
			if(strtolower($gc['start-date']) !== 'all'):
				$params[] = 'timeMin='.urlencode(date('c', strtotime($gc['start-date'])));
			endif;
		else:
			$params[] = 'timeMin='.urlencode(date('c'));			
		endif;
		if(!empty($gc['end-date']))
			$params[] = 'timeMax='.urlencode(date('c', strtotime($gc['end-date'])));

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
	 		 			$s_date[] = $item['start']['dateTime'];
	 		 		endif;
 	 			endforeach; 
 	 			array_multisort($s_date, SORT_DESC, $json['items']);
			endif;
		endif;
		
		return $json;
	}
	public function add_to_settings_menu(){
		add_options_page(__($this->plugin_title . ' Settings', $this->plugin_name), __($this->plugin_title . ' Settings',$this->plugin_name), 'manage_options', __FILE__,array(&$this,'admin_settings_page'));
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
     <fieldset style="border:1px solid #777777; width: 750px; padding-left: 6px;">
		<legend><h3><?php _e('How to use it.', $this->plugin_name); ?></h3></legend>
		<div style="overflow:noscroll; height: 250px;">
		<p><?php _e('Shortcode: ', $this->plugin_name); ?><strong><?php print '[' . $this->plugin_shortcode .']'; ?></strong> <?php _e('(Put the shortcode on a post or page.)', $this->plugin_name); ?></p>
		<p>The following shortcode option is priority than setting values.</p>
		<p><strong><?php print '[' . $this->plugin_shortcode .' start_date="YYYY-MM-DD/ALL" end_date="YYYY-MM-DD" date_format="Y.m.d" orderbysort="ascending/descending"]'; ?></strong></p>
				<ol>
					<li><?php _e('start_date is the value of "Start Date" (Default value is empty (= current date)).', $this->plugin_name); ?> <?php _e('(<a href="http://php.net/manual/en/function.strtotime.php" target="_blank">strtotime</a> date format is supported.)', $this->plugin_name); ?> <?php _e('If "ALL" value is setting up, start_date value is unlimited.', $this->plugin_name); ?></li>
					<li><?php _e('end_date is the value of "End Date".', $this->plugin_name); ?> <?php _e('(<a href="http://php.net/manual/en/function.strtotime.php" target="_blank">strtotime</a> date format is supported.)', $this->plugin_name); ?></li>
					<li><?php _e('date_format is (<a href="http://php.net/manual/en/datetime.formats.date.php" target="_blank">date</a>  format is supported.)', $this->plugin_name); ?></li>
					<li><?php _e('orderbysort can select "ascending" or descending". It behaves like ordersort by Google Calendar API v2.', $this->plugin_name); ?></li>
				</ol>
		</div>
     </fieldset>
	 <br/>
     <fieldset style="border:1px solid #777777; width: 750px; padding-left: 6px;">
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
     <fieldset style="border:1px solid #777777; width: 750px; padding-left: 6px;">
		<legend><h3><?php _e('General Settings', $this->plugin_name); ?></h3></legend>
		<div style="overflow:noscroll; height: 180px;">
		<br/>
		<table>
			<tr><td><strong>1. <?php _e('Order by Sort: ', $this->plugin_name); ?></strong></td><td><input name="google-calendar-orderbysort" type="radio" value="ascending" <?php if(strtolower($this->google_calendar['orderbysort']) !== 'descending') print 'checked';?>/>Ascending <input name="google-calendar-orderbysort" type="radio" value="descending" <?php if(strtolower($this->google_calendar['orderbysort']) === 'descending') print 'checked';?>/>Descending</td></tr>
			<tr><td><strong>2. <?php _e('HTML tag: ', $this->plugin_name); ?></strong></td><td>
			<?php foreach($this->html_tags as $html_tag): ?>
			<input name="google-calendar-html-tag" type="radio" value="<?php print esc_attr($html_tag); ?>" <?php if($this->google_calendar['html-tag'] === $html_tag) print 'checked'; else if(empty($this->google_calendar['html-tag']) && $this->default_html_tag === $html_tag) print 'checked'; ?>/><?php print esc_html('<' . $html_tag . '>');?> 
			<?php endforeach; ?></td></tr>

			<tr><td colspan="2">
				<ol>
					<li><?php _e('Order by Sort behaves like "ordersort" by Google Calendar API v2.', $this->plugin_name); ?></li>
					<li><?php _e('HTML tag is used by the output Google Calendar events. The tag class is "'.$this->plugin_name.'".', $this->plugin_name); ?></li>
				</ol>
			</td></tr>
		</table>
    	 <input type="submit" value="<?php _e('Save', $this->plugin_name);  ?>" />
 		<br/>
		</div>
     </fieldset>
     <br/>
   
   </form>
<?php 
		if($google_calendar_flag):
?>
<div class="<?php print $this->plugin_name;?>_updated"><p><strong><?php _e('Updated', $this->plugin_name); ?></strong></p></div>

<?php 
		endif;
	} // close admin_settings_page function
} // close class
