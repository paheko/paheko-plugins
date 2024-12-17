function addEvent(date, date_end)
{
	var r = window.prompt("Heure et titre de l\'événement ?\nExemple : 17h30-18h00 RDV Bureau #Travail (5 rue de l'Adresse, 21000 Dijon)");

	if (typeof r == "string")
	{
		var offset = -(new Date).getTimezoneOffset();
		var url = "./event_new.php?start="+date+"&end=" + date_end + "&offset=+"+encodeURIComponent(offset)+"&title="+encodeURIComponent(r);


		location.href = url;
	}

	return false;
}

var agenda = document.querySelector("table.calendar");
var selectedCells = [];
var firstSelectedCell, lastSelectedCell;

function selectCells() {
	selectedCells = [firstSelectedCell, lastSelectedCell];
	sortCells();

	var firstRow = selectedCells[0].parentNode.rowIndex;
	var lastRow = selectedCells[selectedCells.length - 1].parentNode.rowIndex;
	var firstCell = selectedCells[0].cellIndex;
	var lastCell = selectedCells[selectedCells.length - 1].cellIndex;

	agenda.querySelectorAll('td').forEach(function (td) {
		td.classList.remove('selected');
	});

	for (var i = firstRow; i <= lastRow; i++) {

		var start = 0, end = 6;

		if (firstRow == i) {
			start = firstCell;
		}
		if (lastRow == i) {
			end = lastCell;
		}

		for (var j = start; j <= end; j++) {
			var cell = agenda.rows[i].cells[j];
			cell.classList.add("selected");
		}
	}
}

function sortCells() {
	// Ordonner les cellules
	selectedCells.sort(function (a, b) {
		if (a.parentNode.rowIndex == b.parentNode.rowIndex) return a.cellIndex > b.cellIndex ? 1 : -1;
		return (a.parentNode.rowIndex > b.parentNode.rowIndex) ? 1 : -1;
	});
}

function getCellTarget(e) {
	if (e.target.tagName == 'H3') {
		return e.target.parentNode;
	}
	else if (e.target.tagName == 'TD') {
		return e.target;
	}
	else {
		return null;
	}

}

agenda.onmousedown = function (e) {
	var target = getCellTarget(e);
	if (null !== target && target.tagName == "TD" && target.hasAttribute("data-date")) {
		firstSelectedCell = lastSelectedCell = target;
		selectCells();
		return false;
	}
};

agenda.onmouseover = function (e) {
	var target = getCellTarget(e);
	if (null !== target && selectedCells.length && target.tagName == "TD" && target.hasAttribute("data-date")) {
		lastSelectedCell = target;
		selectCells();
		return false;
	}
};

agenda.onmouseup = function (e) {
	var target = getCellTarget(e);
	if (null !== target && target.tagName == "TD" && selectedCells.length && target.hasAttribute("data-date")) {
		lastSelectedCell = target;
		selectCells();
		addEvent(selectedCells[0].getAttribute("data-date"), selectedCells[selectedCells.length - 1].getAttribute("data-date"));
	}

	selectedCells.forEach(function (cell) {
		cell.classList.remove("selected");
	})
	selectedCells = [];
	firstSelectedCell = lastSelectedCell = null;
}


var dragcounter = 0;

window.addEventListener('dragover', (e) => {
	e.preventDefault();
	e.stopPropagation();
});

window.addEventListener('dragenter', (e) => {
	e.preventDefault();
	e.stopPropagation();

	if (!dragcounter) {
		document.body.classList.add('dragging');
	}

	dragcounter++;
});

window.addEventListener('dragleave', (e) => {
	e.preventDefault();
	e.stopPropagation();
	dragcounter--;

	if (!dragcounter) {
		document.body.classList.remove('dragging');
	}
});


// Drop ICS file
window.addEventListener('drop', (e) => {
	e.preventDefault();
	e.stopPropagation();

	document.body.classList.remove('dragging');
	document.body.classList.add('upload');
	dragcounter = 0;

	const files = [...e.dataTransfer.items].map(item => item.getAsFile());

	if (!files.length) return;

	(async () => {
		for (var i = 0; i < files.length; i++) {
			var f = files[i];

			if (!f.name.match(/\.ics$/)) {
				continue;
			}

			var url = location.href.replace(/[^\/]*$/, '') + 'event_new.php';
			var body = new FormData;
			body.append('import', 1);
			body.append('file', f);
			document.body.style.opacity = 0.2;
			await fetch(url, {method: 'POST', body});
			location.href = location.href;
			return;
		}
	})();
});