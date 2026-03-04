<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
$cls = esc_attr($html_tag_class);
$date = esc_html($start_end_date_value);
$title_text = esc_html($gc_title);
$title_attr = esc_attr($gc_description_title);
$link = esc_url($gc_link);

// Customizing category html (escape at build time; it's an HTML fragment).
$output_category_temp = '';
if(!empty($enable_view_category)):
	if(!empty($hash_tags_type_title)):
		$output_category_temp .= '<p class="' . $cls . '_category"><span>' . esc_html($hash_tags_type_title) . '</span></p>';
	endif;
	if(!empty($hash_tags_organizer_value)):
		$output_category_temp .= '<p class="' . $cls . '_organizer"><span>' . esc_html($hash_tags_organizer_value) . '</span></p>';
	endif;
endif;
$cat = wp_kses_post($output_category_temp);

if ( isset($no_event_link) && !empty($no_event_link) ):
	$out_temp = sprintf(
		' <li class="%1$s_item"><p class="%1$s_date">%2$s</p>%3$s<span class="%1$s_title" title="%4$s">%5$s</span>',
		$cls, $date, $cat, $title_attr, $title_text
	);
else:
	$out_temp = sprintf(
		' <li class="%1$s_item"><p class="%1$s_date">%2$s</p>%3$s<a target="_blank" rel="noopener noreferrer" class="%1$s_link" href="%4$s" title="%5$s">%6$s</a>',
		$cls, $date, $cat, $link, $title_attr, $title_text
	);
endif;

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
