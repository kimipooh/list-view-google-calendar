<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
$cls = esc_attr($html_tag_class);
$date_name = __("Date:", 'list-view-google-calendar');

$title_text = esc_html($gc_title);
$title_attr = esc_attr($gc_description_title);
$link = esc_url($gc_link);
$cat  = isset($output_category_temp) ? wp_kses_post($output_category_temp) : '';
$date = esc_html($start_end_date_value);

if ( isset($no_event_link) && !empty($no_event_link) ):
    $out_temp = sprintf(
        ' <li class="%1$s_item"><span style="font-weight: bold;" class="%1$s_title" title="%2$s">%3$s</span> %4$s<br/><span class="%1$s_date">%5$s %6$s</span>',
        $cls,
        $title_attr,
        $title_text,
        $cat,
        esc_html($date_name),
        $date
    );
else:
    $out_temp = sprintf(
        ' <li class="%1$s_item"><a target="_blank" rel="noopener noreferrer" class="%1$s_link" style="font-weight: bold;" href="%2$s" title="%3$s">%4$s</a> %5$s<br/><span class="%1$s_date">%6$s %7$s</span>',
        $cls,
        $link,
        $title_attr,
        $title_text,
        $cat,
        esc_html($date_name),
        $date
    );
endif;

if ( isset($view_location) && !empty($view_location) ):
    if ( isset($view_location_name) && !empty($view_location_name) ):
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

$out_temp .= "</li>
";
