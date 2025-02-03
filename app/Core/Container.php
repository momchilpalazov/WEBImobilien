<?php

namespace App\Core;

use App\Interfaces\ContainerInterface;
use Closure;
use Exception;
use ReflectionClass;
use ReflectionParameter;

class Container implements ContainerInterface
{
    private static ?Container $instance = null;
    private array $bindings = [];
    private array $instances = [];

    private function __construct()
    {
    }

    public static function getInstance(): Container
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    public function set(string $id, callable $factory): void
    {
        $this->bindings[$id] = $factory;
    }

    public function get(string $id)
    {
        return self::resolve($id);
    }

    public function has(string $id): bool
    {
        return isset($this->bindings[$id]) || isset($this->instances[$id]);
    }

    public static function singleton(string $abstract, Closure $concrete): void
    {
        $container = self::getInstance();
        $container->bindings[$abstract] = $concrete;
    }

    public static function resolve(string $abstract)
    {
        $container = self::getInstance();

        // Проверка за вече създадена инстанция
        if (isset($container->instances[$abstract])) {
            return $container->instances[$abstract];
        }

        // Проверка за регистриран binding
        if (isset($container->bindings[$abstract])) {
            $concrete = $container->bindings[$abstract];
            $instance = $concrete($container);
            $container->instances[$abstract] = $instance;
            return $instance;
        }

        // Опит за автоматично създаване на инстанция
        try {
            $reflector = new ReflectionClass($abstract);

            if (!$reflector->isInstantiable()) {
                throw new Exception("Class {$abstract} is not instantiable");
            }

            $constructor = $reflector->getConstructor();

            if (is_null($constructor)) {
                return new $abstract();
            }

            $parameters = $constructor->getParameters();
            $dependencies = $container->resolveDependencies($parameters);

            $instance = $reflector->newInstanceArgs($dependencies);
            $container->instances[$abstract] = $instance;
            
            return $instance;
        } catch (Exception $e) {
            throw new Exception("Cannot resolve class {$abstract}: " . $e->getMessage());
        }
    }

    private function resolveDependencies(array $parameters): array
    {
        $dependencies = [];

        foreach ($parameters as $parameter) {
            $dependency = $parameter->getType() && !$parameter->getType()->isBuiltin()
                ? $parameter->getType()->getName()
                : null;

            if ($dependency) {
                $dependencies[] = self::resolve($dependency);
            } elseif ($parameter->isDefaultValueAvailable()) {
                $dependencies[] = $parameter->getDefaultValue();
            } else {
                throw new Exception("Cannot resolve dependency {$parameter->name}");
            }
        }

        return $dependencies;
    }

    public static function mock(string $abstract, $mock): void
    {
        $container = self::getInstance();
        $container->instances[$abstract] = $mock;
    }

    public static function clear(): void
    {
        $container = self::getInstance();
        $container->bindings = [];
        $container->instances = [];
    }
} 