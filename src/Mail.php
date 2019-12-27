<?php

class Mail
{
	
	const TITLE = ["create" => "[%blogname%] Neues Ticket #%id% (%device%/%category%)", "message" => "[%blogname%] Neue Antwort auf Ticket #%id% (%device%/%category%)"];
	const CREATE = "create";
	const MESSAGE = "message";
	
	private $title;
	private $msg;
	
	function __construct($template, $ticket) {
		$this->title = self::format(self::TITLE[$template], $ticket);
		$this->msg = self::format(file_get_contents(dirname(__FILE__) . "/../mail/" . $template . ".html"), $ticket);
	}
	
	static function format($text, $ticket) {
		return str_replace("%blogname%", get_option("blogname"), str_replace("%id%", $ticket->id, str_replace("%device%", $ticket->getDevice()->getName(), str_replace("%category%", $ticket->getCategory()->getName(), $text))));
	}
	
	function send($user) {
		wp_mail($user->user_email, $this->title, str_replace("%user%", $user->display_name, $this->msg), array('Content-Type: text/html; charset=UTF-8'));
	}
	
}