<?php

abstract class RemoteControllable extends Device
{

    private $remoteControl;

    public function __construct(string $id, Model $model, Room $location, ?string $purchaseDate, ?string $notes, ?string $remoteControl)
    {
        parent::__construct($id, $model, $location, $purchaseDate, $notes);
        $this->remoteControl = $remoteControl;
    }

    public function getRemoteControl(): ?string
    {
        return $this->remoteControl;
    }

    public function setRemoteControl(?string $remoteControl): void
    {
        $this->remoteControl = $remoteControl;
    }

    public function save()
    {
        ITDesk::getInstance()->getDatabase()->exec("INSERT INTO " . Constants::DB_TABLE_DEVICE . " VALUES (?, ?, ?, ?, ?, NULL, ?, NULL) ON DUPLICATE KEY UPDATE Location=?, PurchaseDate=?, Notes=?, RemoteControl=?;", htmlspecialchars($this->getId()), $this->getModel()->getId(), $this->getLocation()->getId(), $this->getPurchaseDate() == null ? null : $this->getPurchaseDate(), $this->getNotes() == null ? null : htmlspecialchars($this->getNotes()), $this->getRemoteControl() == null ? null : htmlspecialchars($this->getRemoteControl()), $this->getLocation()->getId(), $this->getPurchaseDate() == null ? null : $this->getPurchaseDate(), $this->getNotes() == null ? null : htmlspecialchars($this->getNotes()), $this->getRemoteControl() == null ? null : htmlspecialchars($this->getRemoteControl()));
        if ($this->getId() == null)
            $this->id = ITDesk::getInstance()->getDatabase()->getLastID();
    }

}