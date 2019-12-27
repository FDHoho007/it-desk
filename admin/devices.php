<?php
defined('ABSPATH') or die('No script kiddies please!');
global $wpdb, $error;
if(isset($_GET["id"])) {
	$dev = new Device($_GET["id"]);
	if(isset($_POST["Type"]) && isset($_POST["Name"]) && isset($_POST["Model"]) && isset($_POST["Location"]) && isset($_POST["PurchaseDate"])) {
		$room = new Room($_POST["Location"]);
	    if($room->exists() && ($_POST["Type"] == 0 || $_POST["Type"] == 1 || $_POST["Type"] == 2 || $_POST["Type"] == 3)) {
			$dev->setName($_POST["Name"]);
			$dev->setLocation($room->getId());
			if(!$dev->exists()) {
				$dev->setId(null);
				$dev->setType($_POST["Type"]);
				$dev->setModel($_POST["Model"]);
				$dev->setPurchaseDate($_POST["PurchaseDate"]);
			}
			$dev->save();
			$error = "success";
			$showall = true;
		}
	   	else
	   		$error = "invalid_data";
	}
	else if(isset($_GET["remove"])) {
		$dev->remove();
		$error = "success";
		$showall = true;
	}
}

function value($val) {
	echo($val == null ? "" : str_replace("\"", "&quot;", $val));
}
?>

<style>

	.container {
		display: inline-block;
		background-color: white;
		border-radius: 10px;
		border: 1px solid grey;
		padding: 10px;
	}
	
	thead td {
		font-weight: bold;
	}

	a {
		cursor: pointer;
	}
	
	#device-table a {
		box-shadow: none;
	}

</style>

<script>

	function filter() {
		var table = document.getElementById("device-table");
		var query = [];
		var queryE = table.getElementsByTagName("tr")[2].getElementsByTagName("input");
		for(var i = 0; i<queryE.length; i++)
			query[i] = queryE[i].value;
		var rows = table.getElementsByTagName("tr");
		for(var i = 3; i<rows.length; i++) {
			var cols = rows[i].getElementsByTagName("td");
			var fits = true;
			for(var j = 0; j<query.length; j++)
				if(cols[j].innerText.toLowerCase().indexOf(query[j].toLowerCase()) == -1)
					fits = false;
			rows[i].style.display = fits ? "" : "none";
		}
	}
	var asc = true, last = null;
	function sort(colId) {
		asc = !asc;
		var table = document.getElementById("device-table");
		var rows = table.getElementsByTagName("tr");
		if(last != null)
			last.innerText = last.innerText.substring(0, last.innerText.length-2);
		last = rows[0].getElementsByTagName("a")[colId];
		last.innerText = last.innerText + (asc ? " ▲" : " ▼");
		for(var i = 3; i<rows.length; i++) {
			var best = null;
			for(var j = i; j<rows.length; j++)
				if(best == null || (asc && rows[j].getElementsByTagName("td")[colId].innerText < best.getElementsByTagName("td")[colId].innerText) || (!asc && rows[j].getElementsByTagName("td")[colId].innerText > best.getElementsByTagName("td")[colId].innerText))
					best = rows[j];
			table.tBodies[0].insertBefore(best, rows[i]);
		}
	}

</script>

