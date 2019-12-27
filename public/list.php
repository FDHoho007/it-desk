<?php
defined('ABSPATH') or die('No script kiddies please!');
?>

<script>WP_NONCE = "<?php echo(wp_create_nonce('wp_rest')); ?>";</script>
<link rel="stylesheet" href="/wp-content/plugins/itcrowd/assets/style.css?<?php echo(rand()); ?>">
<script src="/wp-content/plugins/itcrowd/assets/constants.js.php?<?php echo(rand()); ?>"></script>
<script src="/wp-content/plugins/itcrowd/assets/ajax.js?<?php echo(rand()); ?>"></script>
<script src="/wp-content/plugins/itcrowd/assets/list.js?<?php echo(rand()); ?>"></script>

<style>

	.priority, .state {
		border-radius: 5px;
		padding: 5px;
		color: white;
	}
	
	td div {
		margin: 2px 5px;
    	width: calc(100% - 20px);
	}
	
	#ticket-table a:focus {
		box-shadow: none;
	}
	
</style>

<div class="wrap">
	<h1>Ticket Übersicht</h1>
	<div class="container">
		<table id="ticket-table">
			<thead>
				<tr>
					<td><a href="#" onclick="sort(0);">ID</a></td>
					<td><a href="#" onclick="sort(1);">Zustand</a></td>
					<td><a href="#" onclick="sort(2);">Priorität</a></td>
					<td><a href="#" onclick="sort(3);">Kategorie</a></td>
					<td><a href="#" onclick="sort(4);">Gerät</a></td>
					<td><a href="#" onclick="sort(5);">Erstelldatum</a></td>
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
		</table>
	</div>
</div>

<script>
	//sort(2);sort(1);sort(1);
	loadTickets();
</script>