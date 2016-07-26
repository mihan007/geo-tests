<?php
$filename = time().".php";
$json = file_get_contents('php://input');
$obj = json_decode($json);
$incoming = $obj;
$coords = $incoming->coords;
$out = "\n<?php\n";
foreach ($coords as $i => $coord) {
	$placeName = $incoming->text." ".($i+1);
	$placeHash = md5($placeName);

	$out .= "\n".'$result[\''.$placeHash.'\']=array(
		"text" => "'.$placeName.'",
		"coords" => "'.implode(";", $coord).'");
	';
}

$out .= "\n?>\n";

file_put_contents('ya/'.$filename, $out);