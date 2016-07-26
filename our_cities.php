<?php
/**
 * Created by PhpStorm.
 * User: mihan007
 * Date: 26.07.16
 * Time: 11:53
 */

$fileData = fopen('city.csv', 'r');

$list = [];
while ($row = fgetcsv($fileData)) {
	$list[] = $row[1];
}

function requestNominatim($request)
{
	$url = 'http://nominatim.openstreetmap.org/search?q=' . urlencode($request) . '&format=json&polygon_geojson=1';
	$agent = 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_11_5) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/51.0.2704.106 Safari/537.36';

	$ch = curl_init();
	curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
	curl_setopt($ch, CURLOPT_VERBOSE, true);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
	curl_setopt($ch, CURLOPT_USERAGENT, $agent);
	curl_setopt($ch, CURLOPT_URL, $url);

	return curl_exec($ch);
}

//step 1: request nominatim
foreach ($list as $i => $city) {
	$request = "Российская Федерация, " . $city;
	$filename = 'nominatim/our_city' . $i . '.json';
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
	file_put_contents('out/our_city' . $j . '.php', $out);
}

foreach ($list as $j => $city) {
	echo 'Parsing ' . $j . "($city)\n";
	$filename = 'out/our_city' . $j . '.php';
	if (is_file($filename)) {
		echo 'Already done' . "\n";
		continue;
	}
	ob_start();
	echo "<?php \n";
	$foContent = file_get_contents('nominatim/our_city' . $j . '.json');
	$decoded = json_decode($foContent);

	for ($i = 0; $i < 5; $i++) {
		if (!isset($decoded[$i])) {
			continue;
		}
		$foParts = $decoded[$i]->geojson->coordinates;
		$type = $decoded[$i]->geojson->type;
		if (($type == 'MultiPolygon') || ($type == 'Polygon')) {
			break;
		}
	}

	if ($type == 'MultiPolygon') {
		foreach ($foParts as $i => $russiaP) {
			$russiaPart = $russiaP[0];

			$res = [];
			foreach ($russiaPart as $coord) {
				$res[] = $coord[0] . "," . $coord[1];
			}
			$text = $city . " " . ($i + 1);
			$md5 = md5($text);
			echo "\n" . '$result["' . $md5 . '"]=array(
			"text" => "' . $text . '",
			"coords" => "' . implode(";", $res) . "\n" . '");';
		}
		writeToFile($j);
	} else if ($type == 'Polygon') {
		$res = [];
		$foParts = $foParts[0];
		foreach ($foParts as $coord) {
			$res[] = $coord[0] . "," . $coord[1];
		}
		$text = $city;
		$md5 = md5($text);
		echo "\n" . '$result["' . $md5 . '"]=array(
			"text" => "' . $text . '",
			"coords" => "' . implode(";", $res) . "\n" . '");';

		writeToFile($j);
	} else {
		ob_end_clean();
		echo "Unknown " . $j . "($fo)\n";
	}
}

$out = "";
foreach ($list as $j => $city) {
	$filename = 'out/our_city' . $j . '.php';
	if (is_file($filename)) {
		$fileContent = file_get_contents($filename);
		$out .= $fileContent;
	}
}
file_put_contents('out/our_cities.php', $out);