<?php

class Issue extends Identifiable {

    private $id;
    private $title;
    private $availability;

    public function __construct(?int $id, string $title, array $availability)
    {
        $this->id = $id;
        $this->title = $title;
        $this->availability = $availability;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getTitle(): string
    {
        return $this->title;
    }

    public function setTitle(string $title): void
    {
        $this->title = $title;
    }

    public function getAvailability(): array
    {
        return $this->availability;
    }

    public function isAvailable($deviceType)
    {
        return in_array($deviceType, $this->availability);
    }

    public function setAvailability(array $availability): void
    {
        $this->availability = $availability;
    }

    public function save()
    {
        ITDesk::getInstance()->getDatabase()->exec("INSERT INTO " . Constants::DB_TABLE_ISSUE . " VALUES (?, ?, ?) ON DUPLICATE KEY UPDATE Title=?, Availability=?;", $this->getId(), htmlspecialchars($this->getTitle()), json_encode($this->getAvailability()), htmlspecialchars($this->getTitle()), json_encode($this->getAvailability()));
        if ($this->getId() == null)
            $this->id = ITDesk::getInstance()->getDatabase()->getLastID();
    }

    public function delete()
    {
        ITDesk::getInstance()->getDatabase()->exec("DELETE FROM " . Constants::DB_TABLE_ISSUE . " WHERE ID=?;", $this->getId());
    }

    public function __toString()
    {
        return $this->title;
    }

}