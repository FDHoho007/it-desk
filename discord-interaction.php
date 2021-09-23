<?php

require_once "include/it.php";
require_once "../elliptic-php/EdDSA.php";

const DEBUG = false;

$discord = ITDesk::getInstance()->getDiscord();
$config = ITDesk::getInstance()->getConfig()->getContents()["discord"];
$headers = getallheaders();
$body = file_get_contents("php://input");
$key = (new \Elliptic\EdDSA("ed25519"))->keyFromPublic($config["pubKey"]);
if($_SERVER["REQUEST_METHOD"] == "POST") {
	if((isset($headers["x-signature-ed25519"]) && isset($headers["x-signature-timestamp"]) && $key->verify(array_merge(unpack("C*", $headers["x-signature-timestamp"]), unpack("C*", $body)), $headers["x-signature-ed25519"])) || DEBUG) {
		$body = json_decode($body, true);
		if($body["type"] == 1)
			echo(json_encode(["type" => 1]));
		else if($body["type"] == 2 && isset($body["member"])) {
            $gid = $config["guildId"];
            $uid = $body["member"]["user"]["id"];
			if($body["data"]["name"] == "jahrgangsstufe") {
				$value = $body["data"]["options"][0]["value"];
				$roles = $discord->api("GET", "guilds/$gid/members/$uid")["roles"];
				foreach ($config["jahrgangsstufen"] as $stufe => $role)
					if($value == $stufe && !in_array($role, $roles))
						$discord->api("PUT", "guilds/$gid/members/$uid/roles/$role");
					else if($value != $stufe && in_array($role, $roles))
						$discord->api("DELETE", "guilds/$gid/members/$uid/roles/$role");
				$discord->reply($body, "Deine Jahrgangsstufe wurde auf $value aktualisiert.");
			}
			else if($body["data"]["name"] == "verify") {
				$subcommand = $body["data"]["options"][0]["name"];
				if($subcommand == "wordpress")
					$discord->reply($body, "Bitte melde dich unter https://it.student-gymp.de/discord/verify/$uid an, um deine Mitgliedschaft zu bestätigen.");
				else if($subcommand == "code") {
					$codes = [];
					foreach($discord->api("GET", "channels/" . $config["verificationCodeChannel"] . "/messages") as $msg)
						array_push($codes, $msg["content"]);
					if(in_array($body["data"]["options"][0]["options"][0]["value"], $codes)) {
						$discord->api("PUT", "guilds/$gid/members/$uid/roles/" . $config["verificationRole"]);
						$discord->reply($body, "Du bist nun als IT Crowd Mitglied verifiziert.");
					}
					else
						$discord->reply($body, "Wir konnten nicht verifizieren, dass du ein IT Crow Mitglied bist.");
				}
				else if($subcommand == "hash") {
					$options = $body["data"]["options"][0]["options"];
					$params = [];
					for($i = 0; $i<sizeof($options); $i++)
						$params[$options[$i]["name"]] = $options[$i]["value"];
					if($params["hash"] == hash_hmac("sha256", $params["user"], ITDesk::getInstance()->getConfig()->getContents()["hashSecret"])) {
						$discord->api("PUT", "guilds/$gid/members/$uid/roles/" . $config["verificationRole"]);
						$discord->reply($body, "Du bist nun als IT Crowd Mitglied verifiziert.");
					}
					else
						$discord->reply($body, "Wir konnten nicht verifizieren, dass du ein IT Crow Mitglied bist.");
				}
			}
		}
	}
	else
		header("HTTP/1.1 401 invalid request signature");
}
else
	header("HTTP/1.1 405 Method not allowed");