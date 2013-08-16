<?php

function display_newtitles($query, $pg) {
	if ($pg==1) {
		$limit = " LIMIT $pg, 100";
	} else {
		$limit = " LIMIT ".($pg*100).",100";
	}
	$limit = "";
	$conn = db_connect();
	$results = db_query($query.$limit);
	$res = db_rowcount(db_query($query));
	if (!$results) {
		echo "<h2>No Results found.<br /><br />  Please try a new search.</h2>\n";
		return;
	}
	if (db_rowcount($results)==0) {
		echo "<h2>No Results found.<br /><br />  Please try a new search.</h2>\n";
		return;
	}
	echo "<h2>$res records found.</h2>\n";
	if ($pg==1) {
		$range = $pg."-".($pg+99);
	} else {
		$range = ($pg*100)."-".($pg*100+99);
	}
	#echo "<h3>Records $range</h3>\n";
	#echo "<p></p>";
	
	#echo $res % 100;
	echo "<table id='ntitles' cellspacing='0'>";
	echo "<tr>";
	echo "<th>Data Cataloged</th>";
	echo "<th>Title</th>";
	echo "<th>Author</th>";
	if ($_GET['language']=='') echo "<th>Language</th>";
	echo "<th>Imprint</th>";
	if ($_GET['location']=='') echo "<th>Location</th>";
	echo "<th>Call Number</th>";
	if ($_GET['format']=='') echo "<th>Format</th>";
	echo "</tr>\n";

	while ($row = db_returnrow($results)) {
		echo "<tr>\n";
		echo "<td>".date("m/d/Y",strtotime($row["catDate"]))."&nbsp;</td>\n";
		if (isset($row['titleVernancular'])&&$row['titleVernancular']!='')
			echo "<td><a href='http://catalog.princeton.edu/cgi-bin/Pwebrecon.cgi?BBID=$row[bibID]' target='_blank'>$row[titleVernancular]</a>&nbsp;</td>\n";
		else
			echo "<td><a href='http://catalog.princeton.edu/cgi-bin/Pwebrecon.cgi?BBID=$row[bibID]' target='_blank'>$row[title]</a>&nbsp;</td>\n";
		echo "<td>$row[author]&nbsp;</td>\n";
		if ($_GET['language']=='') echo "<td>$row[language]&nbsp;</td>\n";
		echo "<td>$row[imprint]&nbsp;</td>\n";
		if ($_GET['location']=='') echo "<td>$row[location]&nbsp;</td>\n";
		echo "<td>$row[callnumber]&nbsp;</td>\n";
		if ($_GET['format']=='') echo "<td>$row[format]&nbsp;</td>\n";
		echo "</tr>\n";
	}

	echo "</table>";
	return;
}
function display_newtitles_rss($query) {
	$conn = db_connect();
	$results = db_query($query);
	header("Content-Type: text/xml");
	echo "<?xml version='1.0' encoding='UTF-8'?>\n";
	echo "<rss version='2.0'>\n";
	echo "<channel>\n";
	echo "<title>Princeton University Library Recently Cataloged Titles</title>\n";

	echo "<link>http://library.princeton.edu/catalogs/newtitles.php</link>\n";
	echo "<description>Lists the recently cataloged items.</description>\n";
	$server = $_SERVER['SERVER_NAME'].$_SERVER['REQUEST_URI'];
	$server = str_replace('&','&amp;',$server);
	echo "<generator>http://$server</generator>\n";
	#echo "<language>en-us</language>\n";
	echo "<image>\n";
	echo "	<url>http://library.princeton.edu/images/pulshield.gif</url>\n";
	echo "	<title>Princeton University Logo</title>\n";
	echo "	<link>http://library.princeton.edu/catalogs/rssfeeds/dbs.php</link>\n";
	echo "	<width>66</width>\n";
	echo "	<height>68</height>\n";
	echo "</image>\n";
	echo "<copyright>".date("Y")." Princeton University Library</copyright>\n";
	echo "<managingEditor>abarrera@princeton.edu (Antonio Barrera)</managingEditor>\n";
	echo "<webMaster>abarrera@princeton.edu (Antonio Barrera)</webMaster>\n";

	if (!$results) {
		echo "<item>\n";
		echo "	<title>Error:</title>\n";

		echo "	<link>http://library.princeton.edu/catalogs/newtitles.php</link>\n";

		echo "	<description>No results found.  Please visit the site and try a different feed.</description>\n";
		echo "</item>\n";
		echo "</channel>\n";
		echo "</rss>\n";

		return;
	}
	if (db_rowcount($results)==0) {
		echo "<item>\n";
		echo "	<title>Error:</title>\n";

		echo "	<link>http://library.princeton.edu/catalogs/newtitles.php</link>\n";

		echo "	<description>No results found.  Please visit the site and try a different feed.</description>\n";
		echo "</item>\n";
		echo "</channel>\n";
		echo "</rss>\n";
		return;
	}




	#echo "<pubDate></pubDate>\n";
	#echo "<lastBuildDate></lastBuildDate>\n";
	while ($row = db_returnrow($results)) {
		echo "<item>\n";
		if ($row['titleVernancular']!='') {
			$title = '<![CDATA['.utf8_encode(htmlentities(html_entity_decode($row['titleVernacular']))).']]>';
			#echo "	<title>".str_replace(" & ","&amp;", str_replace("&nbsp;"," ",htmlentities2unicodeentities($row['titleVernancular'])))."</title>\n";
		} else {
			$title = '<![CDATA['.utf8_encode(htmlentities(html_entity_decode($row['title']))).']]>';
			#echo "	<title>".str_replace(" & ","&amp;", str_replace("&nbsp;"," ",htmlentities2unicodeentities($row['title'])))."</title>\n";
		}
		echo "<title>$title</title>\n";
		$url = htmlentities("http://catalog.princeton.edu/cgi-bin/Pwebrecon.cgi?BBID=$row[bibID]");
		echo "	<link>$url</link>\n";
		echo "	<guid>$url</guid>\n";
		#$br = htmlentities("<br />");
		#$br = "<br />";
		$desc = "Author: ".$row['author']."$br \n";
		$desc .= "Language: ".$row['language']."$br \n";
		$desc .= "Imprint: ".$row['imprint']."$br \n";
		$desc .= "Location: ".$row['location']."$br \n";
		$desc .= "Call Number: ".$row['callnumber']."$br \n";
		$desc .= "Call Range: ".$row['callrange']."$br \n";
		$desc .= "Call Range: ".$row['format']."$br \n";
		$desc .= "Cataloged Date: ".date("m/d/Y",strtotime($row["catDate"]))."$br \n";
		$desc = str_replace("&nbsp;"," ",$desc);
		$desc = str_replace("&","&amp;", $desc);


		#$desc = htmlentities($desc);
		$desc = htmlentities2unicodeentities($desc);
		$desc = str_replace("<","&amp;lt;", $desc);
		$desc = str_replace(">","&amp;rt;", $desc);


		#
		echo "	<description>$desc</description>\n";
		echo "</item>\n";
	}

	echo "</channel>\n";
	echo "</rss>\n";

	return;
}

