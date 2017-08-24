<?php 
if(!empty($hash_tags_type_title)):

$out_temp = <<< ___EOF___
 <li class='$html_tag_class_c tlt_item'><p class='tlt_date'>$start_date_value</p><p class='tlt_category'><span>$hash_tags_type_title</span></p><p class='tlt_title'><a href='$gc_link'>$gc_title</a></p></li>
 
___EOF___;

else:

$out_temp = <<< ___EOF___
 <li class='$html_tag_class_c tlt_item'><p class='tlt_date'>$start_date_value</p><p class='tlt_title'><a href='$gc_link'>$gc_title</a></p></li>
 
___EOF___;


endif;