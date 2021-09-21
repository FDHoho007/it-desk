<?php

class Tickets {

    private $issues = [], $tickets = [];

    public function __construct(array $issues, array $tickets, array $events, ITDesk $itdesk)
    {
        foreach ($issues as $issue)
            array_push($this->issues, new Issue($issue["ID"], $issue["Title"], json_decode($issue["Availability"], true)));
        foreach ($tickets as $ticket) {
            $tevents = [];
            foreach (
                (new DataSet($events))->filter(function ($e) use ($ticket) {
                    return $e["Ticket"] == $ticket["ID"];
                }) as $e
            ) {
                foreach ([CreateEvent::class, StatusEvent::class, OperatorEvent::class, MessageEvent::class] as $c)
                    if ($e["Event"] == $c::ID)
                        break;
                array_push($tevents, new $c($e["ID"], null, $e["Meta"], $e["Invoker"], $e["Timestamp"]));
            }
            array_push($this->tickets, ($t = new Ticket($ticket["ID"], $ticket["Status"], $ticket["Level"], $itdesk->getDevice($ticket["Device"]), $this->getIssue($ticket["Issue"]), $ticket["Author"], $ticket["Operator"], $ticket["ShortName"], $ticket["AdminOnly"], $tevents)));
            foreach ($tevents as $e)
                $e->setTicket($t);
        }
    }

    public function getTickets(): DataSet
    {
        return new DataSet($this->tickets);
    }

    public function getIssues(): DataSet
    {
        return new DataSet($this->issues);
    }

    public function getTicket(?string $id): ?Ticket
    {
        return $this->getTickets()->getId($id);
    }

    public function getIssue(?string $id): ?Issue
    {
        return $this->getIssues()->getId($id);
    }

}