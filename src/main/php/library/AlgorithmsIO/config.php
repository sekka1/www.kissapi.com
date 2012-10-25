<?php
define('ROOT', $_SERVER['DOCUMENT_ROOT']);
#define('INCLUDEDIR', realpath(dirname(__FILE__)."/inc/"));
define('INCLUDEDIR', $_SERVER['DOCUMENT_ROOT']."/../library/AlgorithmsIO/");
define('TESTME', "TESTER");

#$localstrings = simplexml_load_file(INCLUDEDIR."localization/en.xml"); # English for now
$localstrings = simplexml_load_file(INCLUDEDIR."localization/en.xml"); # English for now

include_once(INCLUDEDIR."algorithms_local.php");

?>
