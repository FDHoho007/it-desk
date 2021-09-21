<?php

abstract class Event {

    const ID = 0x0;
    const MAIL = null;
    private $id;
    private $ticket;
    private $meta;
    private $invoker, $timestamp;

    public function __construct(?int $id, ?Ticket $ticket, string $meta, int $invoker, int $timestamp)
    {
        $this->id        = $id;
        $this->ticket    = $ticket;
        $this->meta      = $meta;
        $this->invoker   = $invoker;
        $this->timestamp = $timestamp;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getTicket(): ?Ticket
    {
        return $this->ticket;
    }

    public function setTicket(Ticket $ticket): void
    {
        $this->ticket = $ticket;
    }

    public function getMeta(): string
    {
        return $this->meta;
    }

    public function getInvoker(): int
    {
        return $this->invoker;
    }

    public function getTimestamp(): int
    {
        return $this->timestamp;
    }

    public abstract function call();

    public abstract function getCard();

    public function save()
    {
        ITDesk::getInstance()->getDatabase()->exec("INSERT INTO " . Constants::DB_TABLE_TICKET_EVENTS . " VALUES (NULL, ?, ?, ?, ?, ?);", $this->getTicket()->getId(), $this::ID, $this->getMeta(), $this->getInvoker(), $this->getTimestamp());
        if ($this->getId() == null)
            $this->id = ITDesk::getInstance()->getDatabase()->getLastID();
    }

}