<?php
/**
 * Created by PhpStorm.
 * User: mihan007
 * Date: 26.07.16
 * Time: 11:53
 */

$fileData = fopen('districts.csv', 'r');

$list = [];
while ($row = fgetcsv($fileData)) {
	$list[] = [$row[4], $row[2]];
}

function requestNominatim($request)
{
	$url = 'http://nominatim.openstreetmap.org/search?q=' . urlencode($request) . '&format=json&polygon_geojson=1';
	$agent = 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_11_5) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/51.0.2704.106 Safari/537.36';

	$ch = curl_init();
	curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
	curl_setopt($ch, CURLOPT_VERBOSE, false);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
	curl_setopt($ch, CURLOPT_USERAGENT, $agent);
	curl_setopt($ch, CURLOPT_URL, $url);

	return curl_exec($ch);
}

//step 1: request nominatim
foreach ($list as $i => $cityDistrict) {
	$request = "Российская Федерация, " . $cityDistrict[0].", район ".$cityDistrict[1];
	$filename = 'nominatim/our_district' . $i . '.json';
	if (!is_file($filename)) {
		echo $request . "\n";
		$result = requestNominatim($request);
		file_put_contents($filename, $result);
	}
}

/**
 * @param $j
 */
function writeToFile($j)
{
	$out = ob_get_contents();
	$out .= "?>\n";
	ob_end_clean();
	file_put_contents('out/our_district' . $j . '.php', $out);
}

//foreach ($list as $j => $cityDistrict) {
//	//echo 'Parsing ' . $j . "($cityDistrict[0], $cityDistrict[1])\n";
//	$filename = 'out/our_district' . $j . '.php';
//	//if (is_file($filename)) {
//	//	//echo 'Already done' . "\n";
//	//	continue;
//	//}
//	ob_start();
//	echo "<?php \n";
//	$foContent = file_get_contents('nominatim/our_district' . $j . '.json');
//	$decoded = json_decode($foContent);
//
//	$type = '';
//	for ($i = 0; $i < 5; $i++) {
//		if (!isset($decoded[$i])) {
//			continue;
//		}
//		$decodedType = $decoded[$i]->type;
//		if ($decoded[$i]->type == 'administrative') {
//			$foParts = $decoded[$i]->geojson->coordinates;
//			$type = $decoded[$i]->geojson->type;
//			if (($type == 'MultiPolygon') || ($type == 'Polygon')) {
//				break;
//			}
//		}
//	}
//
//	if ($type == 'MultiPolygon') {
//		foreach ($foParts as $i => $russiaP) {
//			$russiaPart = $russiaP[0];
//
//			$res = [];
//			foreach ($russiaPart as $coord) {
//				$res[] = $coord[0] . "," . $coord[1];
//			}
//			$text = $cityDistrict[0] .", " . $cityDistrict[1] . " " . ($i + 1);
//			$md5 = md5($text);
//			echo "\n" . '$result["' . $md5 . '"]=array(
//			"text" => "' . $text . '",
//			"coords" => "' . implode(";", $res) . "\n" . '");';
//		}
//		writeToFile($j);
//	} else if ($type == 'Polygon') {
//		$res = [];
//		$foParts = $foParts[0];
//		foreach ($foParts as $coord) {
//			$res[] = $coord[0] . "," . $coord[1];
//		}
//		$text = $cityDistrict[0] .", " . $cityDistrict[1];
//		$md5 = md5($text);
//		echo "\n" . '$result["' . $md5 . '"]=array(
//			"text" => "' . $text . '",
//			"coords" => "' . implode(";", $res) . "\n" . '");';
//
//		writeToFile($j);
//	} else {
//		writeToFile($j);
//		//if (strpos($cityDistrict[1], 'поселение')>0) {
//		//	$name = str_replace(",", "", $cityDistrict[1]);
//		//	$request = "Российская Федерация, " . $cityDistrict[0].", ".$name;
//		//	echo $request."\n";
//		//	$filename = 'nominatim/our_district' . $j . '.json';
//		//	$result = requestNominatim($request);
//		//	file_put_contents($filename, $result);
//		//} else {
//		//	$name = $cityDistrict[1];
//		//	$request = "Российская Федерация, " . $cityDistrict[0].", ".$name." район";
//		//	echo $request."\n";
//		//	$filename = 'nominatim/our_district' . $j . '.json';
//		//	$result = requestNominatim($request);
//		//	file_put_contents($filename, $result);
//		//}
//		echo "Unknown " . $j . "($cityDistrict[0], $cityDistrict[1], $decodedType)\n";
//	}
//}

$out = "";
foreach ($list as $j => $city) {
	$filename = 'out/our_district' . $j . '.php';
	if (is_file($filename)) {
		$fileContent = file_get_contents($filename);
		$out .= $fileContent;
	}
}
file_put_contents('out/our_districts.php', $out);