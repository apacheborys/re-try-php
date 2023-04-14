<?php
declare(strict_types=1);

namespace ApacheBorys\Retry\Tests\Functional\Container;

use Psr\Container\ContainerInterface;

class FakeContainer implements ContainerInterface
{
    private \SplObjectStorage $storage;

    public function __construct()
    {
        $this->storage = new \SplObjectStorage();
    }

    public function get(string $id): object
    {
        switch ($id) {
            case 'storage':
                return $this->storage;
            default:
                throw new \Exception(sprintf('Can\'t find instance for %s', $id));
        }
    }

    public function has(string $id): bool
    {
        switch ($id) {
            case 'storage':
                return true;
            default:
                return false;
        }
    }
}
