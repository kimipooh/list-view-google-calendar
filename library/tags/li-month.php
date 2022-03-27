<?php 

$start_end_date_value = $start_date_value;
if(!empty($view_end_date) && !empty($end_date_value)):
	$start_end_date_value .=  ' ' . $view_end_date . ' ' . $end_date_value;
endif;

if($pre_start_date_month_value === $start_date_month_value):
	$month_value = '';
else:
	if( isset($month_value) && !empty($month_value) ) :
		$out_temp .= <<< ___EOF___
</ul>

___EOF___;
	endif;
endif;

if( isset($month_value) && !empty($month_value) ) :
	foreach($translate_month_values as $org_lang=>$con_lang):
		if($month_value === $org_lang):
			$month_value = $con_lang;
			break;
		endif;
	endforeach;

$out_temp .= <<< ___EOF___
<span style='font-weight: bold;'  class='${html_tag_class}_item_month'>$month_value</span>
<ul class='${html_tag_class}_item'>

___EOF___;

endif;

if ( isset($no_event_link) && !empty($no_event_link) ): 

$out_temp .= <<< ___EOF___
 <li class='${html_tag_class}_item'><span class='${html_tag_class}_date'>$start_end_date_value</span> $output_category_temp <span title="$gc_description_title">$gc_title</span>
___EOF___;

else:

$out_temp .= <<< ___EOF___
 <li class='${html_tag_class}_item'><span class='${html_tag_class}_date'>$start_end_date_value</span> $output_category_temp <a target="_blank" class='${html_tag_class}_link' href='$gc_link' title="$gc_description_title">$gc_title</a>
___EOF___;

endif;

if ( isset($view_location) && !empty($view_location) ):
	if( isset($view_location_name) && !empty($view_location_name) ): 
	    $location_header_name = $view_location_name;
	 else:
	    $location_header_name = __("Location:", $plugin_name);
	 endif;
	$out_temp .= <<< ___EOF___
<br/><span class='${html_tag_class}_location_head'>$location_header_name</span> <span class='${html_tag_class}_location'>$gc_location</span>
___EOF___;
endif;

$out_temp .= <<< ___EOF___
</li>

___EOF___;
