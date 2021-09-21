<?php

class DocumentCamera extends RemoteControllable
{

    const ID = 1;

    public function __construct(string $id, Model $model, Room $location, ?string $purchaseDate, ?string $notes, ?string $remoteControl)
    {
        parent::__construct($id, $model, $location, $purchaseDate, $notes, $remoteControl);
    }

}