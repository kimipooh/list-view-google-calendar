<?php 

if ( isset($no_event_link) && !empty($no_event_link) ): 

$out_temp = <<< ___EOF___
 <p class='{$html_tag_class}_item'><span class='{$html_tag_class}_date'>$start_end_date_value</span> $output_category_temp <span class="{$html_tag_class}_title" title="$gc_description_title">$gc_title</span>
 
___EOF___;

else:

$out_temp = <<< ___EOF___
 <p class='{$html_tag_class}_item'><span class='{$html_tag_class}_date'>$start_end_date_value</span> $output_category_temp <a target="_blank" class='{$html_tag_class}_link' href='$gc_link' title="$gc_description_title">$gc_title</a>

___EOF___;
 
endif;

if ( isset($view_location) && !empty($view_location) ):
	if( isset($view_location_name) && !empty($view_location_name) ): 
	    $location_header_name = $view_location_name;
	 else:
	    $location_header_name = __("Location:", 'list-view-google-calendar');
	 endif;
$out_temp .= <<< ___EOF___
<br/><span class='{$html_tag_class}_location_head'>$location_header_name</span> <span class='{$html_tag_class}_location'>$gc_location</span>
___EOF___;
endif;

$out_temp .= <<< ___EOF___
</p>

___EOF___;
