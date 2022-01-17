CREATE TABLE `domains`
(
    `id`          int(10) AUTO_INCREMENT,
    `file_id`     int(10) NOT NULL,
    `domain`      VARCHAR(255) NOT NULL,
    `is_valid`    TINYINT(1) NOT NULL DEFAULT 0,
    `expire_date` DATE,
    PRIMARY KEY (`id`),
    FOREIGN KEY (`file_id`) REFERENCES `domain_files`(`id`)
)