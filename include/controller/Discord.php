<?php

class Discord {

    public function __construct(private string $botToken) {
    }

    public function api(string $method, string $url, string $body = "", array $header = ["Authorization: Bot ", "Content-Type: application/json"]) {
        if($header[0] == "Authorization: Bot ")
            $header[0] .= $this->botToken;
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, "https://discord.com/api/v9/$url");
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $method);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $header);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $body);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $response = curl_exec($ch);
        curl_close ($ch);
        return json_decode($response, true);
    }
    
    public function reply(array $body, string $message) {
        $this->api("POST", "interactions/" . $body["id"] . "/" . $body["token"] . "/callback", json_encode(["type" => 4, "data" => ["content" => $message, "flags" => 1<<6]]));
    }

}