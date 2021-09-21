<?php


class CreateEvent extends Event
{

    const ID = 0x1;
    const MAIL = "create";

    public function call()
    {
        ITDesk::getInstance()->getWebhooks()->trigger($this);
        Mail::send($this);
        ITDesk::getInstance()->getTeams()->notify($this);
    }

    public function getCard()
    {
        return Teams::createCard([
            Teams::createTextBlock("Neues Ticket", "large"),
            Teams::createTextBlock("Jemand hat soeben ein neues Ticket(#" . $this->getTicket()->getId() . ") mit dem Problem \"" . $this->getTicket()->getIssue()->getTitle() . "\" erstellt."),
            Teams::createTextBlock("Betroffen ist " . (Constants::ARTIKEL[($device = $this->getTicket()->getDevice())->getModel()->getType()] . " " . Constants::TYPES[$device->getModel()->getType()] . ($device instanceof Nameable && $device->getName() != null ? " (" . $device->getName() . ")" : "")) . " in Raum " . $this->getTicket()->getDevice()->getLocation()->getId() . "."),
            Teams::createTextBlock("Gemeldet wurde das Problem von " . ($this->getInvoker() == - 1 ? "Anonym" : get_user_by("ID", $this->getInvoker())->display_name) . ".")
        ]);
    }

}