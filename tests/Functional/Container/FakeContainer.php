<?php
declare(strict_types=1);

namespace ApacheBorys\Retry\Tests\Functional\Container;

use Psr\Container\ContainerInterface;

class FakeContainer implements ContainerInterface
{
    private array $storage;

    public function __construct()
    {
        $this->storage = [];
    }

    public function get(string $id): object
    {
        if (isset($this->storage[$id])) {
            return $this->storage[$id];
        }

        throw new \Exception(sprintf('Can\'t find instance for %s', $id));
    }

    public function has(string $id): bool
    {
        return isset($this->storage[$id]);
    }

    public function set(string $id, object $object): void
    {
        $this->storage[$id] = $object;
    }
}
