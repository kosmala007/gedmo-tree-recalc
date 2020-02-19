<?php

require_once __DIR__.'/../vendor/autoload.php';

use Doctrine\ORM\Tools\Console\ConsoleRunner;
use DevPack\GedmoTreeRecalc\Tests\Factory\EntityManagerFactory;

$entityManager = EntityManagerFactory::create();

return ConsoleRunner::createHelperSet($entityManager);
