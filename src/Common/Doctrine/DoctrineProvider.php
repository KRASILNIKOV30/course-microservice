<?php

declare(strict_types=1);

namespace App\Common\Doctrine;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\DriverManager;
use Doctrine\DBAL\Exception;
use Doctrine\DBAL\Tools\DsnParser;
use Doctrine\ORM\Configuration;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Exception\MissingMappingDriverImplementation;
use Doctrine\ORM\ORMSetup;

final class DoctrineProvider
{
    private const METADATA_DIRS = [
        __DIR__ . '/src/Model/Domain'
    ];

    public static function getEntityManager(): EntityManager
    {
        static $entityManager = null;
        if ($entityManager === null) {
            try {
                $entityManager = new EntityManager(self::getConnection(), self::getConfiguration());
            } catch (MissingMappingDriverImplementation $e) {
                throw new \RuntimeException($e->getMessage(), $e->getCode(), $e);
            }
        }
        return $entityManager;
    }

    public static function getConnection(): Connection
    {
        static $connection = null;
        if ($connection === null) {
            try {
                $connectionParams = self::getConnectionParams();
                $connection = DriverManager::getConnection($connectionParams, self::getConfiguration());
            } catch (Exception $e) {
                throw new \RuntimeException($e->getMessage(), $e->getCode(), $e);
            }
        }
        return $connection;
    }

    private static function getConfiguration(): Configuration
    {
        static $configuration = null;
        if ($configuration === null) {
            $configuration = ORMSetup::createAttributeMetadataConfiguration(
                self::METADATA_DIRS,
                true
            );
        }
        return $configuration;
    }

    private static function getConnectionParams(): array
    {
        $dsn = self::getEnvString();
        return (new DsnParser())->parse($dsn);
    }

    private static function getEnvString(): string
    {
        return "pdo-mysql://root:Zakunbor7839@127.0.0.1/course";
    }
}
