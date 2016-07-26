<?php

$russia = file_get_contents('russia.json');
$decoded = json_decode($russia);
$russiaParts = $decoded[0]->geojson->coordinates;

echo "<?php \n";

foreach ($russiaParts as $i => $russiaP) {
	$russiaPart = $russiaP[0];

	$res = [];
	foreach ($russiaPart as $coord) {
		$res[]=$coord[0].",".$coord[1];
	}
	$text = "Россия ".($i+1);
	$md5 = md5($text);
	echo "\n".'$result["'.$md5.'"]=array(
	"text" => "'.$text.'",
	"coords" => "'.implode(";", $res)."\n".'");';
}
