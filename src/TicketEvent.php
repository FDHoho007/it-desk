<?php

class TicketEvent
{

	const TABLE = DB_PREFIX . "ticket_event";

    static function activate() {
        global $wpdb;
        $wpdb->query("CREATE TABLE IF NOT EXISTS " . self::TABLE . " (ID INT UNSIGNED NOT NULL PRIMARY KEY AUTO_INCREMENT, Ticket INT UNSIGNED NOT NULL, Action TEXT NOT NULL, Details TEXT, Actor TEXT NOT NULL, Time TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP);");
    }
	
	static function uninstall() {
        global $wpdb;
        $wpdb->query("DROP TABLE " . self::TABLE . ";");
    }

    function __construct($id) {
		$this->id = ctype_digit("".$id) ? $id : 0;
        $this->load();
    }
	
	function exists() {
		return $this->getAction() != null;
	}

	static function all($ticket) {
		global $wpdb;
		$r = [];
		foreach($wpdb->get_results($wpdb->prepare("SELECT ID FROM " . self::TABLE . " WHERE Ticket=%d ORDER BY TIME DESC;", $ticket), ARRAY_A) as $d)
			array_push($r, new TicketEvent($d["ID"]));
		return $r;
	}
	
	static function api($request) {
		$ticket = new Ticket($request["id"]);
		if($ticket->exists() && $ticket->canView()) {
			$all = self::all($request["id"]);
			$r = [];
			foreach($all as $i => $e) {
				if(isset($_GET["startAt"]) && ctype_digit("".$_GET["startAt"]) && $e->getId() <= $_GET["startAt"])
					break;
				else
					array_push($r, $e->prepareApi());
			}
			return $r;
		}
		return [];
	}
	
	function prepareApi() {
		$this->setActor(["id" => $this->getActor(), "username" => $this->getActor() == 0 ? "Anonymous" : get_user_by("ID", $this->getActor())->display_name]);
		if($this->getAction() == "setState")
			$this->setDetails(STATE[$this->getDetails()]);
		else if($this->getAction() == "setOperator")
			$this->setDetails(["id" => $this->getDetails(), "username" => $this->getDetails() == 0 ? "Niemand" : get_user_by("ID", $this->getDetails())->display_name]);
		return $this;
	}
	
	function load() {
		global $wpdb;
		$data = $wpdb->get_row("SELECT * FROM " . self::TABLE . " WHERE ID=" . $this->getId() . ";", ARRAY_A);
		$this->setTicket($data["Ticket"]);
        $this->setAction($data["Action"]);
		$this->setDetails($data["Details"]);
        $this->setActor($data["Actor"]);
		$this->setTime($data["Time"]);
	}
	
	function save() {
		global $wpdb;
		$wpdb->query($wpdb->prepare("REPLACE INTO " . self::TABLE . " (`ID`, `Ticket`, `Action`, `Details`, `Actor`) VALUES (" . $this->getId() . ", %d, %s, %s, %s);", $this->getTicket(), $this->getAction(), $this->getDetails(), $this->getActor()));
	}
	
	function remove() {
		global $wpdb;
		$wpdb->query("DELETE FROM " . self::TABLE . " WHERE ID=" . $this->getId() . ";");
	}

    function getId() {
        return $this->id === null ? "null" : $this->id;
    }
	
	function setId($id) {
		$this->id = $id;
	}

	function getTicket() {
        return $this->ticket;
    }
	
	function setTicket($ticket) {
		$this->ticket = $ticket;
	}

	function getAction() {
		return $this->action;
	}
	
	function setAction($action) {
		$this->action = $action;
	}

	function getDetails() {
		return $this->details === null ? "null" : $this->details;
	}
	
	function setDetails($details) {
		$this->details = $details;
	}
	
	function getActor() {
		return $this->actor;
	}
	
	function setActor($actor) {
		$this->actor = $actor;
	}
						 
	function getTime() {
		return $this->time;
	}
	
	function getDateTime() {
		date_default_timezone_set("Europe/Berlin");
		return strtotime($this->getTime());
	}
	
	function setTime($time) {
		$this->time = $time;
	}
	
}