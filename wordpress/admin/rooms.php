<?php

if (isset($_GET["room"])) {
    $room = ITDesk::getInstance()->getRoom($_GET["room"]);
    if ($room != null && isset($_GET["delete"])) {
        $room->delete();
        $deleted = true;
    } else if (isset($_POST["Floor"]) && isset($_POST["Visibility"])) {
        $floor      = $_POST["Floor"] >= - 2 && $_POST["Floor"] <= 5 ? $_POST["Floor"] : 0;
        $visibility = $_POST["Visibility"] >= 0 && $_POST["Visibility"] <= 4 ? $_POST["Visibility"] : 0;
        if ($room == null && isset($_POST["ID"]))
            if (ITDesk::getInstance()->getRoom($_POST["ID"]) == null) {
                (new Room($_POST["ID"], $floor, $visibility))->save();
                $error = "success";
            } else
                $error = "duplicate";
        else if ($room != null) {
            $room->setFloor($floor);
            $room->setVisibility($visibility);
            $room->save();
            $error = "success";
        }
    }
    ?>

    <div class="wrap">

        <h1 style="margin-bottom: 15px;">Raum <?php echo($room == null ? "erstellen" : "bearbeiten"); ?></h1>

        <?php if (isset($deleted)) { ?>

            Der Raum <?php echo($room->getId()); ?> und alle darin befindlichen Geräte wurden gelöscht.<br>
            <br>
            <a href="?page=rooms">Zurück</a>

        <?php } else {
            if (isset($error))
                if ($error == "success")
                    Wordpress::printMessage("updated", "Änderungen gespeichert.");
                else if ($error == "duplicate")
                    Wordpress::printMessage("error", "Diesen Raum gibt es bereits.");
            ?>

            <form method="post">
                <table>
                    <tr>
                        <td><label for="id">Name des Raums:</label></td>
                        <td>
                            <input type="text" id="id" name="ID" placeholder="Raumname"
                                   <?php if ($room != null)
                                       echo("value=\"" . $room->getId() . "\" disabled "); ?>required>
                        </td>
                    </tr>
                    <tr>
                        <td><label for="floor">Stockwerk:</label></td>
                        <td>
                            <input type="number" id="floor" name="Floor" placeholder="Stockwerk" min="-2" max="5"
                                <?php if ($room != null)
                                    echo("value=\"" . $room->getFloor() . "\""); ?>>
                        </td>
                    </tr>
                    <tr>
                        <td><label for="visibility">Sichtbar für:</label></td>
                        <td>
                            <select id="visibility" name="Visibility">
                                <?php
                                for ($i = 0; $i < 5; $i ++)
                                    echo("<option value=$i" . ($room != null && $room->getVisibility() == $i ? " selected" : "") . ">" . Wordpress::DISPLAY_LEVEL[$i] . "</option>");
                                ?>
                            </select>
                        </td>
                    </tr>
                    <tr>
                        <td colspan=2><?php submit_button(); ?></td>
                    </tr>
                </table>
            </form>

        <?php } ?>

    </div>

<?php } else { ?>

    <div class="wrap">

        <h1 class="wp-heading-inline">Räume</h1>
        <a href="?page=rooms&room" class="page-title-action">Erstellen</a><br><br>

        <table class="wp-list-table widefat fixed striped table-view-list">
            <thead>
            <tr>
                <th>Raum</th>
                <th>Stockwerk</th>
                <th>Sichtbar für</th>
            </tr>
            </thead>
            <tbody>
            <?php
            foreach (ITDesk::getInstance()->getInventory()->getRooms() as $room) {
                ?>
                <tr>
                    <td>
                        <strong><a href="<?php echo(home_url() . "/room/" . $room->getId()); ?>" target="_blank"><?php echo($room->getId()); ?></a></strong>
                        <div class="row-actions">
                            <span class="edit"><a href="?page=rooms&room=<?php echo($room->getId()); ?>">Bearbeiten</a> | </span>
                            <span class="trash"><a
                                        href="?page=rooms&room=<?php echo($room->getId()); ?>&delete">Löschen</a></span>
                        </div>
                    </td>
                    <td>
                        <strong><?php echo($room->getFloor()); ?></strong>
                    </td>
                    <td>
                        <strong><?php echo(Wordpress::DISPLAY_LEVEL[$room->getVisibility()]); ?></strong>
                    </td>
                </tr>
            <?php } ?>
            </tbody>
            <tfoot>
            <tr>
                <th>Raum</th>
                <th>Stockwerk</th>
                <th>Sichtbar für</th>
            </tr>
            </tfoot>
        </table>

    </div>

<?php } ?>
