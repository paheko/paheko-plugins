(function () {
	const main = document.querySelector('#chat');
	const container = document.querySelector('#chat .messages div');
	const chatbox = document.querySelector('#chat .chatbox');
	const chatbox_send_button = document.querySelector('#chat .chatbox footer button');
	const chatbox_input = chatbox.querySelector('textarea');
	const form = chatbox.querySelector('form');
	var bc = new BroadcastChannel("chat");

	function chatError(msg) {
		chatbox.className = 'block alert';
		chatbox.innerHTML = msg;
	}

	// Make sure we only have one active chat tab open
	bc.onmessage = (e) => {
		if (e.source === self) {
			return;
		}

		if (e.data === 'first') {
			bc.postMessage('second');
		}
		else if (e.data === 'second') {
			chatError(`<p>Une autre fenêtre de chat est ouverte dans ce navigateur.</p>
				<p>Cette fenêtre est désactivée. Les nouveaux messages ne seront pas reçus.</p>`);
			bc.close();
			bc = null;
		}
	};

	window.onload = () => {
		bc.postMessage('first');
		window.setTimeout(openChat, 200); // Set timeout, if no other tab is open
	};

	function openChat()
	{
		if (!bc) {
			return;
		}

		var last_message = container.lastElementChild;
		var current_user = last_message ? last_message.dataset.user : '';
		var current_day = last_message ? last_message.dataset.date : '';
		var last_seen_id = last_message ? last_message.dataset.id : '';

		chatbox_input.onkeydown = (e) => {
			if (e.key == 'Enter' && !e.ctrlKey && !e.shiftKey) {
				e.preventDefault();
				form.onsubmit();
				return false;
			}
		};

		form.onsubmit = sendMessage;

		chatbox_input.focus();

		var connection_attempts = 0;
		var sse;

		chatConnect();

		function chatConnect() {
			if (connection_attempts++ > 1) {
				chatError('<p>Erreur de connexion. Essayez de recharger cette page.</p>');
				return;
			}

			sse = new EventSource(g.admin_url + '/../../p/chat/connect.php?id='
				+ main.dataset.channelId+ '&current_user=' + current_user + '&current_day=' + current_day
				+ '&last_seen_id=' + last_seen_id);

			sse.onerror = () => setTimeout(chatConnect, 5000);
			sse.addEventListener('message_new', function(event) {
				container.innerHTML += JSON.parse(event.data);
			});
		}

		function sendMessage(e) {
			e.preventDefault();
			if (chatbox_input.value.trim() === '') {
				return false;
			}

			form.classList.add('progressing');

			fetch(form.action, {
				method: 'POST',
				mode: 'cors',
				cache: 'no-cache',
				headers: {"Accept": "application/json"},
				body: new FormData(form),
			}).then((r) => r.text()).then(() => {
				form.classList.remove('progressing');
				chatbox_input.value = '';
				chatbox_input.focus();
			});

			return false;
		}

		var emoji_selector = null;

		function buildEmojiSelector()
		{

		}

		function openEmojiSelector(callback)
		{
			if (emoji_selector === null) {
				buildEmojiSelector();
			}

			emoji_selector.querySelectorAll('button').forEach((e) => e.onclick = callback);
			emoji_selector.open = true;
			emoji_selector.top = XX;
			emoji_selector.left = XX;
		}

		// See https://github.com/mdn/dom-examples/blob/main/media/web-dictaphone/scripts/app.js
		var recorder;
		var recording_blob;
		var recording_url;
		var recorder_button = $('#record-button');
		var recorder_stop_button = $('#record-stop-button');
		var recorder_container = $('#recorder-container');
		recorder_button.onclick = startRecording;

		$('#record-stop-button').onclick = () => {
			recorder.stop();
			chatbox.classList.add('recorded');
		};

		$('#record-cancel-button').onclick = cancelRecording;

		async function startRecording()
		{
			chatbox.classList.add('audio');
			const config = {
				mediaTrackConstraints: {
					autoGainControl: true,
					noiseSuppression: true,
					echoCancellation: true,
				},
				encoderApplication: 2048, // Voice
				encoderBitRate: 16000,
				encoderPath: g.admin_url + '../../p/chat/opus/encoderWorker.min.js'
			};

			// https://github.com/chris-rudmin/opus-recorder
			g.script('../../p/chat/opus/recorder.min.js', () => {
				recorder = new Recorder(config);
				recorder.ondataavailable = (buffer) => {
					const audio = document.createElement("audio");
					audio.controls = true;

					recording_blob = new Blob([buffer], {type: 'audio/ogg'});
					recording_url = window.URL.createObjectURL(recording_blob);
					audio.src = recording_url;

					chatbox.classList.add('recorded');
					recorder_container.appendChild(audio);
					delete recorder;

					recorder_container.style.display = null;

					form.onsubmit = sendRecording;
					chatbox_send_button.disabled = false;
				};

				recorder.start();
			});

			recorder_container.style.display = 'none';
			chatbox.classList.remove('recorded');
			chatbox_send_button.disabled = true;
		}

		function cancelRecording()
		{
			if (recorder) {
				recorder = null;
			}

			if (recording_url) {
				window.URL.revokeObjectURL(recording_url);
				recording_url = null;
			}

			if (recording_blob) {
				recording_blob = null;
			}

			recorder_container.innerHTML = '';
			chatbox.classList.remove('audio');
			form.onsubmit = sendMessage;
			chatbox_send_button.disabled = false;
		}

		function sendRecording(e)
		{
			if (!recording_blob) {
				alert('No recording has been made');
			}

			form.classList.add('progressing');

			var fd = new FormData(form);
			fd.append('audio', recording_blob, 'recording.opus');
			fd.delete('message');

			fetch(form.action, {
				method: 'POST',
				mode: 'cors',
				cache: 'no-cache',
				headers: {"Accept": "application/json"},
				body: fd,
			}).then((r) => r.text()).then(() => {
				form.classList.remove('progressing');
				cancelRecording();
			});

			e.preventDefault();
			return false;
		}
	}

})();