var t = $('#f_title');
var start_time = $('#f_start_time');
var end_time = $('#f_end_time');
var all_day = $('#f_all_day_1');
var start_day = $('#f_start');
var end_day = $('#f_end');
var loc = $('#f_location');
var id_category = $('#f_id_category');

var pr = document.createElement("span");
pr.className = "cat_color";
id_category.parentNode.appendChild(pr);

function selectCategory() {
	var hue = '';
	var reminder = null;

	if (id_category.value in categories) {
		hue = categories[id_category.value].color;
		reminder = categories[id_category.value].default_reminder;
	}

	pr.style = '--hue: ' + hue;

	if (reminder !== null) {
		$('#f_reminder').value = reminder;
	}
}

id_category.onchange = selectCategory;

selectCategory();

function dateToInteger(str) {
	var r = str.match(/^(\d+)\/(\d+)\/(\d+)$/);

	if (!r) {
		return null;
	}

	return parseInt(r[3] + r[2] + r[1], 10);
}

function formatTime(h, m) {
	h = ('00' + h).substr(-2);
	m = ('00' + m).substr(-2);

	if (h >= 24) {
		h = '00';
	}

	if (m >= 60) {
		m = '00';
	}

	return h + ':' + m;
}

function changeAllDay() {
	if (all_day.checked) {
		start_time.value = '00:00';
		end_time.value = '00:00';
	}

	start_time.disabled = all_day.checked;
	end_time.disabled = all_day.checked;
}

function changeTime() {
	if (start_time.value === '00:00'
		&& end_time.value === '00:00') {
		all_day.checked = true;
	}
	else {
		all_day.checked = false;
	}

	changeAllDay();
}

function changeStartDay() {
	var s = dateToInteger(start_day.value);
	var e = dateToInteger(end_day.value);

	if (s && s > e) {
		end_day.value = start_day.value;
		animateInput(end_day);
	}
}

start_time.onchange = changeTime;
end_time.onchange = changeTime;
all_day.onchange = changeAllDay;
start_day.oninput = changeStartDay;

changeTime();

function ciEquals(a, b) {
	return typeof a === 'string' && typeof b === 'string'
		? a.localeCompare(b, undefined, { sensitivity: 'accent' }) === 0
		: a === b;
}

t.oninput = () => {
	var v = t.value;

	// Extract time from title
	if (match = v.match(/^(\d{1,2})(?:[:h.](\d*))?(?:->?(\d+)[:h.](\d+)?)?\s+/i)) {
		t.value = v.substr(match[0].length);
		start_time.value = formatTime(match[1], match[2] ?? 0);

		if (match[3] ?? null) {
			end_time.value = formatTime(match[3], match[4] ?? 0);
		}
		else {
			end_time.value = formatTime(parseInt(match[1], 10) + 1, match[2] ?? 0);
		}

		animateInput(start_time);
		animateInput(end_time);
		animateInput(all_day);
		all_day.checked = false;
		changeTime();
	}
	else if (t.value.match(/^-\s+/)) {
		start_time.value = '00:00';
		end_time.value = '00:00';
		all_day.checked = true;
	}

	if (m = t.value.match(/\s+#([^~]+)$/)) {
		Object.values(categories).forEach((cat) => {
			if (ciEquals(cat.title, m[1])) {
				id_category.value = cat.id;
				animateInput(id_category);
				t.value = t.value.substr(0, t.value.length - m[0].length);
				selectCategory();
			}
		});
	}

	if (t.value !== v) {
		animateInput(t, 800);
	}
	t.value = t.value.replace(/^\s+/, '');

	return true;
};

function animateInput(elm, duration = 2000) {
	elm.classList.add('auto-animation');
	window.setTimeout(() => elm.classList.remove('auto-animation'), 1000);
}
