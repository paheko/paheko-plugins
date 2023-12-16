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

	fetch(g.admin_url + 'p/caisse/_member_search.php?q=' + encodeURIComponent(v))
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
	ur_timeout = window.setTimeout(completeUserName, 300);
	return true;
};

document.querySelectorAll('input[name*="change_qty"], button[name*="change_price"], button[name*="change_weight"]').forEach((elm) => {
	var label;
	if (elm.name.match('change_qty')) {
		label = 'Saisir la quantité :';
	}
	else if (elm.name.match('change_weight')) {
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
		if (v === null) return false;
		elm.value = v;
	};
});

var pm = document.querySelector('select[name="method_id"]');

function toggleMethod() {
	var o = pm.options[pm.selectedIndex];
	console.log(o.dataset.reference);
	document.querySelector('#f_amount').value = o.getAttribute('data-amount');
	document.querySelector('.reference').style.display = (o.dataset.iscash != 1) ? null : 'none';
}

if (pm) {
	pm.onchange = toggleMethod;
	toggleMethod();
}

var q = document.querySelector('input[name="q"]');

RegExp.escape = function(string) {
  return string.replace(/[-\/\\^$*+?.()|[\]{}]/g, '\\$&')
};

function normalizeString(str) {
	return str.normalize('NFD').replace(/[\u0300-\u036f]/g, "")
}

if (q) {
	q.onkeyup = (e) => {
		var search = new RegExp(RegExp.escape(normalizeString(q.value)), 'i');

		document.querySelectorAll('.products button h3').forEach((elm) => {
			if (normalizeString(elm.innerText).match(search)) {
				elm.parentNode.hidden = false;
			}
			else {
				elm.parentNode.hidden = true;
			}

			// Apparently hidden does not work with <button>
			elm.parentNode.style.display = elm.parentNode.hidden ? 'none' : null;
		});

		// Also hide complete sections if nothing matches
		document.querySelectorAll('.products section').forEach((s) => {
			if (!s.querySelectorAll('button:not([hidden]').length) {
				s.style.display = 'none';
			}
			else {
				s.style.display = null;
			}
		});
	};

	q.focus();
}
