<?php

abstract class Device extends Identifiable {

    const ID = - 1;
    protected $id;
    private $model;
    private $location;
    private $purchaseDate, $notes;

    public function __construct(string $id, Model $model, Room $location, ?string $purchaseDate, ?string $notes)
    {
        $this->id           = htmlspecialchars($id);
        $this->model        = $model;
        $this->location     = $location;
        $this->purchaseDate = preg_match(Constants::PATTERN_DATE, $purchaseDate) ? $purchaseDate : null;
        $this->notes        = $notes;
    }

    public function getId(): string
    {
        return $this->id;
    }

    public function getModel(): Model
    {
        return $this->model;
    }

    public function getLocation(): Room
    {
        return $this->location;
    }

    public function setLocation(Room $location): void
    {
        $this->location = $location;
    }

    public function getVisibility(): int
    {
        return $this->getLocation()->getVisibility();
    }

    public function getPurchaseDate(): ?string
    {
        return $this->purchaseDate;
    }

    public function setPurchaseDate(?string $purchaseDate): void
    {
        if ($purchaseDate == null || preg_match(Constants::PATTERN_DATE, $purchaseDate))
            $this->purchaseDate = $purchaseDate;
    }

    public function getNotes(): ?string
    {
        return $this->notes;
    }

    public function setNotes(?string $notes): void
    {
        $this->notes = $notes;
    }
	
	public function getTickets(): DataSet
    {
        return ITDesk::getInstance()->getTickets()->getTickets()->filter(function ($ticket) { return $ticket->getDevice() == $this; });
    }

    public function save()
    {
        ITDesk::getInstance()->getDatabase()->exec("INSERT INTO " . Constants::DB_TABLE_DEVICE . " VALUES (?, ?, ?, ?, ?, NULL, NULL, NULL) ON DUPLICATE KEY UPDATE Location=?, PurchaseDate=?, Notes=?;", $this->getId(), $this->getModel()->getId(), $this->getLocation()->getId(), $this->getPurchaseDate() == null ? null : $this->getPurchaseDate(), $this->getNotes() == null ? null : htmlspecialchars($this->getNotes()), $this->getLocation()->getId(), $this->getPurchaseDate() == null ? null : $this->getPurchaseDate(), $this->getNotes() == null ? null : htmlspecialchars($this->getNotes()));
        if ($this->getId() == null)
            $this->id = ITDesk::getInstance()->getDatabase()->getLastID();
    }

    public function delete()
    {
        ITDesk::getInstance()->getDatabase()->exec("DELETE FROM " . Constants::DB_TABLE_DEVICE . " WHERE ID=?;", $this->getId());
    }

    public function __toString()
    {
        return $this->getId();
    }

    public static function getSubclasses(): array {
        return array_values(array_filter(get_declared_classes(), function ($class) {
        	$rc = new ReflectionClass($class);
        	return $rc->isSubclassOf(self::class) && !$rc->isAbstract();
        }));
    }

}