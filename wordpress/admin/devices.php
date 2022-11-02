<?php
if (isset($_GET["device"])) {
    $device = ITDesk::getInstance()->getDevice($_GET["device"]);
    if ($device != null && isset($_GET["delete"])) {
        $device->delete();
        $deleted = true;
    } else if (isset($_POST["Location"]) && ($room = ITDesk::getInstance()->getRoom($_POST["Location"])) != null) {
        $purchaseDate = isset($_POST["PurchaseDate"]) ? str_replace("T", " ", $_POST["PurchaseDate"]) : null;
        if ($purchaseDate != null && !preg_match(Constants::PATTERN_DATE, $purchaseDate))
            $purchaseDate = null;
        $name          = isset($_POST["Name"]) ? $_POST["Name"] : null;
        $remoteControl = isset($_POST["RemoteControl"]) ? $_POST["RemoteControl"] : null;
        if ($device == null && isset($_POST["ID"]) && isset($_POST["Model"]) && ($model = ITDesk::getInstance()->getModel($_POST["Model"])) != null)
			if(ITDesk::getInstance()->getDevice($_POST["ID"]) == null) {
				$class = $model->getClass();
				$parent = array_keys(class_parents($class))[0];
				if ($parent == "Nameable")
					(new $class($_POST["ID"], $model, $room, $purchaseDate, json_encode([]), $name, 0))->save();
				else if ($parent == "RemoteControllable")
					(new $class($_POST["ID"], $model, $room, $purchaseDate, json_encode([]), $remoteControl))->save();
				else
					(new $class($_POST["ID"], $model, $room, $purchaseDate, json_encode([])))->save();
				$error = "success";
			}
			else
				$error = "duplicate";
        else if ($device != null) {
            $device->setLocation($room);
            $device->setPurchaseDate($purchaseDate);
            if ($device instanceof Nameable)
                $device->setName($name);
            else if ($device instanceof RemoteControllable)
                $device->setRemoteControl($remoteControl);
            $device->save();
			$error = "success";
        }
    }
    ?>

    <div class="wrap">

        <h1 style="margin-bottom: 15px;">Gerät <?php echo($device == null ? "erstellen" : "bearbeiten"); ?></h1>

        <?php if (isset($deleted)) { ?>

            Das Gerät <?php echo($device->getId()); ?> wurde gelöscht.<br>
            <br>
            <a href="?page=devices">Zurück</a>

        <?php } else {
            if (isset($error)) {
                if ($error == "success")
                    Wordpress::printMessage("updated", "Änderungen gespeichert.");
                else if ($error == "duplicate")
                    Wordpress::printMessage("error", "Dieses Gerät gibt es bereits.");
            }
            ?>

            <form method="post">
                <table>
                    <tr>
                        <td><label for="model">Modell:</label></td>
                        <td>
                            <select id="model" name="Model"
                                    onchange="document.getElementById('nameable').style.display = this.value.getAttribute('data-nameable') == 1 ? 'block' : 'inline'; document.getElementById('rc').style.display = this.value.getAttribute('data-rc') == 1 ? 'block' : 'inline';" <?php if ($device != null) {
                                echo(" disabled");
                            } ?>>
                                <?php
                                foreach (ITDesk::getInstance()->getInventory()->getModels() as $model)
                                    echo("<option value=\"" . $model->getId() . "\"" . ($device != null && $device->getModel() == $model ? " selected" : "") . ">" . $model->getId() . "</option>");
                                ?>
                            </select>
                        </td>
                    </tr>
                    <tr>
                        <td><label for="id">ID (Barcode):</label></td>
                        <td>
                            <input type="text" id="id" name="ID" placeholder="ID"
                                   <?php if ($device != null)
                                       echo("value=\"" . $device->getId() . "\" disabled ");
                                   ?>required>
                        </td>
                    </tr>
                    <tr>
                        <td><label for="location">Standort:</label></td>
                        <td>
                            <select id="location" name="Location">
                                <?php
                                foreach (ITDesk::getInstance()->getInventory()->getRooms() as $room)
                                    echo("<option value=\"" . $room->getId() . "\"" . ($device != null && $device->getLocation() == $room ? " selected" : "") . ">" . $room->getId() . "</option>");
                                ?>
                            </select>
                        </td>
                    </tr>
                    <tr>
                        <td><label for="purchase_date">Kaufdatum:</label></td>
                        <td>
                            <input type="datetime-local" id="purchase_date" name="PurchaseDate" placeholder="Kaufdatum"
                                <?php if ($device != null)
                                    echo("value=\"" . str_replace(" ", "T", $device->getPurchaseDate()) . "\" ");
                                ?>>
                        </td>
                    </tr>
                    <?php if ($device instanceof Nameable) { ?>
                        <tr id="nameable">
                            <td><label for="name">Gerätename:</label></td>
                            <td>
                                <input type="text" id="name" name="Name" placeholder="Gerätename"
                                    <?php if ($device != null && $device instanceof Nameable)
                                        echo("value=\"" . $device->getName() . "\" ");
                                    ?>>
                            </td>
                        </tr>
                    <?php }
                    if ($device instanceof RemoteControllable) { ?>
                        <tr id="rc">
                            <td><label for="remote_control">ID der Fernbedienung:</label></td>
                            <td>
                                <input type="text" id="remote_control" name="RemoteControl" placeholder="Fernbedienung"
                                    <?php if ($device != null && $device instanceof RemoteControllable)
                                        echo("value=\"" . $device->getRemoteControl() . "\" ");
                                    ?>>
                            </td>
                        </tr>
                    <?php } ?>
                    <tr>
                        <td colspan=2><?php submit_button(); ?></td>
                    </tr>
                </table>
            </form>

        <?php } ?>
    </div>

<?php } else { ?>

    <style>
        .heart-green:before {
            color: rgb(80, 175, 81);
        }
        .heart-red:before {
            color: rgb(217, 83, 79);
        }
    </style>

    <div class="wrap">

        <h1 class="wp-heading-inline">Geräte</h1>
        <a href="?page=devices&device" class="page-title-action">Erstellen</a><br><br>

        <table class="wp-list-table widefat fixed striped table-view-list">
            <thead>
            <tr>
                <th style="width: 40px;">Status</th>
				<th>ID</th>
                <th>Modell</th>
                <th>Standort</th>
                <th>Kaufdatum</th>
                <th>Gerätename</th>
                <th>Fernbedienung</th>
            </tr>
            </thead>
            <tbody>
            <?php
            foreach (ITDesk::getInstance()->getInventory()->getDevices() as $device) {
                ?>
                <tr>
					<td>
					    <?php if($device instanceof Nameable) if($device->getLastHeartBeat() != 0 && $device->getLastHeartBeat() > time()-900) { ?>
                            <div class="dashicons-before dashicons-heart heart-green" aria-hidden="true"><br></div>
						<?php } else { ?>
                            <div class="dashicons-before dashicons-heart heart-red" aria-hidden="true"><br></div>
						<?php } ?>
					</td>
                    <td>
                        <strong><a href="<?php echo(home_url() . "/device/" . $device->getId()); ?>" target="_blank"><?php echo($device->getId()); ?></a></strong>
                        <div class="row-actions">
                            <span class="edit"><a
                                        href="?page=devices&device=<?php echo($device->getId()); ?>">Bearbeiten</a> | </span>
                            <span class="trash"><a
                                        href="?page=devices&device=<?php echo($device->getId()); ?>&delete">Löschen</a></span>
                        </div>
                    </td>
                    <td>
                        <strong><a href="<?php echo(home_url() . "/model/" . $device->getModel()->getId()); ?>"><?php echo($device->getModel()->getId() . " (" . Constants::TYPES[$device->getModel()->getType()] . ")"); ?></a></strong>
                    </td>
                    <td>
                        <strong><a href="<?php echo(home_url() . "/room/" . $device->getLocation()->getId()); ?>"><?php echo($device->getLocation()->getId()); ?></a></strong>
                    </td>
                    <td>
                        <strong><?php echo($device->getPurchaseDate() == null ? "<hr>" : $device->getPurchaseDate()); ?></strong>
                    </td>
                    <td>
                        <strong><?php echo(!($device instanceof Nameable) || $device->getName() == null ? "<hr>" : $device->getName()); ?></strong>
                    </td>
                    <td>
                        <strong><?php echo(!($device instanceof RemoteControllable) || $device->getRemoteControl() == null ? "<hr>" : $device->getRemoteControl()); ?></strong>
                    </td>
                </tr>
            <?php } ?>
            </tbody>
            <tfoot>
            <tr>
                <th>ID</th>
                <th>Modell</th>
                <th>Standort</th>
                <th>Kaufdatum</th>
                <th>Gerätename</th>
                <th>Fernbedienung</th>
            </tr>
            </tfoot>
        </table>

    </div>

<?php } ?>