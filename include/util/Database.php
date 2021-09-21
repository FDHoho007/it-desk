<?php

class Database
{
    private $con;

    public function __construct($dsn, $username = "", $password = "")
    {
        $this->con = new PDO($dsn, $username, $password, [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION, PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8"]);
    }

    public function exec($sql, ...$params): void
    {
        $this->con->prepare($sql)->execute($params);
    }

    public function query($sql, ...$params)
    {
        $stmt = $this->con->prepare($sql);
        $stmt->execute($params);
        $r = $stmt->fetch(PDO::FETCH_ASSOC);
        return $r === false ? null : $r;
    }

    public function queryAll($sql, ...$params)
    {
        $stmt = $this->con->prepare($sql);
        $stmt->execute($params);
        $r = $stmt->fetchAll(PDO::FETCH_ASSOC);
        return $r === false ? null : $r;
    }

    public function initialize()
    {
        $this->exec("CREATE TABLE IF NOT EXISTS " . Constants::DB_TABLE_WEBHOOKS . " (URL VARCHAR(255) PRIMARY KEY);");
        $this->exec("CREATE TABLE IF NOT EXISTS " . Constants::DB_TABLE_ROOM . " (ID VARCHAR(255) PRIMARY KEY, Floor INT NOT NULL, Visibility INT NOT NULL);");
        $this->exec("CREATE TABLE IF NOT EXISTS " . Constants::DB_TABLE_MODEL . " (ID VARCHAR(255) NOT NULL PRIMARY KEY, Company TEXT NOT NULL, Type INT NOT NULL);");
        $this->exec("CREATE TABLE IF NOT EXISTS " . Constants::DB_TABLE_MODEL_DETAILS . " (Model VARCHAR(255) NOT NULL, Variable TEXT NOT NULL, Value TEXT NOT NULL);");
        $this->exec("CREATE TABLE IF NOT EXISTS " . Constants::DB_TABLE_DEVICE . " (ID VARCHAR(255) NOT NULL PRIMARY KEY, Model VARCHAR(255) NOT NULL, Location VARCHAR(255) NOT NULL, PurchaseDate TIMESTAMP, Notes TEXT, Name TEXT, RemoteControl TEXT, FOREIGN KEY (Model) REFERENCES " . Constants::DB_TABLE_MODEL . "(ID) ON UPDATE CASCADE ON DELETE CASCADE, FOREIGN KEY (Location) REFERENCES " . Constants::DB_TABLE_ROOM . "(ID) ON UPDATE CASCADE ON DELETE CASCADE);");
        $this->exec("CREATE TABLE IF NOT EXISTS " . Constants::DB_TABLE_ISSUE . " (ID INT NOT NULL PRIMARY KEY AUTO_INCREMENT, Title TEXT NOT NULL, Availability TEXT NOT NULL);");
        $this->exec("CREATE TABLE IF NOT EXISTS " . Constants::DB_TABLE_TICKET . " (ID INT NOT NULL PRIMARY KEY AUTO_INCREMENT, Status INT NOT NULL, Level INT NOT NULL, Device VARCHAR(255) NOT NULL, FOREIGN KEY (Device) REFERENCES " . Constants::DB_TABLE_DEVICE . "(ID), Issue INT NOT NULL, FOREIGN KEY (Issue) REFERENCES " . Constants::DB_TABLE_ISSUE . "(ID), Author INT, ShortName TEXT, Operator INT, AdminOnly BOOLEAN);");
        $this->exec("CREATE TABLE IF NOT EXISTS " . Constants::DB_TABLE_TICKET_EVENTS . " (ID BIGINT NOT NULL PRIMARY KEY AUTO_INCREMENT, Ticket INT NOT NULL, FOREIGN KEY (Ticket) REFERENCES " . Constants::DB_TABLE_TICKET . "(ID) ON UPDATE CASCADE ON DELETE CASCADE, Event INT NOT NULL, Meta TEXT NOT NULL, Invoker INT NOT NULL, Timestamp BIGINT NOT NULL);");
        $this->exec("CREATE TABLE IF NOT EXISTS " . Constants::DB_TABLE_TEAMS_USER . " (TeamsUser VARCHAR(255) NOT NULL PRIMARY KEY, WPUser TEXT, Token TEXT, TokenInvalidate BIGINT(255), Conversation TEXT, Message TEXT);");
    }

    public function delete()
    {
        $this->exec("DROP TABLE IF EXISTS " . Constants::DB_TABLE_TICKET_EVENTS);
        $this->exec("DROP TABLE IF EXISTS " . Constants::DB_TABLE_TICKET);
        $this->exec("DROP TABLE IF EXISTS " . Constants::DB_TABLE_ISSUE);
        $this->exec("DROP TABLE IF EXISTS " . Constants::DB_TABLE_DEVICE);
        $this->exec("DROP TABLE IF EXISTS " . Constants::DB_TABLE_MODEL);
        $this->exec("DROP TABLE IF EXISTS " . Constants::DB_TABLE_MODEL_DETAILS);
        $this->exec("DROP TABLE IF EXISTS " . Constants::DB_TABLE_ROOM);
        $this->exec("DROP TABLE IF EXISTS " . Constants::DB_TABLE_TEAMS_USER);
    }

    public function getLastID()
    {
        return $this->con->lastInsertId();
    }

}