<div class="wrap">
    <h1>ITCrowd › Geräte verwalten</h1>
    <?php
    if (isset($error))
        if ($error == "success")
			ITCrowd::printMessage("updated", "Änderungen gespeichert.");
	   else if ($error == "invalid_data")
		   ITCrowd::printMessage("error", "Sie haben ungültige Daten übermittelt.");

	if(isset($_GET["id"]) && !isset($showall)) {
		$disabled = $dev->exists() ? " disabled" : ""; 
	?>
	
		<h2>Gerät <?php echo($dev->exists() ? "#" . $dev->getId() . " bearbeiten" : "hinzufügen"); ?></h2>
	
		<form method="post">
			<table>
				<tr>
					<td>Typ des Geräts:</td>
					<td><select name="Type" required<?php echo($disabled); ?>><?php foreach(Device::getTypes() as $i => $type) echo("<option value=$i" . ($i == $dev->getType() ? " selected" : "") . ">$type</value>"); ?></select></td>
				</tr>
				<tr>
					<td>Gerätename:</td>
					<td><input type="text" name="Name" placeholder="Gerätename" value="<?php value($dev->getName()); ?>" autofocus required></td>
				</tr>
				<tr>
					<td>Modell:</td>
					<td><input type="text" name="Model" placeholder="Modell" value="<?php value($dev->getModel()); ?>" required<?php echo($disabled); ?>></td></tr>
				<tr>
					<td>Standort:</td>
					<td><select name="Location" required><?php foreach(Room::all() as $room) echo("<option value=" . $room->getId() . ($room->getId() == $dev->getLocation() ? " selected" : "") . ">" . $room->getName() . "</option>"); ?></select></td>
				</tr>
				<tr>
					<td>Kaufdatum:</td>
					<td><input type="date" name="PurchaseDate" value="<?php value($dev->getPurchaseDate()); ?>" required<?php echo($disabled); ?>></td>
				</tr>
				<tr>
					<td colspan=2><?php submit_button(); ?></td>
				</tr>
			</table>
		</form>
	
	<?php } else { ?>
		
		<br><button class="button" onclick="location.href = '?page=<?php echo($_GET["page"]); ?>&id=0';">Gerät hinzufügen</button><br>
		<br>
		<div class="container">
			<table id="device-table">
				<thead>
					<tr>
						<td><a href="#" onclick="sort(0);">ID</a></td>
						<td><a href="#" onclick="sort(1);">Typ</a></td>
						<td><a href="#" onclick="sort(2);">Name</a></td>
						<td><a href="#" onclick="sort(3);">Modell</a></td>
						<td><a href="#" onclick="sort(4);">Standort</a></td>
						<td><a href="#" onclick="sort(5);">Kaufdatum</a></td>
						<td></td>
					</tr>
					<tr><td colspan=7><hr></td></tr>
				</thead>
				<tr>
					<td><input id="search0" type="search" placeholder="Suchbegriff ..." oninput="filter(0, this);"></td>
					<td><input id="search1" type="search" placeholder="Suchbegriff ..." oninput="filter(1, this);"></td>
					<td><input id="search2" type="search" placeholder="Suchbegriff ..." oninput="filter(2, this);"></td>
					<td><input id="search3" type="search" placeholder="Suchbegriff ..." oninput="filter(3, this);"></td>
					<td><input id="search4" type="search" placeholder="Suchbegriff ..." oninput="filter(4, this);"></td>
					<td><input id="search5" type="search" placeholder="Suchbegriff ..." oninput="filter(5, this);"></td>
					<td></td>
				</tr>
				<?php
					foreach (Device::all() as $dev) {
						echo("<tr>
								<td><a style='text-decoration: none;' href='?page=" . $_GET["page"] . "&id=" . $dev->getId() . "'>" . $dev->getId() . "</a></td>
								<td><a onclick='document.querySelector(\"#search1\").value = this.innerText; document.querySelector(\"#search1\").oninput();'>" . Device::getTypes()[$dev->getType()] . "</a></td>
								<td><a onclick='document.querySelector(\"#search2\").value = this.innerText; document.querySelector(\"#search2\").oninput();'>" . $dev->getName() . "</a></td>
								<td><a onclick='document.querySelector(\"#search3\").value = this.innerText; document.querySelector(\"#search3\").oninput();'>" . $dev->getModel() . "</a></td>
								<td><a onclick='document.querySelector(\"#search4\").value = this.innerText; document.querySelector(\"#search4\").oninput();'>" . (new Room($dev->getLocation()))->getName() . "</a></td>
								<td><a onclick='document.querySelector(\"#search5\").value = this.innerText; document.querySelector(\"#search5\").oninput();'>" . $dev->getPurchaseDate() . "</a></td>
								<td><a href='?page=" . $_GET["page"] . "&id=" . $dev->getId() . "'>Bearbeiten</a> <a href='?page=" . $_GET["page"] . "&id=" . $dev->getId() . "&remove'>Entfernen</a></td>
							</tr>");
					}
				?>
			</table>
		</div>
	
	<?php } ?>
</div>