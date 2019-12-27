<?php

class Category
{

	const TABLE = DB_PREFIX . "categories";

    static function activate() {
        global $wpdb;
        $wpdb->query("CREATE TABLE IF NOT EXISTS " . self::TABLE . " (ID INT UNSIGNED NOT NULL PRIMARY KEY AUTO_INCREMENT, Name TEXT NOT NULL, Description TEXT NOT NULL);");
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
		return $this->getName() != null;
	}

	static function all() {
		global $wpdb;
		$r = [];
		foreach($wpdb->get_results("SELECT ID FROM " . self::TABLE . ";", ARRAY_A) as $d)
			array_push($r, new Category($d["ID"]));
		return $r;
	}

	function api() {
		return self::all();
	}
	
	function load() {
		global $wpdb;
		$data = $wpdb->get_row("SELECT * FROM " . self::TABLE . " WHERE ID=" . $this->getId() . ";", ARRAY_A);
		$this->setName($data["Name"]);
		$this->setDescription($data["Description"]);
	}
	
	function save() {
		global $wpdb;
		$wpdb->query($wpdb->prepare("REPLACE INTO " . self::TABLE . " VALUES (" . $this->getId() . ", %s, %s);", $this->getName(), $this->getDescription()));
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

	function getName() {
        return $this->name;
    }
	
	function setName($name) {
		$this->name = $name;
	}
	
	function getDescription() {
		return $this->description;
	}
	
	function setDescription($description) {
		$this->description = $description;
	}

}