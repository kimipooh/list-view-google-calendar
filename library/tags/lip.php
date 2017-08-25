<?php 

// Customizing category html.
$output_category_temp = '';
if(!empty($enable_view_category)):
	if(!empty($hash_tags_type_title)):
		$output_category_temp .= "<p class='${html_tag_class}_category'><span>$hash_tags_type_title</span></p>";
	endif;
	if(!empty($hash_tags_organizer_value)):
		$output_category_temp .= "<p class='${html_tag_class}_organizer'><span>$hash_tags_organizer_value</span></p>";
	endif;
endif;



$out_temp = <<< ___EOF___
 <li class='${html_tag_class}_item'><p class='${html_tag_class}_date'>$start_date_value</p>$output_category_temp<a class='${html_tag_class}_link' href='$gc_link'>$gc_title</a></li>
 
___EOF___;
