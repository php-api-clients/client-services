<?php

use React\EventLoop\Factory;
use WyriHaximus\Travis\AsyncClient;
use WyriHaximus\Travis\Resource\RepositoryInterface;
use function WyriHaximus\ApiClient\resource_pretty_print;

require dirname(__DIR__) . DIRECTORY_SEPARATOR . 'vendor/autoload.php';

$loop = Factory::create();
$client = new AsyncClient($loop);

$repos = [
    'WyriHaximus/php-travis-client',
];

if (count($argv) > 1) {
    unset($argv[0]);
    foreach ($argv as $repo) {
        $repos[] = $repo;
    }
}

foreach ($repos as $repo) {
    $client->repository($repo)->then(function (RepositoryInterface $repo) {
        resource_pretty_print($repo);
    });
}

$loop->run();
