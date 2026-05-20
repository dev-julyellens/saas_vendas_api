<?php

namespace App\Core\DTOs;

/**
 * DTO imutável — transporta dados entre camadas sem expor Model/Eloquent.
 * Reduz acoplamento e documenta contratos de entrada/saída da API.
 */
abstract readonly class BaseDTO
{
    public function toArray(): array
    {
        return get_object_vars($this);
    }

    public static function fromArray(array $data): static
    {
        $reflection = new \ReflectionClass(static::class);
        $constructor = $reflection->getConstructor();

        if ($constructor === null)
        {
            return $reflection->newInstance();
        }

        $args = [];
        foreach ($constructor->getParameters() as $parameter)
        {
            $name = $parameter->getName();
            $args[$name] = $data[$name] ?? ($parameter->isDefaultValueAvailable() ? $parameter->getDefaultValue() : null);
        }

        return $reflection->newInstanceArgs($args);
    }
}
