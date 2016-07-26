<?php
/**
 * Created by PhpStorm.
 * User: mihan007
 * Date: 26.07.16
 * Time: 11:53
 */

$list = [
	'Алтайский край',
	'Архангельская область',
	'Астраханская область',
	'Белгородская область',
	'Брянская область',
	'Владимирская область',
	'Волгоградская область',
	'Вологодская область',
	'Воронежская область',
	'Еврейская автономная область',
	'Забайкальский край',
	'Ивановская область',
	'Иркутская область',
	'Калининградская область',
	'Калужская область',
	'Камчатский край',
	'Кемеровская область',
	'Кировская область',
	'Костромская область',
	'Краснодарский край',
	'Красноярский край',
	'Курганская область',
	'Курская область',
	'Ленинградская область',
	'Липецкая область',
	'Московская область',
	'Мурманская область',
	'Нижегородская область',
	'Новгородская область',
	'Новосибирская область',
	'Омская область',
	'Оренбургская область',
	'Орловская область',
	'Пензенская область',
	'Пермский край',
	'Приморский край',
	'Псковская область',
	'Республика Адыгея',
	'Республика Алтай',
	'Республика Башкортостан',
	'Республика Бурятия',
	'Республика Дагестан',
	'Республика Кабардино-Балкария',
	'Республика Калмыкия',
	'Республика Карачаево-Черкесия',
	'Республика Карелия',
	'Республика Коми',
	'Республика Крым',
	'Республика Марий Эл',
	'Республика Мордовия',
	'Республика Саха (Якутия)',
	'Республика Северная Осетия-Алания',
	'Республика Татарстан',
	'Республика Тыва',
	'Республика Удмуртия',
	'Республика Хакасия',
	'Республика Чечня',
	'Чувашская республика',
	'Ростовская область',
	'Рязанская область',
	'Самарская область',
	'Саратовская область',
	'Сахалинская область',
	'Свердловская область',
	'Смоленская область',
	'Ставропольский край',
	'Тамбовская область',
	'Тверская область',
	'Томская область',
	'Тульская область',
	'Тюменская область',
	'Ульяновская область',
	'Хабаровский край',
	'Ханты-Мансийский автономный округ - Югра',
	'Челябинская область',
	'Ямало-Ненецкий автономный округ',
	'Ярославская область',
   'Магаданская область',
   'Чукотский автономный округ',
   'Ненецкий автономный округ',
   'Амурская область',
   'Севастополь',
   'Москва',
   'Санкт-Петербург'
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
foreach ($list as $i => $fo) {
	$request = "Российская Федерация, " . $fo;
	$filename = 'nominatim/region' . $i . '.json';
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
	file_put_contents('out/region' . $j . '.php', $out);
}

foreach ($list as $j => $fo) {
	echo 'Parsing ' . $j . "($fo)\n";
	$filename = 'out/region'.$j.'.php';
	if (is_file($filename)) {
		echo 'Already done'."\n";
		continue;
	}
	ob_start();
	echo "<?php \n";
	$foContent = file_get_contents('nominatim/region' . $j . '.json');
	$decoded = json_decode($foContent);

	for ($i = 0; $i < 5; $i++) {
		if (!isset($decoded[$i])) {
			continue;
		}
		if ($decoded[$i]->type != 'administrative') {
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
			$text = $fo . " " . ($i + 1);
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
		$text = $fo;
		$md5 = md5($text);
		echo "\n" . '$result["' . $md5 . '"]=array(
			"text" => "' . $text . '",
			"coords" => "' . implode(";", $res) . "\n" . '");';

		writeToFile($j);
	} else {
		ob_end_clean();
		echo "Unknown ".$j."($fo)\n";
	}
}

$out = "";
foreach ($list as $j => $fo) {
	$filename = 'out/region'.$j.'.php';
	if (is_file($filename)) {
		$fileContent = file_get_contents($filename);
		$out .= $fileContent;
	}
}
file_put_contents('out/region.php', $out);