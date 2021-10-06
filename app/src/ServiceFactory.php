<?php

declare(strict_types=1);

namespace Acme;

use Acme\Console\ConsoleServices;
use Acme\Domain\DomainServices;
use Acme\Http\HttpServices;
use Acme\Infrastructure\InfrastructureServices;
use Acme\Persistence\PersistenceServices;
use Acme\System\SystemServices;
use Codeliner\ArrayReader\ArrayReader;
use EventEngine\Discolight\ServiceRegistry;
use EventEngine\EventEngine;
use EventEngine\JsonSchema\OpisJsonSchema;
use EventEngine\Messaging\MessageProducer;
use Psr\Container\ContainerInterface;
use RuntimeException;

use function array_merge;
use function file_exists;
use function file_put_contents;
use function var_export;

final class ServiceFactory
{
    use ConsoleServices;
    use DomainServices;
    use HttpServices;
    use InfrastructureServices;
    use PersistenceServices;
    use ServiceRegistry;
    use SystemServices;

    /** @var ArrayReader */
    private $config;

    /** @var ContainerInterface */
    private $container;

    public function __construct(array $appConfig)
    {
        $this->config = new ArrayReader($appConfig);
    }

    public function setContainer(ContainerInterface $container): void
    {
        $this->container = $container;
    }

    public function eventEngine(bool $notInitialized = false): EventEngine
    {
        if ($notInitialized) {
            $eventEngine = new EventEngine(new OpisJsonSchema());

            foreach ($this->eventEngineDescriptions() as $description) {
                $eventEngine->load($description);
            }

            return $eventEngine;
        }

        $this->assertContainerIsset();

        return $this->makeSingleton(EventEngine::class, function () {
            $cacheEnabled     = $this->config->mixedValue('event_engine.cache_enabled', false);
            $cachedConfigFile = $this->config->mixedValue('event_engine.cached_config_file', '');

            $schema          = $this->schema();
            $flavour         = $this->flavour();
            $multiModelStore = $this->multiModelStore();
            $logger          = $this->logEngine();

            $messageProducer = null;

            if ($this->container->has(MessageProducer::class)) {
                $messageProducer = $this->container->get(MessageProducer::class);
            }

            if ($cacheEnabled && $cachedConfigFile && file_exists($cachedConfigFile)) {
                $cachedConfig = require $cachedConfigFile;

                $eventEngine = EventEngine::fromCachedConfig(
                    $cachedConfig,
                    $schema,
                    $flavour,
                    $multiModelStore,
                    $logger,
                    $this->container,
                    $multiModelStore,
                    $messageProducer
                );
            } else {
                $eventEngine = new EventEngine($schema);

                foreach ($this->eventEngineDescriptions() as $description) {
                    $eventEngine->load($description);
                }

                $eventEngine->initialize(
                    $flavour,
                    $multiModelStore,
                    $logger,
                    $this->container,
                    $multiModelStore,
                    $messageProducer
                );

                if ($cacheEnabled && $cachedConfigFile) {
                    file_put_contents(
                        $cachedConfigFile,
                        "<?php\nreturn " . var_export($eventEngine->compileCacheableConfig(), true) . ';'
                    );
                }
            }

            return $eventEngine;
        });
    }

    private function assertContainerIsset(): void
    {
        if (null === $this->container) {
            throw new RuntimeException("Main container is not set. Use " . self::class . "::setContainer() to set it.");
        }
    }

    private function eventEngineDescriptions(): array
    {
        return array_merge($this->domainDescriptions(), $this->systemDescriptions());
    }

    protected function assertMandatoryConfigExists(string $path): void
    {
        if (null === $this->config->mixedValue($path)) {
            throw  new RuntimeException("Missing application config for $path");
        }
    }

    protected function config(): ArrayReader
    {
        return $this->config;
    }
}
