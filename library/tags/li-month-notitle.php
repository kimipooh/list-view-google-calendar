<?php 

if($pre_start_date_month_value === $start_date_month_value):
	$month_value = '';
else:
	if( isset($month_value) && !empty($month_value) ) :
		$out_temp .= "
</ul>
";
	endif;
endif;

if(isset($months_title_array) && is_array($months_title_array)):
	$month_title = $this->get_month_title($month_value, $months_title_array);
endif;

if( isset($month_value) && !empty($month_value) ) :
	foreach($translate_month_values as $org_lang=>$con_lang):
		if($month_value === $org_lang):
			$month_value = $con_lang;
			break;
		endif;
	endforeach;

if (isset($month_title) && !empty($month_title)):
	$month_value .= " " . $month_title;
endif;
$out_temp .= "
<span style='font-weight: bold;'  class='{$html_tag_class}_item_month'>$month_value</span>
<ul class='{$html_tag_class}_item'>
";

endif;

if ( isset($no_event_link) && !empty($no_event_link) ): 

$out_temp .= "
 <li class='{$html_tag_class}_item'><span class='{$html_tag_class}_date'>$start_end_date_value</span> $output_category_temp
";

else:

$out_temp .= "
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
