<?php
declare(strict_types=1);

namespace App\Core;

use ReflectionClass;
use ReflectionMethod;
use RuntimeException;

/**
 * A simple DI container to support autowiring.
 */
class Container
{
    private array $instances = [];

    /**
     * Get an instance of a class with autowiring.
     */
    public function get(string $class): object
    {
        if (isset($this->instances[$class])) {
            return $this->instances[$class];
        }

        return $this->instances[$class] = $this->resolve($class);
    }

    /**
     * Resolve a class dependencies.
     */
    private function resolve(string $class): object
    {
        $reflection = new ReflectionClass($class);

        if (!$reflection->isInstantiable()) {
            throw new RuntimeException("Class $class is not instantiable");
        }

        $constructor = $reflection->getConstructor();

        if (null === $constructor) {
            return new $class();
        }

        $parameters = $constructor->getParameters();
        $dependencies = [];

        foreach ($parameters as $parameter) {
            $type = $parameter->getType();

            if (null === $type) {
                throw new RuntimeException("Cannot resolve parameter {$parameter->getName()} in $class: missing type hint");
            }

            if (!$type instanceof \ReflectionNamedType) {
                throw new RuntimeException("Cannot resolve parameter {$parameter->getName()} in $class: complex types (union/intersection) not supported");
            }

            if ($type->isBuiltin()) {
                if ($parameter->isDefaultValueAvailable()) {
                    $dependencies[] = $parameter->getDefaultValue();
                    continue;
                }
                throw new RuntimeException("Cannot resolve built-in parameter {$parameter->getName()} in $class: no default value");
            }

            $dependencyClass = $type->getName();
            
            // Handle Interfaces
            if (interface_exists($dependencyClass)) {
                try {
                    $dependencyClass = $this->resolveInterface($dependencyClass);
                } catch (RuntimeException $e) {
                    if ($parameter->isDefaultValueAvailable()) {
                        $dependencies[] = $parameter->getDefaultValue();
                        continue;
                    }
                    throw $e;
                }
            }

            $dependencies[] = $this->get($dependencyClass);
        }

        return $reflection->newInstanceArgs($dependencies);
    }

    /**
     * Simple interface mapping.
     */
    private function resolveInterface(string $interface): string
    {
        return match ($interface) {
            'App\Repository\LeaveRepositoryInterface' => 'App\Repository\LeaveRepository',
            'Symfony\Component\EventDispatcher\EventDispatcherInterface' => 'Symfony\Component\EventDispatcher\EventDispatcher',
            'Symfony\Contracts\Cache\CacheInterface' => 'Symfony\Component\Cache\Adapter\FilesystemAdapter',
            default => throw new RuntimeException("No implementation found for interface $interface"),
        };
    }
}
