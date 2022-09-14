<?php
function custom_page_title($title = "")
{
    $ticket = ITDesk::getInstance()->getTicket(urldecode($_GET["ticket"]));
    return $title != "" ? $title : ($ticket == null || !$ticket->canView() ? null : $ticket->getIssue()->getTitle() . " (#" . $ticket->getId() . ")");
}

function custom_page_subtitle()
{
    $ticket = ITDesk::getInstance()->getTicket(urldecode($_GET["ticket"]));
    return "<a href='" . home_url() . "/device/" . $ticket->getDevice()->getId() . "' target='_blank'>" . Constants::TYPES[$ticket->getDevice()->getModel()->getType()] . ($ticket->getDevice() instanceof Nameable && $ticket->getDevice()->getName() != null ? " (" . $ticket->getDevice()->getName() . ")" : "") . " Raum " . $ticket->getDevice()->getLocation()->getId() . "</a>";
}

function custom_page()
{
    $ticket = ITDesk::getInstance()->getTicket(urldecode($_GET["ticket"]));
    if (isset($_POST["action"]) && isset($_POST["_wpnonce"]) && wp_verify_nonce($_POST["_wpnonce"])) {
        if ($_POST["action"] == "auto") {
            if (($ticket->getStatus() == 0 || $ticket->getStatus() == 3) && Wordpress::hasUserLevel(Constants::USER_LEVEL_ITCROWD)) {
                $ticket->setStatus(1);
                $ticket->callEvent(StatusEvent::class);
                $ticket->setOperator(get_current_user_id());
                $ticket->callEvent(OperatorEvent::class);
                $ticket->save();
            } else if ($ticket->getStatus() == 1 && ($ticket->getAuthor() == get_current_user_id() || $ticket->getOperator() == get_current_user_id() || Wordpress::hasUserLevel(Constants::USER_LEVEL_ITCROWD_HP))) {
                $ticket->setStatus(2);
                $ticket->callEvent(StatusEvent::class);
                $ticket->save();
            }
        } else if ($_POST["action"] == "upgrade" && $ticket->getLevel() > 1 && (($ticket->getStatus() == 0 || $ticket->getStatus() == 3) && Wordpress::hasUserLevel(Constants::USER_LEVEL_ITCROWD)) || ($ticket->getStatus() == 1 && ($ticket->getOperator() == get_current_user_id() || Wordpress::hasUserLevel(Constants::USER_LEVEL_ITCROWD_HP)))) {
            $ticket->setLevel($ticket->getLevel()-1);
            $ticket->save();
        } else if ($_POST["action"] == "message" && isset($_POST["message"]) && ($ticket->getAuthor() == get_current_user_id() || Wordpress::hasUserLevel(Constants::USER_LEVEL_ITCROWD))) {
            $ticket->callEvent(MessageEvent::class, htmlspecialchars($_POST["message"]));
            if ($ticket->getStatus() == 2 && !Wordpress::hasUserLevel(Constants::USER_LEVEL_ITCROWD)) {
                $ticket->setStatus(3);
                $ticket->callEvent(StatusEvent::class);
                $ticket->setOperator(- 1);
                $ticket->callEvent(OperatorEvent::class);
                $ticket->setLevel(1);
                $ticket->save();
            }
        } else if ($_POST["action"] == "issue" && isset($_POST["issue"]) && ($issue = ITDesk::getInstance()->getIssue($_POST["issue"])) != null && ($icket->getStatus() != 2 && Wordpress::userHasUserLevel($user, Constants::USER_LEVEL_ITCROWD_HP))) {
            $ticket->setIssue($issue);
            $ticket->save();
        } else if (Wordpress::hasUserLevel(Constants::USER_LEVEL_ADMIN))
            if ($_POST["action"] == "level" && isset($_POST["level"]) && ($_POST["level"] == 1 || $_POST["level"] == 2 || $_POST["level"] == 3) && $_POST["level"] != $ticket->getLevel()) {
                $ticket->setLevel($_POST["level"]);
                $ticket->save();
            }
            else if ($_POST["action"] == "status" && isset($_POST["status"]) && ($_POST["status"] == 0 || $_POST["status"] == 1 || $_POST["status"] == 2 || $_POST["status"] == 3) && $_POST["status"] != $ticket->getStatus()) {
                $ticket->setStatus($_POST["status"]);
                $ticket->callEvent(StatusEvent::class);
                $ticket->save();
            } else if ($_POST["action"] == "operator" && isset($_POST["operator"]) && get_user_by("ID", $_POST["operator"]) && $_POST["operator"] != $ticket->getOperator()) {
                $ticket->setOperator($_POST["operator"]);
                $ticket->callEvent(OperatorEvent::class);
                $ticket->save();
            }
    }
    ?>

    <style>

        .event {
            text-align: left;
            margin-bottom: 20px;
        }

        .event hr {
            width: 1px;
            height: 50px;
            background: #ddd;
            margin: 10px auto;
        }

        .event .img-wrapper {
            display: inline-block;
            width: 70px;
            vertical-align: top;
            text-align: center;
            margin-right: 35px;
        }

        .event img {
            border-radius: 50%;
            height: 64px;
            margin-top: 15px;
        }

        .event img.small {
            height: 32px;
            margin-top: 0;
        }

        .event .box {
            display: inline-block;
            border: 1px solid #ddd;
            border-radius: 5px;
            padding: 20px;
            width: calc(100% - 109px);
            box-shadow: 0 0 15px #eee;
        }

        .event .box .header {
            margin-bottom: 10px;
        }

        .event .box .header b {
            font-size: 14pt;
        }

        .event .system {
            display: inline-block;
            vertical-align: middle;
            width: calc(100% - 109px);
        }

        .event .date {
            color: #888;
            float: right;
        }

        .event .system .date {
            margin-right: 20px;
        }

        a {
            cursor: pointer;
        }

        #edit {
            background: #eee;
            margin-bottom: 20px;
            border-radius: 10px;
            transition: height 1s ease-in-out;
            overflow: hidden;
        }

        #edit table {
            height: 100%;
        }

        #edit td {
            width: 25%;
        }

        #edit td label {
            font-size: 18pt;
        }

        #edit td select {
            width: 100%;
        }

        #traffic-light .round {
            display: inline-block;
            border-radius: 50%;
            width: 75px;
            height: 75px;
            background: #eee;
            margin: 5px;
            border: 3px solid #999;
        }

        #traffic-light .round.green {
            background: #00b300;
        }

        #traffic-light .round.orange {
            background: orange;
        }

        #traffic-light .round.red {
            background: red;
        }

    </style>

    <div class="main main-raised">
        <div class="container section section-text">
            <?php echo($ticket->getStatus() == 2 ? "<a href=\"/wp-admin/admin.php?page=archive\">Ticketarchiv</a>" : "<a href=\"/wp-admin/admin.php?page=tickets\">Tickets</a>"); ?> &gt; Ticket #<?php echo($ticket->getId()); ?>
            <div class="text-center">
                <div id="traffic-light">
                    <div class="round<?php echo($ticket->getStatus() == 0 || $ticket->getStatus() == 3 ? " red" : ""); ?>"></div>
                    <div class="round<?php echo($ticket->getStatus() == 1 ? " orange" : ""); ?>"></div>
                    <div class="round<?php echo($ticket->getStatus() == 2 ? " green" : ""); ?>"></div>
                    <br><br>
                    <b style="font-size: 14pt;"><?php echo(Constants::STATUS[$ticket->getStatus()] . ($ticket->getStatus() == 1 ? " (" . get_user_by("ID", $ticket->getOperator())->display_name . ")" : "")); ?></b>
                </div>
                <?php 
                $permissionBlockA = (($ticket->getStatus() == 0 || $ticket->getStatus() == 3) && Wordpress::hasUserLevel(Constants::USER_LEVEL_ITCROWD)) || ($ticket->getStatus() == 1 && ($ticket->getAuthor() == get_current_user_id() || $ticket->getOperator() == get_current_user_id() || Wordpress::hasUserLevel(Constants::USER_LEVEL_ITCROWD_HP)));
                //$permissionBlockB = ($ticket->getStatus() == 0 || $ticket->getStatus() == 3) && Wordpress::hasUserLevel(Constants::USER_LEVEL_ITCROWD);
                $permissionBlockB = Wordpress::hasUserLevel(Constants::USER_LEVEL_ADMIN);
                if ($permissionBlockA || $permissionBlockB) { ?>
                    <div style="text-align: right; margin-bottom: 10px; user-select: none;">
                        <a onclick="let e = document.getElementById('edit'); e.style.height = e.style.height === '0px' ? '200px' : '0px';">Administration</a>
                    </div>
                <?php } ?>
                <div id="edit" style="height: 0px;">
                    <table>
                        <tr>
                            <?php if ($permissionBlockA) { ?>
                                <td>
                                    <form method="post">
                                        <?php wp_nonce_field(); ?>
                                        <input type="hidden" name="action" value="auto">
                                        <button type="submit"><?php if ($ticket->getStatus() == 0 || $ticket->getStatus() == 3)
                                                echo("Bearbeiten"); else if ($ticket->getStatus() == 1 && ($ticket->getAuthor() == get_current_user_id() || $ticket->getOperator() == get_current_user_id() || Wordpress::hasUserLevel(Constants::USER_LEVEL_ITCROWD_HP)))
                                                echo("Schließen"); ?></button>
                                    </form>
                                </td>
                                <?php if($ticket->getLevel() > 1) { ?>
                                    <td>
                                        <form method="post">
                                            <?php wp_nonce_field(); ?>
                                            <input type="hidden" name="action" value="upgrade">
                                            <button type="submit">Upgraden</button>
                                        </form>
                                    </td>
                                <?php } ?>
                            <?php }
                            if ($permissionBlockB) { ?>
                                    <td>
                                        <form method="post">
                                            <label>Levelauswahl</label><br>
                                            <?php wp_nonce_field(); ?>
                                            <input type="hidden" name="action" value="level">
                                            <button type="submit" name="level" value="1">1</button>
                                            <button type="submit" name="level" value="2">2</button>
                                            <button type="submit" name="level" value="3">3</button>
                                        </form>
                                    </td>
                                </tr>
                                <tr>
                            <?php }
                            if($permissionBlockB || ($icket->getStatus() != 2 && Wordpress::userHasUserLevel($user, Constants::USER_LEVEL_ITCROWD_HP))) { ?>
                                <td>
                                    <form method="post">
                                        <?php wp_nonce_field(); ?>
                                        <label for="issue">Problem</label><br>
                                        <input type="hidden" name="action" value="issue">
                                        <select id="issue" name="issue">
                                            <?php foreach (ITDesk::getInstance()->getTickets()->getIssues()->filter(function ($issue) { return in_array($ticket->getDevice()->getModel()->getType(), $issue->getAvailability()); }) as $issue)
                                                echo("<option value=" . ($issue->getId() . $ticket->getIssue()->getId() == $issue->getId() ? " selected" : "") . ">" . $issue->getTitle() . "</option>"); ?>
                                        </select><br>
                                        <button type="submit">Speichern</button>
                                    </form>
                                </td>
                            <?php }
                            if ($permissionBlockB) { ?>
                                <td>
                                    <form method="post">
                                        <?php wp_nonce_field(); ?>
                                        <label for="status">Status</label><br>
                                        <input type="hidden" name="action" value="status">
                                        <select id="status" name="status">
                                            <?php for ($i = 0; $i < 4; $i ++)
                                                echo("<option value=$i" . ($ticket->getStatus() == $i ? " selected" : "") . ">" . Constants::STATUS[$i] . "</option>"); ?>
                                        </select><br>
                                        <button type="submit">Speichern</button>
                                    </form>
                                </td>
                                <td>
                                    <form method="post">
                                        <?php wp_nonce_field(); ?>
                                        <label for="operator">Bearbeiter</label><br>
                                        <input type="hidden" name="action" value="operator">
                                        <select id="operator" name="operator">
                                            <?php foreach (get_users() as $user)
                                                if (Wordpress::userHasUserLevel($user, Constants::USER_LEVEL_ITCROWD))
                                                    echo("<option value='" . $user->ID . "'" . ($ticket->getOperator() == $user->ID ? " selected" : "") . ">" . $user->display_name . "</option>"); ?>
                                        </select><br>
                                        <button type="submit">Speichern</button>
                                    </form>
                                </td>
                            <?php } ?>
                        </tr>
                    </table>
                </div>
                <div class="event">
                    <div class="img-wrapper">
                        <img src="<?php echo(get_avatar_url(get_current_user_id())); ?>"
                             alt="Profilbild">
                    </div>
                    <div class="box">
                        <div class="header"><b>Neue Nachricht verfassen</b>
                        </div>
                        <div class="content">
                            <form method="post">
                                <?php wp_nonce_field(); ?>
                                <input type="hidden" name="action" value="message">
                                <textarea id="message" name="message" style="overflow-y: hidden;"></textarea>
                                <button type="submit" style="float: right">Nachricht senden</button>
                            </form>
                        </div>
                    </div>
                </div>
                <?php
                foreach ($ticket->getEvents() as $event) {
                    $name = $event->getInvoker() == - 1 ? "Anonym" : get_user_by("ID", $event->getInvoker())->display_name;
                    if ($event instanceof MessageEvent) { ?>
                        <div class="event">
                            <div class="img-wrapper">
                                <img src="<?php echo(get_avatar_url($event->getInvoker())); ?>"
                                     alt="Profilbild">
                            </div>
                            <div class="box">
                                <div class="header"><b><?php echo($name); ?></b><span
                                            class="date"><?php echo(date("d/m/Y - H:i", $event->getTimestamp())); ?></span>
                                </div>
                                <div class="content"><?php echo(str_replace("\n", "<br>", $event->getMeta())); ?></div>
                            </div>
                        </div>
                    <?php } else { ?>
                        <div class="event">
                            <div class="img-wrapper">
                                <img src="<?php echo(get_avatar_url($event->getInvoker())); ?>"
                                     alt="Profilbild" class="small">
                            </div>
                            <div class="system">
                                <?php
                                if ($event instanceof CreateEvent)
                                    echo("Das Ticket wurde von " . ($ticket->getAuthor() == - 1 ? "einem nicht angemeldeten Benutzer mit dem Lehrerkürzel \"" . $ticket->getShortName() . "\"" : get_user_by("ID", $ticket->getAuthor())->display_name) . " erstellt.");
                                else if ($event instanceof StatusEvent)
                                    echo("$name hat den Zustand dieses Tickets zu  " . Constants::STATUS[$event->getMeta()] . " geändert.");
                                else if ($event instanceof OperatorEvent)
                                    if ($event->getMeta() == - 1)
                                        echo("Die zuständige Person wurde entfernt.");
                                    else if ($event->getMeta() == $event->getInvoker())
                                        echo("Zuständige Person zu " . get_user_by("ID", $event->getMeta())->display_name . " geändert.");
                                    else
                                        echo("$name hat die zuständige Person zu " . get_user_by("ID", $event->getMeta())->display_name . " geändert.");
                                echo("<span class='date'>" . date("d/m/Y - H:i", $event->getTimestamp()) . "</span>");
                                ?>
                            </div>
                        </div>
                    <?php }
                }
                ?>
            </div>
        </div>
    </div>

    <script>

        var observe;
        if (window.attachEvent) {
            observe = function (element, event, handler) {
                element.attachEvent('on' + event, handler);
            };
        } else {
            observe = function (element, event, handler) {
                element.addEventListener(event, handler, false);
            };
        }
        var text = document.getElementById('message');

        function resize() {
            text.style.height = 'auto';
            text.style.height = text.scrollHeight + 'px';
        }

        /* 0-timeout to get the already changed text */
        function delayedResize() {
            window.setTimeout(resize, 0);
        }

        observe(text, 'change', resize);
        observe(text, 'cut', delayedResize);
        observe(text, 'paste', delayedResize);
        observe(text, 'drop', delayedResize);
        observe(text, 'keydown', delayedResize);

        text.focus();
        text.select();
        resize();

    </script>

<?php } ?>
