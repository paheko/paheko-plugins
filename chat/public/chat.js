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
				+ main.dataset.channelId
				+ '&last_seen_id=' + last_seen_id);

			sse.onerror = () => setTimeout(chatConnect, 5000);
			sse.addEventListener('message', function(event) {
				var data = JSON.parse(event.data);
				var element = document.getElementById('msg-' + data.message.id);

				if (element) {
					element.outerHTML = data.html;
					element = document.getElementById('msg-' + data.message.id);
					addMessageEvents(element);
				}
				else if (data.message.id > last_message.dataset.id) {
					container.innerHTML += data.html;
					last_message = container.lastElementChild;
					addMessageEvents(last_message);
				}
				else {
					// Ignore messages out of current scope
					console.log('ignored', data.message, last_message.dataset.id);
				}
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

		function sendReaction(message, emoji)
		{
			var fd = new FormData(form);
			fd.append('reaction_emoji', emoji);
			fd.append('reaction_message_id', message.dataset.id);
			fd.delete('message');

			fetch(form.action, {
				method: 'POST',
				mode: 'cors',
				cache: 'no-cache',
				headers: {"Accept": "application/json"},
				body: fd,
			}).then((r) => r.text()).then(() => {
				g.closeDialog();
				emoji_selector = null;
			});
		}

		function openEmojiSelector(message)
		{
			g.script('../../p/chat/emojis.js', () => {
				emoji_selector = document.createElement('div');
				emoji_selector.className = 'content emoji-selector';
				emoji_selector.open = false;

				var search = document.createElement('input');
				search.type = 'search';
				search.className = 'full-width';
				search.oninput = () => {
					var s = search.value.trim().toLowerCase();
					if (selected = emoji_selector.querySelector('nav .selected')) {
						selected.classList.remove('selected');
					}

					if (s === '') {
						emoji_selector.querySelectorAll('section').forEach((e) => e.classList.toggle('hidden', true));
						emoji_selector.querySelectorAll('section button').forEach((e) => e.classList.toggle('hidden', false));
						emoji_selector.querySelector('nav').classList.remove('hidden');
						return;
					}

					emoji_selector.querySelectorAll('nav, section, section button').forEach((e) => e.classList.toggle('hidden', true));
					emoji_selector.querySelectorAll('section button').forEach((e) => {
						var match = e.dataset.text.includes(s);
						if (!match) {
							return;
						}
						e.parentNode.classList.toggle('hidden', !match);
						e.classList.toggle('hidden', !match);
					});
				};
				search.autofocus = true;
				emoji_selector.appendChild(search);

				var tabs = document.createElement('nav');

				for (const [cat_emoji, list] of Object.entries(emojis)) {
					const cat = document.createElement('section');
					cat.className = 'hidden';

					const b = document.createElement('button');
					b.innerText = cat_emoji;
					b.onclick = () => {
						emoji_selector.querySelectorAll('nav button').forEach((e) => e.classList.remove('selected'));
						b.classList.toggle('selected');

						if (b.classList.contains('selected')) {
							emoji_selector.querySelectorAll('section').forEach((e) => e.classList.add('hidden'));
							cat.classList.remove('hidden');
						}

						search.focus();
					};
					tabs.appendChild(b);

					for (const [emoji, match] of Object.entries(list)) {
						const e = document.createElement('button');
						e.onclick = () => {
							sendReaction(message, e.innerText);
						};
						e.innerText = emoji;
						e.dataset.text = match;
						cat.appendChild(e);
					}

					emoji_selector.appendChild(cat);
				}

				emoji_selector.insertBefore(tabs, search.nextSibling);

				g.openDialog(emoji_selector);
				search.focus();
			});
		}

		function addMessageEvents(message)
		{
			message.querySelector('footer [data-action="react"]').onclick = () => openEmojiSelector(message);
			message.querySelectorAll('.reactions button').forEach((e) => { e.onclick = () => sendReaction(message, e.dataset.emoji);});
		}

		$('.messages article').forEach((e) => addMessageEvents(e));

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

	window.openJitsi = () => {
		const jitsi_url = 'https://meet.jit.si/';
		var name = main.dataset.orgName + '--' + main.dataset.channelName;
		name = name.replace(/[^\w\p{L}-]+/gu, '_');
		name = name.replace(/--/, '/');
		window.open(jitsi_url + name + '?lang=fr#config.prejoinConfig.enabled=false&userInfo.displayName=' + encodeURIComponent('"' + main.dataset.userName + '"', 'jitsi'));
	};
})();