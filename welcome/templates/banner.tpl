{literal}
<style type="text/css">
.block.banner {
	background: rgba(222,147,29,0.4);
	color: #000;
	border-radius: 1em;
	padding: 1em;
}
.banner svg {
	stroke: #3771c8;
	float: right;
	width: 120px;
	height: 120px;
}
.banner a {
	background: rgba(255, 255, 255, 0.7);
	color: #000;
	border-color: #fff;
	font-size: 1.3em;
	margin: 0;
}
.banner a::after {
	content: "";
	display: inline-block;
	width: 24px;
	height: 24px;
	vertical-align: middle;
	margin-left: .5rem;
	background: no-repeat center center url('data:image/svg+xml;utf8,<svg viewBox="0 0 64 64" xmlns="http://www.w3.org/2000/svg"><circle cx="32" cy="32" fill="%23ffdd67" r="30"/><g fill="%23664e27"><circle cx="20.5" cy="26.6" r="5"/><circle cx="43.5" cy="26.6" r="5"/><path d="m44.6 40.3c-8.1 5.7-17.1 5.6-25.2 0-1-.7-1.8.5-1.2 1.6 2.5 4 7.4 7.7 13.8 7.7s11.3-3.6 13.8-7.7c.6-1.1-.2-2.3-1.2-1.6"/></g></svg>');
}

</style>
{/literal}

<div class="block banner">
	<svg stroke="red"><use xlink:href='p/welcome/icon.svg#img' href="p/welcome/icon.svg#img"></use></svg>
	<h3>Paheko vous plaît&nbsp;?</h3>
	<p>Ce logiciel est financé grâce aux dons des associations qui l'utilisent&nbsp;: aidez-nous à continuer&nbsp;!</p>
	<p class="btn">{linkbutton href="https://fossil.kd2.org/paheko/wiki?name=Contribuer" target="_blank" label="Cliquer ici pour contribuer"}</p>
</div>
