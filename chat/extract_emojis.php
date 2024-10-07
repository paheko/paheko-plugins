<?php
//https://github.com/julien-marcou/unicode-emoji
$fp = gzopen('unicode-emoji.csv.gz', 'r');

$header = null;
$i = 0;
$categories = [];
$categories_map = [
	'face-emotion' => '😀️',
	'food-drink' => '🥗',
	'animals-nature' => '🐢',
	'activities-events' => '♟️',
	'person-people' => '👶',
	'travel-places' => '✈️',
	'objects' => '👒',
	'symbols' => '💬️',
	//'flags' => '🚩',
];

while (!feof($fp)) {
	$line = fgetcsv($fp);

	if (!$line) {
		continue;
	}

	if (!$header) {
		$header = $line;
		continue;
	}

	$line = array_combine($header, $line);
	$line = (object) $line;

	if (!version_compare($line->version, '14', '<=')) {
		continue;
	}

	$cat = $categories_map[$line->category] ?? null;

	if (!$cat) {
		//var_dump($line);
		continue;
	}

	// Skip skin tone variations
	if (str_contains($line->description, 'skin tone')) {
		continue;
	}

	$categories[$cat] ??= [];
	$categories[$cat][$line->emoji] = $line->keywords;
}

echo json_encode($categories, JSON_UNESCAPED_UNICODE);
