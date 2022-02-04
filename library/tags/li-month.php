<?php 

$pre_start_date_value_style = '';
$start_date_month_value = $this->wp_datetime_converter_get_date_from_gmt("Ym", $dateTime);
$pre_item_head_style='';
if( isset($pre_start_dateTime) ):
	$pre_start_date_month_value = $this->wp_datetime_converter_get_date_from_gmt("Ym", $pre_start_dateTime);
	if($pre_start_date_month_value === $start_date_month_value):
		$month_value = '';
	else:
		$month_value = $this->wp_datetime_converter_get_date_from_gmt("F", $dateTime);

		if( isset($month_value) && !empty($month_value) ) :
			$out_temp .= <<< ___EOF___
</ul>

___EOF___;

		endif;
	endif;
else:
	$month_value = $this->wp_datetime_converter_get_date_from_gmt("F", $dateTime);
endif;

if( isset($month_value) && !empty($month_value) ) :
$out_temp .= <<< ___EOF___
<span style='font-weight: bold;'  class='${html_tag_class}_item_month'>$month_value</span>
<ul class='${html_tag_class}_item'>

___EOF___;

endif;

if ( isset($no_event_link) && !empty($no_event_link) ): 

$out_temp .= <<< ___EOF___
 <li class='${html_tag_class}_item'><span class='${html_tag_class}_date'>$start_date_value</span> $output_category_temp <span title="$gc_description_title">$gc_title</span>
___EOF___;

else:

$out_temp .= <<< ___EOF___
 <li class='${html_tag_class}_item'><span class='${html_tag_class}_date'>$start_date_value</span> $output_category_temp <a target="_blank" class='${html_tag_class}_link' href='$gc_link' title="$gc_description_title">$gc_title</a>
___EOF___;

endif;

if ( isset($view_location) && !empty($view_location) ):
	if( isset($view_location_name) && !empty($view_location_name) ): 
	    $location_header_name = $view_location_name;
	 else:
	    $location_header_name = __("Location:", $this->plugin_name);
	 endif;
	$pre_start_date_value_style = '';    
$out_temp .= <<< ___EOF___
<br/><span class='${html_tag_class}_location_head'>$location_header_name</span> <span class='${html_tag_class}_location'>$gc_location</span>
___EOF___;
endif;

$out_temp .= <<< ___EOF___
</li>

___EOF___;
