<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
$pre_start_date_value_style = '';
$pre_start_date_value_br= '<br/>';
$pre_item_style='style="padding-left: 3em;"';
$pre_item_head_style='';
if( isset($pre_start_date_value) && ($start_date_value === $pre_start_date_value) ):
	$pre_start_date_value_style = 'style="display:none;"';
	$pre_start_date_value_br= '';
	$pre_item_head_style='style="list-style: none; padding-left: 0;"';
endif;

$cls = esc_attr($html_tag_class);
$date = esc_html($start_end_date_value);
$cat  = isset($output_category_temp) ? wp_kses_post($output_category_temp) : '';
$title_text = esc_html($gc_title);
$title_attr = esc_attr($gc_description_title);
$link = esc_url($gc_link);

if ( isset($no_event_link) && !empty($no_event_link) ):
	$out_temp .= sprintf(
		' <li %1$s class="%2$s_item"><span %3$s class="%2$s_date">%4$s</span> %5$s %6$s<div %7$s>- <span class="%2$s_title" title="%8$s">%9$s</span>',
		$pre_item_head_style,
		$cls,
		$pre_start_date_value_style,
		$date,
		$cat,
		$pre_start_date_value_br,
		$pre_item_style,
		$title_attr,
		$title_text
	);
else:
	$out_temp .= sprintf(
		' <li %1$s class="%2$s_item"><span %3$s class="%2$s_date">%4$s</span> %5$s %6$s<div %7$s>- <a target="_blank" rel="noopener noreferrer" class="%2$s_link" href="%8$s" title="%9$s">%10$s</a>',
		$pre_item_head_style,
		$cls,
		$pre_start_date_value_style,
		$date,
		$cat,
		$pre_start_date_value_br,
		$pre_item_style,
		$link,
		$title_attr,
		$title_text
	);
endif;

if ( isset($view_location) && !empty($view_location) ):
	if( isset($view_location_name) && !empty($view_location_name) ):
		$location_header_name = $view_location_name;
	else:
		$location_header_name = __("Location:", 'list-view-google-calendar');
	endif;
	$pre_start_date_value_style = '';
	$out_temp .= sprintf(
		'<br/><span class="%1$s_location_head">%2$s</span> <span class="%1$s_location">%3$s</span>',
		$cls,
		esc_html($location_header_name),
		wp_kses_post($gc_location)
	);
endif;

$out_temp .= "</div></li>
";
