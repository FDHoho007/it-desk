<?php

class TeamsUser
{

    private $teams, $teamsUser, $wpUser, $token, $tokenInvalidate, $conversation, $message;

    public function __construct(Teams $teams, string $teamsUser, ?string $wpUser, ?string $token, ?int $tokenInvalidate, ?string $conversation, ?string $message)
    {
        $this->teams = $teams;
        $this->teamsUser = $teamsUser;
        $this->wpUser = $wpUser;
        $this->token = $token;
        $this->tokenInvalidate = $tokenInvalidate;
        $this->conversation = $conversation;
        $this->message = $message;
    }

    public function getTeams(): Teams
    {
        return $this->teams;
    }

    public function setTeams(Teams $teams): void
    {
        $this->teams = $teams;
    }

    public function getTeamsUser(): string
    {
        return $this->teamsUser;
    }

    public function setTeamsUser(string $teamsUser): void
    {
        $this->teamsUser = $teamsUser;
    }

    public function getWpUser(): ?string
    {
        return $this->wpUser;
    }

    public function setWpUser(?string $wpUser): void
    {
        $this->wpUser = $wpUser;
    }

    public function getToken(): ?string
    {
        return $this->token;
    }

    public function setToken(?string $token): void
    {
        $this->token = $token;
    }

    public function getTokenInvalidate(): ?int
    {
        return $this->tokenInvalidate;
    }

    public function setTokenInvalidate(?int $tokenInvalidate): void
    {
        $this->tokenInvalidate = $tokenInvalidate;
    }

    public function getConversation(): ?string
    {
        return $this->conversation;
    }

    public function setConversation(?string $conversation): void
    {
        $this->conversation = $conversation;
    }

    public function getMessage(): ?string
    {
        return $this->message;
    }

    public function setMessage(?string $message): void
    {
        $this->message = $message;
    }

    public function save()
    {
        ITDesk::getInstance()->getDatabase()->exec("REPLACE INTO " . Constants::DB_TABLE_TEAMS_USER . " VALUES (?, ?, ?, ?, ?, ?);", $this->getTeamsUser(), $this->getWPUser(), $this->getToken(), $this->getTokenInvalidate(), $this->getConversation(), $this->getMessage());
    }

    public function sendMessage(string $msg, array $attachments = [], string $replyToId = null)
    {
        echo($this->getTeams()->getServiceUrl() . "/v3/conversations/" . $this->getConversation() . "/activities" . ($replyToId == null ? "" : "/" . $replyToId) . json_encode(["type" => "message",
                "text" => $msg,
                "attachments" => $attachments
            ]));
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $this->getTeams()->getServiceUrl() . "/v3/conversations/" . $this->getConversation() . "/activities" . ($replyToId == null ? "" : "/" . $replyToId));
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
        $d = ["type" => "message",
            "text" => $msg,
            "attachments" => $attachments
        ];
        if ($replyToId != null)
            $d = array_merge($d, ["replyToId" => $replyToId]);
//        if ($conversation != null)
//            $d = array_merge($d, ["conversation" => $conversation]);
//        if ($from != null)
//            $d = array_merge($d, ["from" => $from]);
//        if ($recipient != null)
//            $d = array_merge($d, ["recipient" => $recipient]);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($d));
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            "Content-Type: application/json",
            "Authorization: Bearer " . $this->getTeams()->getToken()
        ]);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $id = json_decode(curl_exec($ch), true);
        print_r($id);
        curl_close($ch);
        return $id["id"];
    }

    public function updateMessage(string $activityId, string $msg, array $attachments = [])
    {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $this->getTeams()->getServiceUrl() . "/v3/conversations/" . $this->getConversation() . "/activities/" . $activityId);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "PUT");
        $d = ["type" => "message",
            "text" => $msg,
            "replyToId" => $activityId,
            "attachments" => $attachments
        ];
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($d));
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            "Content-Type: application/json",
            "Authorization: Bearer " . $this->getTeams()->getToken()
        ]);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_exec($ch);
        curl_close($ch);
    }

    public function deleteMessage(string $activityId)
    {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $this->getTeams()->getServiceUrl() . "/v3/conversations/" . $this->getConversation() . "/activities/" . $activityId);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "DELETE");
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            "Authorization: Bearer " . $this->getTeams()->getToken()
        ]);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_exec($ch);
        curl_close($ch);
    }

}