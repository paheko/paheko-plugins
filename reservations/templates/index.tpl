<!DOCTYPE html>
<html>
<head>
	<meta charset="utf-8" />
	<title>Réservations</title>
	<link rel="stylesheet" type="text/css" href="{$www_url}admin/static/wiki.css" />
	<style type="text/css">
	{literal}
	* {
		margin: 0;
		padding: 0;
	}
	body {
		font-family: Verdana, Arial, Helvetica, sans-serif;
	}
	section {
		max-width: 60rem;
		margin: 1em auto;
	}

	h1 {
		text-align: center;
		font-size: 2em;
		margin: 1em 0;
	}

	fieldset {
		border: .1rem solid #ccc;
		padding: 1rem;
		text-align: center;
		border-radius: 1rem;
		font-size: 1.5em;
		margin: 2rem 0;
	}

	fieldset legend {
		padding: 0 .5em;
	}

	input {
		padding: .5rem 1rem;
		font-size: inherit;
		cursor: pointer;
	}

	fieldset dd {
		margin: .5em;
	}

	{/literal}
	{$css}
	</style>
</head>

<body>
<section>

<h1>Réservation de créneau</h1>

{include file="%s/_form.tpl"|args:$plugin_tpl ask_name=true}

</section>
</body>
</html>