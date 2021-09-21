<?php

abstract class Nameable extends Device
{

    private $name;
	private $lastHeartBeat;

    public function __construct(string $id, Model $model, Room $location, ?string $purchaseDate, ?string $notes, ?string $name, ?int $lastHeartBeat)
    {
        parent::__construct($id, $model, $location, $purchaseDate, $notes);
        $this->name = $name;
		$this->lastHeartBeat = $lastHeartBeat;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(?string $name): void
    {
        $this->name = $name;
    }

	public function heartBeat() {
		ITDesk::getInstance()->getDatabase()->exec("UPDATE " . Constantc::DB_TABLE_DEVICE . " SET LastHeartBeat=? WHERE ID=?;", time(), $this->getId());
	}
	
	public function getLastHeartBeat(): ?ìnt {
		return $this->lastHeartBeat;
	}
	
    public function save()
    {
        ITDesk::getInstance()->getDatabase()->exec("INSERT INTO " . Constants::DB_TABLE_DEVICE . " VALUES (?, ?, ?, ?, ?, ?, NULL, NULL) ON DUPLICATE KEY UPDATE Location=?, PurchaseDate=?, Notes=?, Name=?;", htmlspecialchars($this->getId()), $this->getModel()->getId(), $this->getLocation()->getId(), $this->getPurchaseDate() == null ? null : $this->getPurchaseDate(), $this->getNotes() == null ? null : htmlspecialchars($this->getNotes()), $this->getName() == null ? null : htmlspecialchars($this->getName()), $this->getLocation()->getId(), $this->getPurchaseDate() == null ? null : $this->getPurchaseDate(), $this->getNotes() == null ? null : htmlspecialchars($this->getNotes()), $this->getName() == null ? null : htmlspecialchars($this->getName()));
        if ($this->getId() == null)
            $this->id = ITDesk::getInstance()->getDatabase()->getLastID();
    }

}