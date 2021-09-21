<?php


class OperatorEvent extends Event {

    const ID = 0x3;
    const MAIL = "operator";

    public function call()
    {
        Mail::send($this);
        ITDesk::getInstance()->getTeams()->notify($this);
    }

    public function getCard()
    {
        return Teams::createCard([
            Teams::createTextBlock("Bearbeiter gewechselt", "large"),
            Teams::createTextBlock("Das Tickets #" . $this->getTicket()->getId() . " mit dem Problem \"" . $this->getTicket()->getIssue()->getTitle() . "\" wird nun von " . get_user_by("ID", $this->getMeta())->display_name . " bearbeitet."),
            Teams::createTextBlock("Zur Erinnerung: Betroffen ist " . (Constants::ARTIKEL[($device = $this->getTicket()->getDevice())->getModel()->getType()] . " " . Constants::TYPES[$device->getModel()->getType()] . ($device instanceof Nameable && $device->getName() != null ? " (" . $device->getName() . ")" : "")) . " in Raum " . $this->getTicket()->getDevice()->getLocation()->getId() . "."),
        ]);
    }

}