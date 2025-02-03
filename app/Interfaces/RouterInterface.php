<?php

namespace App\Interfaces;

interface RouterInterface
{
    /**
     * Добавя GET маршрут
     *
     * @param string $path Пътят на маршрута
     * @param array|callable $handler Обработчик на маршрута
     * @param string|null $name Име на маршрута (опционално)
     * @return self
     */
    public function get(string $path, $handler, ?string $name = null): self;

    /**
     * Добавя POST маршрут
     *
     * @param string $path Пътят на маршрута
     * @param array|callable $handler Обработчик на маршрута
     * @param string|null $name Име на маршрута (опционално)
     * @return self
     */
    public function post(string $path, $handler, ?string $name = null): self;

    /**
     * Добавя група от маршрути
     *
     * @param string $prefix Префикс на групата
     * @param callable $callback Функция за конфигуриране на групата
     * @return self
     */
    public function group(string $prefix, callable $callback): self;

    /**
     * Добавя middleware към текущия маршрут или група
     *
     * @param string|callable $middleware
     * @return self
     */
    public function middleware($middleware): self;

    /**
     * Обработва текущата заявка
     *
     * @return mixed
     */
    public function dispatch();

    /**
     * Генерира URL по име на маршрут
     *
     * @param string $name Име на маршрута
     * @param array $params Параметри за URL-а
     * @return string
     */
    public function generateUrl(string $name, array $params = []): string;
} 