function display_search_choices($selectedinfo) {
	$conn = db_connect();

	echo "<div id='nt_form'>\n";
	echo "<form action='$_SERVER[PHP_SELF]?action=search&amp;' method='get' >\n";
	echo "<table cellspacing='6px' cellpadding='0' border='0'>\n";

	$q = "select DISTINCT language from ".NEWTITLES_DB." where language NOT LIKE '' and language NOT LIKE '&nbsp;' order by language";
	$results = db_query($q);
	if (!$results) {
	}
	else if (db_rowcount($results)==0) {
	}
	else {
		echo "<tr>\n";
		echo "<td><label for='language'>Language:</label></td>\n";
		echo "<td><select name='language' id='language'>\n";

		echo "<option value='' selected>Select</option>\n";
		echo " ";
		while ($row = db_returnrow($results)) {
			/*if ($selectedinfo['language'] == $row['language']) {
				$sel = 'selected="selected"';
			} else {
			$sel = '';
			}*/
			echo "<option value='$row[language]'>$row[language]</option>";
		}
		echo "</select>\n";
		echo "</td></tr>";
	}

	$q = "select DISTINCT location from ".NEWTITLES_DB." where location NOT LIKE '' and location NOT LIKE '&nbsp;' order by location";
	$results = db_query($q);
	if (!$results) {
	}
	else if (db_rowcount($results)==0) {
	}
	else {
		echo "<tr>\n";
		echo "<td><label for='location'>Location:</label> </td>\n";
		echo "<td><select name='location' id='location'>";
		echo "<option value='' selected>Select</option>\n";
		while ($row = db_returnrow($results)) {
			/*if ($selectedinfo['location'] == $row['location']) {
				$sel = 'selected="selected"';
			} else {
			$sel = '';
			}*/
			echo "<option value=\"$row[location]\">$row[location]</option>";
		}
		echo "</select>\n";
		echo "</td></tr>";
	}

	$q = "select DISTINCT format from ".NEWTITLES_DB." where format NOT LIKE '' and format NOT LIKE '&nbsp;' order by format";
	$results = db_query($q);
	if (!$results) {
	}
	else if (db_rowcount($results)==0) {
	}
	else {
		echo "<tr>\n";
		echo "<td><label for='format'>Format:</label> </td>\n";
		echo "<td><select name='format' id='format'>";
		echo "<option value='' selected>Select</option>\n";
		while ($row = db_returnrow($results)) {
			/*if ($selectedinfo['format'] == $row['format']) {
				$sel = 'selected="selected"';
			} else {
			$sel = '';
			}*/
			echo "<option value='$row[format]'>$row[format]</option>";
		}
		echo "</select>\n";
		echo "</td></tr>";
	}

	$q = "select DISTINCT callrange from ".NEWTITLES_DB." where callrange NOT LIKE '' or callrange NOT LIKE '&nbsp;' order by callrange";
	$results = db_query($q);
	if (!$results) {
	}
	else if (db_rowcount($results)==0) {
	}
	else {
		echo "<tr>\n";
		echo "<td><label for='callrange'>Call Number Range:</label> </td>\n";
		echo "<td><select name='callrange' id='callrange'>";

		echo "<option value='' selected>Select</option>\n";
		while ($row = db_returnrow($results)) {
			/*if ($selectedinfo['callrange'] == $row['callrange']) {
				$sel = 'selected="selected"';
			} else {
			$sel = '';
			}*/
			echo "<option value='$row[callrange]'>$row[callrange]</option>";
		}
		echo "</select>\n";
		echo "</td></tr>";
	}
	echo "<tr>\n";
	echo "<td><label for='catdate'>Cataloged within:</label> </td>\n";
	echo "<td><select name='catdate' id='catdate'>";

	echo "<option value='' selected>Select</option>\n";
	echo "<option value='-1' >Last Week</option>";
	echo "<option value='-2' >Last 2 Weeks</option>";
	echo "<option value='-3' >Last 3 Weeks</option>";
	echo "<option value='-4' >Last Month</option>";
	echo "<option value='-5' >Last 2 Months</option>";
	echo "<option value='-6' >Last 3 Months</option>";
	echo "</select>\n";
	echo "</td></tr>";
	echo "<tr>\n";
	echo "<td><label for='order'>Sort By:</label></td>\n";
	echo "<td><select name='order' id='order'>";
	echo "<option value='title' selected>Select</option>\n";
	echo "<option value='author'>Author</option>";
	echo "<option value='callsort'>Call Number</option>";
	echo "<option value='catdate'>Date Cataloged</option>";
	echo "<option value='format'>Format</option>";
	echo "<option value='imprint'>Imprint</option>";
	echo "<option value='language'>Language</option>";
	echo "<option value='location'>Location</option>";
	echo "<option value='title'>Title</option>";
	echo "</select>\n";
	echo "</td></tr>";
	/*echo "<tr><td><label for='searchtype'>Search Type:</label></td>";
	 $sel = ($selectedinfo['searchtype']=='and') ? 'checked="checked"' : "";
	echo "<td>AND <input type='radio' value='and' name='searchtype' $sel /> ";
	$sel = ($selectedinfo['searchtype']=='or') ? 'checked="checked"' : "";
	echo "OR <input type='radio' value='or' name='searchtype' $sel />\n";
	echo "</td></tr>\n";*/
	echo "<tr><td><a href='$_SERVER[PHP_SELF]?act=comment&".$_SERVER['QUERY_STRING']."'>Comments or Suggestions</a></td><td><input type='submit' value='Go' name='submit' /><input type='reset' name='Reset' value='Clear Fields' ></td></tr>\n";


	echo "</table>\n";
	echo "</form>\n";
	echo "</div>\n";

	return;
}
function display_search_choices_multi($selectedinfo) {
	$conn = db_connect();

	echo "<div id='nt_form'>\n";
	echo "<form action='$_SERVER[PHP_SELF]' method='get' >\n";
	echo "<table cellspacing='2px' cellpadding='0' border='0'>\n";

	$q = "select DISTINCT language from ".NEWTITLES_DB." where language NOT LIKE '' and language NOT LIKE '&nbsp;' order by language";
	$results = db_query($q);
	if (!$results) {
	}
	else if (db_rowcount($results)==0) {
	}
	else {
		echo "<tr>\n";
		echo "<td><label for='language'>Language:</label></td>\n";
		echo "<td><select name='language' id='language'>";
		echo "<option value=''>Select</option>\n";
		echo "";
		while ($row = db_returnrow($results)) {
			if ($selectedinfo['language'] == $row['language']) {
				$sel = 'selected="selected"';
			} else {
				$sel = '';
			}
			echo "<option value='$row[language]' $sel>$row[language]</option>";
		}
		echo "</select>\n";
		echo "</td></tr>";
	}

	$q = "select DISTINCT location from ".NEWTITLES_DB." where location NOT LIKE '' and location NOT LIKE '&nbsp;' order by location";
	$results = db_query($q);
	if (!$results) {
	}
	else if (db_rowcount($results)==0) {
	}
	else {
		echo "<tr>\n";
		echo "<td><label for='location'>Location:</label> </td>\n";
		echo "<td><select name='location' id='location'>";
		echo "<option value=''>Select</option>\n";
		while ($row = db_returnrow($results)) {
			if ($selectedinfo['location'] == $row['location']) {
				$sel = 'selected="selected"';
			} else {
				$sel = '';
			}
			echo "<option value='$row[location]' $sel>$row[location]</option>";
		}
		echo "</select>\n";
		echo "</td></tr>";
	}

	$q = "select DISTINCT format from ".NEWTITLES_DB." where format NOT LIKE '' and format NOT LIKE '&nbsp;' order by format";
	$results = db_query($q);
	if (!$results) {
	}
	else if (db_rowcount($results)==0) {
	}
	else {
		echo "<tr>\n";
		echo "<td><label for='format'>Format:</label> </td>\n";
		echo "<td><select name='format' id='format'>";
		echo "<option value=''>Select</option>\n";
		while ($row = db_returnrow($results)) {
			if ($selectedinfo['format'] == $row['format']) {
				$sel = 'selected="selected"';
			} else {
				$sel = '';
			}
			echo "<option value='$row[format]' $sel>$row[format]</option>";
		}
		echo "</select>\n";
		echo "</td></tr>";
	}

	$q = "select DISTINCT callrange from ".NEWTITLES_DB." where callrange NOT LIKE '' or callrange NOT LIKE '&nbsp;' order by callrange";
	$results = db_query($q);
	if (!$results) {
	}
	else if (db_rowcount($results)==0) {
	}
	else {
		echo "<tr>\n";
		echo "<td><label for='callrange'>Call Number Range:</label> </td>\n";
		echo "<td><select name='callrange' id='callrange'>";

		echo "<option value=''>Select</option>\n";
		while ($row = db_returnrow($results)) {
			if ($selectedinfo['callrange'] == $row['callrange']) {
				$sel = 'selected="selected"';
			} else {
				$sel = '';
			}
			echo "<option value='$row[callrange]' $sel>$row[callrange]</option>";
		}
		echo "</select>\n";
		echo "</td></tr>";
	}
	echo "<tr>\n";
	echo "<td><label for='catdate'>Cataloged within:</label> </td>\n";
	echo "<td><select name='catdate' id='catdate'>";

	echo "<option value=''>Select</option>\n";
	echo "<option value='-1' $sel>Last Week</option>";
	echo "<option value='-2' $sel>Last 2 Weeks</option>";
	echo "<option value='-3' $sel>Last 3 Weeks</option>";
	echo "<option value='-4' $sel>Last 4 Weeks</option>";
	echo "</select>\n";
	echo "</td></tr>";
	echo "<tr>\n";
	echo "<td><label for='order'>Sort By:</label></td>\n";
	echo "<td><select name='order' id='order'>";
	echo "<option value='title'>Select</option>\n";
	echo "<option value='author'";
	$sel = ($selectedinfo['order']=='author') ? 'selected="selected"' : "";
	echo " $sel>Author</option>";
	echo "<option value='callsort'";
	$sel = ($selectedinfo['order']=='callsort') ? 'selected="selected"' : "";
	echo " $sel>Call Number</option>";
	echo "<option value='catdate'";
	$sel = ($selectedinfo['order']=='catdate') ? 'selected="selected"' : "";
	echo " $sel>Date Cataloged</option>";
	echo "<option value='format'";
	$sel = ($selectedinfo['order']=='format') ? 'selected="selected"' : "";
	echo " $sel>Format</option>";
	echo "<option value='imprint'";
	$sel = ($selectedinfo['order']=='imprint') ? 'selected="selected"' : "";
	echo " $sel>Imprint</option>";
	echo "<option value='language'";
	$sel = ($selectedinfo['order']=='language') ? 'selected="selected"' : "";
	echo " $sel>Language</option>";

	echo "<option value='location'";
	$sel = ($selectedinfo['order']=='location') ? 'selected="selected"' : "";
	echo " $sel>Location</option>";
	echo "<option value='title'";
	$sel = ($selectedinfo['order']=='title') ? 'selected="selected"' : "";
	echo " $sel>Title</option>";

	echo "</select>\n";
	echo "</td></tr>";
	echo "<tr><td><label for='searchtype'>Search Type:</label></td>";
	$sel = ($selectedinfo['searchtype']=='and') ? 'checked="checked"' : "";
	echo "<td>AND <input type='radio' value='and' name='searchtype' $sel /> ";
	$sel = ($selectedinfo['searchtype']=='or') ? 'checked="checked"' : "";
	echo "OR <input type='radio' value='or' name='searchtype' $sel />\n";
	echo "<input type='hidden' value='sub' name='action' /></td></tr>\n";
	echo "<tr><td>&nbsp;</td><td><input type='submit' value='Go' name='submit' /><input type='button' name='Reset' value='Clear Fields' onclick='allreset(); return false;'></td></tr>\n";


	echo "</table>\n";
	echo "</form>\n";
	echo "</div>\n";

	return;
}

