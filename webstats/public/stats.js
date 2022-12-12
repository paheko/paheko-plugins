(function () {
	const ua = window.navigator.userAgent;

	const is_bot = /Googlebot|Bingbot|Slurp|DuckDuckBot|Baiduspider|YandexBot|Exabot|facebookexternalhit|facebot|curl|ia_archiver|GoogleImageProxy|MJ12bot|MegaIndex|https?:\/\/|urllib|libwww|Yacy|wget/i.test(ua)

	if (is_bot) {
		return;
	}

	const is_new_visitor = !/__visitor/.test(document.cookie);
	const is_mobile = /Mobile|iPhone|Android|Opera Mobi|Opera Mini|UCBrowser|SamsungBrowser/.test(ua)
	const uri = window.location.pathname;

	if (is_new_visitor) {
		document.cookie = '__visitor=1; path=/; max-age=' + (3600*6);
	}

	const url = document.currentScript.src.replace(/\/[^\/]*$/, '/stats.php');

	data = {is_mobile, is_new_visitor, uri};

	fetch(url, {
		headers: {
		  'Accept': 'application/json',
		  'Content-Type': 'application/json'
		},
		body: JSON.stringify(data),
		method: 'POST'
	});
})();