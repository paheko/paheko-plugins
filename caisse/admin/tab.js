var show_button = $('#showBtn');
var hidden = 0;

$('.pos-tabs li.tab').forEach((elm) => {
	var parent_height = elm.parentNode.offsetHeight;
	if (elm.offsetTop > parent_height) {
		hidden++;
	}
});

if (!hidden) {
	show_button.remove();
}
else {
	show_button.onclick = () => {
		$('.pos-tabs')[0].classList.add('open');
		show_button.remove();
		return false;
	};
}

if (!document.querySelector('.pos')) {
	throw 'Not in a tab';
}

function renameTabUser(id, name) {
	var form = document.querySelector('form input[name="rename_id"]').form;
	form.rename_id.value = id;
	form.rename_name.value = name;
	form.submit();
}

document.querySelector('#tab_user_rename').onclick = () => {
	g.openFrameDialog('./user_search.php?_dialog');
	return false;
};

document.querySelectorAll('input[name*="change_qty"], button[name*="change_price"], button[name*="change_weight"]').forEach((elm) => {
	var label;
	if (elm.name.includes('change_qty')) {
		label = 'Saisir la quantité :';
	}
	else if (elm.name.includes('change_weight')) {
		label = 'Saisir le poids (en kilogrammes) :';
	}
	else {
		label = 'Saisir le prix :'
	}

	elm.onclick = (e) => {
		var v = prompt(label, elm.value);
		if (v === null) return false;
		elm.value = v;
	};
});

document.querySelectorAll('button[name*="rename_item"]').forEach((elm) => {
	elm.onclick = (e) => {
		var v = prompt('Renommer ce produit :', elm.value);
		if (v === null) return false;
		elm.value = v;
	};
});

document.querySelectorAll('button[data-ask-weight]').forEach((elm) => {
	elm.onclick = (e) => {
		var label = 'Saisir le poids (en kilogrammes) :';
		var v = prompt(label, elm.value);
		if (!v) return false;
		elm.value = v;
	};
});

var pm = document.querySelector('select[name="method_id"]');

function toggleMethod() {
	var o = pm.options[pm.selectedIndex];
	document.querySelector('#f_amount').value = o.getAttribute('data-amount');
	document.querySelector('.reference').style.display = (o.dataset.type == 0) ? null : 'none';
}

if (pm) {
	pm.onchange = toggleMethod;
	toggleMethod();
}

// Quick search field
var q = document.querySelector('input[name="q"]');
var list = null;

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
				item = elm;
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


$('.products ul li a').forEach((elm) => {
	elm.onclick = () => {
		elm.parentNode.parentNode.querySelector('.current').classList.remove('current');
		elm.parentNode.classList.add('current');
		q.focus();

		if (!elm.dataset.cat) {
			g.toggle('.products section', true);
			history.replaceState( {} , 'foo', window.location.href.replace(/&cat=\d+|$/, ''));
		}
		else {
			g.toggle('.products section', false);
			g.toggle('.products section[data-cat="' + elm.dataset.cat + '"]', true);
			history.replaceState( {} , 'foo', window.location.href.replace(/&cat=\d+|$/, '&cat=' + elm.dataset.cat));
		}
		return false;
	};
});

if (a = $('#f_amount')) {
	function updatePaidAmount() {
		var o = pm.options[pm.selectedIndex];

		if (!o.dataset.type) {
			return;
		}

		var max = g.getMoneyAsInt(o.dataset.amount);
		var diff = g.getMoneyAsInt(a.value) - max;

		g.toggle('form.payment .submit', diff <= 0);
		g.toggle('form.payment .toomuch', diff > 0);

		document.querySelector('form.payment .toomuch b').innerText = g.formatMoney(diff);
	}

	a.addEventListener('keyup', updatePaidAmount);

	document.querySelector('form.payment .toomuch button').onclick = () => {
		var o = pm.options[pm.selectedIndex];
		a.value = o.dataset.amount;
		updatePaidAmount();
	};
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
