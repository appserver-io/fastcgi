<?php
require __DIR__ . '/../vendor/autoload.php';

$factory = new \Crunch\FastCGI\ConnectionFactory(new \Socket\Raw\Factory());

$factory->listen('0.0.0.0:8080', function (\Crunch\FastCGI\Connection $connection) {
    // Currently no idea what to do here
    var_dump($connection);
});
