<?php

class Server extends Device
{

    const ID = 7;

    public function __construct(string $id, Model $model, Room $location, ?string $purchaseDate, ?string $notes)
    {
        parent::__construct($id, $model, $location, $purchaseDate, $notes);
    }

}