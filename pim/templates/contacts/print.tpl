<!DOCTYPE html>
<html>
<head>
	<title>Impression</title>
	<style type="text/css">
	{literal}
	@page { size: A4 landscape; margin: 1.2cm; padding: 0;}
	* { margin: 0; padding: 0; }
	body { font-family: sans-serif; columns: 4; font-size: 10pt;}
	ul {
		list-style-type: none;
	}
	h1 {
		background: #000;
		color: #fff;
		margin: 0 .5em;
		padding: .5em;
		font-size: 1.4em;
		margin-left: -.5em;
	}
	h2 {
		font-size: 1.2em;
	}
	h3 {
		font-size: 1em;
		font-weight: normal;
		font-style: italic;
		clear: both;
	}
	div {
		margin: 1em;
		break-inside: avoid;
	}
	ul {
		border-top: 1px solid #999;
	}
	{/literal}
	</style>
</head>
<body>

<?php $letter = null; ?>

{foreach from=$list item="contact"}
	<?php
	$l = strtoupper(Utils::unicodeTransliterate(mb_substr(trim($contact->getFullName()), 0 , 1)));

	if ($l !== $letter)
	{
		printf('<h1>%s</h1>', htmlspecialchars($l));
		$letter = $l;
	}
	?>

	<div>

	<h2>{$contact->getFullName()}</h2>

	{if $contact.title}
		<h3>{$contact.title}</h3>
	{/if}

	<ul>

	{if $contact.mobile_phone}
		<li>{$contact.mobile_phone}</li>
	{/if}

	{if $contact.phone}
		<li>{$contact.phone}</li>
	{/if}

	{if $contact.email}
		<li>{$contact.email}</li>
	{/if}

	{if $contact.address}
		<li>{$contact.address|escape|nl2br}</li>
	{/if}

	</ul>
	</div>
{/foreach}

</body>
</html>
