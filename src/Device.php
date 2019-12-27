<?php

class Device
{
	
	const TABLE = DB_PREFIX . "device";

    static function activate() {
        global $wpdb;
        $wpdb->query("CREATE TABLE IF NOT EXISTS " . self::TABLE . " (ID INT UNSIGNED NOT NULL PRIMARY KEY AUTO_INCREMENT, Type INT NOT NULL, Name TEXT NOT NULL, Model TEXT NOT NULL, Location INT NOT NULL, PurchaseDate DATE NOT NULL);");
    }
	
	static function uninstall() {
        global $wpdb;
        $wpdb->query("DROP TABLE " . self::TABLE . ";");
    }

	static function getTypes() {
        return ["Computer", "Dokumentenkamera", "Beamer", "Whiteboard"];
    }
	
    function __construct($id) {
		$this->id = ctype_digit("".$id) ? $id : 0;
        $this->load();
    }
	
	function exists() {
		return $this->getType() != null;
	}
	
	static function api($request) {
		$result = (new Finder(self::all()))->find("getLocation", $request["id"])->fetchAll();
		$types = self::getTypes();
		foreach($result as $device) {
			unset($device->purchaseDate);
			$device->type = $types[$device->type];
		}
		return $result;
	}

	static function all() {
		global $wpdb;
		$r = [];
		foreach($wpdb->get_results("SELECT ID FROM " . Device::TABLE . ";", ARRAY_A) as $d)
			array_push($r, new Device($d["ID"]));
		return $r;
	}
	
	function load() {
		global $wpdb;
		$data = $wpdb->get_row("SELECT * FROM " . Device::TABLE . " WHERE ID=" . $this->getId() . ";", ARRAY_A);
		$this->setType($data["Type"]);
        $this->setName($data["Name"]);
        $this->setModel($data["Model"]);
        $this->setLocation($data["Location"]);
        $this->setPurchaseDate($data["PurchaseDate"]);
	}
	
	function save() {
		global $wpdb;
		$wpdb->query($wpdb->prepare("REPLACE INTO " . Device::TABLE . " VALUES (" . $this->getId() . ", %d, %s, %s, %d, %s);", $this->getType(), $this->getName(), $this->getModel(), $this->getLocation(), $this->getPurchaseDate()));
	}
	
	function remove() {
		global $wpdb;
		$wpdb->query("DELETE FROM " . Device::TABLE . " WHERE ID=" . $this->getId() . ";");
	}
	
    function getId() {
        return $this->id == null ? "null" : $this->id;
    }
	
	function setId($id) {
		$this->id = $id;
	}
	
	function getType() {
        return $this->type;
    }
	
	function setType($type) {
		$this->type = $type;
	}

    function getName() {
        return $this->name;
    }
					 
	function setName($name) {
		$this->name = $name;
	}
	
	function getModel() {
        return $this->model;
    }
					 
	function setModel($model) {
		$this->model = $model;
	}

    function getLocation() {
        return $this->location;
    }

	function setLocation($location) {
		$this->location = $location;
	}
					 
    function getPurchaseDate() {
        return $this->purchaseDate;
    }
					 
	function setPurchaseDate($purchaseDate) {
		$this->purchaseDate = $purchaseDate;
	}

}