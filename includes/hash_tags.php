<?php
/*
 * Processing hash tags in the description field on Google Calendar.
 *
 * hash_tags can use by adding [hash tag name].php in library/hash_tags/ folder.
 *
 * @2.11 #lang en (required 2 values)
 *    added #lang for displaying a Google calendar event each languages.
 *    ex. #lang en 
 * @2.2  #type [event type] [event type title] (required 3 values)
      added #type for categorizing an event type, such as seminar, symposium, and so on.  
 *    ex. #type seminar Special Seminar (event type = seminar, event title = "Special Seminar") <br />
 *    Of course, the event title can use the function of WordPress translation. 
 */
 
/* 
 * Getting hash tags the description field on Google Calendar.
 *   Implemented by @2.2
 *   $has_tags = array(
 *      "event key" => array("hash tag name"=>array(
 *         "value"=> "hash tag value", "title"=>"hash tag title"),
 *      ),
 *   );
 *   A hash tag should specific 2 or 3 values (name, value, and title).
 */

class gclv_hash_tags{
 protected function get_hash_tags_all($gc_data, $atts){
	if (empty($gc_data)) return $gc_data;
	if($atts) extract($atts);

	$hash_tags = array();
	if(!$gc_data['items']) return $hash_tags;

	$h_desc = '';
	foreach($gc_data['items'] as $gc_key=>$gc_value):
		if(isset($gc_value['description'])):
			$h_desc = trim(str_replace('　', ' ', $gc_value['description']));
			if(preg_match_all('/#(\w+)(\s+)(\w+)/u', $h_desc, $matches, PREG_SET_ORDER)):
				foreach($matches as $match):
					if(isset($match[1]) && isset($match[3])):
						$hash_tags[$gc_key][wp_strip_all_tags($match[1])] = array('value'=>wp_strip_all_tags($match[3]), 'title'=>'');
					endif;
				endforeach;
			endif;
			if(preg_match_all('/#(\w+)(\s+)(\w+)(\s+)([\w ]+)/u', $h_desc, $matches, PREG_SET_ORDER)):
				foreach($matches as $match):
					if(isset($match[1]) && isset($match[3]) && isset($match[5])):
						$hash_tags[$gc_key][wp_strip_all_tags($match[1])] = array('value'=>wp_strip_all_tags($match[3]), 'title'=>wp_strip_all_tags($match[5]));
					endif;
				endforeach;
			endif;
		endif;
	endforeach;

	return $hash_tags;
}
 protected function get_hash_tags($gc_value, $atts){
	if (empty($gc_value)) return $gc_value;
	if($atts) extract($atts);

	$hash_tags = array();

	$h_desc = '';
	if(isset($gc_value['description'])):
		$h_desc = trim(str_replace('　', ' ', $gc_value['description']));
		if(preg_match_all('/#(\w+)(\s+)(\w+)/u', $h_desc, $matches, PREG_SET_ORDER)):
			foreach($matches as $match):
				if(isset($match[1]) && isset($match[3])):
					$hash_tags[wp_strip_all_tags($match[1])] = array('value'=>wp_strip_all_tags($match[3]), 'title'=>'');
				endif;
			endforeach;
		endif;		if(preg_match_all('/#(\w+)(\s+)(\w+)(\s+)([\w ]+)/u', $h_desc, $matches, PREG_SET_ORDER)):
			foreach($matches as $match):
				if(isset($match[1]) && isset($match[3]) && isset($match[5])):
					$hash_tags[wp_strip_all_tags($match[1])] = array('value'=>wp_strip_all_tags($match[3]), 'title'=>wp_strip_all_tags($match[5]));
				endif;
			endforeach;
		endif;
	endif;
	return $hash_tags;
}

/*
 * Picking out the events regarding #lang hash tag from the google calendar events.
 *
 * ex. [$plugin_shortcode  lang="en"] means that the events which match "#lang en" is picked out. 
 */
 protected function get_select_lang_data($gc_data, $atts){
	if (empty($gc_data)) return $gc_data;
	if($atts) extract($atts);

	$hash_tags = $this->get_hash_tags_all($gc_data, $atts); 

	if(empty($hash_tags)) return $gc_data;
	
	if($gc_data['items']): 
		foreach($gc_data['items'] as $gc_key=>$gc_value):
			if(!empty($lang)):
				if(isset($gc_value['description'])):
					if(!isset($hash_tags[$gc_key]['lang']['value']) || (isset($hash_tags[$gc_key]['lang']['value']) && $hash_tags[$gc_key]['lang']['value'] !== $lang)): 
						unset($gc_data['items'][$gc_key]); 
						continue;
					endif;
				else:
					unset($gc_data['items'][$gc_key]); 
					continue;
				endif;
			endif;
		endforeach;
	endif;
	
	return $gc_data;
 }
}