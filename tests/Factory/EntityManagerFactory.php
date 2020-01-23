<?php

declare(strict_types=1);

namespace DevPack\Tests\Factory;

use Doctrine\Common\Annotations\AnnotationReader;
use Doctrine\Common\Annotations\CachedReader;
use Doctrine\Common\Cache\ArrayCache;
use Doctrine\Common\EventManager;
use Doctrine\Common\Persistence\Mapping\Driver\MappingDriverChain;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Mapping\Driver\AnnotationDriver;
use Doctrine\ORM\Tools\Setup;
use Gedmo\DoctrineExtensions;
use Gedmo\Tree\TreeListener;

class EntityManagerFactory
{
    const BASE_PATH = __DIR__.'/../..';

    public static function create(): EntityManager
    {
        $config = Setup::createAnnotationMetadataConfiguration([self::BASE_PATH.'/tests/Entity'], true);

        $cache = new ArrayCache();
        $annotationReader = new AnnotationReader();
        $cachedAnnotationReader = new CachedReader($annotationReader,$cache);
        $driverChain = new MappingDriverChain();

        DoctrineExtensions::registerAbstractMappingIntoDriverChainORM(
            $driverChain, // our metadata driver chain, to hook into
            $cachedAnnotationReader // our cached annotation reader
        );

        $annotationDriver = new AnnotationDriver(
            $cachedAnnotationReader, // our cached annotation reader
            [self::BASE_PATH.'/tests/Entity'] // paths to look in
        );
        $driverChain->addDriver($annotationDriver, 'Entity');

        $config->setMetadataCacheImpl($cache);
        $config->setQueryCacheImpl($cache);

        $evm = new EventManager();

        $treeListener = new TreeListener();
        $treeListener->setAnnotationReader($cachedAnnotationReader);
        $evm->addEventSubscriber($treeListener);

        $conn = [
            'driver' => 'pdo_sqlite',
            'path' => self::BASE_PATH.'/config/db.sqlite',
        ];

        return EntityManager::create($conn, $config, $evm);
    }
}
