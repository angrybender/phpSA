<?php
// Compute the margins of the @page style
$margin_top    = $initialcb_style->length_in_pt($initialcb_style->margin_top,    $initialcb["h"]);
$margin_right  = $initialcb_style->length_in_pt($initialcb_style->margin_right,  $initialcb["w"]);
$margin_bottom = $initialcb_style->length_in_pt($initialcb_style->margin_bottom, $initialcb["h"]);
$margin_left   = $initialcb_style->length_in_pt($initialcb_style->margin_bottom,   $initialcb["w"]);