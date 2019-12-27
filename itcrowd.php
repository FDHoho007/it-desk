<?php
/**
 * Plugin Name: ITCrowd Desk
 * Description: Ticketsystem für definierte Probleme bei Computern eines Netzwerks
 * Author: Fabian Dietrich
 * Author URI: https://fdserver.de/
 * Version: 1.0.0
 */

defined('ABSPATH') or die('No script kiddies please!');
global $wpdb;
define("DB_PREFIX", $wpdb->base_prefix);
define("STATE", [["text" => "Geschlossen", "color" => "#3dbb3d", "value" => 0], ["text" => "In Bearbeitung", "color" => "#ffbe4c", "value" => 1], ["text" => "@Systemadministration", "color" => "#ffbe4c", "value" => 2], ["text" => "Offen", "color" => "#ff4c4c", "value" => 3]]);
define("PRIORITY", [["text" => "Niedrig", "color" => "#3dbb3d", "value" => 0], ["text" => "Normal", "color" => "#ffbe4c", "value" => 1], ["text" => "Hoch", "color" => "#ff4c4c", "value" => 2], ["text" => "Notfall", "color" => "red", "value" => 3]]);
require_once("constants.php");

new ITCrowd();

class ITCrowd
{

    function __construct()
	{
        foreach (scandir(dirname(__FILE__) . "/src") as $srcFile)
            if ($srcFile != "." && $srcFile != "..")
                require_once "src/" . $srcFile;

        register_activation_hook(__FILE__, [$this, "activate"]);
        register_uninstall_hook(__FILE__, "uninstall");

        add_action("admin_menu", [$this, "setup_admin_menu"]);
		add_shortcode(ITCROWD_SHORTCODE_TICKETS, [$this, "public_page_create_sc"]);
		
		add_action("admin_head", [$this, "no_branding"]);
		add_action("wp_head", [$this, "no_branding"]);
		add_action("admin_bar_menu", [$this, "admin_bar"]);
		add_action("rest_api_init", [new API(), "register"]);
		
		add_action("show_user_profile", ["Profile", "extra_user_profile_fields"]);
		add_action("edit_user_profile", ["Profile", "extra_user_profile_fields"]);
		add_action("personal_options_update", ["Profile", "save_extra_user_profile_fields"]);
		add_action("edit_user_profile_update", ["Profile", "save_extra_user_profile_fields"]);
    }

    function activate() {
		Ticket::activate();
		TicketEvent::activate();
		Device::activate();
		Room::activate();
		Category::activate();
    }

    static function uninstall() {
		Ticket::uninstall();
		TicketEvent::uninstall();
		Device::uninstall();
		Room::uninstall();
		Category::uninstall();
    }

	function no_branding() {
		echo("<style>#wpfooter,#wp-admin-bar-wp-logo{display:none;}</style>");
	}
	
	function admin_bar() {
		global $wp_admin_bar;
		$wp_admin_bar->add_menu( array(
			'parent' => 'new-content',
			'id' => 'new_ticket',
			'title' => __('Ticket'),
			'href' => admin_url( 'admin.php?page=tickets-create'),
			'meta' => false
		));
	}

    function setup_admin_menu() {
        add_menu_page("Tickets", "Tickets", "read", "tickets", [$this, "public_page_list"], "dashicons-admin-comments", 3);
        add_submenu_page(null, "Ticket ansehen", "Ticket ansehen", "read", "ticket", [$this, "public_page_view"]);
		add_submenu_page("tickets", "Ticket erstellen", "Ticket erstellen", "read", "tickets-create", [$this, "public_page_create"]);
        add_menu_page("Geräte verwalten", "Geräte verwalten", ITCROWD_PERMISSION_ADMINISTRATOR, "desk-devices", [$this, "admin_page_devices"], "", 3);
		add_submenu_page("desk-devices", "Räume verwalten", "Räume verwalten", ITCROWD_PERMISSION_ADMINISTRATOR, "desk-rooms", [$this, "admin_page_rooms"]);
		add_submenu_page("desk-devices", "Kategorien verwalten", "Kategorien verwalten", ITCROWD_PERMISSION_ADMINISTRATOR, "desk-categories", [$this, "admin_page_categories"]);
    }
	
	function public_page_list() {
		require "public/list.php";
	}
	
	function public_page_view() {
		require "public/view.php";
	}
	
	function public_page_create() {
		require "public/create.php";
	}
	
	function public_page_create_sc() {
		ob_start();
		require "public/create.php";
		return ob_get_clean();
	}

    function admin_page_devices() {
        require "admin/devices.php";
    }
	
	function admin_page_rooms() {
        require "admin/rooms.php";
    }
	
	function admin_page_categories() {
        require "admin/categories.php";
    }
	
	static function printMessage($type, $message, $dismissible = true) {
		echo("<div class=\"" . $type . " inline" . ($dismissible ? " is-dismissible" : "") . "\"><p><strong>" . $message . "</strong></p>" . ($dismissible ? "<button type=\"button\" class=\"notice-dismiss\"><span class=\"screen-reader-text\">Diese Meldung ausblenden.</span></button>" : "") . "</div>");
	}

}