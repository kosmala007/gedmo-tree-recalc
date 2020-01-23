<?php

declare(strict_types=1);

namespace DevPack\Tests\Service;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Tools\SchemaTool;

class DatabaseService
{
    public static function databaseUp(EntityManagerInterface $em)
    {
        $schemaTool = new SchemaTool($em);

        $metadatas = $em->getMetadataFactory()->getAllMetadata();

        $schemaTool->dropSchema($metadatas);
        $schemaTool->createSchema($metadatas);
    }
}
