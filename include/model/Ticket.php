<?php


class Ticket extends Identifiable {

    private $id;
    private $status, $level;
    private $device;
    private $issue;
    private $author, $operator;
    private $shortName;
    private $adminOnly;
    private $events;

    public function __construct(?int $id, int $status, int $level, Device $device, Issue $issue, ?int $author, ?int $operator, ?string $shortName, bool $adminOnly, array $events)
    {
        $this->id        = $id;
        $this->status    = $status;
        $this->level     = $level;
        $this->device    = $device;
        $this->issue     = $issue;
        $this->author    = $author;
        $this->operator  = $operator;
        $this->shortName = $shortName;
        $this->adminOnly = $adminOnly;
        $this->events    = $events;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getStatus(): int
    {
        return $this->status;
    }

    public function setStatus(int $status): void
    {
        $this->status = $status;
    }

    public function getLevel(): int
    {
        return $this->level;
    }

    public function setLevel(int $level): void
    {
        $this->level = $level;
    }

    public function getDevice(): Device
    {
        return $this->device;
    }

    public function getIssue(): Issue
    {
        return $this->issue;
    }

    public function setIssue(Issue $issue): void
    {
        $this->issue = $issue;
    }

    public function getAuthor(): ?int
    {
        return $this->author;
    }

    public function getShortName(): ?string
    {
        return $this->shortName;
    }

    public function getOperator(): ?int
    {
        return $this->operator;
    }

    public function setOperator(?int $operator): void
    {
        $this->operator = $operator;
    }

    public function isAdminOnly(): bool
    {
        return $this->adminOnly;
    }

    public function getEvents(): DataSet
    {
        return (new DataSet($this->events))->sort(function ($e1, $e2) {
            return $e2->getId() <=> $e1->getId();
        });
    }

    public function canView()
    {
        return (Wordpress::hasUserLevel(Constants::USER_LEVEL_ADMIN) || Wordpress::hasUserLevel(Constants::USER_LEVEL_ITCROWD) && !$this->isAdminOnly()) || get_current_user_id() == $this->getAuthor() || get_current_user_id() == $this->getOperator();
    }

    public function canViewUser($user)
    {
        return (Wordpress::userHasUserLevel($user, Constants::USER_LEVEL_ADMIN) || Wordpress::userHasUserLevel($user, Constants::USER_LEVEL_ITCROWD) && !$this->isAdminOnly()) || $user->id == $this->getAuthor() || $user->id == $this->getOperator();
    }

    public function callEvent($class, $meta = "")
    {
        $event = new $class(null, $this, $meta == "" ? ($class == StatusEvent::class ? $this->getStatus() : ($class == OperatorEvent::class ? $this->getOperator() : $meta)) : $meta, is_user_logged_in() ? get_current_user_id() : - 1, time());
        $event->save();
        $event->call();
        array_push($this->events, $event);
    }

    public function save()
    {
        ITDesk::getInstance()->getDatabase()->exec("INSERT INTO " . Constants::DB_TABLE_TICKET . " VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?) ON DUPLICATE KEY UPDATE Issue=?, Status=?, Level=?, Operator=?;", $this->getId(), $this->getStatus(), $this->getLevel(), $this->getDevice()->getId(), $this->getIssue()->getId(), $this->getAuthor() == null ? null : $this->getAuthor(), $this->getShortName() == null ? null : htmlspecialchars($this->getShortName()), $this->getOperator() == null ? null : $this->getOperator(), $this->isAdminOnly() ? 1 : 0, $this->getIssue()->getId(), $this->getStatus(), $this->getLevel(), $this->getOperator() == null ? null : $this->getOperator());
        if ($this->getId() == null)
            $this->id = ITDesk::getInstance()->getDatabase()->getLastID();
    }

    public function delete()
    {
        ITDesk::getInstance()->getDatabase()->exec("DELETE FROM " . Constants::DB_TABLE_TICKET . " WHERE ID=?;", $this->getId());
    }

    public function __toString()
    {
        return dechex($this->getId());
    }

}