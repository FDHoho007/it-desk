<?php
function custom_page_title($title = "")
{
    return $title != "" ? $title : "Discord";
}

function custom_page_subtitle()
{
    return "";
}

function custom_page() {
    $discord = ITDesk::getInstance()->getDiscord();
    $config = ITDesk::getInstance()->getConfig()->getContents()["discord"];
    if($_GET["discord"] == null) {
        if(wp_get_current_user() == null) { ?>

            <div class="main main-raised">
                <div class="container section section-text text-center">
                    Bitte melde dich an, um dich als IT Crowd Mitglied zu bestätigen.
                </div>
            </div>

        <?php } else if (Wordpress::hasUserLevel(Constants::USER_LEVEL_ITCROWD)) { ?>

            <div class="main main-raised">
                <div class="container section section-text text-center">
                    Du bist bereits als IT Crowd Mitglied bestätigt.
                </div>
            </div>

        <?php } else if(isset($_GET["code"])) {
            $token = $discord->api("POST", "oauth2/token", "client_id=" . $config["clientId"] . "&client_secret=" . $config["clientSecret"] . "&grant_type=authorization_code&code=" . $_GET["code"] . "&redirect_uri=https%3A%2F%2Fit.student-gymp.de%2Fdiscord%2Fverify", ["Content-Type: application/x-www-form-urlencoded"]);
            if(isset($token["access_token"])) {
                $me = $discord->api("GET", "users/@me", "", ["Authorization: Bearer " . $token["access_token"]]);
                $member = $discord->api("GET", "guilds/" . $config["guildId"] . "/members/" . $me["id"]);
                if(isset($member["roles"])) {
                    if(in_array($config["verificationRole"], $member["roles"])) {
                        wp_get_current_user()->set_role("itcrowd"); ?>
                        
                        <div class="main main-raised">
                            <div class="container section section-text text-center">
                                Du bist nun als IT Crowd Mitglied im TicketClient bestätigt.
                            </div>
                        </div>

                    <?php }
                    else { ?>

                        <div class="main main-raised">
                            <div class="container section section-text text-center">
                                Du bist auf dem IT Crowd Discord Server nicht als Mitglied bestätigt.
                            </div>
                        </div>

                    <?php }
                }
                else { ?>

                    <div class="main main-raised">
                        <div class="container section section-text text-center">
                            Du bist noch nicht auf dem IT Crowd Discord Server. Kannst aber gerne <a href="/discord">hier</a> beitreten.
                        </div>
                    </div>

                <?php }
            }
        }
        else { ?>

            <div class="main main-raised">
                <div class="container section section-text text-center">
                    Wenn du bereits auf dem IT Crowd Discord Server bist und dort als IT Crowd Mitglied bestätigt bist,<br>
                    kannst du dich über Discord als IT Crowd Mitglied bestätigen und deine Rolle auch hier im TicketClient bekommen.<br><br>
                    <button onclick="location.href = 'https://discord.com/api/oauth2/authorize?response_type=code&client_id=<?php echo($config["clientId"]); ?>&scope=identify&redirect_uri=https%3A%2F%2Fit.student-gymp.de%2Fdiscord%2Fverify&prompt=consent';">Über Discord bestätigen</button>
                </div>
            </div>

        <?php }
    }
    else if(ctype_digit("".$_GET["discord"])) {
        if(!is_user_logged_in())
            header("Location: " . home_url("/wp-login.php"));
        else if(!Wordpress::hasUserLevel(Constants::USER_LEVEL_ITCROWD)) { ?>

                <div class="main main-raised">
                    <div class="container section section-text text-center">
                        Du bist momentan nicht als IT Crowd Mitglied angemeldet.
                    </div>
                </div>

        <?php } else {
            $member = $discord->api("GET", "guilds/" . $config["guildId"] . "/members/" . $_GET["discord"]);
            if(isset($member["user"])) {
                if(isset($_POST["_wpnonce"]) && wp_verify_nonce($_POST["_wpnonce"])) {
                    $discord->api("PUT", "guilds/" . $config["guildId"] . "/members/" . $_GET["discord"] . "/roles/" . $config["verificationRole"]); ?>

                <div class="main main-raised">
                    <div class="container section section-text text-center">
                        Dein Discord Account wurde bestätigt.
                    </div>
                </div>

                <?php } else { ?>

                    <div class="main main-raised">
                        <div class="container section section-text text-center">
                            <form method="post">
                                <?php wp_nonce_field(); ?>
                                Bist du sicher, dass du folgenden Discord Account über deinen WordPress Account<br>
                                als IT Crowd Mitglied bestätigen möchtest: <?php echo($member["user"]["username"] . "#" . $member["user"]["discriminator"]); ?><br><br>
                                <button type="submit">Account bestätigen</button>
                            </form>
                        </div>
                    </div>

                <?php }
            }
            else { ?>

                <div class="main main-raised">
                    <div class="container section section-text text-center">
                        Diesen Discord Nutzer gibt es nicht.
                    </div>
                </div>

            <?php }
        }
    }
    else { ?>

        <div class="main main-raised">
            <div class="container section section-text text-center">
            Diesen Discord Nutzer gibt es nicht.
            </div>
        </div>

    <?php }
}