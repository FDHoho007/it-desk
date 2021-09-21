<?php

class Inventory
{

    private $devices = [], $models = [], $rooms = [];

    public function __construct(array $devices, array $models, array $rooms, Database $database)
    {
        foreach ($models as $model) {
            $links = [];
            $pics = [];
            $connections = [];
            foreach ($database->queryAll("SELECT * FROM " . Constants::DB_TABLE_MODEL_DETAILS . " WHERE Model=?;", $model["ID"]) as $detail)
                if ($detail["Variable"] == "link")
                    $links[explode(Constants::URL_SEPARATOR, $detail["Value"])[0]] = explode(Constants::URL_SEPARATOR, $detail["Value"])[1];
                else if ($detail["Variable"] == "pic")
                    array_push($pics, $detail["Value"]);
                else if ($detail["Variable"] == "connection")
                    array_push($connections, $detail["Value"]);
            array_push($this->models, new Model($model["ID"], $model["Company"], $model["Type"], $links, $pics, $connections));
        }
        foreach ($rooms as $room)
            array_push($this->rooms, new Room($room["ID"], $room["Floor"], $room["Visibility"]));
        foreach ($devices as $device) {
            $model = $this->getModel($device["Model"]);
            $room = $this->getRoom($device["Location"]);
            $purchaseDate = $device["PurchaseDate"] == null ? null : substr($device["PurchaseDate"], 0, -3);
            $notes = $device["Notes"];
            switch ($model->getType()) {
                case DocumentCamera::ID:
                    array_push($this->devices, new DocumentCamera($device["ID"], $model, $room, $purchaseDate, $notes, $device["RemoteControl"]));
                    break;
                case Beamer::ID:
                    array_push($this->devices, new Beamer($device["ID"], $model, $room, $purchaseDate, $notes, $device["RemoteControl"]));
                    break;
                case Screen::ID:
                    array_push($this->devices, new Screen($device["ID"], $model, $room, $purchaseDate, $notes));
                    break;
                case Printer::ID:
                    array_push($this->devices, new Printer($device["ID"], $model, $room, $purchaseDate, $notes, $device["Name"], $device["LastHeartBeat"]));
                    break;
                case Laptop::ID:
                    array_push($this->devices, new Laptop($device["ID"], $model, $room, $purchaseDate, $notes, $device["Name"], $device["LastHeartBeat"]));
                    break;
                case Computer::ID:
                    array_push($this->devices, new Computer($device["ID"], $model, $room, $purchaseDate, $notes, $device["Name"], $device["LastHeartBeat"]));
                    break;
            }
        }
    }

    public function getDevices(): DataSet
    {
        return new DataSet($this->devices);
    }

    public function getModels(): DataSet
    {
        return new DataSet($this->models);
    }

    public function getRooms(): DataSet
    {
        return new DataSet($this->rooms);
    }

    /* Redirect Functions */

    public function getDevice(?string $id): ?Device
    {
        return $this->getDevices()->getId($id);
    }

    public function getModel(?string $id): ?Model
    {
        return $this->getModels()->getId($id);
    }

    public function getRoom(?string $id): ?Room
    {
        return $this->getRooms()->getId($id);
    }

}