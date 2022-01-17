<?php declare(strict_types=1);

namespace DomainChecker\handlers;

/**
 * Class FileValidator used to validate uploaded files.
 *
 * @package DomainChecker\handlers
 */
class FileValidator
{
    /**
     * Csv file type.
     */
    private const CSV_FILE_TYPE = 'application/vnd.ms-excel';

    /**
     * @var FileValidator|null
     */
    public static ?FileValidator $instance = null;

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
     * FileValidator constructor.
     */
    private function __construct()
    {
    }

    /**
     * Returns validated files needle data.
     *
     * @param array $data
     *
     * @return array
     */
    public function getValidatedFiles(array $data): array
    {
        $validatedFiles = [];

        if (empty($data['files'])) {
            return [];
        }

        $files = $data['files'];

        foreach ($files['type'] as $index => $fileType) {
            if ($fileType !== self::CSV_FILE_TYPE) {
                continue;
            }

            $validatedFiles[] = [
                'name' => $files['name'][$index],
                'tmp_name' => $files['tmp_name'][$index]
            ];
        }

        return $validatedFiles;
    }
}