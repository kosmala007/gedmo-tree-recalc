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
    const PROJECT_PATH = __DIR__.'/../..';

    const ENTITIES_PATH = self::PROJECT_PATH.'/tests/Entity';

    const CONNECTION = [
        'driver' => 'pdo_sqlite',
        'path' => self::PROJECT_PATH.'/var/db.sqlite',
    ];

    public static function create(): EntityManager
    {
        $annotationReader = new AnnotationReader();
        $cache = new ArrayCache();
        $driverChain = new MappingDriverChain();
        $evm = new EventManager();
        $treeListener = new TreeListener();
        $cachedAnnotationReader = new CachedReader($annotationReader, $cache);

        DoctrineExtensions::registerAbstractMappingIntoDriverChainORM(
            $driverChain,
            $cachedAnnotationReader
        );

        $annotationDriver = new AnnotationDriver(
            $cachedAnnotationReader,
            [self::ENTITIES_PATH]
        );

        $driverChain->addDriver($annotationDriver, 'Entity');

        $config = Setup::createAnnotationMetadataConfiguration(
            [self::ENTITIES_PATH],
            true,
            null,
            $cache
        );
        $config->setMetadataCacheImpl($cache);
        $config->setQueryCacheImpl($cache);
        $config->setAutoGenerateProxyClasses(true);

        $treeListener->setAnnotationReader($cachedAnnotationReader);
        $evm->addEventSubscriber($treeListener);

        return EntityManager::create(self::CONNECTION, $config, $evm);
    }
}
