<?php
include_once("../includes/config.php");
include_once("../includes/newtitles.php");
if ($_GET['searchtype']!='') {
	$st = $_GET['searchtype'];
} else {
	$st = "AND";
}
if ($_GET['language']!='') {
	$arr[] = "language = '$_GET[language]'";
}
if ($_GET['location']!='') {
	$arr[] = "location = '$_GET[location]'";
} 
if ($_GET['callrange']!='') {
	$arr[] = "callrange = '$_GET[callrange]'";
}
if ($_GET['format']!='') {
	$arr[] = "format = '$_GET[format]'";
}

	if ($_GET['catdate']!='') {
		if ($_GET['catdate']>-4) {
			$arr[] = "catDate >= '".date("Y-m-d",strtotime("$_GET[catdate] week"))."'";
			$urlarr[] = "catdate=$_GET[catdate]";
		} else {
			$cdate = $_GET['catdate'] + 3;
			$arr[] = "catDate >= '".date("Y-m-d",strtotime("$cdate month"))."'";
			$urlarr[] = "catdate=$_GET[catdate]";
		}			
		if ($_GET['catdate'] == -1) $filtdate = "Week";
		if ($_GET['catdate'] == -2) $filtdate = "2 Weeks";
		if ($_GET['catdate'] == -3) $filtdate = "3 Weeks";
		if ($_GET['catdate'] == -4) $filtdate = "Month";
		if ($_GET['catdate'] == -5) $filtdate = "2 Months";
		if ($_GET['catdate'] == -6) $filtdate = "3 Months";
		$filter .= "<li><strong>Cataloged within the last:</strong> $filtdate</li>\n";

		
	}
$query = "select * from newtitles";
if (sizeof($arr)==1) {
	$query .= " where $arr[0]";
} else if (sizeof($arr)>1) {
	$query .= " where ".implode(" $st ", $arr);
}
if (isset($_GET['order'])) {
	$order = " order by ".$_GET['order'];
} else {
	$order = " order by catDate DESC";
}
$query .= $order;

display_newtitles_rss($query);

?>
