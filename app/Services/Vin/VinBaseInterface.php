<?php

namespace App\Services\Vin;

use Database\Vin\BaseData;

abstract class VinBaseInterface implements VinInterface
{
    protected string $vin;

    protected int $year;

    public function __construct(string $vin)
    {
        $this->vin = $vin;
        $this->year = $this->determineYear();

        return $this->toArray();
    }

    protected function determineYear(): int
    {
        return BaseData::YEARS[substr($this->vin, 9, 1)];
    }

    protected function getValueFromList(array $list, int $position, int $length = 1): string|int|null
    {
        $key = substr($this->vin, $position - 1, $length);

        if (! array_key_exists($key, $list)) {
            return null;
        }

        return $list[$key];
    }

    public function getNote(): ?string
    {
        return null;
    }

    public function getYear(): int
    {
        return $this->year;
    }

    public function toArray(): array
    {
        return [
            'assembly' => $this->getAssembly(),
            'engine' => $this->getEngine(),
            'length' => $this->getLength(),
            'manufacturer' => $this->getManufacturer(),
            'model' => $this->getModel(),
            'note' => $this->getNote(),
            'propulsion' => $this->getPropulsion(),
            'sequence' => $this->getSequence(),
            'year' => $this->getYear(),
        ];
    }

    public function hasInfo(): bool
    {
        return true;
    }
}
