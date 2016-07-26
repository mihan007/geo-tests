<?php
/**
 * Created by PhpStorm.
 * User: mihan007
 * Date: 26.07.16
 * Time: 11:53
 */

$foList = [
	"Центральный федеральный округ",
	"Северо-Западный федеральный округ",
	"Южный федеральный округ",
	"Северо-Кавказский федеральный округ",
	"Приволжский федеральный округ",
	"Уральский федеральный округ",
	"Сибирский федеральный округ",
	"Дальневосточный федеральный округ",
	"Крымский федеральный округ",
];

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
//foreach ($foList as $i => $fo) {
//	$request = "Россия, " . $fo;
//	$result = requestNominatim($request);
//	file_put_contents('nominatim/fo'.$i.'.json', $result);
//}


foreach ($foList as $j => $fo) {
	ob_start();
	echo "<?php \n";
	$foContent = file_get_contents('nominatim/fo' . $j . '.json');
	$decoded = json_decode($foContent);
	$foParts = $decoded[0]->geojson->coordinates;
	$type = $decoded[0]->geojson->type;

	if ($type == 'MultiPolygon') {
		foreach ($foParts as $i => $russiaP) {
			$russiaPart = $russiaP[0];

			$res = [];
			foreach ($russiaPart as $coord) {
				$res[] = $coord[0] . "," . $coord[1];
			}
			$text = $fo . " " . ($i + 1);
			$md5 = md5($text);
			echo "\n" . '$result["' . $md5 . '"]=array(
			"text" => "' . $text . '",
			"coords" => "' . implode(";", $res) . "\n" . '");';
		}
	} else if ($type == 'Polygon') {
		$res = [];
		$foParts = $foParts[0];
		foreach ($foParts as $coord) {
			$res[] = $coord[0] . "," . $coord[1];
		}
		$text = $fo;
		$md5 = md5($text);
		echo "\n" . '$result["' . $md5 . '"]=array(
			"text" => "' . $text . '",
			"coords" => "' . implode(";", $res) . "\n" . '");';
	} else {
		throw new Exception('Unknown type: ' . $type);
	}

	$out = ob_get_contents();
	ob_end_clean();
	file_put_contents('out/fo' . $j . '.php', $out);

	$out .= "?>\n\n";
	file_put_contents('out/fo' . '.php', $out, FILE_APPEND);
}