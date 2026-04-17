<?php

declare(strict_types=1);

namespace Jomisacu\RolesAndPermissions;

trait ReadsPrivateProperties
{
    /**
     * @return mixed
     */
    public function __get(string $name)
    {
        if (!property_exists($this, $name)) {
            throw new \OutOfBoundsException(sprintf('Undefined property %s::$%s.', static::class, $name));
        }

        return $this->$name;
    }
}
