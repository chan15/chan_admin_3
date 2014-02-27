<?php
include 'main.php';
$chan->connect();
$chan->checkMigrations();

$name = 'create_admins_table';
if (NULL === $chan->checkMigrations($name)) {
    $chan->table = 'admins';
    $chan->increments('id');
    $chan->string('name');
    $chan->string('password');
    $chan->boolean('on');
    $chan->timestamp = true;
    $chan->migrate();
    saveToMigarations($name);
}

$name = 'insert_admin_default';
if (NULL === $chan->checkMigrations($name)) {
    $chan->table = 'admins';
    $chan->addField('name', 'admin');
    $chan->addField('password', 1234);
    $chan->addField('on', 1, 'int');
    $chan->addField('created_at', $chan->retNow(), 'date');
    $chan->save();
    saveToMigarations($name);
}

echo 'finished';

function saveToMigarations($name) {
    global $chan;

    $chan->table = 'migrations';
    $chan->addField('name', $name);
    $chan->addField('created_at', $chan->retNow(), 'date');
    $chan->save();
}
