<?php

use App\Kernel;
use DAMA\DoctrineTestBundle\Doctrine\DBAL\StaticDriver;
use DG\BypassFinals;
use Doctrine\ORM\Tools\SchemaTool;
use Symfony\Component\Dotenv\Dotenv;

require dirname(__DIR__).'/vendor/autoload.php';

BypassFinals::enable();

if (method_exists(Dotenv::class, 'bootEnv')) {
    (new Dotenv())->bootEnv(dirname(__DIR__).'/.env');
}

if ($_SERVER['APP_DEBUG']) {
    umask(0000);
}

StaticDriver::setKeepStaticConnections(true);

$kernel = new Kernel('test', true);
$kernel->boot();

$em = $kernel->getContainer()->get('doctrine')->getManager();
$metadata = $em->getMetadataFactory()->getAllMetadata();

$schemaTool = new SchemaTool($em);
$schemaTool->dropSchema($metadata);
$schemaTool->createSchema($metadata);

StaticDriver::commit();

$kernel->shutdown();
