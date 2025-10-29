<?php 

// Date (02.09.’25)– Start Time (14:00)-End Time (17:30) ·Location (Bandraum)

if ( isset($no_event_link) && !empty($no_event_link) ): 

$out_temp = "
 <li class='{$html_tag_class}_item'><span class='{$html_tag_class}_date'>$start_end_date_value</span> $output_category_temp
";

else:

$out_temp = "
 <li class='{$html_tag_class}_item'><span class='{$html_tag_class}_date'>$start_end_date_value</span> $output_category_temp
";

endif;

if ( isset($view_location) && !empty($view_location) ):
	if( isset($view_location_name) && !empty($view_location_name) ): 
	    $location_header_name = $view_location_name;
	 else:
	    $location_header_name = __("Location:", 'list-view-google-calendar');
	 endif;

$out_temp .= "
<span class='{$html_tag_class}_location_head'>$location_header_name</span> <span class='{$html_tag_class}_location'>$gc_location</span>
";
endif;

$out_temp .= "
</li>
";
