<?php


class MessageEvent extends Event {

    const ID = 0x4;
    const MAIL = "message";

    public function call()
    {
        Mail::send($this);
        ITDesk::getInstance()->getTeams()->notify($this);
    }

    public function getCard()
    {
        return Teams::createCard([
            Teams::createTextBlock("Neue Antwort", "large"),
            Teams::createTextBlock("Jemand hat soeben auf das Ticket(#" . $this->getTicket()->getId() . ") mit dem Problem \"" . $this->getTicket()->getIssue()->getTitle() . "\" geantwortet."),
            Teams::createTextBlock("Zur Erinnerung: Betroffen ist " . (Constants::ARTIKEL[($device = $this->getTicket()->getDevice())->getModel()->getType()] . " " . Constants::TYPES[$device->getModel()->getType()] . ($device instanceof Nameable && $device->getName() != null ? " (" . $device->getName() . ")" : "")) . " in Raum " . $this->getTicket()->getDevice()->getLocation()->getId() . "."),
            Teams::createTextBlock(($this->getInvoker() == - 1 ? "Anonym" : get_user_by("ID", $this->getInvoker())->display_name) . " schreibt nun:"),
            Teams::createTextBlock($this->getMeta())
        ]);
    }

}