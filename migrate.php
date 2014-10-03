<?php

include 'main.php';
include 'libs/class.migration.php';

$migration = new Migration;
$migration->checkMigrations();

$migration->migrationName = 'create_admins_table';
if (null === $migration->checkMigrations()) {
    $migration->table = 'admins';
    $migration->increments('id');
    $migration->string('username');
    $migration->string('password');
    $migration->boolean('on');
    $migration->timestamp = true;
    $migration->migrate();
}

$migration->migrationName = 'insert_admin_default';
if (null === $migration->checkMigrations()) {
    $migration->table = 'admins';
    $migration->addField('username', 'admin');
    $migration->addField('password', 1234);
    $migration->addField('on', 1, 'int');
    $migration->addField('created_at', $chan->retNow());
    $migration->save();
    $migration->migrate();
}

echo 'all migration finished';
