<?php
/**
 * Created by PhpStorm.
 * User: mihan007
 * Date: 26.07.16
 * Time: 11:53
 */

$json = '{"Адыгея":"Майкоп","Республика Алтай":"Горно-Алтайск","Башкортостан":"Уфа","Бурятия":"Улан-Удэ","Дагестан":"Махачкала","Ингушетия":"Магас","Кабардино-Балкария":"Нальчик","Калмыкия":"Элиста","Карачаево-Черкесия":"Черкесск","Республика Карелия":"Петрозаводск","Республика Коми":"Сыктывкар","Республика Крым":"Симферополь","Марий Эл":"Йошкар-Ола","Мордовия":"Саранск","Якутия":"Якутск","Северная Осетия":"Владикавказ","Татарстан":"Казань","Тыва":"Кызыл","Удмуртия":"Ижевск","Хакасия":"Абакан","Чечня":"Грозный","Чувашия":"Чебоксары","Алтайский край":"Барнаул","Забайкальский край":"Чита","Камчатский край":"Петропавловск-Камчатский","Краснодарский край":"Краснодар","Красноярский край":"Красноярск","Пермский край":"Пермь","Приморский край":"Владивосток","Ставропольский край":"Ставрополь","Хабаровский край":"Хабаровск","Амурская область":"Благовещенск","Архангельская область":"Архангельск","Астраханская область":"Астрахань","Белгородская область":"Белгород","Брянская область":"Брянск","Владимирская область":"Владимир (город)","Волгоградская область":"Волгоград","Вологодская область":"Вологда","Воронежская область":"Воронеж","Ивановская область":"Иваново","Иркутская область":"Иркутск","Калининградская область":"Калининград","Калужская область":"Калуга","Кемеровская область":"Кемерово","Кировская область":"Киров (Кировская область)","Костромская область":"Кострома","Курганская область":"Курган (город)","Курская область":"Курск","Ленинградская область":"Санкт-Петербург","Липецкая область":"Липецк","Магаданская область":"Магадан","Московская область":"Москва","Мурманская область":"Мурманск","Нижегородская область":"Нижний Новгород","Новгородская область":"Великий Новгород","Новосибирская область":"Новосибирск","Омская область":"Омск","Оренбургская область":"Оренбург","Орловская область":"Орёл (город)","Пензенская область":"Пенза","Псковская область":"Псков","Ростовская область":"Ростов-на-Дону","Рязанская область":"Рязань","Самарская область":"Самара","Саратовская область":"Саратов","Сахалинская область":"Южно-Сахалинск","Свердловская область":"Екатеринбург","Смоленская область":"Смоленск","Тамбовская область":"Тамбов","Тверская область":"Тверь","Томская область":"Томск","Тульская область":"Тула","Тюменская область":"Тюмень","Ульяновская область":"Ульяновск","Челябинская область":"Челябинск","Ярославская область":"Ярославль","Москва":"Москва","Санкт-Петербург":"Санкт-Петербург","Севастополь":"Севастополь","Еврейская автономная область":"Биробиджан","Ненецкий автономный округ":"Нарьян-Мар","Ханты-Мансийский автономный округ — Югра":"Ханты-Мансийск","Чукотский автономный округ":"Анадырь","Ямало-Ненецкий автономный округ":"Салехард"}';
$list = json_decode($json);

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
$i=0;
foreach ($list as $region => $city) {
	$request = "Российская Федерация, " . $region.", ".$city;
	$filename = 'nominatim/city' . $i . '.json';
	if (!is_file($filename)) {
		echo $request . "\n";
		$result = requestNominatim($request);
		file_put_contents($filename, $result);
	}
	$i++;
}

/**
 * @param $j
 */
function writeToFile($j)
{
	$out = ob_get_contents();
	$out .= "?>\n";
	ob_end_clean();
	file_put_contents('out/city' . $j . '.php', $out);
}

$j = 0;
foreach ($list as $region => $city) {
	echo 'Parsing ' . $j . "($region, $city)\n";
	$filename = 'out/city'.$j.'.php';
	if (is_file($filename)) {
		echo 'Already done'."\n";
		$j++;
		continue;
	}
	ob_start();
	echo "<?php \n";
	$foContent = file_get_contents('nominatim/city' . $j . '.json');
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
		echo "Unknown ".$j."($fo)\n";
	}
	$j++;
}

$out = "";
$j = 0;
foreach ($list as $region => $city) {
	$filename = 'out/city'.$j.'.php';
	if (is_file($filename)) {
		$fileContent = file_get_contents($filename);
		$out .= $fileContent;
	}
	$j++;
}
file_put_contents('out/cities.php', $out);