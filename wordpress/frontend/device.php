<?php
// Gerätename
// Modell
// Standort
// Kaufdatum
// Fernbedienung

// Aktionen
// Bild von Modell
// Probleme
// Wartungstermine
function custom_page_title($title = "")
{
    $device = ITDesk::getInstance()->getDevice(urldecode($_GET["device"]));
    return $title != null ? $title : ($device == null ? null : (Wordpress::hasUserLevel($device->getVisibility()) ? $device->getId() . ($device instanceof Nameable && $device->getName() != null ? " (" . $device->getName() . ")" : "") : "Zugriff verweigert"));
}

function custom_page_subtitle()
{
    $device = ITDesk::getInstance()->getDevice(urldecode($_GET["device"]));
    return !Wordpress::hasUserLevel($device->getVisibility()) ? "Dieses Gerät befindet sich in einem durch die Systemadministration geschützten Raum." : "<a href='" . home_url() . "/model/" . $device->getModel()->getId() . "'>Modell " . $device->getModel()->getId() . " (" . Constants::TYPES[$device->getModel()->getType()] . ")</a><br><a href='" . home_url() . "/room/" . $device->getLocation()->getId() . "'>Raum " . $device->getLocation()->getId() . "</a>";
}

function custom_page()
{
    $device = ITDesk::getInstance()->getDevice(urldecode($_GET["device"]));
    if (Wordpress::hasUserLevel($device->getVisibility())) {
        if(isset($_POST["Notes"]) && Wordpress::hasUserLevel(Constants::USER_LEVEL_ITCROWD)) {
            $device->setNotes($_POST["Notes"]);
            $device->save();
        }
        ?>

        <style>

            table {
                width: 40%;
                margin: 40px auto;
                border-collapse: collapse;
            }

            td {
                border: 1px solid #eee !important;
            }

        </style>

        <div class="main main-raised">
            <div class="container section section-text">
                <table>
                    <?php if ($device->getPurchaseDate() != null && Wordpress::hasUserLevel(Constants::USER_LEVEL_ITCROWD)) {
                        echo("<tr><td>gekauft am:</td><td>" . $device->getPurchaseDate() . "</td></tr>");
                    } ?>
                    <?php if ($device instanceof RemoteControllable && $device->getRemoteControl() != null) {
                        echo("<tr><td>Fernbedienung:</td><td>" . $device->getRemoteControl() . "</td></tr>");
                    } ?>
                </table>
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