<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
if($pre_start_date_month_value === $start_date_month_value):
	$month_value = '';
else:
	if( isset($month_value) && !empty($month_value) ) :
		$out_temp .= "</ul>\n";
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
	$out_temp .= sprintf(
		'<span style="font-weight: bold;" class="%1$s_item_month">%2$s</span><ul class="%1$s_item">',
		esc_attr($html_tag_class),
		esc_html($month_value)
	);
endif;

$cls = esc_attr($html_tag_class);
$date = esc_html($start_end_date_value);
$cat  = isset($output_category_temp) ? wp_kses_post($output_category_temp) : '';

$out_temp .= sprintf(
	' <li class="%1$s_item"><span class="%1$s_date">%2$s</span> %3$s',
	$cls, $date, $cat
);

if ( isset($view_location) && !empty($view_location) ):
	if( isset($view_location_name) && !empty($view_location_name) ):
		$location_header_name = $view_location_name;
	else:
		$location_header_name = __("Location:", 'list-view-google-calendar');
	endif;
	$out_temp .= sprintf(
		'<br/><span class="%1$s_location_head">%2$s</span> <span class="%1$s_location">%3$s</span>',
		$cls,
		esc_html($location_header_name),
		wp_kses_post($gc_location)
	);
endif;

$out_temp .= "</li>\n";
