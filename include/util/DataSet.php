<?php

class DataSet implements Iterator {

    private $position;
    private $contents;

    public function __construct($contents)
    {
        $this->contents = $contents;
    }

    public function getContents(): array
    {
        return $this->contents;
    }

    public function getIndex(int $index): object
    {
        return $this->getContents()[$index];
    }

    public function getId(?string $id): ?Identifiable
    {
        if ($id == null)
            return null;
        foreach ($this->contents as $e)
            if ($e instanceof Identifiable && $e->getId() == $id)
                return $e;
        return null;
    }

    public function filter(callable $filter): DataSet
    {
        $contentsNew = [];
        foreach ($this->contents as $e)
            if ($filter($e))
                array_push($contentsNew, $e);
        $this->contents = $contentsNew;
        return $this;
    }

    public function sort(callable $func): DataSet
    {
        usort($this->contents, $func);
        return $this;
    }

    public function current(): mixed
    {
        return $this->contents[$this->position];
    }

    public function next(): void
    {
        ++ $this->position;
    }

    public function key(): mixed
    {
        return $this->position;
    }

    public function valid(): bool
    {
        return isset($this->contents[$this->position]);
    }

    public function rewind(): void
    {
        $this->position = 0;
    }
}