<?php

namespace Paheko\Plugin\Discuss;

class Forums
{
	static public function message_format(string $msg, ?string $query = null): string
	{
		if (substr(trim($str), 0, 1) == '<') {
			$str = strip_tags($str);
		}

		$str = htmlspecialchars($str);
		$str = preg_replace('/^\s*&gt;.*$/m', '<i>\\0</i>', $str);
		$str = preg_replace('/^(?:On|Le)\s.*(?:écrit|wrote).*:\s*$/m', '<i>\\0</i>', $str);
		$str = wordwrap($str, 90);

		if ($query) {
			$str = self::highlight($str, $query);
		}

		return $str;
	}

	static public function highlight(string $text, string $query): string
	{
		$text = \Normalizer::normalize($text, \Normalizer::FORM_KD);
		$query = \Normalizer::normalize($query, \Normalizer::FORM_KD);
		$query = preg_quote($query, '/');
		return preg_replace('/' . $query . '(?![^"]*">)/ui', '<mark>\\0</mark>', $text);
	}


}
