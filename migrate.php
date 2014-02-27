<?php
include 'main.php';
$path = 'db/';
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
