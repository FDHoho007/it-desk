function loadTickets() {
	ajaxGet(ITCROWD_URL_API_TICKETS + "?_wpnonce=" + WP_NONCE, function(res) {
		var data = JSON.parse(res);
		var table = document.getElementById("ticket-table");
		while(table.tBodies[0].children[1] != null)
			table.tBodies[0].children[1].remove();
		for(var ticket of data) {
			var row = document.createElement("tr");
			var td = document.createElement("td");
			td.setAttribute("sort-value", ticket["id"]);
			var a = document.createElement("a");
			a.href = "?page=ticket&id=" + ticket["id"] + "";
			a.innerText = ticket["id"];
			a.style.textDecoration = "none";
			td.appendChild(a);
			row.appendChild(td);
			var td = document.createElement("td");
			td.setAttribute("sort-value", ticket["state"]["value"]);
			var a = document.createElement("a");
			a.onclick = function() { prepareSort(this, 1) };
			a.innerText = ticket["state"]["text"];
			td.appendChild(a);
			row.appendChild(td);
			var td = document.createElement("td");
			td.setAttribute("sort-value", ticket["priority"]["value"]);
			var a = document.createElement("a");
			a.onclick = function() { prepareSort(this, 2) };
			a.innerText = ticket["priority"]["text"];
			a.style.color = ticket["priority"]["color"];
			td.appendChild(a);
			row.appendChild(td);
			var td = document.createElement("td");
			td.setAttribute("sort-value", ticket["category"]["name"]);
			var a = document.createElement("a");
			a.onclick = function() { prepareSort(this, 3) };
			a.innerText = ticket["category"]["name"];
			td.appendChild(a);
			row.appendChild(td);
			var td = document.createElement("td");
			td.setAttribute("sort-value", ticket["device"]["name"]);
			var a = document.createElement("a");
			a.onclick = function() { prepareSort(this, 4) };
			a.innerText = ticket["device"]["name"];
			td.appendChild(a);
			row.appendChild(td);
			var td = document.createElement("td");
			td.setAttribute("sort-value", ticket["submitter"]["time"]);
			var a = document.createElement("a");
			a.onclick = function() { prepareSort(this, 5) };
			a.innerText = ticket["submitter"]["time"];
			td.appendChild(a);
			row.appendChild(td);
			var td = document.createElement("td");
			var a = document.createElement("a");
			a.href = "?page=ticket&id=" + ticket["id"] + "";
			a.innerText = "Ansehen";
			td.appendChild(a);
			row.appendChild(td);
			table.tBodies[0].appendChild(row);
		}
	});
}

function prepareSort(e, i) {
	document.querySelector("#search" + i).value = e.innerText;
	document.querySelector("#search" + i).oninput();
}

function filter() {
		var table = document.getElementById("ticket-table");
		var query = [];
		var queryE = table.getElementsByTagName("tr")[2].getElementsByTagName("input");
		for(var i = 0; i<queryE.length; i++)
			query[i] = queryE[i].value;
		var rows = table.getElementsByTagName("tr");
		for(var i = 3; i<rows.length; i++) {
			var cols = rows[i].getElementsByTagName("td");
			var fits = true;
			for(var j = 0; j<query.length; j++)
				if(cols[j].children[0].innerText.toLowerCase().indexOf(query[j].toLowerCase()) == -1)
					fits = false;
			rows[i].style.display = fits ? "" : "none";
		}
	}
	var asc = true, last = null;
	function sort(colId) {
		asc = !asc;
		var table = document.getElementById("ticket-table");
		var rows = table.getElementsByTagName("tr");
		if(last != null)
			last.innerText = last.innerText.substring(0, last.innerText.length-2);
		last = rows[0].getElementsByTagName("a")[colId];
		last.innerText = last.innerText + (asc ? " ▲" : " ▼");
		for(var i = 3; i<rows.length; i++) {
			var best = null;
			for(var j = i; j<rows.length; j++) {
				if(best == null)
					best = rows[j];
				else {
					var e1 = rows[j].getElementsByTagName("td")[colId], e2 = best.getElementsByTagName("td")[colId];
					if(asc && e1.getAttribute("sort-value") < e2.getAttribute("sort-value") || !asc && e1.getAttribute("sort-value") > e2.getAttribute("sort-value"))
						best = rows[j];
				}
			}
			table.tBodies[0].insertBefore(best, rows[i]);
		}
	}
	function clear(e) {
		var input = e.previousSibling; 
		input.value = '';
		input.focus();
	}