<?php

$urls = [
	'https://raw.githubusercontent.com/mitchellkrogza/apache-ultimate-bad-bot-blocker/master/_generator_lists/good-user-agents.list',
	'https://raw.githubusercontent.com/mitchellkrogza/apache-ultimate-bad-bot-blocker/master/_generator_lists/limited-user-agents.list',
	'https://raw.githubusercontent.com/mitchellkrogza/apache-ultimate-bad-bot-blocker/master/_generator_lists/bad-user-agents.list',
];

echo '/';

foreach ($urls as $url) {
	$text = file($url);
	echo strtr(implode('|', array_filter(array_map('trim', $text))), [
		'/' => '\\/',
		'\\ ' => ' ',
	]);
}

echo '/';
echo PHP_EOL;
