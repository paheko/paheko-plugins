var fr = document.querySelector('button[name="rename"]');


var ur = $('#user_rename');
var ur_input = $('#user_rename input[type=text]')[0];
var ur_id = $('[name="rename_id"]')[0];
var ur_list = $('#user_rename_list');
var ur_list_template = '';
var ur_timeout = null;
var ur_current = null;
var ur_keydown = null;

if (fr) {
	fr.onclick = function(e) {
		g.toggle(' #user_rename', true);
		ur_input.focus();
		ur_input.select();

		var ur_keydown = window.addEventListener('keydown', navigateUserRename);
		return false;
	}
}

ur.onclick = (e) => {
	if (e.target === ur) closeUserRename();
};

function closeUserRename () {
	window.removeEventListener('keydown', ur_keydown);
	g.toggle(' #user_rename', false);
	ur_input.value = '';
	$('#user_rename_list').innerHTML = '';
	return false;
}

function selectUserRename (id, name) {
	closeUserRename();
	ur_id.value = id;
	ur_input.value = name;
	ur_input.form.submit();
	return false;
}

function navigateUserRename(e) {
	if (e.key === 'Escape') {
		closeUserRename();
		return false;
	}

	if (e.key !== 'ArrowDown' && e.key !== 'ArrowUp') {
		return true;
	}

	var first = ur_list.querySelector('button');

	if (!first) {
		return true;
	}

	var last = ur_list.querySelector('button:last-child');

	if (e.key === 'ArrowDown') {
		ur_current = ur_current ? ur_current.nextElementSibling : first;
	}
	else {
		ur_current = ur_current ? ur_current.previousElementSibling : last;
	}

	if (ur_current) {
		ur_current.focus();
	}
	else {
		ur_input.focus();
		ur_input.select();
	}

	e.preventDefault();
	return false;
}

function completeUserName(list) {
	var v = ur_input.value.replace(/^\s+|\s+$/g, '');

	if (!v.match(/^\d+$/) && v.length < 3) {
		return false;
	}

	fetch(g.admin_url + 'p/caisse/user_search.php?q=' + encodeURIComponent(v))
		.then(response => response.text())
		.then(list => {
			ur_list.innerHTML = list;
			ur_list.querySelectorAll('button').forEach(btn => btn.onclick = (e) => {
				selectUserRename(btn.dataset.id, btn.dataset.name);
			});
		});
}

ur_input.onkeyup = (e) => {
	window.clearTimeout(ur_timeout);
	ur_timeout = window.setTimeout(completeUserName, 200);
	return true;
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
	document.querySelector('.reference').style.display = (o.dataset.iscash != 1) ? null : 'none';
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
		window.clearTimeout(q_timeout);
		q_timeout = window.setTimeout(searchProduct, 150);
		return true;codes
	};

	function searchProduct() {
		var search = g.normalizeString(q.value);
		var code = q.value.replace(/\s/, '');

		// Try to match barcodes
		if (code.match(/^\d+$/)) {
			search = q.value;

			document.querySelectorAll('.products button').forEach((elm) => {
				var found = elm.hasAttribute('data-code') && elm.dataset.code.includes(search);

				if (found && code.length === 13) {
					elm.click();
					return;
				}

				g.toggle(elm, found);
				elm.hidden = !found;
			});
		}
		else {
			document.querySelectorAll('.products button h3').forEach((elm) => {
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

var show_button = $('.pos-tabs .plus')[0];
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
