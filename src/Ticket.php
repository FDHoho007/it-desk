<?php

class Ticket
{

	const TABLE = DB_PREFIX . "ticket";

    static function activate() {
        global $wpdb;
        $wpdb->query("CREATE TABLE IF NOT EXISTS " . self::TABLE . " (ID INT UNSIGNED NOT NULL PRIMARY KEY AUTO_INCREMENT, Priority INT NOT NULL, Device INT NOT NULL, Category INT NOT NULL, AdminOnly BOOLEAN NOT NULL);");
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
		return $this->getPriority() != null;
	}
	
	static function apiAll($request) {
		$result = [];
		foreach(self::all() as $ticket)
			if($ticket->canView())
				array_push($result, $ticket->prepareApi());
		return $result;
	}
	
	function prepareApi() {
		unset($this->getDevice()->purchaseDate);
		$this->state = STATE[$this->getState()];
		$this->setPriority(PRIORITY[$this->getPriority()]);
		$this->submitter = $this->getLatestEvent("create")->prepareApi();
		$this->operator = $this->getLatestEvent("setOperator");
		if($this->operator != null)
			$this->operator = $this->operator->prepareApi();
		return $this;
	}
	
	static function api($request) {
		$result = new Ticket($request["id"]);
		if($result->exists() && $result->canView())
			$result->prepareApi();
		else {
			$result = new Ticket(0);
			unset($result->priority);
			unset($result->device);
			unset($result->category);
		}
		return $result;
	}
	
	static function apiState($request) {
		$ticket = new Ticket($request["id"]);
		if($ticket->exists() && $ticket->getState() != $request["state"] && $ticket->canView()) {
			if($request["state"] == 0 && $ticket->getState() != 0 && ($ticket->getOperator() == get_current_user_id() || $ticket->getSubmitter() == get_current_user_id() || current_user_can(ITCROWD_PERMISSION_ADMINISTRATOR)))
				$ticket->addEvent("setState", 0, null);
			else if($request["state"] == 1 && ($ticket->getState() == 3 && current_user_can(ITCROWD_PERMISSION_TICKETS) || current_user_can(ITCROWD_PERMISSION_ADMINISTRATOR))) {
				$ticket->addEvent("setState", 1, null);
				$ticket->addEvent("setOperator", get_current_user_id(), null);
			}
			else if($request["state"] == 2 && ($ticket->getState() == 1 && $ticket->getOperator() == get_current_user_id() || current_user_can(ITCROWD_PERMISSION_ADMINISTRATOR)))
				$ticket->addEvent("setState", 2, null);
			else if($request["state"] == 3 && current_user_can(ITCROWD_PERMISSION_ADMINISTRATOR)) {
				$ticket->addEvent("setState", 3, null);
				$ticket->addEvent("setOperator", 0, null);
			}
		}
	}
	
	static function apiOperator($request) {
		$ticket = new Ticket($request["id"]);
		if($ticket->exists() && current_user_can(ITCROWD_PERMISSION_ADMINISTRATOR) && $ticket->getOperator() != $request["operator"] && ($request["operator"] == 0 || get_user_by("ID", $request["operator"]) != null))
			$ticket->addEvent("setOperator", $request["operator"], null);
	}
	
	static function apiMessage($request) {
		$ticket = new Ticket($request["id"]);
		if($ticket->exists() && isset($_POST["Message"]) && $_POST["Message"] != "" && $ticket->canView()) {
			$ticket->addEvent("message", htmlspecialchars($_POST["Message"]), null);
			if($ticket->getState() == 1)
				$ticket->addEvent("setState", 2, null);
			$mail = new Mail(Mail::MESSAGE, $ticket);
			if($ticket->getOperator() != 0 && $ticket->getSubmitter() == get_current_user_id() && get_user_meta($ticket->getOperator(), 'email-new-message', false)[0])
				$mail->send(get_user_by("ID", $ticket->getOperator()));
			else if($ticket->getSubmitter() != 0 && $ticket->getOperator() == get_current_user_id() && get_user_meta($ticket->getSubmitter(), 'email-new-message', false)[0])
				$mail->send(get_user_by("ID", $ticket->getSubmitter()));
		}
	}

	static function all() {
		global $wpdb;
		$r = [];
		foreach($wpdb->get_results("SELECT ID FROM " . self::TABLE . ";", ARRAY_A) as $d)
			array_push($r, new Ticket($d["ID"]));
		return $r;
	}
	
