// Quick search field
var q = document.querySelector('input[name="q"]');

if (q) {
	var q_timeout;

	q.onkeyup = (e) => {
		if (e.key === 'Enter' && (a = document.querySelector('.products section button:not([hidden])'))) {
			a.click();
			return;
		}

		window.clearTimeout(q_timeout);
		q_timeout = window.setTimeout(searchProduct, 150);
		return true;
	};

	function searchProduct() {
		var search = g.normalizeString(q.value);
		var code = q.value.replace(/\s/, '');

		// Try to match barcodes
		if (code.match(/^\d{4,}$/)) {
			search = q.value;
			var count = 0;
			var item = null;

			document.querySelectorAll('.products section button').forEach((elm) => {
				var found = elm.hasAttribute('data-code') && elm.dataset.code.includes(search);

				if (found) {
					count++;
				}

				g.toggle(elm, found);
				elm.hidden = !found;

				if (found) {
					item = elm;
				}
			});

			if (count === 1 && item) {
				item.click()
			}
		}
		else {
			document.querySelectorAll('.products section button h3').forEach((elm) => {
				if (!elm.hasAttribute('data-search')) {
					// Add some cache
					elm.dataset.search = g.normalizeString(elm.innerText);
				}

				var found = elm.dataset.search.includes(search);
				g.toggle(elm.parentNode, found);
				elm.parentNode.hidden = !found;
			});
		}

		// Also hide complete sections if nothing matches
		document.querySelectorAll('.products section').forEach((s) => {
			g.toggle(s, s.querySelectorAll('button:not([hidden]').length > 0);
		});

		g.toggle('.pos .products ul', search.length === 0);
	}

	q.focus();
	enableBarcodeScanner();
}

function enableBarcodeScanner()
{
	var barcode_btn = $('#scanbarcode');

	if (!('BarcodeDetector' in window)) {
		if (window['barcodeDetectorPolyfill']) {
			window['BarcodeDetector'] = barcodeDetectorPolyfill.BarcodeDetectorPolyfill;
		}
		else {
			return;
		}
	}

	g.toggle(barcode_btn, true);
	barcode_btn.onclick = async () => {
		var video = document.createElement('video');
		video.style.width = '100%';
		video.style.height = '100%';
		video.autoplay = true;
			video.srcObject = await navigator.mediaDevices.getUserMedia({ audio: false, video: { facingMode: 'environment' } });

		g.openDialog(video, {"callback": async () => {
			try {
				const barcodeDetector = new BarcodeDetector({formats: ["ean_13"]});
				g.addDialogEvent('close', () => {
					var stream = video.srcObject;
					stream.getTracks().forEach(track => { track.stop(); stream.removeTrack(track); });
				});

				while (true) {
					var barcodes = await barcodeDetector.detect(video);

					if (barcodes.length == 0) {
						// The higher the interval the longer the battery lasts.
						await new Promise(r => setTimeout(r, 50));
						continue;
					}

					navigator.vibrate(200);
					q.value = barcodes[0].rawValue;
					g.closeDialog();
					searchProduct();
					return;
				}
			}
			catch (e) {
				alert("La détection du code barre ne semble pas fonctionner sur votre terminal");
				g.closeDialog();
				throw e;
			}
		}});
	};
}
