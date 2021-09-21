<?php

class API
{

    public static function init()
    {
        register_rest_route(Constants::API_NAMESPACE, Constants::API_CREATE, [
            "method" => "GET",
            "callback" => ["API", "create"],
            "permission_callback" => "__return_true",
        ]);
        register_rest_route(Constants::API_NAMESPACE, Constants::API_TICKETS, [
            "method" => "GET",
            "callback" => ["API", "tickets"],
            "permission_callback" => "__return_true",
        ]);
        register_rest_route(Constants::API_NAMESPACE, Constants::API_TICKET, [
            "method" => "GET",
            "callback" => ["API", "ticket"],
            "permission_callback" => "__return_true",
        ]);
		register_rest_route(Constants::API_NAMESPACE, Constants::API_HEARTBEAT, [
            "method" => "GET",
            "callback" => ["API", "heartbeat"],
            "permission_callback" => "__return_true",
        ]);
    }

    public static function create()
    {
        $data = ["data" => [], "issues" => []];
        foreach (ITDesk::getInstance()->getInventory()->getDevices() as $device) {
            $room = $device->getLocation();
            if (Wordpress::hasUserLevel($room->getVisibility())) {
                if (!array_key_exists($room->getFloor(), $data["data"]))
                    $data["data"][$room->getFloor()] = [];
                if (!array_key_exists($room->getId(), $data["data"][$room->getFloor()]))
                    $data["data"][$room->getFloor()][$room->getId()] = [];
                $type = Constants::TYPES[$device->getModel()->getType()];
                if (!array_key_exists($type, $data["data"][$room->getFloor()][$room->getId()]))
                    $data["data"][$room->getFloor()][$room->getId()][$type] = [];
                $tickets = [];
                foreach ($device->getTickets() as $ticket)
                    array_push($tickets, $ticket->getIssue()->getId());
                array_push($data["data"][$room->getFloor()][$room->getId()][$type], [
                    "id" => $device->getId(),
                    "model" => ($device instanceof Nameable && $device->getName() != null ? $device->getName() . "/" : "") . $device->getModel()->getId(),
                    "tickets" => $tickets
                ]);
            }
        }
        foreach (ITDesk::getInstance()->getTickets()->getIssues() as $issue) {
            $availability = [];
            foreach ($issue->getAvailability() as $av)
                array_push($availability, Constants::TYPES[$av]);
            $data["issues"][$issue->getId()] = ["title" => $issue->getTitle(), "availability" => $availability];
        }
        return $data;
    }

    public static function tickets()
    {
        if (isset($_GET["ticket"])) {

            return false;
        } else {
            $tickets = [];
            foreach (ITDesk::getInstance()->getTickets()->getTickets() as $ticket)
                if ($ticket->canView() && $ticket->getStatus() != 2) {
                    array_push($tickets, [
                        "id" => $ticket->getId(),
                        "author" => $ticket->getAuthor() == -1 ? "Anonym" : get_user_by("ID", $ticket->getAuthor())->display_name,
                        "me" => is_user_logged_in() && get_current_user_id() == $ticket->getOperator(),
                        "img" => $ticket->getAuthor() == -1 ? "https://secure.gravatar.com/avatar/d28dd643c742dcb198d80413af29c490?s=96&d=mm&r=g" : get_avatar_url($ticket->getAuthor()),
                        "issue" => $ticket->getIssue()->getTitle(),
                        "status" => $ticket->getStatus(),
                        "statusText" => Constants::STATUS[$ticket->getStatus()],
                        "level" => $ticket->getLevel(),
                        "device" => [
                            "id" => $ticket->getDevice()->getId(),
                            "room" => $ticket->getDevice()->getLocation()->getId(),
                            "type" => Constants::TYPES[$ticket->getDevice()->getModel()->getType()]
                        ],
						"shortName" => $ticket->getShortName(),
						"operator" => $ticket->getOperator() == -1 ? "Niemand" : get_user_by("ID", $ticket->getOperator())->display_name
                    ]);
                }
            return $tickets;
        }
    }

    public static function ticket()
    {
        if (isset($_GET["id"]) && isset($_GET["state"]) && isset($_GET["level"])) {
            $ticket = ITDesk::getInstance()->getTicket($_GET["id"]);
            $level = intval($_GET["level"]);
            if ($level != 1 && $level != 2 && $level != 3)
                $level = 3;
            if ($ticket != null && $ticket->canView()) {
                if ($_GET["state"] == 0) {
                    $ticket->setLevel($level);
                    if ($ticket->getStatus() != 0) {
                        $ticket->setStatus(0);
                        $ticket->callEvent(StatusEvent::class);
                    }
                    if ($ticket->getOperator() != -1) {
                        $ticket->setOperator(-1);
                        $ticket->callEvent(OperatorEvent::class);
                    }
                    $ticket->save();
                } else if ($_GET["state"] == 1) {
                    if ($ticket->getStatus() != 1) {
                        $ticket->setStatus(1);
                        $ticket->callEvent(StatusEvent::class);
                    }
                    if ($ticket->getOperator() != get_current_user_id()) {
                        $ticket->setOperator(get_current_user_id());
                        $ticket->callEvent(OperatorEvent::class);
                    }
                    $ticket->save();
                }
            }
        }
    }
	
	public static function heartbeat() {
		if(isset(getallheaders()["Authorization"]) && getallheaders()["Authorization"] == "Bearer " . Constants::HEARTBEAT_SECRET && isset($_GET["id"])) {
			$device = ITDesk::getInstance()->getDevice($_GET["id"]);
			if($device != null && $device instanceof Nameable) {
				$device->heartBeat();
				if(isset($_GET["name"]) && $device->getName() != $_GET["name"]) {
					$device->setName($_GET["name"]);
					$device->save();
				}
				return true;
			}
		}
		return false;
	}

}