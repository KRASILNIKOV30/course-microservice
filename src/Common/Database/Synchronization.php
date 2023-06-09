<?php

declare(strict_types=1);

namespace App\Common\Database;

use Closure;
use Throwable;

class Synchronization
{
    private Connection $connection;

    public function __construct(Connection $connection)
    {
        $this->connection = $connection;
    }


    /**
     * @param Closure $action
     * @return mixed
     * @throws Throwable
     */
    public function doWithTransaction(Closure $action)
    {
        $this->connection->beginTransaction();
        try {
            $result = $action();
            $this->connection->commit();
            return $result;
        } catch (Throwable $exception) {
            $this->connection->rollBack();
            throw $exception;
        }
    }
}
