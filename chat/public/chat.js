var main = document.querySelector('#chat');

var container = document.querySelector('#chat .messages div');
var last_message = container.lastElementChild;
var current_user = last_message.dataset.user || '';
var current_day = last_message.dataset.date || '';
var last_seen_id = last_message.dataset.id || '';

var input = document.querySelector('#chat .chatbox textarea');

input.onkeydown = (e) => {
	if (e.key == 'Enter') {
		input.form.querySelector('button').click();
		e.preventDefault();
		return false;
	}
};

input.form.onsubmit = (e) => {
	if (input.value.trim() === '') {
		return false;
	}

	fetch(input.form.action, {
		method: 'POST',
		mode: 'cors',
		cache: 'no-cache',
		headers: {"Accept": "application/json"},
		body: new FormData(input.form),
	}).then((r) => r.text()).then(() => input.value = '');

	e.preventDefault();
	return false;
};

input.focus();

const evtSource = new EventSource(g.admin_url + '/../../p/chat/connect.php?id=' 
	+ main.dataset.channelId+ '&current_user=' + current_user + '&current_day=' + current_day
	+ '&last_seen_id=' + last_seen_id);

evtSource.addEventListener('message_new', function(event) {
	container.innerHTML += JSON.parse(event.data);
});
