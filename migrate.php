<?php

include 'main.php';
$chan->checkMigrations();

$chan->migrationName = 'create_admins_table';
if (null === $chan->checkMigrations()) {
    $chan->table = 'admins';
    $chan->increments('id');
    $chan->string('username');
    $chan->string('password');
    $chan->boolean('on');
    $chan->timestamp = true;
    $chan->migrate();
}

$chan->migrationName = 'insert_admin_default';
if (null === $chan->checkMigrations()) {
    $chan->table = 'admins';
    $chan->addField('username', 'admin');
    $chan->addField('password', 1234);
    $chan->addField('on', 1, 'int');
    $chan->addField('created_at', $chan->retNow());
    $chan->save();
    $chan->migrate();
}

echo 'all migration finished';
