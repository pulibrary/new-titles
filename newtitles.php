<?php
header('Content-Type: text/html; charset=utf-8');
session_start();
include_once("includes/newtitles.php");
include_once("includes/config.php");

$info = $_GET;

$rss_feed = "http://library.princeton.edu/catalogs/rssfeeds/newtitles.php?";
$url_rep = "http://".$_SERVER['HTTP_HOST'].$_SERVER['PHP_SELF']."?";

$filter = "<h3>Filters Selected:</h3><ul>";
if (isset($_GET['searchtype'])&&$_GET['searchtype']!='') {
	$st = $_GET['searchtype'];
	$urlarr[] = "searchtype=$_GET[searchtype]";
} else {
	$st = "AND";
}
if (isset($_GET['language'])&&$_GET['language']!='') {
	$arr[] = "language = '$_GET[language]'";
	$urlarr[] = str_replace(" ","+","language=$_GET[language]");
	$filter .= "<li><strong>Language:</strong> $_GET[language]</li>\n";
}
if (isset($_GET['location'])&&$_GET['location']!='') {
	$arr[] = "location = '$_GET[location]'";
	$urlarr[] = str_replace(" ","+","location=$_GET[location]");
	$filter .= "<li><strong>Location:</strong> $_GET[location]</li>\n";
}
if (isset($_GET['callrange'])&&$_GET['callrange']!='') {
	$arr[] = "callrange = '$_GET[callrange]'";
	$urlarr[] = str_replace(" ","+","callrange=$_GET[callrange]");
	$filter .= "<li><strong>Call Range:</strong> $_GET[callrange]</li>\n";
}
if (isset($_GET['format'])&&$_GET['format']!='') {
	$arr[] = "format = '$_GET[format]'";
	$urlarr[] = str_replace(" ","+","format=$_GET[format]");
	$filter .= "<li><strong>Format:</strong> $_GET[format]</li>\n";
}
if (isset($_GET['catdate'])&&$_GET['catdate']!='') {
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
$filter .= "</ul>";
$query = "select * from ".NEWTITLES_DB." ";
if (isset($arr)&&sizeof($arr)==1) {
	$query .= " where $arr[0]";
	$rss_feed .= "$urlarr[0]";
	$url_rep .= "$urlarr[0]";
} else if (isset($arr)&&sizeof($arr)>1) {
	$query .= " where ".implode(" $st ", $arr);
	$urr = implode("&amp;",$urlarr);
	$rss_feed .= $urr;
	$url_rep .= $urr;
}
if (isset($_GET['order'])) {
	$order = " order by ".$_GET['order'];
	if ($_GET['order']=="catdate") {
		$order .= " DESC";
	}

} else {
	$order = " order by title";
}
if (isset($_GET["pg"])) {
	$pg = $_GET["pg"];
} else {
	$pg = 1;
}
$query .= $order;

display_header();
display_search_choices($info); 
$qquery = "select timeStamp from ".NEWTITLES_DB." order by timeStamp DESC limit 0,1";
$res = db_query($qquery);
$info = db_returnrow($res);
echo "<h4>Last Updated: ".date("m/d/Y",strtotime($info["timeStamp"]))."</h4>";

if (isset($query)&&$query!="select * from ".NEWTITLES_DB."  order by title") {
	echo "<h4>Add the Web Feed to your News Reader:</h4>";
	echo "<p><a href='$rss_feed'>$rss_feed</a></p>";
	echo "<p><a href='/help/rss.php'>What is a Web Feed?</a></p>";
	echo $filter;
	display_newtitles($query, $pg);

} 
display_footer();


?>
