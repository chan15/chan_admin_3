<?php

return array(
    'single' => array(
        'host'     => 'localhost',
        'database' => 'test',
        'username' => 'root',
        'password' => 123456
    ),
    'double' => array(
        'host' => array(
            'read'  => 'localhost',
            'write' => 'localhost'
        ),
        'database' => 'test',
        'username' => 'root',
        'password' => 123456
    ),
);
