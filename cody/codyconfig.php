<?php

/**
 * @see       https://github.com/event-engine/php-code-generator-cody for the canonical source repository
 * @copyright https://github.com/event-engine/php-code-generator-cody/blob/master/COPYRIGHT.md
 * @license   https://github.com/event-engine/php-code-generator-cody/blob/master/LICENSE.md MIT License
 */

declare(strict_types=1);

use EventEngine\CodeGenerator\Cody\Aggregate;
use EventEngine\CodeGenerator\Cody\BoundedContext;
use EventEngine\CodeGenerator\Cody\Command;
use EventEngine\CodeGenerator\Cody\Context;
use EventEngine\CodeGenerator\Cody\Document;
use EventEngine\CodeGenerator\Cody\Event;
use EventEngine\CodeGenerator\Cody\Feature;
use EventEngine\CodeGenerator\Cody\Sync;
use EventEngine\CodeGenerator\Cody\SyncDeleted;
use EventEngine\CodeGenerator\EventEngineAst\Config\EventEngineConfig;
use EventEngine\CodeGenerator\EventEngineAst\Config\PreConfiguredNaming;
use EventEngine\InspectioCody\CodyConfig;
use EventEngine\InspectioGraphCody\EventSourcingAnalyzer;
use EventEngine\InspectioGraphCody\EventSourcingGraph;
use EventEngine\CodeGenerator\EventEngineAst\Metadata;
use EventEngine\CodeGenerator\EventEngineAst\Config\PhpPrinterTrait;

/**
 * business application is mounted to /service in docker-compose.yml
 */
$contextName = 'Acme';
$basePath = '/service';

//$codingStandardFile = $basePath . DIRECTORY_SEPARATOR . 'phpcs.xml.dist';
$codingStandardFile = __DIR__ . '/phpcs.xml.dist';

/**
 * DON'T CHANGE ANY LINES FROM HERE
 */

$composerFile = $basePath . '/composer.json';

$config = new EventEngineConfig();
$config->setBasePath($basePath);
$config->addComposerInfo($composerFile);

$namingConfig = new PreConfiguredNaming($config);
$namingConfig->setDefaultContextName($contextName);

$analyzer = new EventSourcingAnalyzer(
    new EventSourcingGraph(
        $config->getFilterConstName(),
        new Metadata\MetadataFactory(new Metadata\InspectioJson\MetadataFactory())
    )
);

$context = new Context(
    $analyzer,
    ['--standard=' . $codingStandardFile, '--no-cache']
);

$config = $namingConfig->config();

if (in_array(PhpPrinterTrait::class, class_uses($config), true) === true) {
    $config->setPrinter($context->getPrinter());
}

return new CodyConfig(
    $context,
    [
        CodyConfig::HOOK_ON_AGGREGATE => new Aggregate($namingConfig),
        CodyConfig::HOOK_ON_COMMAND => new Command($namingConfig),
        CodyConfig::HOOK_ON_EVENT => new Event($namingConfig),
        CodyConfig::HOOK_ON_DOCUMENT => new Document($namingConfig),
        CodyConfig::HOOK_ON_FEATURE => new Feature($namingConfig),
        CodyConfig::HOOK_ON_BOUNDED_CONTEXT => new BoundedContext($namingConfig),
        CodyConfig::HOOK_ON_SYNC => new Sync(),
        CodyConfig::HOOK_ON_SYNC_UPDATED => new Sync(),
        CodyConfig::HOOK_ON_SYNC_DELETED => new SyncDeleted(),
    ]
);
