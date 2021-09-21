<?php

class Mail {

    const TITLE = [
        "create"  => "[%blogname%] Neues Ticket #%id% (%devicesc%)",
        "message" => "[%blogname%] Neue Antwort auf Ticket #%id% (%devicesc%)",
        "status"  => "[%blogname%] Zustandsänderung bei Ticket #%id% (%devicesc%)",
        "operator" => "[%blogname%] Ticket #%id% (%devicesc%) wird nun von %operator% bearbeitet"
    ];

    static function format(string $msg, Event $event)
    {
        $device     = $event->getTicket()->getDevice();
        $motivation = json_decode(file_get_contents(dirname(__FILE__) . "/../../wordpress/frontend/mail/motivation.json"), true);
        $msg        = str_replace("%blogname%", get_option("blogname"), $msg);
        $msg        = str_replace("%id%", $event->getTicket()->getId(), $msg);
        $msg        = str_replace("%issue%", $event->getTicket()->getIssue()->getTitle(), $msg);
        $msg        = str_replace("%device%", Constants::ARTIKEL[$device->getModel()->getType()] . " " . Constants::TYPES[$device->getModel()->getType()] . ($device instanceof Nameable && $device->getName() != null ? " (" . $device->getName() . ")" : ""), $msg);
        $msg        = str_replace("%devicesc%", Constants::TYPES[$device->getModel()->getType()] . "/" . $device->getLocation()->getId(), $msg);
        $msg        = str_replace("%room%", $event->getTicket()->getDevice()->getLocation()->getId(), $msg);
        $msg        = str_replace("%invoker%", $event->getInvoker() == - 1 ? "Anonym" : get_user_by("ID", $event->getInvoker())->display_name, $msg);
        if ($event instanceof MessageEvent)
            $msg = str_replace("%message%", str_replace("\n", "<br>", $event->getMeta()), $msg);
        if ($event instanceof StatusEvent)
            $msg = str_replace("%status%", Constants::STATUS[$event->getMeta()], $msg);
        if ($event instanceof OperatorEvent)
            $msg = str_replace("%operator%", get_user_by("ID", $event->getMeta())->display_name, $msg);
        $msg = str_replace("%url%", home_url() . "/ticket/" . $event->getTicket()->getId(), $msg);
        $msg = str_replace("%motivation%", $motivation[rand(0, sizeof($motivation) - 1)], $msg);
        return $msg;
    }

    static function send(Event $event)
    {
        if ($event::MAIL != null) {
            $title = self::format(self::TITLE[$event::MAIL], $event);
            $msg   = self::format(file_get_contents(dirname(__FILE__) . "/../../wordpress/frontend/mail/" . $event::MAIL . ".html"), $event);
            foreach (get_users() as $user)
                if ($user->ID != $event->getInvoker() && Profile::email_status($user, $event::MAIL) && (($event instanceof CreateEvent && $event->getTicket()->canViewUser($user) || ($event->getTicket()->getAuthor() == $user->ID || $event->getTicket()->getOperator() == $user->ID))))
                    wp_mail($user->user_email, $title, str_replace("%user%", $user->display_name, $msg), array('Content-Type: text/html; charset=UTF-8'));
        }
    }

}