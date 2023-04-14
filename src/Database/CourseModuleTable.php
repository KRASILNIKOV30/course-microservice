<?php

namespace App\Database;

use App\Model\Domain\Module;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Exception;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;

class CourseModuleTable
{
    private Connection $connection;
    private EntityManagerInterface $entityManager;
    private EntityRepository $repository;
    public function __construct(Connection $connection, EntityManagerInterface $entityManager)
    {
        $this->connection = $connection;
        $this->entityManager = $entityManager;
        $this->repository = $entityManager->getRepository(Module::class);
    }

    public function findOne(string $id): ?Module
    {
        return $this->repository->find($id);
    }

    public function add(Module $module): void
    {
        $this->entityManager->persist($module);
    }

    public function flush(): void
    {
        $this->entityManager->flush();
    }

    /**
     * @param string $moduleId
     * @param string $enrollmentId
     * @return void
     * @throws Exception
     */
    public function enroll(string $moduleId, string $enrollmentId): void
    {
        $query = <<<SQL
            INSERT INTO course_module_status
                (enrollment_id, module_id, progress, duration)
            VALUES
                (?, ?, 0, 0)
            ON DUPLICATE KEY UPDATE
              module_id = module_id
            SQL;

        $params = [$enrollmentId, $moduleId];
        $this->connection->executeQuery($query, $params);
    }

    public function getProgress(string $enrollmentId, string $moduleId): ?int
    {
        $query = <<<SQL
            SELECT
                progress
            FROM course_module_status
            WHERE enrollment_id = ?
                AND module_id = ?
            SQL;

        $params = [$enrollmentId, $moduleId];
        $stmt = $this->connection->execute($query, $params);
        $value = $stmt->fetch(\PDO::FETCH_ASSOC);
        if (!$value) {
            return null;
        }
        return (int)$value['progress'];
    }

    public function getDuration(string $enrollmentId, string $moduleId): ?int
    {
        $query = <<<SQL
            SELECT
                duration
            FROM course_module_status
            WHERE enrollment_id = ?
                AND module_id = ?
            SQL;

        $params = [$enrollmentId, $moduleId];
        $stmt = $this->connection->execute($query, $params);
        $value = $stmt->fetch(\PDO::FETCH_ASSOC);
        if (!$value) {
            return null;
        }
        return (int)$value['duration'];
    }

    public function setProgress(string $enrollmentId, string $moduleId, int $progress): void
    {
        $query = <<<SQL
            UPDATE course_module_status
            SET
                progress = $progress
            WHERE enrollment_id = ?
                AND module_id = ?
            SQL;

        $params = [$enrollmentId, $moduleId];
        $this->connection->execute($query, $params);
    }

    public function increaseDuration(string $enrollmentId, string $moduleId, int $duration): void
    {
        $query = <<<SQL
            UPDATE course_module_status
            SET
                duration = duration + $duration
            WHERE enrollment_id = ?
                AND module_id = ?
            SQL;

        $params = [$enrollmentId, $moduleId];
        $this->connection->execute($query, $params);
    }

    public function delete(string $moduleId)
    {
        $query = <<<SQL
            UPDATE course_material
            SET
                deleted_at = CURRENT_TIMESTAMP
            WHERE
                module_id = ?
            SQL;
        $params = [$moduleId];
        $this->connection->execute($query, $params);
    }

    public function deleteStatus(string $enrollmentId, string $moduleId): void
    {
        $query = <<<SQL
            UPDATE course_module_status
            SET
                deleted_at = CURRENT_TIMESTAMP
            WHERE enrollment_id = ?
                AND module_id = ?
            SQL;
        $params = [$enrollmentId, $moduleId];
        $this->connection->execute($query, $params);
    }
}
