#!/usr/bin/php
<?php

$argv = $_SERVER['argv'];

if (count($argv) < 3) {
		echo "Invalid call.\n";
		exit(0);
}

$to = $argv[1];
$url = $argv[2];

// Fetch message from STDIN
stream_set_blocking(STDIN, false);
$message = file('php://stdin');

// Ignore first non-headers lines, eg. "From email@domain.tld Date"
foreach ($message as $k => $line) {
	if (preg_match('/^[^\s:]+:/', $line)) {
		break;
	}
	else {
		unset($message[$k]);
	}
}

$message = implode('', $message);

if (strlen($message) < 1) {
	echo "No message content was supplied.\r\n";
	exit(1);
}

$ch = curl_init(sprintf($url, rawurlencode($to)));

curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: message/rfc822; charset=utf-8']);
curl_setopt($ch, CURLOPT_HEADER, false);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, false);
curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'PUT');
curl_setopt($ch, CURLOPT_POSTFIELDS, $message);

$response = curl_exec($ch);

if ($e = curl_error($ch)) {
	curl_close($ch);
	printf("Webhook temporarily unavailable: %s\r\n", $e);
	exit(75);
}

$code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

if ($code != 204) {
	printf("Webhook failed (%d): %s\r\n", $code, $response);
	exit(75);
}

exit(1);
