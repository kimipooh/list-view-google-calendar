<?php 

$out_temp = <<< ___EOF___
 <li class='${html_tag_class}_item'><span class='${html_tag_class}_date'>$start_date_value</span> $output_category_temp <a target="_blank" class='${html_tag_class}_link' href='$gc_link'>$gc_title</a>
___EOF___;

if ( isset($view_location) && !empty($view_location) ):
    $location_header_name = __('Venue:', $this->plugin_name);
$out_temp .= <<< ___EOF___
<br/><span class='${html_tag_class}_location_head'>$location_header_name</span> <span class='${html_tag_class}_location'>$gc_location</span>
___EOF___;
endif;

$out_temp .= <<< ___EOF___
</li>

___EOF___;