	function load() {
		global $wpdb;
		$data = $wpdb->get_row("SELECT * FROM " . self::TABLE . " WHERE ID=" . $this->getId() . ";", ARRAY_A);
		$this->setPriority($data["Priority"]);
        $this->setDevice(new Device($data["Device"]));
        $this->setCategory(new Category($data["Category"]));
		$this->setAdminOnly($data["AdminOnly"]);
	}
	
	function save() {
		global $wpdb;
		$wpdb->query($wpdb->prepare("REPLACE INTO " . self::TABLE . " VALUES (" . $this->getId() . ", %d, %d, %d, %s);", $this->getPriority(), $this->getDevice()->getId(), $this->getCategory()->getId(), $this->isAdminOnly()));
	}
	
	function remove() {
		global $wpdb;
		$wpdb->query("DELETE FROM " . self::TABLE . " WHERE ID=" . $this->getId() . ";");
	}

    function getId() {
        return $this->id == null ? "null" : $this->id;
    }
	
	function setId($id) {
		$this->id = $id;
	}
	
	function getPriority() {
        return $this->priority;
    }
	
	function setPriority($priority) {
		$this->priority = $priority;
	}

	function getDevice() {
		return $this->device;
	}
	
	function setDevice($device) {
		$this->device = $device;
	}

	function getCategory() {
		return $this->category;
	}
	
	function setCategory($category) {
		$this->category = $category;
	}
	
	function isAdminOnly() {
		return $this->adminOnly;
	}
	
	function setAdminOnly($adminOnly) {
		$this->adminOnly = $adminOnly;
	}
	
	function getState($time = null) {
		$e = $this->getLatestEvent("setState", $time);
        return $e->getDetails();
    }
	
	function getSubmitter() {
		$e = $this->getLatestEvent("create");
        return $e->getActor();
    }
	
	function getOperator() {
		$e = $this->getLatestEvent("setOperator");
        return $e == null ? 0 : $e->getDetails();
    }
	
	function getCreateDate() {
		$e = $this->getLatestEvent("create");
		return $e->getDateTime();
	}

	function canView() {
		return current_user_can(ITCROWD_PERMISSION_ADMINISTRATOR) || (current_user_can(ITCROWD_PERMISSION_TICKETS) && !$this->isAdminOnly()) || ($this->getSubmitter() != 0 && $this->getSubmitter() == get_current_user_id()) || ($this->getOperator() != 0 && $this->getOperator() == get_current_user_id());
	}
	
	function addEvent($action, $details = "", $actor = null) {
		$ticketEvent = new TicketEvent(null);
		$ticketEvent->setTicket($this->getId());
		$ticketEvent->setAction($action);
		$ticketEvent->setDetails($details);
		$ticketEvent->setActor($actor == null ? get_current_user_id() : $actor);
		$ticketEvent->setTime(null);
		$ticketEvent->save();
	}
	
	function getLatestEvent($action, $time = null) {
		return (new Finder(TicketEvent::all($this->getId())))->find("getAction", $action)->find("getDateTime", $time === null ? current_time("timestamp") : $time, "<")->fetchOne();
	}
	
	function create($request) {
		global $wpdb;
		$device = new Device($request["device"]);
		$category = new Category($request["category"]);
		$priority = $request["priority"];
		$message = htmlspecialchars($request["message"]);
		$adminOnly = $request["adminOnly"] == "1";
		if($device->exists() && $category->exists() && ($priority == 0 || $priority == 1 || $priority == 2 || $priority == 3)) {
			$ticket = new Ticket(null);
			$ticket->setDevice($device);
			$ticket->setCategory($category);
			$ticket->setPriority($priority);
			$ticket->setAdminOnly($adminOnly);
			$ticket->save();
			$ticket->setId($wpdb->get_row("SELECT ID FROM " . self::TABLE . " ORDER BY ID DESC;", ARRAY_A)["ID"]);
			$ticket->addEvent("create");
			$ticket->addEvent("setState", 3);
			if($message != "")
				$ticket->addEvent("message", $message);
			$id = $ticket->getId();
			$mail = new Mail(Mail::CREATE, $ticket);
			foreach(get_users() as $user)
				if(user_can($user, ITCROWD_PERMISSION_TICKETS) && get_user_meta($user->ID, 'email-new-ticket', false)[0])
					$mail->send($user);
		}
		return ["success" => isset($id), "id" => $id];
	}
	
}