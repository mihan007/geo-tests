<?php
/**
 * Created by PhpStorm.
 * User: mihan007
 * Date: 25.07.16
 * Time: 21:22
 */
$param = isset($_GET['what']) ? $_GET['what'] : 'database';

require_once ('out/'.$param.'.php');

$js = [];

foreach ($result as $placeHash => $placeDescription)
{
	$coords = explode(";", $placeDescription['coords']);

	$result = [];
	$first = false;
	foreach ($coords as $coord)
	{
		$item = "[" . $coord . "]";
		if (!$first) {
			$first = $item;
		}
		$result[] = $item;
	}

	$yandex = implode(",", $result);

	$js[$placeHash] = '
		// Создаем многоугольник, используя вспомогательный класс Polygon.
		var myPolygon'.$placeHash.' = new ymaps.Polygon([
			// Указываем координаты вершин многоугольника.
			// Координаты вершин внешнего контура.
			[
				'. $yandex .'
			]
		], {
			// Описываем свойства геообъекта.
			// Содержимое балуна.
			hintContent: "'.$placeDescription['text'].'"
		}, {
			// Задаем опции геообъекта.
			// Цвет заливки.
			fillColor: \'#00FF0088\',
			// Ширина обводки.
			strokeWidth: 5
		});

		// Добавляем многоугольник на карту.
		myMap.geoObjects.add(myPolygon'.$placeHash.');
	';
}

$resultJs = implode("\n\n", $js);

?>

<!DOCTYPE html>
<html>
<head>
	<title>Примеры. Многоугольник</title>
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
	<!-- Если вы используете API локально, то в URL ресурса необходимо указывать протокол в стандартном виде (http://...)-->
	<script src="//api-maps.yandex.ru/2.1/?lang=ru_RU&coordorder=longlat" type="text/javascript"></script>
	<style>
		html, body, #map {
			width: 100%; height: 100%; padding: 0; margin: 0;
		}
	</style>
</head>
<body>
	<div id="map"></div>
</body>
<script>
	ymaps.ready(init);

	function init() {
		var myMap = new ymaps.Map("map", {
			center: <?= $first ?>,
			zoom: 3
		});

		<?= $resultJs ?>
	}
</script>
</html>

