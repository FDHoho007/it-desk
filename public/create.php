<?php
defined('ABSPATH') or die('No script kiddies please!');
?>

<link rel="stylesheet" href="/wp-content/plugins/itcrowd/assets/style.css?<?php echo(rand()); ?>">
<script src="/wp-content/plugins/itcrowd/assets/constants.js.php?<?php echo(rand()); ?>"></script>
<script src="/wp-content/plugins/itcrowd/assets/ajax.js?<?php echo(rand()); ?>"></script>
<script src="/wp-content/plugins/itcrowd/assets/create.js?<?php echo(rand()); ?>"></script>

<style>
	
	form div h3 {
		margin: 0;
		padding: 0;
	}
	
	.container input {
		margin: 10px 10px 0 10px;
		width: calc(100% - 20px);
	}
	
	ul.select {
		margin: 10px 0;
		max-height: 200px;
		overflow: auto;
	}
	
	ul.select li {
		margin: 0;
		padding: 5px 10px;
		cursor: pointer;
	}
	
	ul.select li.selected, ul.select li.selected small, ul.select li:hover, ul.select li:hover small {
		background-color: #3399FF;
		color: white !important;
	}
	
	ul.select li small {
		color: #969696;
	}

<?php echo(is_admin() ? "" : "
.error, .updated {
	display: block !important;
	background: #fff;
    border: 1px solid #ccd0d4;
    border-left-width: 4px;
    box-shadow: 0 1px 1px rgba(0,0,0,.04);
    margin: 5px 15px 2px;
    padding: 1px 12px;
}
.error { border-left-color: #dc3232; }
.updated { border-left-color: #46b450; }"); ?>
	
</style>

<script>
	
	function ajaxGet(url, callback) {
		var xhttp = new XMLHttpRequest();
		xhttp.onreadystatechange = function() {
			if (this.readyState == 4 && this.status == 200)
		 		callback(this.responseText);
	  	};
	  	xhttp.open("GET", "/wp-json/<?php echo(ITCROWD_URL_API); ?>" + url, true);
	 	xhttp.send();
	}

	var currentStep = null;
	
	function loada() {
		if(currentStep != null)
			document.getElementById(currentStep).style.display = "none";
		document.getElementById('loading').style.display = "";
	}
	
	function loadb(id) {
		currentStep = id;
		document.getElementById('loading').style.display = "none";
		document.getElementById(currentStep).style.display = "";
	}
	
	function load(step, url, callback) {
		document.getElementsByClassName("wrap")[0].getElementsByTagName("h2")[0].innerText = "Schritt " + step + " von 4";
		loada();
		if(url == null)
			loadb("step" + step);
		else ajaxGet(url, function(res) {
			callback(JSON.parse(res));
			loadb("step" + step);
		});
	}
	
	function select(e, id) {
		e.classList.toggle("selected");
		for(var e2 of document.getElementById(id).children)
			if(e != e2 && e2.classList.contains("selected"))
				e2.classList.remove("selected");
	}
	
	function search(search, id) {
		search = search.toLowerCase();
		for(var e2 of document.getElementById(id).children)
			e2.style.display = e2.innerHTML.toLowerCase().includes(search) ? "" : "none";
	}
	
	function getSelected(id) {
		for(var e of document.getElementById(id).children)
			if(e.classList.contains("selected"))
				return e;
		return null;
	}
	
	function load1() {
		load(1, "<?php echo(ITCROWD_URL_API_ROOMS); ?>", function(res) {
			var e = document.getElementById("select-room");
			while(e.children.length > 0)
				e.children[0].remove();
			for(room of res) {
				var li = document.createElement("li");
				li.onclick = function() {select(this, "select-room");};
				li.value = room["id"];
				li.innerHTML = room["name"] + "<small> - " + room["deviceCount"] + " Geräte</small>";
				e.appendChild(li);
			}
		});
	}
	
	function load2() {
		var selected = getSelected("select-room");
		if(selected != null)
			load(2, "<?php echo(ITCROWD_URL_API_ROOM); ?>/" + selected.value, function(res) {
				var e = document.getElementById("select-device");
				while(e.children.length > 0)
					e.children[0].remove();
				for(device of res) {
					var li = document.createElement("li");
					li.onclick = function() {select(this, "select-device");};
					li.value = device["id"];
					li.innerHTML = device["name"] + "<small> - " + device["type"] + "</small>";
					e.appendChild(li);
				}
			});
	}
	
	function load3() {
		var selected = getSelected("select-device");
		if(selected != null)
			load(3, "<?php echo(ITCROWD_URL_API_CATEGORIES); ?>", function(res) {
				var e = document.getElementById("select-category");
				while(e.children.length > 0)
					e.children[0].remove();
				for(device of res) {
					var li = document.createElement("li");
					li.onclick = function() {select(this, "select-category");};
					li.value = device["id"];
					li.innerHTML = device["name"] + "<br><small>" + device["description"] + "</small>";
					e.appendChild(li);
				}
			});
	}
	
	function load4() {
		var selected = getSelected("select-category");
		if(selected != null)
			load(4, null, null);
	}
	
	var confirm_submit = false;
	
	function submit_ticket() {
		loada();
		if(getSelected("select-device") == null || getSelected("select-category") == null)
		   loadb("error1");
		else {
			var device = getSelected("select-device").value;
			var category = getSelected("select-category").value;
			var priority = document.getElementById("select-priority").value;
			var message = document.getElementById("message").value;
			var adminOnly = document.getElementById("admin-only").checked ? "1" : "0";
			if(priority == 3 && !confirm_submit) {
				confirm_submit = true;
				loadb("error2");
			}
			else {
				var xhttp = new XMLHttpRequest();
				xhttp.onreadystatechange = function() {
					if (this.readyState == 4 && this.status == 200) {
						var data = JSON.parse(this.responseText);
						if(data["success"]) {
							var id = data["id"];
							for(var e of document.getElementsByClassName("ticket-id")) {
								e.innerText = id;
								if(e.parentElement.tagName == "A")
									e.parentElement.href = e.parentElement.href + id;
							}
							loadb("error4");
						}
						else
							loadb("error3");
					}
				};
				xhttp.open("POST", "/wp-json/<?php echo(ITCROWD_URL_API . ITCROWD_URL_API_TICKET_NEW); if(is_user_logged_in()) echo("?_wpnonce=" . wp_create_nonce('wp_rest')); ?>", true);
				xhttp.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
				xhttp.send("device=" + device + "&category=" + category + "&priority=" + priority + "&message=" + message + "&adminOnly=" + adminOnly);
			}
		}
	}

</script>

<div class="wrap">
    <?php if(is_admin()) echo("<h1>Ticket erstellen</h1>"); ?>
	<h2>Schritt 0 von 4</h2>
    <form onsubmit="submit_ticket(); return false;">
		<div id="loading" style="display: none;">
			<div class="lds-ellipsis"><div></div><div></div><div></div><div></div></div>
		</div>
        <div id="step1" style="display: none;">
			<h3>Raum auswählen</h3>
			<i>Wählen sie den Raum aus, in dem das Problem auftritt.</i><br><br>
			<div class="container">
				<input type="text" onkeyup="search(this.value, 'select-room');" placeholder="Raumnummer eingeben">
				<ul class="select" id="select-room"></ul>
			</div><br><br>
			<button type="button" class="button" onclick="load2();">Fortfahren</button>
		</div>
		<div id="step2" style="display: none;">
			<h3>Computer auswählen</h3>
			<i>Wählen sie den Computer aus, an dem das Problem auftritt.</i><br><br>
			<div class="container">
				<input type="text" onkeyup="search(this.value, 'select-device');" placeholder="Computernamen eingeben">
				<ul class="select" id="select-device"></ul>
			</div><br><br>
			<button type="button" class="button" onclick="load1();">Zurück</button> <button type="button" class="button" onclick="load3();">Fortfahren</button>
		</div>
		<div id="step3" style="display: none;">
			<h3>Kategorie auswählen</h3>
			<i>Bitte wählen sie die Kategorie aus, die das Problem am treffensten beschreibt.</i><br><br>
			<div class="container">
				<input type="text" onkeyup="search(this.value, 'select-category');" placeholder="Kategorienamen eingeben">
				<ul class="select" id="select-category"></ul>
			</div><br><br>
			<button type="button" class="button" onclick="load2();">Zurück</button> <button type="button" class="button" onclick="load4();">Fortfahren</button>
		</div>
		<div id="step4" style="display: none;">
			<h3>Nachricht hinzufügen</h3>
			<i>Sie haben nun die Möglichkeit eine Nachricht zum Ticket hinzuzufügen.</i><br><br>
			<input type="checkbox" id="admin-only"> Dieses Ticket darf nur von Systemadministratoren gesehen werden.<br><br>
			<b>Priorität Ihres Tickets:</b> <select id="select-priority" onchange="this.nextElementSibling.nextElementSibling.children[1].innerText = this.children[this.selectedIndex].getAttribute('note')"><option value=3 note="Diese Priorität darf nur im äußersten Notfall angewendet werden. Tickets dieser Priorität haben höchsten Vorrang.">Notfall</option><option value=2 note="Tickets dieser Priorität haben Vorrang vor anderen Tickets, da das Problem den Schulalltag erheblich beeinflusst.">Hoch</option><option value=1 note="Diese Priorität kann auf alle generellen Probleme angewendet werden." selected>Normal</option><option value=0 note="Tickets dieser Priorität werden nachranging bearbeitet, da diese den Schulalltag nicht beeinflussen.">Niedrig</option></select><br>
			<small><b>Hinweis zur Priorität:</b> <span>Diese Priorität kann auf alle generellen Probleme angewendet werden.</span></small><br><br>
			<b>Ihre Nachricht (optional):</b><br>
			<textarea id="message" rows=12 cols=75></textarea><br><br>
			<button type="button" class="button" onclick="load3();">Zurück</button> <button type="submit" class="button">Ticket abschicken</button>
		</div>
		<div id="error1" style="display: none;">
			Es fehlen noch erforderliche Daten, bitte kehren sie zurück.<br><br>
			<button type="button" class="button" onclick="load4();">Zurück</button>
		</div>
		<div id="error2" style="display: none;">
			Sie haben die Notfall Priorität ausgewählt. Diese hat Vorrang vor allen anderen Tickets und darf nur im Notfall ausgewählt werden.<br><br>
			<button type="button" class="button" onclick="load4();">Abbrechen</button> <button type="submit" class="button">Ticket abschicken</button>
		</div>
		<div id="error3" style="display: none;">
			<?php ITCrowd::printMessage("error", "Beim Erstellen des Tickets ist ein Fehler aufgetreten.", false) ?>
		</div>
		<div id="error4" style="display: none;">
			<?php ITCrowd::printMessage("updated", "Zu Ihrem Problem wurde ein Ticket mit der ID #<span class='ticket-id'></span> erstellt." . (is_user_logged_in() ? "<br>
			Sie können von nun an den Fortschritt der Bearbeitung unter <a href=\"" . ITCROWD_URL_TICKET_VIEW . "\">" . ITCROWD_URL_TICKET_VIEW . "<span class='ticket-id'></span></a> einsehen." : ""), false) ?>
		</div>
    </form>
</div>


<script>load1();</script>