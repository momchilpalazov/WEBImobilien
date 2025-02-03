<?php

namespace App\Interfaces;

interface MiddlewareInterface
{
    /**
     * Обработва заявката
     *
     * @param ContainerInterface $container
     * @return mixed
     */
    public function __invoke(ContainerInterface $container);
} 