<?php
declare(strict_types=1);

namespace ApacheBorys\Retry\Traits;

trait GetterEntity
{
    public function __call($name, $arguments)
    {
        if (substr($name, 0, 3) === 'get' && property_exists($this, substr($name, 3))) {
            return $this->{strtolower(substr($name, 3))};
        }

        throw new \Exception(
            sprintf('Method name or property %s does not exist', $name)
        );
    }
}
