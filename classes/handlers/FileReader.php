<?php declare(strict_types=1);

namespace DomainChecker\handlers;

/**
 * Class FileReader used to read csv file data.
 *
 * @package DomainChecker\handlers
 */
class FileReader
{
    /**
     * @var FileReader|null
     */
    public static ?FileReader $instance = null;

    /**
     * Returns an singleton instance of this class
     *
     * @return self
     */
    public static function getInstance(): self
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }

        return self::$instance;
    }

    /**
     * FileReader constructor.
     */
    private function __construct()
    {
    }


    /**
     * Read data from file.
     *
     * @param string $filePath
     *
     * @return array
     */
    public function getDataFromFile(string $filePath): array
    {
        if (!file_exists($filePath)) {
            return [];
        }

        $rows = array_map(static function ($row) {
            return str_getcsv($row);
        }, file($filePath));

        array_shift($rows);

        $csv = [];

        foreach ($rows as $row) {
            $csv[] = trim($row[0]);
        }

        return $csv;
    }
}