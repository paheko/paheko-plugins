{include file="_head.tpl" title="Carte des membres"}

<nav class="tabs">
	<ul>
		<li><a href="./">Statistiques</a></li>
		<li class="current"><a href="map.php">Carte</a></li>
	</ul>
</nav>

<div id="map" style="width: 100%; height: 80vh; margin: 1em 0;"></div>

<script src="leaflet/leaflet-src.js"></script>
<script src="leaflet/heat.js"></script>

<script type="text/javascript">
var list = {$list|escape:'json'};
var center = {$center|escape:'json'};
{literal}
var tiles = L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
		maxZoom: 18,
		attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors, Points &copy 2012 LINZ'
	}),
	latlng = L.latLng(center.lat, center.lon);

var map = L.map('map', {center: latlng, zoom: 11, layers: [tiles]});

addressPoints = list.map(function (p) { return [p.lat, p.lon]; });

var heat = L.heatLayer(addressPoints, {minOpacity: 0.1}).addTo(map);
{/literal}
</script>

{include file="_foot.tpl"}