function display_archive_links_finehall($currdate=false) {
	$conn = db_connect ();
	if ($conn) {
		echo "<ul>";
		for ($i=0; $i <= 12; $i++) {
			$datecalc = strtotime("-$i month");
			$sql = "select * from newtitles_finehall where month(catdate) = '".date("m", $datecalc)."' and year(catdate)= '".date("Y", $datecalc)."' ";
			$results = db_query($sql);

			if (!$results) {
			}
				
			else {
				if (db_rowcount($results)>0) {
					echo "<li>";
					if ($currdate==date("Y-m",$datecalc)) {
						echo date("F", $datecalc);
						if (date("Y",$datecalc)!=date("Y")) {
							echo " ".date("Y",$datecalc);
						}
					} else {
						echo "<a href='".$_SERVER['PHP_SELF']."?date=",date('Y-m',$datecalc)."'>".date("F", $datecalc);
						if (date("Y",$datecalc)!=date("Y")) {
							echo " ".date("Y",$datecalc);
						}
						echo "</a>";
					}
					echo "</li>\n";
				}
			}
		}
		echo "</ul>";
	}
}

function display_header ($title="") {
	?>

<!DOCTYPE html PUBLIC "-//W3C//DTD HTML+RDFa 1.1//EN">
<html class="js" dir="ltr" version="HTML+RDFa 1.1"
	xmlns:content="http://purl.org/rss/1.0/modules/content/"
	xmlns:dc="http://purl.org/dc/terms/"
	xmlns:foaf="http://xmlns.com/foaf/0.1/" xmlns:og="http://ogp.me/ns#"
	xmlns:rdfs="http://www.w3.org/2000/01/rdf-schema#"
	xmlns:sioc="http://rdfs.org/sioc/ns#"
	xmlns:sioct="http://rdfs.org/sioc/types#"
	xmlns:skos="http://www.w3.org/2004/02/skos/core#"
	xmlns:xsd="http://www.w3.org/2001/XMLSchema#"
	xmlns:schema="http://schema.org/" lang="en">
<head profile="http://www.w3.org/1999/xhtml/vocab">
<meta http-equiv="x-ua-compatible" content="IE=Edge">
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
<link rel="shortcut icon"
	href="http://library.princeton.edu/sites/default/files/libicon_0.ico"
	type="image/vnd.microsoft.icon">
<meta name="viewport"
	content="width=device-width, initial-scale=1, maximum-scale=2, minimum-scale=1, user-scalable=yes">
<meta about="/research" property="sioc:num_replies" content="0"
	datatype="xsd:integer">
<meta name="Generator" content="Drupal 7 (http://drupal.org)">
<meta content="Research" about="/research" property="dc:title">
<title>Research | Princeton University Library</title>
<link type="text/css" rel="stylesheet"
	href="assets/css_pbm0lsQQJ7A7WCCIMgxLho6mI_kBNgznNUWmTWcnfoE.css"
	media="all">
<link type="text/css" rel="stylesheet"
	href="assets/css_Je6S6BR9AWsyRI31UdJGx7OSVy5rPIoGJzFGeSQOqaY.css"
	media="all">
<link type="text/css" rel="stylesheet"
	href="assets/css_tT4NbUcYdt1VBJujPpGn3MAuhD7veM9XymJjslpF2tI.css"
	media="all">
<link type="text/css" rel="stylesheet"
	href="assets/css_KpHsFsHL5G3x9EUJr5pMqQKsGs4fdCXEGrY6HhOgLHA.css"
	media="screen">
<link type="text/css" rel="stylesheet"
	href="assets/css__lyI41Jt7ejqszQbBSb0anDM4pDT-OSGSep4ayTTjs4.css"
	media="all">
<link type="text/css" rel="stylesheet"
	href="assets/css_mym-kBIgwR5qonwBgo_3kH256xHlPc7BGpv4NEFB2Os.css"
	media="all">
<link type="text/css" rel="stylesheet"
	href="assets/css_7Zm-__T-_BMsdxTKMPHiKgvZwzhQ7yb09IElsrSWiG0.css"
	media="all">
<link type="text/css" rel="stylesheet" href="assets/css.css" media="all">
<link type="text/css" rel="stylesheet"
	href="assets/css_hIr0LdvrvB35LAZl2pSGtj7N3-GdPhXXTNlfv6ozc7w.css"
	media="all">

<!--[if (lt IE 9)&(!IEMobile)]>
<link type="text/css" rel="stylesheet" href="http://library.princeton.edu/sites/default/files/css/css_6SzlB7l0O1FEag4bTeEBGt5OY_vURsek231cGlPBV9Y.css" media="all" />
<![endif]-->

<!--[if gte IE 9]><!-->
<link type="text/css" rel="stylesheet"
	href="assets/css_nkCzvEQwtMmHcOK8xZLijaVDi_Xait5wxJgTqc8elug.css"
	media="all">
<!--<![endif]-->
<script src="assets/ga.js" async="" type="text/javascript"></script>
<script type="text/javascript"
	src="assets/js_d47q41yxdaR7-iGOaqYMA6jhbH5Ec_e6TOrRga9Ulz0.js"></script>
<script type="text/javascript"
	src="assets/js_NPmZS756d5KXOHM9ceoYz0D0Gj0utabgJ1xUNUmd5Q0.js"></script>
<script type="text/javascript"
	src="assets/js_NNWHx7iIsBF1flmCUkRIdsyAj-u_bQ8og7N5TXExTwo.js"></script>
<script type="text/javascript"
	src="assets/js_xluiF6dKsnmfL_2Uj1pORTflA0hjNccoLXcqKnCYKcI.js"></script>
<script type="text/javascript">
<!--//--><![CDATA[//><!--
var _gaq = _gaq || [];_gaq.push(["_setAccount", "UA-15870237-13"]);_gaq.push(["_trackPageview"]);(function() {var ga = document.createElement("script");ga.type = "text/javascript";ga.async = true;ga.src = ("https:" == document.location.protocol ? "https://ssl" : "http://www") + ".google-analytics.com/ga.js";var s = document.getElementsByTagName("script")[0];s.parentNode.insertBefore(ga, s);})();
//--><!]]>
</script>
<script type="text/javascript"
	src="assets/js_ZwtHh_UtMhEyIZ7P23uU_tO_tZHXgPG1rk11b_m8s9Y.js"></script>
<script type="text/javascript"
	src="assets/js_yFnjFy3k_zKUTerFGOEEuA0u2JIdAvoDQq_ZG4RH-9M.js"></script>
<script type="text/javascript"
	src="assets/js_43n5FBy8pZxQHxPXkf-sQF7ZiacVZke14b0VlvSA554.js"></script>
<script type="text/javascript">
<!--//--><![CDATA[//><!--
jQuery.extend(Drupal.settings, {"basePath":"\/","pathPrefix":"","ajaxPageState":{"theme":"pul_development_theme","theme_token":"gQ8794xa9jTqsilKVC_yhpanqcyyKZb6Pd060H2tmxc","js":{"sites\/all\/modules\/jquery_update\/replace\/jquery\/1.5\/jquery.min.js":1,"misc\/jquery.once.js":1,"sites\/all\/modules\/underscore\/lib\/underscore-min.js":1,"misc\/drupal.js":1,"sites\/all\/modules\/jquery_update\/replace\/ui\/ui\/minified\/jquery.ui.core.min.js":1,"sites\/all\/modules\/jquery_update\/replace\/ui\/ui\/minified\/jquery.ui.widget.min.js":1,"sites\/all\/modules\/jquery_update\/replace\/ui\/ui\/minified\/jquery.ui.accordion.min.js":1,"sites\/all\/modules\/jquery_update\/replace\/ui\/ui\/minified\/jquery.ui.button.min.js":1,"sites\/all\/modules\/jquery_update\/replace\/ui\/ui\/minified\/jquery.ui.progressbar.min.js":1,"sites\/all\/modules\/jquery_update\/replace\/ui\/ui\/minified\/jquery.ui.mouse.min.js":1,"sites\/all\/modules\/jquery_update\/replace\/ui\/ui\/minified\/jquery.ui.draggable.min.js":1,"sites\/all\/modules\/jquery_update\/replace\/ui\/ui\/minified\/jquery.ui.position.min.js":1,"sites\/all\/modules\/jquery_update\/replace\/ui\/ui\/minified\/jquery.ui.resizable.min.js":1,"sites\/all\/modules\/jquery_update\/replace\/ui\/ui\/minified\/jquery.ui.dialog.min.js":1,"sites\/all\/modules\/views\/js\/jquery.ui.dialog.patch.js":1,"misc\/ajax.js":1,"sites\/all\/modules\/colorbox_node\/colorbox_node.js":1,"sites\/all\/modules\/caption_filter\/js\/caption-filter.js":1,"sites\/all\/libraries\/colorbox\/colorbox\/jquery.colorbox-min.js":1,"sites\/all\/modules\/colorbox\/js\/colorbox.js":1,"sites\/all\/modules\/colorbox\/styles\/default\/colorbox_default_style.js":1,"sites\/all\/modules\/colorbox\/js\/colorbox_load.js":1,"sites\/all\/modules\/colorbox\/js\/colorbox_inline.js":1,"sites\/all\/modules\/panels\/js\/panels.js":1,"sites\/all\/modules\/views_slideshow\/js\/views_slideshow.js":1,"sites\/all\/modules\/google_analytics\/googleanalytics.js":1,"0":1,"misc\/progress.js":1,"sites\/all\/themes\/pul_development_theme\/menu.js":1,"sites\/all\/themes\/pul_development_theme\/theme.js":1,"sites\/all\/themes\/omega\/omega\/js\/jquery.formalize.js":1,"sites\/all\/themes\/omega\/omega\/js\/omega-mediaqueries.js":1},"css":{"modules\/system\/system.base.css":1,"modules\/system\/system.menus.css":1,"modules\/system\/system.messages.css":1,"modules\/system\/system.theme.css":1,"misc\/ui\/jquery.ui.core.css":1,"misc\/ui\/jquery.ui.theme.css":1,"misc\/ui\/jquery.ui.accordion.css":1,"misc\/ui\/jquery.ui.button.css":1,"misc\/ui\/jquery.ui.progressbar.css":1,"misc\/ui\/jquery.ui.resizable.css":1,"misc\/ui\/jquery.ui.dialog.css":1,"modules\/book\/book.css":1,"sites\/all\/modules\/calendar\/css\/calendar_multiday.css":1,"sites\/all\/modules\/colorbox_node\/colorbox_node.css":1,"modules\/comment\/comment.css":1,"sites\/all\/modules\/date\/date_api\/date.css":1,"sites\/all\/modules\/date\/date_popup\/themes\/datepicker.1.7.css":1,"sites\/all\/modules\/date\/date_repeat_field\/date_repeat_field.css":1,"modules\/field\/theme\/field.css":1,"sites\/all\/modules\/flexslider\/assets\/css\/flexslider_img.css":1,"sites\/all\/modules\/mollom\/mollom.css":1,"modules\/node\/node.css":1,"sites\/all\/modules\/office_hours\/office_hours.css":1,"modules\/search\/search.css":1,"modules\/user\/user.css":1,"sites\/all\/modules\/views\/css\/views.css":1,"sites\/all\/modules\/caption_filter\/caption-filter.css":1,"sites\/all\/modules\/colorbox\/styles\/default\/colorbox_default_style.css":1,"sites\/all\/modules\/ctools\/css\/ctools.css":1,"sites\/all\/libraries\/font_awesome\/css\/font-awesome.css":1,"sites\/all\/modules\/panels\/css\/panels.css":1,"sites\/all\/modules\/views_slideshow\/views_slideshow.css":1,"sites\/all\/themes\/omega\/alpha\/css\/alpha-reset.css":1,"sites\/all\/themes\/omega\/alpha\/css\/alpha-mobile.css":1,"sites\/all\/themes\/omega\/alpha\/css\/alpha-alpha.css":1,"sites\/all\/themes\/omega\/omega\/css\/formalize.css":1,"sites\/all\/themes\/omega\/omega\/css\/omega-text.css":1,"sites\/all\/themes\/omega\/omega\/css\/omega-branding.css":1,"sites\/all\/themes\/omega\/omega\/css\/omega-menu.css":1,"sites\/all\/themes\/omega\/omega\/css\/omega-forms.css":1,"sites\/all\/themes\/omega\/omega\/css\/omega-visuals.css":1,"http:\/\/fonts.googleapis.com\/css?family=Arvo:700,700italic,italic,regular|Droid+Serif:700,700italic,italic,regular|PT+Sans:700,700italic,italic,regular|PT+Sans+Caption:700,regular|PT+Sans+Narrow:700,regular|Roboto+Condensed:italic,regular|Roboto:italic,regular\u0026subset=latin,latin-ext":1,"sites\/all\/themes\/pul_development_theme\/css\/global.css":1,"ie::normal::sites\/all\/themes\/pul_development_theme\/css\/pul-development-theme-alpha-default.css":1,"ie::normal::sites\/all\/themes\/pul_development_theme\/css\/pul-development-theme-alpha-default-normal.css":1,"ie::normal::sites\/all\/themes\/omega\/alpha\/css\/grid\/alpha_default\/normal\/alpha-default-normal-12.css":1,"ie::normal::sites\/all\/themes\/omega\/alpha\/css\/grid\/alpha_default\/normal\/alpha-default-normal-16.css":1,"narrow::sites\/all\/themes\/pul_development_theme\/css\/pul-development-theme-alpha-default.css":1,"narrow::sites\/all\/themes\/pul_development_theme\/css\/pul-development-theme-alpha-default-narrow.css":1,"sites\/all\/themes\/omega\/alpha\/css\/grid\/alpha_default\/narrow\/alpha-default-narrow-12.css":1,"sites\/all\/themes\/omega\/alpha\/css\/grid\/alpha_default\/narrow\/alpha-default-narrow-16.css":1,"normal::sites\/all\/themes\/pul_development_theme\/css\/pul-development-theme-alpha-default.css":1,"normal::sites\/all\/themes\/pul_development_theme\/css\/pul-development-theme-alpha-default-normal.css":1,"sites\/all\/themes\/omega\/alpha\/css\/grid\/alpha_default\/normal\/alpha-default-normal-12.css":1,"sites\/all\/themes\/omega\/alpha\/css\/grid\/alpha_default\/normal\/alpha-default-normal-16.css":1}},"colorbox":{"opacity":"0.85","current":"{current} of {total}","previous":"\u00ab Prev","next":"Next \u00bb","close":"Close","maxWidth":"100%","maxHeight":"100%","fixed":true},"googleanalytics":{"trackOutbound":1,"trackMailto":1,"trackDownload":1,"trackDownloadExtensions":"7z|aac|arc|arj|asf|asx|avi|bin|csv|doc|exe|flv|gif|gz|gzip|hqx|jar|jpe?g|js|mp(2|3|4|e?g)|mov(ie)?|msi|msp|pdf|phps|png|ppt|qtm?|ra(m|r)?|sea|sit|tar|tgz|torrent|txt|wav|wma|wmv|wpd|xls|xml|z|zip"},"omega":{"layouts":{"primary":"normal","order":["narrow","normal"],"queries":{"narrow":"all and (min-width: 740px) and (min-device-width: 740px), (max-device-width: 800px) and (min-width: 740px) and (orientation:landscape)","normal":"all and (min-width: 980px) and (min-device-width: 980px), all and (max-device-width: 1024px) and (min-width: 1024px) and (orientation:landscape)"}}}});
//--><!]]>
</script>
<!--[if lt IE 9]><script src="http://html5shiv.googlecode.com/svn/trunk/html5.js"></script><![endif]-->
</head>
<body
	class="html not-front not-logged-in page-node page-node- page-node-3528 node-type-panel context-research no-sidebars omega-mediaqueries-processed responsive-layout-normal">
	<div id="omega-media-query-dummy">
		<style media="all">
#omega-media-query-dummy {
	position: relative;
	z-index: -1;
}
</style>
		<!--[if (lt IE 9)&(!IEMobile)]><style media="all">#omega-media-query-dummy { z-index: 1; }</style><![endif]-->
		<style
			media="all and (min-width: 740px) and (min-device-width: 740px), (max-device-width: 800px) and (min-width: 740px) and (orientation:landscape)">
