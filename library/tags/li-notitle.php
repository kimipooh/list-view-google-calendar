<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
$cls = esc_attr($html_tag_class);
$date = esc_html($start_end_date_value);
$cat  = isset($output_category_temp) ? wp_kses_post($output_category_temp) : '';

$out_temp = sprintf(
    ' <li class="%1$s_item"><span class="%1$s_date">%2$s</span> %3$s',
    $cls, $date, $cat
);

if ( isset($view_location) && !empty($view_location) ):
    if ( isset($view_location_name) && !empty($view_location_name) ):
        $location_header_name = $view_location_name;
    else:
        $location_header_name = __("Location:", 'list-view-google-calendar');
    endif;

    $out_temp .= sprintf(
        ' <span class="%1$s_location_head">%2$s</span> <span class="%1$s_location">%3$s</span>',
        $cls,
        esc_html($location_header_name),
        wp_kses_post($gc_location)
    );
endif;

$out_temp .= "</li>
";
