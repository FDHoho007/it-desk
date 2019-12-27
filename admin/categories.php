<?php
defined('ABSPATH') or die('No script kiddies please!');
if (isset($_POST["Action"])) {
    if (isset($_POST["ID"])) {
		for($i = 1; $i <= 3; $i++)
			foreach(Category::all() as $data)
				if(!in_array($data->getId(), $_POST["ID"]))
					$data->remove();
        for ($i = 0; $i < sizeof($_POST["ID"]); $i++) {
			$data = new Category($_POST["ID"][$i]);
            if (!$data->exists())
				$data->setId(null);
			$data->setName(stripcslashes($_POST["Name"][$i]));
			$data->setDescription(stripcslashes($_POST["Description"][$i]));
			if($data->getName() == "")
				$data->remove();
			else
				$data->save();
		}
    } else
        foreach(Category::all() as $data)
			$data->remove();
    $success = true;
}

function value($val) {
	echo($val == null ? "" : str_replace("\"", "&quot;", $val));
}
?>

<style>
	
	thead td {
		font-weight: bold;
	}
	
	#table {
		background-color: white;
		border-radius: 20px;
		border: 1px solid grey;
		padding: 10px;
		display: inline-block;
	}
	
</style>

<script>
	
	function isLastRow(e) {
		return getLastRow() == e.parentElement.parentElement;
	}
	
	function getLastRow() {
		return document.getElementById('table').tBodies[0].lastElementChild;
	}
	
	function duplicateLastRow() {
		document.getElementById('table').tBodies[0].appendChild(getLastRow().cloneNode(true));
	}
	
</script>

<div class="wrap">
    <h1>Kategorien verwalten</h1>
	
    <?php 
		if (isset($success))
			ITCrowd::printMessage("updated", "Änderungen gespeichert.");
	?>
	
    <form method="post" action="?page=<?php echo($_GET["page"]); ?>">
        <input type="hidden" name="Action" value="Update">
        <br>
        <table id="table">
            <thead>
				<tr>
					<td>ID</td>
					<td>Name</td>
					<td>Beschreibung</td>
					<td>Entfernen</td>
				</tr>
				<tr><td colspan=4><hr></td></tr>
            </thead>
            <?php
			$list = Category::all();
			array_push($list, new Category(0));
			foreach($list as $data) { ?>
				<tr>
                	<td><input type="hidden" name="ID[]" value="<?php value($data->getId()); ?>"><?php echo($data->getId()); ?></td>
                    <td><input type="text" name="Name[]" placeholder="Name" value="<?php value($data->getName()); ?>" onkeydown="if(isLastRow(this)) duplicateLastRow();"></td>
					<td><input type="text" name="Description[]" placeholder="Beschreibung" value="<?php value($data->getDescription()); ?>" onkeydown="if(isLastRow(this)) duplicateLastRow();"></td>
                    <td><a href="#" onclick="if(!isLastRow(this)) this.parentElement.parentElement.remove();">&times; Entfernen &times;</a></td>
                </tr>
            <?php } ?>
        </table>
        <?php submit_button(); ?>
    </form>
</div>