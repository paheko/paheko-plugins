console.log(g);
const evtSource = new EventSource(g.admin_url + "/../../p/chat/connect.php?id=1");

evtSource.addEventListener("message_new", function(event) {
	console.log(event);

	// désérialisation de l'événement reçu
	//var evtData = JSON.parse(event.data);
});