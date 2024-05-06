<?php

$db=[
        'host'=>'localhost',
        'user'=>'phpmyadmin',
        'pass'=>'root',
        'name'=>'LaBenditera',
        'options' => [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
            ],
    ];


return $db;
