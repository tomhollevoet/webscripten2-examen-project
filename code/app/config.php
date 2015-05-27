<?php

return array(
    'db.options' => array(
    'driver' => 'pdo_mysql',
    'host' => 'localhost',
    'dbname' => 'immotom',
    'user' => 'root',
    'password' => 'Azerty123',
    'charset' => 'utf8'
),

'repository.repositories' => array(
    'immotom' => 'Immotom\\Repository\\SiteRepository'
)


);