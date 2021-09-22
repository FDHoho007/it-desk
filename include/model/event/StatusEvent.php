<?php


class StatusEvent extends Event {

    const ID = 0x2;
    const MAIL = "status";

    public function call()
    {
        Mail::send($this);
        ITDesk::getInstance()->getTeams()->notify($this);
    }

    public function getCard()
    {
        return Teams::createCard([
            Teams::createTextBlock("Zustandsänderung", "large"),
            Teams::createTextBlock("Der Zustand des Tickets #" . $this->getTicket()->getId() . " mit dem Problem \"" . $this->getTicket()->getIssue()->getTitle() . "\" wurde von " . ($this->getInvoker() == - 1 ? "Anonym" : get_user_by("ID", $this->getInvoker())->display_name) . " zu \"" . Constants::STATUS[$this->getMeta()] . "\" geändert."),
            Teams::createTextBlock("Zur Erinnerung: Betroffen ist " . (Constants::ARTIKEL[($device = $this->getTicket()->getDevice())->getModel()->getType()] . " " . Constants::TYPES[$device->getModel()->getType()] . ($device instanceof Nameable && $device->getName() != null ? " (" . $device->getName() . ")" : "")) . " in Raum " . $this->getTicket()->getDevice()->getLocation()->getId() . "."),
        ]);
    }

}
