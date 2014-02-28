<?php
include 'main.php';
$chan->connect();
$chan->checkMigrations();

$chan->migrationName = 'create_admins_table';
if (NULL === $chan->checkMigrations()) {
    $chan->table = 'admins';
    $chan->increments('id');
    $chan->string('name');
    $chan->string('password');
    $chan->boolean('on');
    $chan->timestamp = true;
    $chan->migrate();
}

$chan->migrationName = 'insert_admin_default';
if (NULL === $chan->checkMigrations()) {
    $chan->table = 'admins';
    $chan->addField('name', 'admin');
    $chan->addField('password', 1234);
    $chan->addField('on', 1, 'int');
    $chan->addField('created_at', $chan->retNow(), 'date');
    $chan->save();
    $chan->migrate();
}

echo 'all migration finished';
