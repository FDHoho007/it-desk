<?php

class Teams
{

    // Update Bot Password here: https://portal.azure.com/#view/Microsoft_AAD_RegisteredApps/ApplicationMenuBlade/~/Credentials/appId/d8193f0a-7b87-4edf-8227-8bab58c79d70/isMSAApp/
    private $users = [];
    private $token = null;

    public function __construct(array $users)
    {
        foreach ($users as $user)
            array_push($this->users, new TeamsUser($this, $user["TeamsUser"], $user["WPUser"], $user["Token"], $user["TokenInvalidate"], $user["Conversation"], $user["Message"]));
    }

    public function getUsers(): DataSet
    {
        return new DataSet($this->users);
    }

    public function cleanup()
    {
        ITDesk::getInstance()->getDatabase()->exec("UPDATE " . Constants::DB_TABLE_TEAMS_USER . " SET Token=NULL, TokenInvalidate=NULL WHERE TokenInvalidate<?;", time());
    }

    public function getServiceUrl(): string
    {
        return "https://smba.trafficmanager.net/de/";
    }

    static function generateToken(): string
    {
        $alphabet = "abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789";
        $token = "";
        for ($i = 0; $i < 6; $i++)
            $token .= $alphabet[rand(0, strlen($alphabet) - 1)];
        return $token;
    }

    function notify(Event $event)
    {
        $card = $event->getCard();
        foreach ($this->getUsers() as $teamsUser)
            if($teamsUser->getWPUser() != null)
                if (($user = get_user_by("ID", $teamsUser->getWPUser()))->ID != $event->getInvoker() && Profile::teams_status($user, $event::MAIL) && (($event instanceof CreateEvent && $event->getTicket()->canViewUser($user) || ($event->getTicket()->getAuthor() == $user->ID || $event->getTicket()->getOperator() == $user->ID))))
                    $teamsUser->sendMessage("", [$card]);
    }

    function getToken(): ?string
    {
        if ($this->token == null) {
            $config = ITDesk::getInstance()->getConfig()->getContents()["teams"];
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, "https://login.microsoftonline.com/botframework.com/oauth2/v2.0/token");
            curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
            curl_setopt($ch, CURLOPT_POSTFIELDS, "grant_type=client_credentials&client_id=" . $config["bot_id"] . "&client_secret=" . $config["bot_password"] . "&scope=https%3A%2F%2Fapi.botframework.com%2F.default");
            curl_setopt($ch, CURLOPT_HTTPHEADER, [
                "Content-Type: application/x-www-form-urlencoded",
            ]);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            $data = json_decode(curl_exec($ch), true);
            if (isset($data["access_token"]))
                $this->token = $data["access_token"];
            else
                return null;
            curl_close($ch);
        }
        return $this->token;
    }

    static function createCard(array $body = [], array $actions = []): array
    {
        return ["contentType" => "application/vnd.microsoft.card.adaptive", "content" => ["type" => "AdaptiveCard", "version" => "1.0", "body" => $body, "actions" => $actions]];
    }

    static function createTextBlock(string $text, string $size = "default"): array
    {
        return ["type" => "TextBlock", "text" => $text, "size" => $size, "wrap" => true];
    }

    static function createUrlAction(string $url, string $title): array
    {
        return ["type" => "Action.OpenUrl", "url" => $url, "title" => $title];
    }

    static function createSubmitAction(array $data, string $title): array
    {
        return ["type" => "Action.Submit", "data" => $data, "title" => $title];
    }

    function sendMessage(string $token, string $method, string $serviceUrl, string $conversationId, string $activityId, ?array $conversation, ?array $from, ?array $recipient, string $msg, array $attachments = [])
    {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $serviceUrl . "/v3/conversations/" . $conversationId . "/activities/" . $activityId);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $method);
        $d = ["type" => "message",
            "text" => $msg,
            "replyToId" => $activityId,
            "attachments" => $attachments
        ];
        if ($conversation != null)
            $d = array_merge($d, ["conversation" => $conversation]);
        if ($from != null)
            $d = array_merge($d, ["from" => $from]);
        if ($recipient != null)
            $d = array_merge($d, ["recipient" => $recipient]);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($d));
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            "Content-Type: application/json",
            "Authorization: Bearer " . $token
        ]);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $id = json_decode(curl_exec($ch), true)["id"];
        curl_close($ch);
        return $id;
    }

    function deleteMessage(string $token, string $serviceUrl, string $conversationId, string $activityId)
    {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $serviceUrl . "/v3/conversations/" . $conversationId . "/activities/" . $activityId);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "DELETE");
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            "Authorization: Bearer " . $token
        ]);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_exec($ch);
        curl_close($ch);
    }

}