<?php

class Beamer extends RemoteControllable
{

    const ID = 2;

    public function __construct(string $id, Model $model, Room $location, ?string $purchaseDate, ?string $notes, ?string $remoteControl)
    {
        parent::__construct($id, $model, $location, $purchaseDate, $notes, $remoteControl);
    }

}