<div class="wrap">

    <h1 class="wp-heading-inline">Teams Integration</h1><br><br>

    <?php if (isset($_GET["token"])) {
        foreach (ITDesk::getInstance()->getTeams()->getUsers() as $user)
            if ($user->getToken() == $_GET["token"]) {
                $teamsUser = $user;
                break;
            }
        if(isset($teamsUser)) {
            $teamsUser->setWPUser(get_current_user_id());
            $teamsUser->setToken(null);
            $teamsUser->setTokenInvalidate(null);
            $teamsUser->save();
            $teamsUser->updateMessage($teamsUser->getMessage(), "", [Teams::createCard([Teams::createTextBlock("Du bist angemeldet", "large"), Teams::createTextBlock("Du bist aktuell als " . get_user_by("ID", $teamsUser->getWPUser())->display_name . " angemeldet.")], [Teams::createSubmitAction(["action" => "logout"], "Verbindung auflösen")])]);
            echo("<script>window.close();</script>");
        }
        else
            echo("Der übermittelte Token ist ungültig. Bitte fordere einen neuen Token beim Bot mit \"login\" an!");
    } else { ?>

        Über diese Seite kannst du dich mit deinem Microsoft Teams Konto verknüpfen, um auch dort Benachrichtigungen zu
        erhalten. Zudem kannst du über den Bot per Befehl Tickets verwalten.<br>
        Füge dazu zunächst die App "Ticket Client" zu deinen Apps hinzu. Schreibe dann dem Bot: "login"<br>
        <br>
        <?php

        foreach (ITDesk::getInstance()->getTeams()->getUsers() as $user)
            if ($user->getWPUser() == get_current_user_id()) {
                $teamsUser = $user;
                break;
            }

        if (isset($teamsUser) && isset($_GET["logout"])) {
            $teamsUser->setWPUser(null);
            $teamsUser->save();
        }
        echo(!isset($teamsUser) || $teamsUser->getTeamsUser() == null ? "Du bist aktuell mit keinem Teams Konto verbunden." : "Du bist aktuell mit folgendem Teams Konto verbunden: " . $teamsUser->getTeamsUser() . "<br><a href='?page=teams&logout'>Verbindung auflösen</a>");
    }
    ?>

</div>