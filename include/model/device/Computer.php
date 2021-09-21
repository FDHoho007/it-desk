<?php

class Computer extends Nameable
{

    const ID = 6;

    public function __construct(string $id, Model $model, Room $location, ?string $purchaseDate, ?string $notes, ?string $name, ?int $lastHeartBeat)
    {
        parent::__construct($id, $model, $location, $purchaseDate, $notes, $name, $lastHeartBeat);
    }

}