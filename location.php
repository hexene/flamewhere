<?php
if(!isset($_POST['uri'])) {
    header("Location: /hack");
}
include("geoipcity.inc");
include("geoipregionvars.php");
function display_location($ip) {
	$gi = geoip_open("GeoLiteCity.dat",GEOIP_STANDARD);
	$record = geoip_record_by_addr($gi,$ip);
	geoip_close($gi);
	
	$city = $record->city;
	if ($city == "") return;

	$url_post = "http://where.yahooapis.com/v1/places.q('".urlencode($city)."')?appid=k0Ynt5bV34HdGAMTOjBPAk9pMo8FeVdlxiSVgRVV2Gk3TiT8p3B4QD62c6ENIPbiJbdDnIfMIzRS";
	$details = file_get_contents($url_post);
	$objDOM = new DOMDocument();
	$objDOM->loadXML($details);
	if (!$objDOM->getElementsByTagName("places")->item(0)->getAttribute('yahoo:count')) return;
	$lat = $objDOM->getElementsByTagName("place")->item(0)->getElementsByTagName("centroid")->item(0)->getElementsByTagName("latitude")->item(0)->nodeValue;
    $long = $objDOM->getElementsByTagName("place")->item(0)->getElementsByTagName("centroid")->item(0)->getElementsByTagName("longitude")->item(0)->nodeValue;
	
    $padstr = str_pad("",1024," ");
    echo $padstr;
	echo "<script type=\"text/javascript\">
	var cur_point = new YGeoPoint($lat, $long);
    map.addMarker(cur_point);
    </script><br/>";
    flush();
}	
ini_set('user_agent', 'Mozilla/5.0 (Windows; U; Windows NT 6.0; en-GB; rv:1.9.0.3) Gecko/2008092417 Firefox/3.0.3');
error_reporting(E_ALL);
ini_set('display_errors', '1');

// Hack for forcing immediate output (padstr and final <br/>)
$padstr = str_pad("",1024," ");
echo $padstr;
// Map Init
echo "<html><title>Wiki Users</title>
    <script type=\"text/javascript\"
    src=\"http://api.maps.yahoo.com/ajaxymap?v=3.8&appid=k0Ynt5bV34HdGAMTOjBPAk9pMo8FeVdlxiSVgRVV2Gk3TiT8p3B4QD62c6ENIPbiJbdDnIfMIzRS\">
</script>
<style type=\"text/css\">
#map{
  height: 85%;
  width: 100%;
}
</style>
<body>
<div id=\"map\"></div>
<script type=\"text/javascript\">
	var map = new YMap(document.getElementById('map'));
	map.addTypeControl();
	map.addZoomLong();
	map.addPanControl();

	// Set map type to either of: YAHOO_MAP_SAT, YAHOO_MAP_HYB, YAHOO_MAP_REG
	map.setMapType(YAHOO_MAP_SAT);
	map.drawZoomAndCenter(new YGeoPoint(0,0),16);
	map.addMarker(new YGeoPoint(0,0));
	</script><br/>";

flush();

$final_url = "";
$url = $_POST['uri'];
if( ($parsed_url = parse_url($url)) ) {
    $final_url = str_replace("wiki/","w/index.php?title=",$url);
    $final_url = $final_url."&limit=500&action=history";
}
else {
    echo "Error";
    echo "</body></html>";
    exit;
}

$doc = new DOMDocument();
$doc->loadHTMLFile($final_url);
$xpath = new DOMXpath($doc);
$node = $xpath->query('//ul[@id="pagehistory"]');
$edit=$xpath->query('//li', $node->item(0));
$users = $xpath->query('//span[@class="history-user"]', $edit->item(0));
foreach($users as $user) {
    list($id, $extra) = explode(' ', $user->nodeValue);

    if(preg_match("^([1-9]|[1-9][0-9]|1[0-9][0-9]|2[0-4][0-9]|25[0-5])(\.([0-9]|[1-9][0-9]|1[0-9][0-9]|2[0-4][0-9]|25[0-5])){3}^", $id))
        display_location($id);
}
echo "</body></html>";
?>
