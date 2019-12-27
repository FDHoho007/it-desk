var data = [];
var lastEvent = 0;
addEventListener("DOMContentLoaded", function() {
	ajaxGet(ITCROWD_URL_API_DATA + "?_wpnonce=" + WP_NONCE, function(res) {
		data = JSON.parse(res);
		
		if(data["permission"]["administrator"]) {
			var select = document.getElementsByName("state")[0];
			for(state of data["state"]) {
				var option = document.createElement("option");
				option.value = state["value"];
				option.innerText = state["text"];
				select.appendChild(option);
			}
			var select = document.getElementsByName("operator")[0];
			for(user of data["users"]) {
				var option = document.createElement("option");
				option.value = user["id"];
				option.innerText = user["username"];
				select.appendChild(option);
			}
			document.getElementById("admin-panel").style.display = "block";
		}
		ajaxGet(ITCROWD_URL_API_TICKET + "/" + TICKET_ID + "?_wpnonce=" + WP_NONCE, function(res) {
			var ticket = JSON.parse(res);
			document.getElementById("loading").style.display = "none";
			document.getElementById(ticket["id"] == 0 ? "ticket-null" : "ticket-not-null").style.display = "block";
			for(e of document.getElementsByClassName("field-ticket-id"))
				e.innerText = ticket["id"];
			document.getElementById("field-device").innerText = ticket["device"]["name"];
			document.getElementById("field-device").title = "#" + ticket["device"]["id"];
			document.getElementById("field-category").innerText = ticket["category"]["name"];
			document.getElementById("field-category").title = ticket["category"]["description"];
			document.getElementById("field-state").innerText = ticket["state"]["text"];
			document.getElementById("field-state").style.color = ticket["state"]["color"];
			document.getElementById("field-priority").innerText = ticket["priority"]["text"];
			document.getElementById("field-priority").style.color = ticket["priority"]["color"];
			document.getElementById("field-submitter").innerText = ticket["submitter"]["actor"]["username"];
			document.getElementById("field-submitter").title = ticket["submitter"]["time"];
			if(ticket["operator"] == null)
				document.getElementById("field-operator").parentElement.style.display = "none";
			else {
				document.getElementById("field-operator").parentElement.style.display = "block";
				document.getElementById("field-operator").innerText = ticket["operator"]["details"]["username"];
				document.getElementById("field-operator").title = ticket["operator"]["time"];
			}
			var buttongroup = "closed";
			if(ticket["state"]["value"] == 2 || (!data["permission"]["tickets"] && !data["permission"]["administrator"]))
				buttongroup = "close";
			else if(ticket["state"]["value"] == 3)
				buttongroup = "edit";
			else if(ticket["state"]["value"] == 1)
				buttongroup = "sysadm";
			document.getElementById("buttongroup-" + buttongroup).style.display = "block";
			document.getElementsByName("state")[0].children[ticket["state"]["value"]].selected = true;
			for(var e of document.getElementsByName("operator")[0].children)
				if(ticket["operator"] == null)
					e.selected = e.value == 0;
				else
					e.selected = e.value == ticket["operator"]["details"]["id"];
		});
		fetchEvents();
	});
});

function update() {
	ajaxGet(ITCROWD_URL_API_TICKET + "/" + TICKET_ID + "?_wpnonce=" + WP_NONCE, function(res) {
		var ticket = JSON.parse(res);
		document.getElementById("field-state").innerText = ticket["state"]["text"];
		document.getElementById("field-state").style.color = ticket["state"]["color"];
		if(ticket["operator"] == null)
			document.getElementById("field-operator").parentElement.style.display = "none";
		else {
			document.getElementById("field-operator").parentElement.style.display = "block";
			document.getElementById("field-operator").innerText = ticket["operator"]["details"]["username"];
			document.getElementById("field-operator").title = ticket["operator"]["time"];
		}
		var buttongroup = "closed";
		if(ticket["state"]["value"] == 2 || (!data["permission"]["tickets"] && !data["permission"]["administrator"]))
			buttongroup = "close";
		else if(ticket["state"]["value"] == 3)
			buttongroup = "edit";
		else if(ticket["state"]["value"] == 1)
			buttongroup = "sysadm";
		document.getElementById("buttongroup-closed").style.display = "none";
		document.getElementById("buttongroup-close").style.display = "none";
		document.getElementById("buttongroup-edit").style.display = "none";
		document.getElementById("buttongroup-sysadm").style.display = "none";
		document.getElementById("buttongroup-" + buttongroup).style.display = "block";
		document.getElementsByName("state")[0].children[ticket["state"]["value"]].selected = true;
		for(var e of document.getElementsByName("operator")[0].children)
				if(ticket["operator"] == null)
					e.selected = e.value == 0;
				else
					e.selected = e.value == ticket["operator"]["details"]["id"];
	});
	fetchEvents();
}

function fetchEvents() {
	ajaxGet(ITCROWD_URL_API_TICKET_EVENTS + "/" + TICKET_ID + "?startAt=" + lastEvent + "&_wpnonce=" + WP_NONCE, function(res) {
		var events = JSON.parse(res);
		events.reverse();
		for(var e of events) {
			if(e["id"] > lastEvent)
				lastEvent = e["id"];
			var div = document.createElement("div");
			if(e["action"] == "message") {
				div.classList.add("message");
				var div1 = document.createElement("div");
				div1.classList.add("msg");
				div1.classList.add("message-header");
				div1.innerHTML = e["actor"]["username"] + "<span style=\"float: right;\">" + e["time"] + "</span>";
				div.appendChild(div1);
				var div2 = document.createElement("div");
				div2.classList.add("msg");
				div2.classList.add("message-content");
				div2.innerText = e["details"];
				div.appendChild(div2);
			}
			else {
				div.classList.add("msg");
				div.classList.add("system-msg");
				div.title = e["time"];
				if(e["action"] == "create")
					div.innerText = "Das Ticket wurde" + (e["actor"]["id"] != 0 ? " von " + e["actor"]["username"] : "") + " eröffnet.";
				else if(e["action"] == "setState")
					div.innerText = "Dieses Ticket ist nun " + e["details"]["text"] + ".";
				else if(e["action"] == "setOperator")
					div.innerText = e["details"]["username"] + " bearbeitet nun dieses Ticket.";
			}
			document.getElementById("col-left").insertBefore(div, document.getElementById("col-left").children[1]);
		}
	});
}

function setState(state) {
	ajaxGet(ITCROWD_URL_API_TICKET_STATE + "/" + TICKET_ID + "/" + state + "?_wpnonce=" + WP_NONCE, update);
}

function setOperator(operator) {
	ajaxGet(ITCROWD_URL_API_TICKET_OPERATOR + "/" + TICKET_ID + "/" + operator + "?_wpnonce=" + WP_NONCE, update);
}

function addMessage(message) {
	ajaxPost(ITCROWD_URL_API_TICKET_MESSAGE + "/" + TICKET_ID + "?_wpnonce=" + WP_NONCE, "Message=" + message, update);
}