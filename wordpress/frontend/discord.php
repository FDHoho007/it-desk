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

    }
    else if(ctype_digit("".$_GET["discord"])) {
        if(!is_user_logged_in())
            header("Location: " . home_url("/wp-login.php"));
        else if(!Wordpress::hasUserLevel(Constants::USER_LEVEL_ITCROWD)) { ?>

                <div class="main main-raised">
                    <div class="container section section-text">
                        Du bist momentan nicht als IT Crowd Mitglied angemeldet.
                    </div>
                </div>

        <?php } else {
            $member = $discord->api("GET", "guilds/" . $config["guildId"] . "/members/" . $_GET["discord"]);
            if(isset($member["user"])) {
                if(isset($_POST["_wpnonce"]) && wp_verify_nonce($_POST["_wpnonce"])) {
                    $discord->api("PUT", "guilds/" . $config["guildId"] . "/members/" . $_GET["discord"] . "/roles/" . $config["verificationRole"]);
                    header("Location: " . home_url());
                }
                else { 
                    ?>

                    <div class="main main-raised">
                        <div class="container section section-text">
                            <form method="post">
                                <?php wp_nonce_field(); ?>
                                Bist du sicher, dass du folgenden Discord Account über deinen WordPress Account als IT Crowd Mitglied bestätigen möchtest: 
                                <?php echo($member["user"]["username"] . "#" . $member["user"]["discriminator"]); ?>
                                <button type="submit">Account bestätigen</button>
                            </form>
                        </div>
                    </div>

                <?php }
            }
            else { ?>

                <div class="main main-raised">
                    <div class="container section section-text">
                        Diesen Discord Nutzer gibt es nicht.
                    </div>
                </div>

            <?php }
        }
    }
    else { ?>

        <div class="main main-raised">
            <div class="container section section-text">
            Diesen Discord Nutzer gibt es nicht.
            </div>
        </div>

    <?php }
}