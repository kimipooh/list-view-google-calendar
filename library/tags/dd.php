<?php 
if(!empty($hash_tags_type_title)):
	
	$out_temp = <<< ___EOF___
 <dd class='$html_tag_class_c tlt_item'><span class='tlt_date'>$start_date_value</span> <span class='tlt_category'>$hash_tags_type_title</span> <span class='tlt_title'><a href='$gc_link'>$gc_title</a></span></dd>
 
___EOF___;

else:
	
	$out_temp = <<< ___EOF___
 <dd class='$html_tag_class_c tlt_item'><span class='tlt_date'>$start_date_value</span> <span class='tlt_title'><a href='$gc_link'>$gc_title</a></span></dd>
 
___EOF___;

endif;
