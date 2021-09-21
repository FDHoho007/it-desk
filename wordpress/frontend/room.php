<?php
// Raumnummer
// Geräte im Raum
//  Verkabelung
// Probleme in diese Raum
function custom_page_title($title = "")
{
    $room = ITDesk::getInstance()->getRoom(urldecode($_GET["room"]));
    return $title != null ? $title : ($room == null ? null : (Wordpress::hasUserLevel($room->getVisibility()) ? "Raum " . $room->getId() : "Zugriff verweigert"));
}

function custom_page_subtitle()
{
    $room = ITDesk::getInstance()->getRoom(urldecode($_GET["room"]));
    return !Wordpress::hasUserLevel($room->getVisibility()) ? "Dieser Raum wurde durch die Systemadministration geschützt." : "";
}

function custom_page()
{
    $room = ITDesk::getInstance()->getRoom(urldecode($_GET["room"]));
    if (Wordpress::hasUserLevel($room->getVisibility())) { ?>

        <div class="main main-raised">
            <div class="container section section-text">

            </div>
        </div>

    <?php } else { ?>

        <div class="main main-raised">
            <div class="container section section-text">
                <svg viewBox="0 0 448 512" style="height: 200px; display: block; margin: auto;">
                    <path fill="currentColor"
                          d="M400 224h-24v-72C376 68.2 307.8 0 224 0S72 68.2 72 152v72H48c-26.5 0-48 21.5-48 48v192c0 26.5 21.5 48 48 48h352c26.5 0 48-21.5 48-48V272c0-26.5-21.5-48-48-48zm-104 0H152v-72c0-39.7 32.3-72 72-72s72 32.3 72 72v72z"></path>
                </svg>
            </div>
        </div>

    <?php }
} ?>