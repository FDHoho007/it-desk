<?php

class Webhooks {

    private $webhooks = [];

    public function __construct($urls)
    {
        foreach ($urls as $url)
            array_push($this->webhooks, $url["URL"]);
    }

    public function trigger(CreateEvent $event)
    {
        $ticket = $event->getTicket();
		if(!$ticket->isAdminOnly()) {
			$payload = ["content" => "Soeben wurde ein neues Ticket erstellt!", "embeds" => [[
				"title" => "Ticket #" . $ticket->getId(),
				"url" => home_url() . "/ticket/" . $ticket->getId(),
				"color" => 2589371,
				"fields" => [
					[
						"name" => "Problem",
						"value" => $event->getTicket()->getIssue()->getTitle(),
						"inline" => true
					],
					[
						"name" => "betroffenes Gerät",
						"value" => $ticket->getDevice()->getLocation()->getId() . "/" . Constants::TYPES[$ticket->getDevice()->getModel()->getType()],
						"inline" => true
					]
				],
				"author" => [
					"name" => $ticket->getAuthor() == - 1 ? "Anonym" : get_user_by("ID", $ticket->getAuthor())->display_name,
					"icon_url" => $ticket->getAuthor() == - 1 ? "https://secure.gravatar.com/avatar/84013c34db30179ccfc3576255d2e89f?s=96&d=mm&f=y&r=g" : get_avatar_url($ticket->getAuthor())
				]
			]]];
			foreach ($this->webhooks as $webhook) {
				$ch = curl_init();
				curl_setopt($ch, CURLOPT_URL, $webhook);
				curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
				curl_setopt($ch, CURLOPT_POST, true);
				curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));
				curl_setopt($ch, CURLOPT_HTTPHEADER, ["Content-Type: application/json"]);
				echo(curl_exec($ch));
				curl_close($ch);
			}
		}
    }

}
