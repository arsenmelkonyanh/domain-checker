<?php declare(strict_types=1);

namespace DomainChecker\dal\mappers;

use DomainChecker\dal\connection\Db;
use PDO;

/**
 * Class AbstractMapper used as abstract class for mappers.
 * Contains method getTableName() which returns table of current mapper,
 * method fetchRows() which fetch data from db and method executeQuery() which executes given query.
 *
 * @package DomainChecker\dal\mappers
 */
abstract class AbstractMapper
{
    /**
     * @var Db|null
     */
    private ?Db $db;

    /**
     * AbstractMapper constructor.
     */
    public function __construct()
    {
        $this->db = Db::getInstance();
    }

    /**
     * Returns table name of mapper.
     *
     * @return string
     */
    public function getTableName(): string
    {
        return $this->tableName;
    }

    /**
     * Returns db instance.
     *
     * @return Db
     */
    private function getDb(): Db
    {
        return $this->db;
    }

    /**
     * Fetch data from db by given query and params.
     *
     * @param string $query
     * @param array $params
     * @param int $fetchMode
     *
     * @return array
     */
    final public function fetchRows(string $query, array $params = [], int $fetchMode = PDO::FETCH_ASSOC): array
    {
        $statement = $this->getDb()->getConnection()->prepare($query, $params);

        $statement->setFetchMode($fetchMode);

        $results = $statement->execute($params);

        if (!$results) {
            return [];
        }

        return $statement->fetchAll();
    }

    /**
     * Executes given query with given params.
     *
     * @param string $query
     * @param array $params
     *
     * @return bool
     */
    final public function executeQuery(string $query, array $params = []): bool
    {
        $statement = $this->getDb()->getConnection()->prepare($query, $params);

        if ($statement) {
            return $statement->execute($params);
        }

        return false;
    }

}