<?php

class API {
	
	function register() {
		register_rest_route(ITCROWD_URL_API, ITCROWD_URL_API_DATA, ["methods" => "GET", "callback" => [$this, "getData"]]);
		register_rest_route(ITCROWD_URL_API, ITCROWD_URL_API_STATS, ["methods" => "GET", "callback" => [$this, "getStats"]]);
		register_rest_route(ITCROWD_URL_API, ITCROWD_URL_API_ROOMS, ["methods" => "GET", "callback" => ["Room", "api"]]);
		register_rest_route(ITCROWD_URL_API, ITCROWD_URL_API_ROOM . "/(?P<id>\d+)", ["methods" => "GET", "callback" => ["Device", "api"]]);
		register_rest_route(ITCROWD_URL_API, ITCROWD_URL_API_CATEGORIES, ["methods" => "GET", "callback" => ["Category", "api"]]);
		register_rest_route(ITCROWD_URL_API, ITCROWD_URL_API_TICKETS, ["methods" => "GET", "callback" => ["Ticket", "apiAll"]]);
		register_rest_route(ITCROWD_URL_API, ITCROWD_URL_API_TICKET . "/(?P<id>\d+)", ["methods" => "GET", "callback" => ["Ticket", "api"]]);
		register_rest_route(ITCROWD_URL_API, ITCROWD_URL_API_TICKET_EVENTS . "/(?P<id>\d+)", ["methods" => "GET", "callback" => ["TicketEvent", "api"]]);
		register_rest_route(ITCROWD_URL_API, ITCROWD_URL_API_TICKET_NEW, ["methods" => "POST", "callback" => ["Ticket", "create"]]);
		register_rest_route(ITCROWD_URL_API, ITCROWD_URL_API_TICKET_STATE . "/(?P<id>\d+)/(?P<state>\d+)", ["methods" => "GET", "callback" => ["Ticket", "apiState"]]);
		register_rest_route(ITCROWD_URL_API, ITCROWD_URL_API_TICKET_OPERATOR . "/(?P<id>\d+)/(?P<operator>\d+)", ["methods" => "GET", "callback" => ["Ticket", "apiOperator"]]);
		register_rest_route(ITCROWD_URL_API, ITCROWD_URL_API_TICKET_MESSAGE . "/(?P<id>\d+)", ["methods" => "POST", "callback" => ["Ticket", "apiMessage"]]);
	}
	
	function getData($request) {
		$r = ["permission" => ["tickets" => current_user_can(ITCROWD_PERMISSION_TICKETS), "administrator" => current_user_can(ITCROWD_PERMISSION_ADMINISTRATOR)], "state" => STATE, "priority" => PRIORITY];
		if($r["permission"]["administrator"]) {
			$users = [];
			foreach(get_users() as $user)
				array_push($users, ["id" => $user->id, "username" => $user->display_name]);
			$r["users"] = $users;
		}
		return $r;
	}
	
	function getStats($request) {
		date_default_timezone_set("Europe/Berlin");
		$response = [];
		$finder = new Finder(Ticket::all());
		//$response["open"] = sizeof($finder->find("getState", 0, "==")->fetchAll());
		//$response["wip"] = sizeof($finder->find("getState", 2, ">=")->fetchAll());
		//$response["closed"] = sizeof($finder->find("getState", 1)->fetchAll());
		$time = strtotime(date("Y-m-d", current_time("timestamp")));
		for($i = 0; $i < 7; $i++) {
			$response["date" . $i. "d"] = date("d.m", $time);
			$response["new" . $i . "d"] = sizeof($finder->find("getCreateDate", $time, ">=")->find("getCreateDate", $time + 86400, "<=")->fetchAll());
			$response["open" . $i . "d"] = sizeof($finder->find("getState", "0", "===", $time + 86400)->fetchAll());
			$response["wip" . $i . "d"] = sizeof($finder->find("getState", 2, ">=", $time + 86400)->fetchAll());
			$time -= 86400;
		}
		return $response;
	}
	
}