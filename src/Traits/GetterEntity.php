<?php
declare(strict_types=1);

namespace ApacheBorys\Retry\Traits;

trait GetterEntity
{
    public function __call(string $name, array $arguments)
    {
        if (substr($name, 0, 3) === 'get' && property_exists($this, lcfirst(substr($name, 3)))) {
            return $this->{lcfirst(substr($name, 3))};
        }

        throw new \Exception(
            sprintf('Method name or property %s does not exist', $name)
        );
    }
}
