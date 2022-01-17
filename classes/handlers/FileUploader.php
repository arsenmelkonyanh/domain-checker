<?php declare(strict_types=1);

namespace DomainChecker\handlers;

/**
 * Class FileUploader used to upload file to files directory.
 *
 * @package DomainChecker\handlers
 */
class FileUploader
{
    /**
     * @var FileUploader|null
     */
    public static ?FileUploader $instance = null;

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
     * FileUploader constructor.
     */
    private function __construct()
    {
        if (!is_dir(UPLOAD_FILE_DIR) && !mkdir($concurrentDirectory = UPLOAD_FILE_DIR) && !is_dir($concurrentDirectory)) {
            throw new \RuntimeException(sprintf('Directory "%s" was not created', $concurrentDirectory));
        }
    }

    /**
     * Uploads given files.
     *
     * @param array $files
     *
     * @return array
     */
    public function upload(array $files): array
    {
        $filesData = [];

        foreach ($files as $index => $file) {
            $path = pathinfo($file['name']);
            // set file name as random string to make sure
            // that same file can be uploaded multiple times
            $filename = $this->getRandomString();
            $ext = $path['extension'];
            $tempName = $file['tmp_name'];
            $fullPath = UPLOAD_FILE_DIR . $filename . "." . $ext;

            if (!file_exists($fullPath)) {
                if (move_uploaded_file($tempName, $fullPath)) {
                    $filesData[] = [
                        'path' => $fullPath,
                        'name' => $path['basename']
                    ];
                }
            }
        }

        return $filesData;
    }

    /**
     * Returns random string
     *
     * @return string
     */
    private function getRandomString(): string
    {
        return md5(uniqid(mt_rand() . microtime(true), true));
    }
}