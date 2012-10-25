<?php

function get_mysqli_connection_options() {
	$options = array(
	'host'=> 'localhost',
        'user'=> 'root',
        'pass'=> 'lompoc',
        'dbname'=> 'algorithms',
        'port'=> 3306,
        'sock'=> false 
	);
	return $options;	
	//$mysqli = new mysqli("localhost", "root", "lompoc", "algorithms");
	//return $mysqli;
}

function wizard_navigation($nav) {
	return "";
	global $localstrings;
	$output="";
	$zindex_counter = 10;
	foreach ($nav as $item=>$active) {
		if ($active) { $css_class = $localstrings->wizard->navigation->$item->css_class; } else {$css_class = "chevron-grey";}	
		$item_text = $localstrings->wizard->navigation->$item->text;
		$output .= "<span class=\"process-chevron $css_class\" style=\"z-index: $zindex_counter; margin: 1px;\"><a href=\"\">$item_text</a></span>\n";
		$zindex_counter--;
	}
	return $output;
}


function pageheader ($pageargs = array()) {
	include_once(INCLUDEDIR."header.php");
}

function pagefooter ($pageargs = array()) {
	include_once(INCLUDEDIR."footer.php");
}
?>
