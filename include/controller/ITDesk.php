<?php

class ITDesk {

    private static $instance;
    private $absPath;
    private $config;
    private $database;
    private $inventory;
    private $tickets;
    private $webhooks;
    private $teams;
    private $discord;

    public static function getInstance(): ITDesk
    {
        if (!isset(self::$instance))
            self::$instance = new self();
        return self::$instance;
    }

    public function __construct()
    {
        $this->absPath  = dirname(__FILE__, 2);
        $this->config   = new Config($this->absPath . "/config.json");
        $dbdata         = $this->getConfig()->getContents()["database"];
        $this->database = new Database($dbdata["dsn"], $dbdata["user"], $dbdata["password"]);
        $this->getDatabase()->initialize();
        $this->inventory = new Inventory($this->getDatabase()->queryAll("SELECT * FROM " . Constants::DB_TABLE_DEVICE . ";"), $this->getDatabase()->queryAll("SELECT * FROM " . Constants::DB_TABLE_MODEL . ";"), $this->getDatabase()->queryAll("SELECT * FROM " . Constants::DB_TABLE_ROOM . ";"), $this->database);
        $this->tickets   = new Tickets($this->getDatabase()->queryAll("SELECT * FROM " . Constants::DB_TABLE_ISSUE), $this->getDatabase()->queryAll("SELECT * FROM " . Constants::DB_TABLE_TICKET), $this->getDatabase()->queryAll("SELECT * FROM " . Constants::DB_TABLE_TICKET_EVENTS . ";"), $this);
        $this->webhooks = new Webhooks($this->getDatabase()->queryAll("SELECT * FROM " . Constants::DB_TABLE_WEBHOOKS . ";"));
        $this->teams = new Teams($this->getDatabase()->queryAll("SELECT * FROM " . Constants::DB_TABLE_TEAMS_USER . ";"));
        $this->discord = new Discord($this->getConfig()->getContents()["discord"]["botToken"]);
    }

    public function getAbsPath(): string
    {
        return $this->absPath;
    }

    public function getConfig(): Config
    {
        return $this->config;
    }

    public function getDatabase(): Database
    {
        return $this->database;
    }

    public function getInventory(): Inventory
    {
        return $this->inventory;
    }

    public function getTickets(): Tickets
    {
        return $this->tickets;
    }

    public function getWebhooks(): Webhooks
    {
        return $this->webhooks;
    }

    public function getTeams(): Teams
    {
        return $this->teams;
    }

    public function getDiscord(): Discord
    {
        return $this->discord;
    }

    /* Redirect Functions */

    public function getTicket(?string $id): ?Ticket
    {
        return $this->getTickets()->getTicket($id);
    }

    public function getIssue(?string $id): ?Issue
    {
        return $this->getTickets()->getIssue($id);
    }

    public function getDevice(?string $id): ?Device
    {
        return $this->getInventory()->getDevice($id);
    }

    public function getModel(?string $id): ?Model
    {
        return $this->getInventory()->getModel($id);
    }

    public function getRoom(?string $id): ?Room
    {
        return $this->getInventory()->getRoom($id);
    }

}