<?php

class Room extends Identifiable
{

    private $id;
    private $floor, $visibility;

    public function __construct(string $id, int $floor, int $visibility)
    {
        $this->id = htmlspecialchars($id);
        $this->floor = $floor;
        $this->visibility = $visibility;
    }

    public function getId(): string
    {
        return $this->id;
    }

    public function getFloor(): int
    {
        return $this->floor;
    }

    public function setFloor(int $floor): void
    {
        $this->floor = $floor;
    }

    public function getVisibility(): int
    {
        return $this->visibility;
    }

    public function setVisibility(int $visibility): void
    {
        $this->visibility = $visibility;
    }

    public function save()
    {
        ITDesk::getInstance()->getDatabase()->exec("INSERT INTO " . Constants::DB_TABLE_ROOM . " VALUES (?, ?, ?) ON DUPLICATE KEY UPDATE Floor=?, Visibility=?;", $this->getId(), $this->getFloor(), $this->getVisibility(), $this->getFloor(), $this->getVisibility());
        if ($this->getId() == null)
            $this->id = ITDesk::getInstance()->getDatabase()->getLastID();
    }

    public function delete()
    {
        ITDesk::getInstance()->getDatabase()->exec("DELETE FROM " . Constants::DB_TABLE_ROOM . " WHERE ID=?;", $this->getId());
    }

    public function __toString()
    {
        return $this->getId();
    }

}