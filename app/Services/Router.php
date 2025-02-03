<?php

namespace App\Services;

use App\Core\Container;
use Exception;

class Router
{
    private array $routes = [];
    private array $middlewares = [];
    private Container $container;
    private ?string $prefix = null;
    private array $groupMiddlewares = [];

    public function __construct(Container $container)
    {
        $this->container = $container;
    }

    public function get(string $path, array|callable $handler, ?string $name = null): void
    {
        $this->addRoute('GET', $path, $handler, $name);
    }

    public function post(string $path, array|callable $handler, ?string $name = null): void
    {
        $this->addRoute('POST', $path, $handler, $name);
    }

    public function put(string $path, array|callable $handler, ?string $name = null): void
    {
        $this->addRoute('PUT', $path, $handler, $name);
    }

    public function delete(string $path, array|callable $handler, ?string $name = null): void
    {
        $this->addRoute('DELETE', $path, $handler, $name);
    }

    public function middleware(string $middleware): void
    {
        $this->middlewares[] = $middleware;
    }

    public function group(string $prefix, callable $callback): void
    {
        $previousPrefix = $this->prefix;
        $previousMiddlewares = $this->groupMiddlewares;

        $this->prefix = $previousPrefix ? $previousPrefix . $prefix : $prefix;
        $this->groupMiddlewares = array_merge($this->groupMiddlewares, $this->middlewares);
        $this->middlewares = [];

        $callback($this);

        $this->prefix = $previousPrefix;
        $this->groupMiddlewares = $previousMiddlewares;
    }

    private function addRoute(string $method, string $path, array|callable $handler, ?string $name): void
    {
        $path = $this->prefix ? $this->prefix . $path : $path;
        
        $this->routes[] = [
            'method' => $method,
            'path' => $path,
            'handler' => $handler,
            'name' => $name,
            'middlewares' => array_merge($this->groupMiddlewares, $this->middlewares)
        ];
        
        $this->middlewares = [];
    }

    public function dispatch(): void
    {
        $method = $_SERVER['REQUEST_METHOD'];
        $path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);

        foreach ($this->routes as $route) {
            $pattern = $this->getRoutePattern($route['path']);
            
            if (preg_match($pattern, $path, $matches) && $route['method'] === $method) {
                array_shift($matches); // Remove the full match
                
                // Run middlewares
                foreach ($route['middlewares'] as $middleware) {
                    $middlewareInstance = $this->container->get($middleware);
                    $middlewareInstance->__invoke($this->container);
                }
                
                // Call the handler
                if (is_array($route['handler'])) {
                    [$controller, $method] = $route['handler'];
                    $controllerInstance = $this->container->get($controller);
                    echo $controllerInstance->$method(...$matches);
                } else {
                    echo $route['handler'](...$matches);
                }
                
                return;
            }
        }

        // No route found
        header("HTTP/1.0 404 Not Found");
        echo "404 Not Found";
    }

    private function getRoutePattern(string $path): string
    {
        return '#^' . preg_replace('#\{([a-zA-Z0-9_]+)\}#', '([^/]+)', $path) . '$#';
    }
} 