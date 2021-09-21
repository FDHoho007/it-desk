<?php
if (isset($_GET["issue"])) {
    $issue = ITDesk::getInstance()->getIssue($_GET["issue"]);
    if ($issue != null && isset($_GET["delete"])) {
        $issue->delete();
        $deleted = true;
    } else if (isset($_POST["Title"]) && isset($_POST["Availability"])) {
        $availability = [];
        foreach ($_POST["Availability"] as $av)
            array_push($availability, intval($av));
        if($issue == null)
            (new Issue(null, $_POST["Title"], $availability))->save();
        else {
            $issue->setTitle($_POST["Title"]);
            $issue->setAvailability($availability);
            $issue->save();
        }
        $error = "success";
    }
    ?>

    <div class="wrap">

        <h1 style="margin-bottom: 15px;">Problem <?php echo($issue == null ? "erstellen" : "bearbeiten"); ?></h1>

        <?php if (isset($deleted)) { ?>

            Das Problem und alle daran gebundene Tickets wurden gelöscht.<br>
            <br>
            <a href="?page=issues">Zurück</a>

        <?php } else {
            if (isset($error))
                if ($error == "success")
                    Wordpress::printMessage("updated", "Änderungen gespeichert.");
            ?>

            <form method="post">
                <table>
                    <tr>
                        <td><label for="title">Problem:</label></td>
                        <td>
                            <input type="text" id="title" name="Title" placeholder="Problemtitel"
                                   <?php if ($issue != null) echo("value=\"" . $issue->getTitle() . "\" "); ?>required>
                        </td>
                    </tr>
                    <tr>
                        <td><label>verfügbar bei Gerätetyp:</label></td>
                        <td>
                            <?php
                                for($i = 1; $i<sizeof(Constants::TYPES); $i++)
                                    echo("<input type=\"checkbox\" name=\"Availability[]\" value=$i" . ($issue != null && $issue->isAvailable($i) ? " checked" : "") . "> " . Constants::TYPES[$i] . "<br>");
                            ?>
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

        <h1 class="wp-heading-inline">Probleme</h1>
        <a href="?page=issues&issue" class="page-title-action">Erstellen</a><br><br>

        <table class="wp-list-table widefat fixed striped table-view-list">
            <thead>
            <tr>
                <th>Problem</th>
                <th>Verfügbar bei Gerätettyp</th>
            </tr>
            </thead>
            <tbody>
            <?php
            foreach (ITDesk::getInstance()->getTickets()->getIssues() as $issue) {
                ?>
                <tr>
                    <td>
                        <strong><?php echo($issue->getTitle()); ?></strong>
                        <div class="row-actions">
                            <span class="edit"><a href="?page=issues&issue=<?php echo($issue->getId()); ?>">Bearbeiten</a> | </span>
                            <span class="trash"><a
                                        href="?page=issues&issue=<?php echo($issue->getId()); ?>&delete">Löschen</a></span>
                        </div>
                    </td>
                    <td>
                        <strong><?php $av = []; foreach ($issue->getAvailability() as $a) array_push($av, Constants::TYPES[$a]); echo(sizeof($av) == 0 ? "-" : implode(", ", $av)); ?></strong>
                    </td>
                </tr>
            <?php } ?>
            </tbody>
            <tfoot>
            <tr>
                <th>Problem</th>
                <th>Verfügbar bei Gerätetyp</th>
            </tr>
            </tfoot>
        </table>

    </div>

<?php } ?>