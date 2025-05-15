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
	document.querySelector('#f_amount').value = o.dataset.max;
	document.querySelector('.reference').style.display = (o.dataset.type == 0) ? null : 'none';
}

if (pm) {
	pm.addEventListener('change', toggleMethod);
	toggleMethod();
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

		// Skip non-cash amounts
		if (!o.dataset.type) {
			diff = 0;
		}
		else {
			var amount = g.getMoneyAsInt(a.value);
			var max = g.getMoneyAsInt(o.dataset.max);
			var diff = amount - max;

			if (amount < 0) {
				diff = 0;
			}
		}

		g.toggle('form.payment .submit', diff <= 0);
		g.toggle('form.payment .toomuch', diff > 0);

		document.querySelector('form.payment .toomuch b').innerText = g.formatMoney(diff);
	}

	a.addEventListener('keyup', updatePaidAmount);

	pm.addEventListener('change', updatePaidAmount);

	document.querySelector('form.payment .toomuch button').onclick = () => {
		var o = pm.options[pm.selectedIndex];
		a.value = o.dataset.max;
		updatePaidAmount();
	};
}
