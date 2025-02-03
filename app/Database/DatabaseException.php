<?php

namespace App\Database;

class DatabaseException extends \Exception {
    protected $query;
    protected $params;

    public function __construct($message = "", $query = null, $params = [], $code = 0, \Throwable $previous = null) {
        parent::__construct($message, $code, $previous);
        $this->query = $query;
        $this->params = $params;
    }

    public function getQuery() {
        return $this->query;
    }

    public function getParams() {
        return $this->params;
    }
} 