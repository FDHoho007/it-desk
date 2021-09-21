<?php

class Config
{

    private $file;
    private $contents;

    public function __construct($file)
    {
        $this->file = $file;
        $this->load();
    }

    public function load(): void
    {
        $this->contents = json_decode(file_get_contents($this->file), true);
    }

    public function save(): void
    {
        file_put_contents($this->file, json_encode($this->contents));
    }

    public function getContents(): array
    {
        return $this->contents;
    }

    public function setContents(array $contents): void
    {
        $this->contents = $contents;
    }

}