#omega-media-query-dummy {
	z-index: 0;
}
</style>
		<style
			media="all and (min-width: 980px) and (min-device-width: 980px), all and (max-device-width: 1024px) and (min-width: 1024px) and (orientation:landscape)">
#omega-media-query-dummy {
	z-index: 1;
}
</style>
	</div>
	<div id="skip-link">
		<a href="#main-content" class="element-invisible element-focusable">Skip
			to main content</a>
	</div>
	<div class="region region-page-top" id="region-page-top">
		<div class="region-inner region-page-top-inner"></div>
	</div>
	<div class="page clearfix" id="page">
		<header id="section-header" class="section section-header">
		<div id="zone-alpha-wrapper"
			class="zone-wrapper zone-alpha-wrapper clearfix">
			<div id="zone-alpha" class="zone zone-alpha clearfix container-12">
				<div class="grid-7 region region-advert-1" id="region-advert-1">
					<div class="region-inner region-advert-1-inner">
						<div
							class="block block-block block-23 block-block-23 odd block-without-title"
							id="block-block-23">
							<div class="block-inner clearfix">

								<div class="content clearfix">
									<p>
										<i class="icon-asterisk"> </i>Explore our new site and
										provide&nbsp;<a
											href="http://library.princeton.edu/services/new-website-feedback">Feedback</a>
									</p>
								</div>
							</div>
						</div>
					</div>
				</div>
				<div class="grid-5 region region-advert-2" id="region-advert-2">
					<div class="region-inner region-advert-2-inner">
						<div
							class="block block-block block-27 block-block-27 odd block-without-title"
							id="block-block-27">
							<div class="block-inner clearfix">

								<div class="content clearfix">
									<p>
										<i class="icon-external-link"></i> <a
											href="http://library2.princeton.edu/">&nbsp;Return to Old
											Site</a>
									</p>
								</div>
							</div>
						</div>
					</div>
				</div>
			</div>
		</div>
		<div id="zone-user-wrapper"
			class="zone-wrapper zone-user-wrapper clearfix">
			<div id="zone-user" class="zone zone-user clearfix container-12">
				<div class="grid-12 region region-user-second"
					id="region-user-second">
					<div class="region-inner region-user-second-inner">
						<div
							class="block block-menu block-menu-user-tool-bar block-menu-menu-user-tool-bar odd block-without-title"
							id="block-menu-menu-user-tool-bar">
							<div class="block-inner clearfix">

								<div class="content clearfix">
									<ul class="menu">
										<li class="first leaf"><a
											href="http://library.princeton.edu/hours"
											title="See Library Hours">Hours</a></li>
										<li class="leaf"><a
											href="http://library.princeton.edu/find/all"
											title="Search for library materials and web content">Search</a>
										</li>
										<li class="leaf"><a href="http://library.princeton.edu/help"
											title="Find out how to get help">Help</a></li>
										<li class="leaf"><a
											href="http://library.princeton.edu/services/technology/off-campus-access"
											title="Access library resources from off campus.">Off Campus?</a>
										</li>
										<li class="leaf"><a
											href="http://library.princeton.edu/help/contact-us"
											title="Contact the library with comments, suggestions, or concerns about our services and staff.">Contact
												Us</a></li>
										<li class="last leaf"><a
											href="http://catalog.princeton.edu/cgi-bin/Pwebrecon.cgi?DB=local&amp;PAGE=pbLogon"
											title="Manage your library materials">Your Account</a></li>
									</ul>
								</div>
							</div>
						</div>
					</div>
				</div>
			</div>
		</div>
		<div id="zone-branding-wrapper"
			class="zone-wrapper zone-branding-wrapper clearfix">
			<div id="zone-branding"
				class="zone zone-branding clearfix container-12">
				<div class="grid-12 region region-branding" id="region-branding">
					<div class="region-inner region-branding-inner">
						<div class="branding-data clearfix">
							<div class="logo-img">
								<a href="http://library.princeton.edu/" rel="home" title=""><img
									src="assets/pul-logo.png" alt="" id="logo"> </a>
							</div>
						</div>
						<div
							class="block block-system block-menu block-main-menu block-system-main-menu odd block-without-title"
							id="block-system-main-menu">
							<div class="block-inner clearfix">

								<div class="content clearfix">
									<ul class="menu">
										<li class="first leaf active-trail"><a
											href="http://library.princeton.edu/research"
											title="Find library materials" class="active-trail active">Research</a>
										</li>
										<li class="leaf menu-views"><a
											href="http://library.princeton.edu/libraries">Libraries and
												Collections</a>
											<div
												class="view view-libraries-and-collections-combined view-id-libraries_and_collections_combined view-display-id-default libraries-collections-menu view-dom-id-e6f9181995481e03351287b6cc1a49a9">



												<div class="view-content">
													<div class="item-list">
														<h3>Libraries</h3>
														<ul>
															<li
																class="views-row views-row-1 views-row-odd views-row-first">
																<div>
																	<div></div>
																</div>
																<div>
																	<div>
																		<a href="http://archlib.princeton.edu/">Architecture
																			Library</a>
																	</div>
																</div>
															</li>
															<li class="views-row views-row-2 views-row-even">
																<div>
																	<div></div>
																</div>
																<div>
																	<div>
																		<a href="http://eastasianlib.princeton.edu/">East
																			Asian Library</a>
																	</div>
																</div>
															</li>
															<li class="views-row views-row-3 views-row-odd">
																<div>
																	<div></div>
																</div>
																<div>
																	<div>
																		<a href="http://englib.princeton.edu/">Engineering
																			Library</a>
																	</div>
																</div>
															</li>
															<li class="views-row views-row-4 views-row-even">
																<div>
																	<div></div>
																</div>
																<div>
																	<div>
																		<a
																			href="http://library.princeton.edu/libraries/firestone">Firestone
																			Library</a>
																	</div>
																</div>
															</li>
															<li class="views-row views-row-5 views-row-odd">
																<div>
																	<div></div>
																</div>
																<div>
																	<div>
																		<a href="http://www.princeton.edu/hrc/">Humanities
																			Resource Center</a>
																	</div>
																</div>
															</li>
															<li class="views-row views-row-6 views-row-even">
																<div>
																	<div></div>
																</div>
																<div>
																	<div>
																		<a href="http://scilib.princeton.edu/">Lewis Science
																			Library</a>
																	</div>
																</div>
															</li>
															<li class="views-row views-row-7 views-row-odd">
																<div>
																	<div></div>
																</div>
																<div>
																	<div>
																		<a href="http://marquand.princeton.edu/">Marquand
																			Library</a>
																	</div>
																</div>
															</li>
															<li class="views-row views-row-8 views-row-even">
																<div>
																	<div></div>
																</div>
																<div>
																	<div>
																		<a href="http://musiclib.princeton.edu/">Mendel Music
																			Library</a>
																	</div>
																</div>
															</li>
															<li class="views-row views-row-9 views-row-odd">
																<div>
																	<div></div>
																</div>
																<div>
																	<div>
																		<a href="http://mudd.princeton.edu/">Mudd Manuscript
																			Library</a>
																	</div>
																</div>
															</li>
															<li class="views-row views-row-10 views-row-even">
																<div>
																	<div></div>
																</div>
																<div>
																	<div>
																		<a href="http://library.pppl.gov/">Plasma Physics
																			Library</a>
																	</div>
																</div>
															</li>
															<li class="views-row views-row-11 views-row-odd">
																<div>
																	<div></div>
																</div>
																<div>
																	<div>
																		<a href="http://rbsc.princeton.edu/">Rare Books and
																			Special Collections</a>
																	</div>
																</div>
															</li>
															<li class="views-row views-row-12 views-row-even">
																<div>
																	<div></div>
																</div>
																<div>
																	<div>
																		<a href="http://recap.princeton.edu/">ReCAP</a>
																	</div>
																</div>
															</li>
															<li
																class="views-row views-row-13 views-row-odd views-row-last">
																<div>
																	<div></div>
																</div>
																<div>
																	<div>
																		<a href="http://stokeslib.princeton.edu/">Stokes
																			Library</a>
																	</div>
																</div>
															</li>
														</ul>
													</div>
													<div class="item-list">
														<h3>Collections</h3>
														<ul>
															<li
																class="views-row views-row-1 views-row-odd views-row-first">
																<div>
																	<div>
																		<a href="http://www.princeton.edu/cotsen/">Cotsen
																			Children’s Library</a>
																	</div>
																</div>
																<div>
																	<div></div>
																</div>
															</li>
															<li class="views-row views-row-2 views-row-even">
																<div>
																	<div>
																		<a href="http://princeton.lib.overdrive.com/">Dixon
																			eBooks</a>
																	</div>
																</div>
																<div>
																	<div></div>
																</div>
															</li>
															<li class="views-row views-row-3 views-row-odd">
																<div>
																	<div>
																		<a href="http://libguides.princeton.edu/govdocs">Government
																			Documents</a>
																	</div>
																</div>
																<div>
																	<div></div>
																</div>
															</li>
															<li class="views-row views-row-4 views-row-even">
																<div>
																	<div>
																		<a
																			href="http://www.princeton.edu/%7Erbsc/department/manuscripts/">Manuscripts
																			Division</a>
																	</div>
																</div>
																<div>
																	<div></div>
																</div>
															</li>
															<li class="views-row views-row-5 views-row-odd">
																<div>
																	<div>
																		<a
																			href="http://library.princeton.edu/collections/pumagic">Maps
																			and Geospatial Information</a>
																	</div>
																</div>
																<div>
																	<div></div>
																</div>
															</li>
															<li class="views-row views-row-6 views-row-even">
																<div>
																	<div>
																		<a
																			href="http://dss.princeton.edu/cgi-bin/dataresources/guides.cgi">Numeric
																			Data and Statistics</a>
																	</div>
																</div>
																<div>
																	<div></div>
																</div>
															</li>
															<li class="views-row views-row-7 views-row-odd">
																<div>
																	<div>
																		<a href="http://stokeslib.princeton.edu/main.htm">Population
																			Research Collection</a>
																	</div>
																</div>
																<div>
																	<div></div>
																</div>
															</li>
															<li class="views-row views-row-8 views-row-even">
																<div>
																	<div>
																		<a href="http://pudl.princeton.edu/">Princeton
																			University Digital Library</a>
																	</div>
																</div>
																<div>
																	<div></div>
																</div>
															</li>
															<li class="views-row views-row-9 views-row-odd">
																<div>
																	<div>
																		<a
																			href="http://www.princeton.edu/%7Emudd/finding_aids/policy.html">Public
																			Policy Papers</a>
																	</div>
																</div>
																<div>
																	<div></div>
																</div>
															</li>
															<li class="views-row views-row-10 views-row-even">
																<div>
																	<div>
																		<a
																			href="http://www.princeton.edu/%7Erbsc/department/scheide/">Scheide
																			Library</a>
																	</div>
																</div>
																<div>
																	<div></div>
																</div>
															</li>
															<li
																class="views-row views-row-11 views-row-odd views-row-last">
																<div>
																	<div>
																		<a
																			href="http://www.princeton.edu/%7Emudd/finding_aids/archives.html">University
																			Archives</a>
																	</div>
																</div>
																<div>
																	<div></div>
																</div>
															</li>
															<li class="last leaf more"><a
																href="http://library.princeton.edu/collections"
																title="See All Collections">More Collections</a></li>
														</ul>
													</div>
												</div>




												<div class="view-footer">
													<div class="see-more" id="libraries-collections-see-more">
														<h3>
															<a href="http://library.princeton.edu/about/collections">Collections
																and Collecting at Princeton</a>
														</h3>
													</div>
												</div>


											</div></li>
										<li class="leaf"><a
											href="http://library.princeton.edu/services"
											title="Find library services">Services</a></li>
										<li class="last leaf"><a
											href="http://library.princeton.edu/about"
											title="About the library">About</a></li>
									</ul>
								</div>
							</div>
						</div>
					</div>
				</div>
			</div>
		</div>
		<div id="zone-menu-wrapper"
			class="zone-wrapper zone-menu-wrapper clearfix">
			<div id="zone-menu" class="zone zone-menu clearfix container-12">
				<div class="grid-12 region region-menu" id="region-menu">
					<div class="region-inner region-menu-inner"></div>
				</div>
			</div>
		</div>
		</header>
		<section id="section-content" class="section section-content">
		<div id="zone-content-wrapper"
			class="zone-wrapper zone-content-wrapper clearfix">
			<div id="zone-content"
				class="zone zone-content clearfix container-12">
				<div class="grid-12 region region-content" id="region-content">
					<div class="region-inner region-content-inner">
						<a id="main-content"></a>
						<div id="breadcrumb" class="grid-">
							<h2 class="element-invisible">You are here</h2>
							<div class="breadcrumb">
								<a href="http://library.princeton.edu/">Home</a>
							</div>
						</div>
						<h1 class="title" id="page-title">Research</h1>
						<div
							class="block block-system block-main block-system-main odd block-without-title"
							id="block-system-main">
							<div class="block-inner clearfix">

								<div class="content clearfix">
									<article about="/research" typeof="sioc:Item foaf:Document"
										class="node node-panel node-promoted node-published node-not-sticky author-kr2 odd clearfix"
										id="node-panel-3528">


									<div class="content clearfix">

										<?php
}

