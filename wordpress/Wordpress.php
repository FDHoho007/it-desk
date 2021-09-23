<?php

class Wordpress
{

    const PERMISSION = ["", "itdesk_vip", "itdesk_itcrowd", "itdesk_itcrowd_vip", "itdesk_admin"];
    const DISPLAY_LEVEL = ["Jeden", "Lehrer", "ITCrowd", "fortgeschrittene ITCrowd", "Systemadministratoren"];

    function __construct()
    {
        register_activation_hook(__FILE__, [$this, "activate"]);
        register_uninstall_hook(__FILE__, "uninstall");

        add_action("admin_menu", [$this, "setup_admin_menu"]);
        add_filter('query_vars', [$this, "filter_query_vars"]);
        add_action('parse_request', [$this, "parse_request"]);

        add_action("admin_head", [$this, "no_branding"]);
        add_action("wp_head", [$this, "no_branding"]);
        add_action("admin_bar_menu", [$this, "admin_bar"]);
        add_action("rest_api_init", ["API", "init"]);

        add_action("user_register", ["Profile", "register"]);
        add_action("show_user_profile", ["Profile", "extra_user_profile_fields"]);
        add_action("edit_user_profile", ["Profile", "extra_user_profile_fields"]);
        add_action("personal_options_update", ["Profile", "save_extra_user_profile_fields"]);
        add_action("edit_user_profile_update", ["Profile", "save_extra_user_profile_fields"]);

        add_action("wp_dashboard_setup", [$this, "add_widget"]);
    }

    function activate()
    {
        ITDesk::getInstance();
    }

    static function uninstall()
    {
        ITDesk::getInstance()->getDatabase()->delete();
    }

    function no_branding()
    {
        echo("<style>#wpfooter,#wp-admin-bar-wp-logo{display:none;}</style>");
    }

    function admin_bar()
    {
        global $wp_admin_bar;
        $wp_admin_bar->add_menu(array(
            'parent' => 'new-content',
            'id' => 'new_ticket',
            'title' => __('Ticket'),
            'href' => home_url() . "/create",
            'meta' => false
        ));
    }

    function setup_admin_menu()
    {
        add_menu_page("Tickets", "Tickets", "read", "tickets", [
            $this,
            "admin_page_tickets"
        ], "dashicons-admin-comments", 2);
        add_submenu_page("tickets", "Ticketarchiv", "Ticketarchiv", "read", "archive", [
            $this,
            "admin_page_archive"
        ]);
        add_options_page("Teams Integration", "Teams Integration", "read", "teams", [
            $this,
            "admin_page_teams"
        ]);
        add_menu_page("Geräte verwalten", "Geräte verwalten", self::PERMISSION[Constants::USER_LEVEL_ADMIN], "devices", [
            $this,
            "admin_page_devices"
        ], "", 3);
        add_submenu_page("devices", "Modelle verwalten", "Modelle verwalten", self::PERMISSION[Constants::USER_LEVEL_ADMIN], "models", [
            $this,
            "admin_page_models"
        ]);
        add_submenu_page("devices", "Räume verwalten", "Räume verwalten", self::PERMISSION[Constants::USER_LEVEL_ADMIN], "rooms", [
            $this,
            "admin_page_rooms"
        ]);
        add_submenu_page("devices", "Probleme verwalten", "Probleme verwalten", self::PERMISSION[Constants::USER_LEVEL_ADMIN], "issues", [
            $this,
            "admin_page_issues"
        ]);
    }

    function filter_query_vars($query_vars)
    {
        $query_vars[] = 'ticket';
        $query_vars[] = 'device';
        $query_vars[] = 'model';
        $query_vars[] = 'room';
        return $query_vars;
    }

    function parse_request(&$wp)
    {
        foreach (["create", "scan", "credits"] as $key)
            if (preg_match("/^" . str_replace(".", "\\.", str_replace("/", "\\/", home_url())) . "\\/$key\\/?(\?.*)?$/", $_SERVER["REQUEST_SCHEME"] . "://" . $_SERVER["HTTP_HOST"] . $_SERVER["REQUEST_URI"]))
                if ($key == "credits") {
                    require "frontend/credits.php";
                    exit;
                } else
                    $this->custom_page($key);
        foreach (["ticket", "device", "model", "room", "discord"] as $key)
            if (array_key_exists($key, $wp->query_vars))
                $this->custom_page($key);
    }

    function custom_page($page)
    {
        require "frontend/$page.php";
        global $wp_query;
        if (custom_page_title() == null) {
            $wp_query->is_404 = true;
        } else {
            $wp_query->is_single = true;
            global $post;
            $post = new WP_Post((object)["post_author" => 1]);
            _wp_admin_bar_init();
            add_action("wp_before_admin_bar_render", [$this, "remove_customizer"]);
            add_filter("the_title", "custom_page_title");
            add_filter("hestia_single_post_meta", "custom_page_subtitle");
            get_header();
            do_action("hestia_before_index_wrapper");
            custom_page();
            get_footer();
            exit;
        }
    }

    function remove_customizer()
    {
        global $wp_admin_bar;
        $wp_admin_bar->remove_node("customize");
    }

    function add_widget()
    {
        wp_add_dashboard_widget("it-desk-stats", "IT Desk Statistiken", [$this, "widget"]);
    }

    function widget()
    {
        require "admin/dashboard.php";
    }

    function admin_page_tickets()
    {
        require "admin/tickets.php";
    }

    function admin_page_archive()
    {
        require "admin/archive.php";
    }

    function admin_page_teams()
    {
        require "admin/teams.php";
    }

    function admin_page_devices()
    {
        require "admin/devices.php";
    }

    function admin_page_models()
    {
        require "admin/models.php";
    }

    function admin_page_rooms()
    {
        require "admin/rooms.php";
    }

    function admin_page_issues()
    {
        require "admin/issues.php";
    }

    static function printMessage($type, $message, $dismissible = true)
    {
        echo("<div class=\"" . $type . " notice inline " . ($dismissible ? " is-dismissible" : "") . "\"><p><strong>" . $message . "</strong></p>" . ($dismissible ? "<button type=\"button\" class=\"notice-dismiss\"><span class=\"screen-reader-text\">Diese Meldung ausblenden.</span></button>" : "") . "</div>");
    }

    static function hasUserLevel($userLevel): bool
    {
        if (self::PERMISSION[$userLevel] == "" || current_user_can(self::PERMISSION[$userLevel]))
            return true;
        return $userLevel < 4 ? self::hasUserLevel($userLevel + 1) : false;
    }

    static function userHasUserLevel(WP_User $user, $userLevel): bool
    {
        if (self::PERMISSION[$userLevel] == "" || $user->has_cap(self::PERMISSION[$userLevel]))
            return true;
        return $userLevel < 4 ? self::hasUserLevel($userLevel + 1) : false;
    }

}