<?php 

$date_name = __("Date:", 'list-view-google-calendar');
if ( isset($no_event_link) && !empty($no_event_link) ): 
$out_temp = "
 <li class='{$html_tag_class}_item'><span style='font-weight: bold;' class='{$html_tag_class}_title' title='$gc_description_title'>$gc_title</span> $output_category_temp<br/>
<span class='{$html_tag_class}_date'>$date_name $start_end_date_value</span> 
";

else:

$out_temp = "
 <li class='{$html_tag_class}_item'><a target='_blank' class='{$html_tag_class}_link' style='font-weight: bold;' href='$gc_link' title='$gc_description_title'>$gc_title</a> $output_category_temp<br/>
 <span class='{$html_tag_class}_date'>$date_name $start_end_date_value</span>
";

endif;

if ( isset($view_location) && !empty($view_location) ):
	if( isset($view_location_name) && !empty($view_location_name) ): 
	    $location_header_name = $view_location_name;
	 else:
	    $location_header_name = __("Location:", 'list-view-google-calendar');
	 endif;

$out_temp .= "
<br/><span class='{$html_tag_class}_location_head'>$location_header_name</span> <span class='{$html_tag_class}_location'>$gc_location</span>
";
endif;

$out_temp .= "
</li>
";
