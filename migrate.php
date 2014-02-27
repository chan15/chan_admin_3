<?php
include 'main.php';
$path = 'migrations/';

$file = isset($argv[1]) ? $argv[1] : '';
$maxNumber = 0;

if ('' !== $file) {
    // Create table
    if ($handle = opendir($path)) {
        while (false !== ($entry = readdir($handle))) {
            if (false === is_dir($entry) && 'migrations' !== $entry) {
                $number = intval(current(explode('_', $entry)));

                if ($number > $maxNumber) {
                    $maxNumber = $number;
                }
            }
        }

        $maxNumber += 1;
        $file  = $maxNumber . '_' . $file;
        closedir($handle);

        $sql = "CREATE TABLE `table` (\n";
        $sql .= "`id` INT UNSIGNED PRIMARY KEY AUTO_INCREMENT,\n";
        $sql .= "`name` VARCHAR(255) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL,\n";
        $sql .= "`on` TINYINT(1) DEFAULT 1,\n";
        $sql .= "`created_at` TIMESTAMP NULL DEFAULT NULL,\n";
        $sql .= "`updated_at` TIMESTAMP NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP\n";
        $sql .= ") ENGINE=innoDB CHARACTER SET utf8 COLLATE utf8_unicode_ci;\n";
        $handle = fopen($path . $file, 'wb');
        fwrite($handle, $sql);
        fclose($handle);

        echo $file . ' table created';
    }
} else {
    // Migration
    $chan->connect();

    // Check if migrations table exists
    $sql = "DESCRIBE `migrations`";
    $result = $chan->sqlExecute($sql);

    if (false === $result) {
        $sql = file_get_contents($path . 'migrations');
        $chan->sqlExecute($sql);
    }

    $tableName = '';

    // Start to read file and check
    if ($handle = opendir($path)) {
        while (false !== ($entry = readdir($handle))) {
            if (false === is_dir($entry) && 'migrations' !== $entry) {
                $sql = sprintf("SELECT * FROM `migrations` WHERE `name` = %s",
                    $chan->toSql($entry, 'text'));
                $row = $chan->myOneRow($sql);

                if (NULL === $row) {
                    $sql = file_get_contents($path . $entry);
                    $chan->sqlExecute($sql);
                    $tableName .= $entry . '<br>';

                    $chan->table = 'migrations';
                    $chan->addField('name', $entry);
                    $chan->addField('created_at', $chan->retNow(), 'date');
                    $chan->save();
                }
            }
        }

        closedir($handle);
    }

    if ('' === $tableName) {
        echo 'nothing to migrate';
    } else {
        echo $tableName . 'migrated';
    }
}

