<?php 

$pre_start_date_value_style = '';
$pre_start_date_value_br= '<br/>';
$pre_item_style='style="padding-left: 3em;"';
$pre_item_head_style='';
if( isset($pre_start_date_value) && ($start_date_value === $pre_start_date_value) ):
	$pre_start_date_value_style = 'style="display:none;"';
	$pre_start_date_value_br= '';
	$pre_item_head_style='style="list-style: none; padding-left: 0;"';
endif;

if ( isset($no_event_link) && !empty($no_event_link) ): 

$out_temp .= <<< ___EOF___
 <li $pre_item_head_style class='${html_tag_class}_item'><span $pre_start_date_value_style class='${html_tag_class}_date'>$start_date_value</span> $output_category_temp $pre_start_date_value_br<div $pre_item_style>- <span title="$gc_description_title">$gc_title</span>
___EOF___;

else:

$out_temp .= <<< ___EOF___
 <li $pre_item_head_style class='${html_tag_class}_item'><span $pre_start_date_value_style class='${html_tag_class}_date'>$start_date_value</span> $output_category_temp $pre_start_date_value_br<div $pre_item_style>- <a target="_blank" class='${html_tag_class}_link' href='$gc_link' title="$gc_description_title">$gc_title</a>
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
</div></li>

___EOF___;
