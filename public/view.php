<?php
defined('ABSPATH') or die('No script kiddies please!');
global $wpdb;
?>

<script>TICKET_ID = <?php echo(ctype_digit("".$_GET["id"]) ? $_GET["id"] : 0); ?>; WP_NONCE = "<?php echo(wp_create_nonce('wp_rest')); ?>";</script>
<link rel="stylesheet" href="/wp-content/plugins/itcrowd/assets/style.css?<?php echo(rand()); ?>">
<script src="/wp-content/plugins/itcrowd/assets/constants.js.php?<?php echo(rand()); ?>"></script>
<script src="/wp-content/plugins/itcrowd/assets/ajax.js?<?php echo(rand()); ?>"></script>
<script src="/wp-content/plugins/itcrowd/assets/view.js?<?php echo(rand()); ?>"></script>

<div class="wrap" id="loading">
	<div class="lds-ellipsis"><div></div><div></div><div></div><div></div></div>
</div>

<div class="wrap" id="ticket-null" style="display: none;">
	<b>Dieses Ticket gibt es nicht oder du darfst es nicht ansehen!</b>
</div>

<div class="wrap" id="ticket-not-null" style="display: none;">
	<h1 style="margin-bottom: 20px;">Ticket #<span class="field-ticket-id"></span></h1>
	<div id="col-left">
		<div class="message">
			<div class="msg message-header" style="background-color: #d9edf7; color: #31708f;">
				Antworten
			</div>
			<div class="msg message-content">
				<form onsubmit="addMessage(this.getElementsByTagName('textarea')[0].value); return false;">
					<textarea style="width: 100%;" rows=7></textarea><br>
					<br>
					<button class="button">Antworten</button>
				</form>
			</div>
		</div>
	</div>
	<div id="col-right">
		<div class="box">
			<h1>Ticket Informationen<span style="float: right; cursor: pointer;" onclick="update();">↻</span></h1>
			<ul>
				<li><b>ID</b><br><span class="field-ticket-id"></span></li>
				<li><b>Gerät</b><br><span id="field-device"></span></li>
				<li><b>Kategorie</b><br><span id="field-category"></span></li>
				<li><b>Zustand</b><br><span id="field-state"></span></li>
				<li><b>Priorität</b><br><span id="field-priority"></span></li>
				<li><b>Ersteller</b><br><span id="field-submitter"></span></li>
				<li><b>Bearbeiter</b><br><span id="field-operator"></span></li>
				<li>
					<button id="buttongroup-edit" style="display: none; width: 100%;" class="red" onclick="setState(1);">Bearbeiten</button>
					<button id="buttongroup-close" style="display: none; width: 100%;" class="red" onclick="setState(0);">Schließen</button>
					<span id="buttongroup-sysadm" style="display: none;">
						<button style="width: 49%;" class="red" onclick="setState(2);">@SysAdm</button>
						<button style="width: 49%;" class="red" onclick="setState(0);">Schließen</button>
					</span>
					<button id="buttongroup-closed" style="display: none; width: 100%;" class="red" disabled>Geschlossen</button>
				</li>
			</ul>
		</div>
		<span id="admin-panel" style="display: none;">
			<div class="box">
				<h1>Zustand setzen</h1>
				<form onsubmit="setState(this.getElementsByTagName('select')[0].value); return false;">
					<select name="state"></select>
					<button class="green">Ändern</button>
				</form>
			</div>
			<div class="box">
				<h1>Bearbeiter setzen</h1>
				<form onsubmit="setOperator(this.getElementsByTagName('select')[0].value); return false;">
					<select name="operator">
						<option value=0>--- keiner ---</option>
					</select>
					<button class="green">Ändern</button>
				</form>
			</div>
		</span>
	</div>
</div>