function display_footer() {
	?>
									</div>

									<div class="clearfix">
										<nav class="links node-links clearfix"></nav>

									</div>
									</article>
								</div>
							</div>
						</div>
					</div>
				</div>
			</div>
		</div>
		</section>

		<footer id="section-footer" class="section section-footer">
		<div id="zone-footer-wrapper"
			class="zone-wrapper zone-footer-wrapper clearfix">
			<div id="zone-footer" class="zone zone-footer clearfix container-16">
				<div class="grid-3 region region-footer-first"
					id="region-footer-first">
					<div class="region-inner region-footer-first-inner">
						<section
							class="block block-menu block-menu-find block-menu-menu-find odd"
							id="block-menu-menu-find">
						<div class="block-inner clearfix">
							<h2 class="block-title">Research</h2>

							<div class="content clearfix">
								<ul class="menu">
									<li class="first leaf"><a
										href="http://library.princeton.edu/find/all" title="">Search</a>
									</li>
									<li class="leaf"><a href="http://catalog.princeton.edu/"
										title="Access the library's main catalog">Main Catalog</a>
									</li>
									<li class="leaf"><a
										href="http://library.princeton.edu/research/databases"
										title="Browse Research Databases by Title or Subject">Databases</a>
									</li>
									<li class="leaf"><a href="http://dss.princeton.edu/" title="">Data
											and Statistics</a>
									</li>
									<li class="leaf"><a href="http://libguides.princeton.edu/"
										title="">Library Research Guides</a>
									</li>
									<li class="leaf"><a
										href="http://library.princeton.edu/databases/subject/special-collections"
										title="Finding Aids for the Princeton University Archives and Special Collections">Special
											Collections</a>
									</li>
									<li class="last leaf active-trail"><a
										href="http://library.princeton.edu/research" title=""
										class="active-trail active">More...</a>
									</li>
								</ul>
							</div>
						</div>
						</section>
					</div>
				</div>
				<div class="grid-3 region region-footer-second"
					id="region-footer-second">
					<div class="region-inner region-footer-second-inner">
						<section
							class="block block-menu block-menu-information-for block-menu-menu-information-for odd"
							id="block-menu-menu-information-for">
						<div class="block-inner clearfix">
							<h2 class="block-title">Services</h2>

							<div class="content clearfix">
								<ul class="menu">
									<li class="first leaf"><a
										href="http://library.princeton.edu/services/privileges"
										title="">Library Privileges</a>
									</li>
									<li class="leaf"><a
										href="http://library.princeton.edu/services/privileges/renewing"
										title="">Renew Material</a>
									</li>
									<li class="leaf"><a
										href="http://library.princeton.edu/services/reserves" title="">Course
											Reserves</a>
									</li>
									<li class="leaf"><a
										href="http://library.princeton.edu/services/borrowdirect"
										title="">Borrow Direct</a>
									</li>
									<li class="leaf"><a
										href="http://library.princeton.edu/services/interlibrary-services"
										title="Interlibrary Loan Services">Interlibrary Loan</a>
									</li>
									<li class="leaf"><a
										href="http://library.princeton.edu/collections/microforms"
										title="Microforms Service">Microforms</a>
									</li>
									<li class="leaf"><a
										href="http://library.princeton.edu/services/study-spaces"
										title="">Study Spaces and Lockers</a>
									</li>
									<li class="last leaf"><a
										href="http://library.princeton.edu/services" title="">More...</a>
									</li>
								</ul>
							</div>
						</div>
						</section>
					</div>
				</div>
				<div class="grid-3 region region-footer-third"
					id="region-footer-third">
					<div class="region-inner region-footer-third-inner">
						<section
							class="block block-menu block-menu-get-help block-menu-menu-get-help odd"
							id="block-menu-menu-get-help">
						<div class="block-inner clearfix">
							<h2 class="block-title">Help</h2>

							<div class="content clearfix">
								<ul class="menu">
									<li class="first leaf"><a
										href="http://library.princeton.edu/staff/specialists"
										title="Find a Subject Librarian">Subject Specialists</a>
									</li>
									<li class="leaf"><a href="http://libguides.princeton.edu/"
										title="">Research Guides</a>
									</li>
									<li class="leaf"><a
										href="http://library.princeton.edu/services/technology/wireless-access"
										title="">Wireless Access</a>
									</li>
									<li class="leaf"><a
										href="http://scilib.princeton.edu/collections/digitalmapgis.html"
										title="">GIS Consulting</a>
									</li>
									<li class="leaf"><a
										href="http://dss.princeton.edu/contacts/consultants.html"
										title="">Statistical Consulting</a>
									</li>
									<li class="last leaf"><a
										href="http://library.princeton.edu/help" title="">More...</a>
									</li>
								</ul>
							</div>
						</div>
						</section>
					</div>
				</div>
				<div class="grid-3 region region-footer-fourth"
					id="region-footer-fourth">
					<div class="region-inner region-footer-fourth-inner">
						<section
							class="block block-menu block-menu-about block-menu-menu-about odd"
							id="block-menu-menu-about">
						<div class="block-inner clearfix">
							<h2 class="block-title">About</h2>

							<div class="content clearfix">
								<ul class="menu">
									<li class="first leaf"><a
										href="http://library.princeton.edu/about/locations" title="">Library
											Locations</a>
									</li>
									<li class="leaf"><a
										href="http://library.princeton.edu/staff/directory" title="">Staff
											Directory</a>
									</li>
									<li class="leaf"><a
										href="http://library.princeton.edu/about/collections" title="">Collections
											and Collecting</a>
									</li>
									<li class="leaf"><a
										href="http://www.princeton.edu/%7Erbsc/exhibitions/" title="">Exhibitions</a>
									</li>
									<li class="leaf"><a
										href="http://library.princeton.edu/about/friends" title="">Friends
											of the Library</a>
									</li>
									<li class="leaf"><a href="http://library.princeton.edu/staff"
										title="">For Library Staff</a>
									</li>
									<li class="last leaf"><a
										href="http://library.princeton.edu/about" title="">More...</a>
									</li>
								</ul>
							</div>
						</div>
						</section>
					</div>
				</div>
				<div class="grid-4 region region-footer-fifth"
					id="region-footer-fifth">
					<div class="region-inner region-footer-fifth-inner">
						<div
							class="block block-block block-1 block-block-1 odd block-without-title"
							id="block-block-1">
							<div class="block-inner clearfix">

								<div class="content clearfix">
									<div id="footer-logo-block">
										<div id="lib-address">
											<h2>Princeton University Library</h2>
											<div class="social-media">
												<a href="http://www.facebook.com/PULibrary"
													title="Friend us on Facebook"><img
													src="assets/facebook.png" alt="Facebook" border="0"> </a><a
													href="https://twitter.com/PULibrary"
													title="Follow us on Twitter"><img src="assets/twitter.png"
													alt="Twitter" border="0"> </a><a
													href="http://libguides.princeton.edu/govdocs"
													title="Government Documents Depository"><img
													src="assets/depository.png"
													alt="Government Documents Depository" border="0"> </a><a
													href="http://library.princeton.edu/about/friends"
													title="Friends of Princeton University Library"><img
													src="assets/friends.png"
													alt="Friends of Princeton University Library" border="0"> </a>
											</div>
											<address>
												One Washington Road <br> Princeton, NJ 08544-2098 USA <br>
												609.258.1470 phone <br> 609.258.0441 fax
											</address>
										</div>
										<div id="pu-footer-logo">
											<a href="http://www.princeton.edu/"><img
												src="assets/pu_logo_trans.png" border="0"> </a>
										</div>
										<div id="university-copyright-footer">© 2013 The Trustees of
											Princeton University. All rights reserved.</div>
									</div>
								</div>
							</div>
						</div>
					</div>
				</div>
			</div>
		</div>
		</footer>
	</div>
</body>
</html>
<?php 


}
?>
