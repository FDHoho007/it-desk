<?php

class Model extends Identifiable
{

    private $type;
    private $company, $id;
    private $links, $pics, $connections;

    public function __construct(string $id, string $company, int $type, array $links, array $pics, array $connections)
    {
        $this->id = htmlspecialchars($id);
        $this->company = htmlspecialchars($company);
        $this->type = $type;
        $this->links = $links;
        $this->pics = $pics;
        $this->connections = $connections;
    }

    public function getId(): string
    {
        return $this->id;
    }

    public function getCompany(): string
    {
        return $this->company;
    }

    public function getType(): int
    {
        return $this->type;
    }

    public function getLinks(): array
    {
        return $this->links;
    }

    public function setLinks(array $links): void
    {
        $this->links = $links;
    }

    public function getPics(): array
    {
        return $this->pics;
    }

    public function setPics(array $pics): void
    {
        $this->pics = $pics;
    }

    public function getConnections(): array
    {
        return $this->connections;
    }

    public function setConnections(array $connections): void
    {
        $this->connections = $connections;
    }

    public function getClass(): ?string
    {
        foreach (Device::getSubclasses() as $c)
            if ($c::ID == $this->getType())
                return $c;
        return null;
    }

    public function save()
    {
        ITDesk::getInstance()->getDatabase()->exec("INSERT INTO " . Constants::DB_TABLE_MODEL . " VALUES (?, ?, ?) ON DUPLICATE KEY UPDATE Company=?, Type=?;", $this->getId(), $this->getCompany(), $this->getType(), $this->getCompany(), $this->getType());
        if ($this->getId() == null)
            $this->id = ITDesk::getInstance()->getDatabase()->getLastID();
        ITDesk::getInstance()->getDatabase()->exec("DELETE FROM " . Constants::DB_TABLE_MODEL_DETAILS . " WHERE Model=?;", $this->getId());
        foreach (["link" => $this->links, "pic" => $this->pics, "connection" => $this->connections] as $key => $value)
            foreach ($value as $a => $val)
                ITDesk::getInstance()->getDatabase()->exec("INSERT INTO " . Constants::DB_TABLE_MODEL_DETAILS . " VALUES (?, ?, ?);", $this->getId(), $key, ($key == "link" ? htmlspecialchars($a) . Constants::URL_SEPARATOR : "") . $val);
    }

    public function delete()
    {
        ITDesk::getInstance()->getDatabase()->exec("DELETE FROM " . Constants::DB_TABLE_MODEL . " WHERE ID=?;", $this->getId());
    }

    public function __toString()
    {
        return $this->getId();
    }

}