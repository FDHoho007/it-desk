<?php

if (isset($_GET["model"])) {
    $model = ITDesk::getInstance()->getModel($_GET["model"]);
    if ($model != null && isset($_GET["delete"])) {
        $model->delete();
        $deleted = true;
    }
    if (isset($_POST["Variable"]) && isset($_POST["Value"])) {
        $links       = [];
        $pics        = [];
        $connections = [];
        for ($i = 0; $i < sizeof($_POST["Value"]); $i ++)
            if (str_replace(" ", "", $_POST["Value"][$i]) != "")
                if ($_POST["Variable"][$i] == "link") {
                    $exp                                         = explode(Constants::URL_SEPARATOR, $_POST["Value"][$i]);
                    $links[sizeof($exp) == 2 ? $exp[0] : "Link"] = sizeof($exp) == 2 ? $exp[1] : $_POST["Value"][$i];
                } else if ($_POST["Variable"][$i] == "pic")
                    array_push($pics, $_POST["Value"][$i]);
                else if ($_POST["Variable"][$i] == "connection")
                    array_push($connections, $_POST["Value"][$i]);
        if ($model == null && isset($_POST["Type"]) && $_POST["Type"] > 0 && $_POST["Type"] < 7 && isset($_POST["Company"]) && isset($_POST["ID"]))
            if (ITDesk::getInstance()->getModel($_POST["ID"]) == null) {
                (new Model($_POST["ID"], $_POST["Company"], $_POST["Type"], $links, $pics, $connections))->save();
                $error = "success";
            } else
                $error = "duplicate";
        else {
            $model->setLinks($links);
            $model->setPics($pics);
            $model->setConnections($connections);
            $model->save();
        }
    }
    ?>

    <div class="wrap">

        <h1 style="margin-bottom: 15px;">Modell <?php echo($model == null ? "erstellen" : "bearbeiten"); ?></h1>

        <?php if (isset($deleted)) { ?>

            Das Modell <?php echo($model->getId()); ?> und alle Geräte dieses Modells wurden gelöscht.<br>
        <br>
            <a href="?page=models">Zurück</a>

        <?php } else {
        if (isset($error))
            if ($error == "success")
                Wordpress::printMessage("updated", "Änderungen gespeichert.");
            else if ($error == "duplicate")
                Wordpress::printMessage("error", "Dieses Modell gibt es bereits.");
        ?>

            <form method="post">
                <table>
                    <tr>
                        <td><label for="type">Typ:</label></td>
                        <td>
                            <select id="type" name="Type"<?php if ($model != null)
                                echo(" disabled"); ?>>
                                <?php
                                for ($i = 1; $i < sizeof(Constants::TYPES); $i ++)
                                    echo("<option value=\"$i\"" . ($model != null && $model->getType() == $i ? " selected" : "") . ">" . Constants::TYPES[$i] . "</option>");
                                ?>
                            </select>
                        </td>
                    </tr>
                    <tr>
                        <td><label for="id">Modell:</label></td>
                        <td>
                            <input type="text" id="id" name="ID" placeholder="Modell"
                                   <?php if ($model != null)
                                       echo("value=\"" . $model->getId() . "\" disabled "); ?>required>
                        </td>
                    </tr>
                    <tr>
                        <td><label for="company">Hersteller:</label></td>
                        <td>
                            <input type="text" id="company" name="Company" placeholder="Hersteller"
                                   <?php if ($model != null)
                                       echo("value=\"" . $model->getCompany() . "\" disabled "); ?>required>
                        </td>
                    </tr>
                    <?php
                    if ($model != null)
                        foreach (
                            [
                                "link"       => $model->getLinks(),
                                "pic"        => $model->getPics(),
                                "connection" => $model->getConnections()
                            ] as $key => $value
                        )
                            foreach ($value as $a => $val) { ?>
                                <tr data-detail>
                                    <td>
                                        <select name="Variable[]">
                                            <option value="link"<?php if ($key == "link")
                                                echo(" selected"); ?>>Link
                                            </option>
                                            <option value="pic"<?php if ($key == "pic")
                                                echo(" selected"); ?>>Bild
                                            </option>
                                            <option value="connection"<?php if ($key == "connection")
                                                echo(" selected"); ?>>
                                                Anschluss
                                            </option>
                                        </select>
                                    </td>
                                    <td>
                                        <input type="text" name="Value[]" oninput="update();"
                                               value="<?php echo(($key == "link" ? $a . Constants::URL_SEPARATOR : "") . $val); ?>">
                                    </td>
                                </tr>
                            <?php } ?>
                    <tr data-detail>
                        <td>
                            <select name="Variable[]">
                                <option value="link">Link</option>
                                <option value="pic">Bild</option>
                                <option value="connection">Anschluss</option>
                            </select>
                        </td>
                        <td>
                            <input type="text" name="Value[]" oninput="update();">
                        </td>
                    </tr>
                    <tr>
                        <td colspan=2><?php submit_button(); ?></td>
                    </tr>
                </table>
            </form>

            <script>

                function update() {
                    let table = document.getElementsByTagName("table")[0].tBodies[0];
                    for (let e of table.querySelectorAll("tr[data-detail] td:last-child input"))
                        if (e.value == "")
                            return;
                    let e = table.querySelector("tr[data-detail]").cloneNode(true);
                    e.querySelector("td:last-child input").value = "";
                    table.insertBefore(e, table.querySelector("tr:last-child"));
                }

            </script>

        <?php } ?>

    </div>

<?php } else { ?>

    <div class="wrap">

        <h1 class="wp-heading-inline">Modelle</h1>
        <a href="?page=models&model" class="page-title-action">Erstellen</a><br><br>

        <table class="wp-list-table widefat fixed striped table-view-list">
            <thead>
            <tr>
                <th>Modell</th>
                <th>Typ</th>
                <th>Hersteller</th>
            </tr>
            </thead>
            <tbody>
            <?php
            foreach (ITDesk::getInstance()->getInventory()->getModels() as $model) {
                ?>
                <tr>
                    <td>
                        <strong><a href="<?php echo(home_url() . "/model/" . $model->getId()); ?>" target="_blank"><?php echo($model->getId()); ?></a></strong>
                        <div class="row-actions">
                            <span class="edit"><a
                                        href="?page=models&model=<?php echo($model->getId()); ?>">Bearbeiten</a> | </span>
                            <span class="trash"><a
                                        href="?page=models&model=<?php echo($model->getId()); ?>&delete">Löschen</a></span>
                        </div>
                    </td>
                    <td>
                        <strong><?php echo(Constants::TYPES[$model->getType()]); ?></strong>
                    </td>
                    <td>
                        <strong><?php echo($model->getCompany()); ?></strong>
                    </td>
                </tr>
            <?php } ?>
            </tbody>
            <tfoot>
            <tr>
                <th>Modell</th>
                <th>Typ</th>
                <th>Hersteller</th>
            </tr>
            </tfoot>
        </table>

    </div>

<?php } ?>
