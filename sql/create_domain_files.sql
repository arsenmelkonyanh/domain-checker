CREATE TABLE `domain_files`
(
    `id`                          int(10) AUTO_INCREMENT,
    `path`                        VARCHAR(255) NOT NULL,
    `original_name`               VARCHAR(255) NOT NULL,
    `status`                      TINYINT(1) NOT NULL DEFAULT 0,
    `domains_count`               int(10),
    `created_at`                  DATETIME(3) NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`)
)