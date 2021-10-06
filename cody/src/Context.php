<?php

/**
 * @see       https://github.com/event-engine/php-code-generator-cody for the canonical source repository
 * @copyright https://github.com/event-engine/php-code-generator-cody/blob/master/COPYRIGHT.md
 * @license   https://github.com/event-engine/php-code-generator-cody/blob/master/LICENSE.md MIT License
 */

declare(strict_types=1);

namespace EventEngine\CodeGenerator\Cody;

include 'vendor/squizlabs/php_codesniffer/autoload.php';

use EventEngine\CodeGenerator\Cody\Printer\CodeSnifferPrinter;
use EventEngine\CodeGenerator\Cody\Printer\PrettyPrinter;
use EventEngine\CodeGenerator\Cody\Printer\Standard;
use EventEngine\InspectioCody\CodyContext;
use EventEngine\InspectioGraphCody\EventSourcingAnalyzer;
use OpenCodeModeling\CodeGenerator\Transformator\PhpCodeSniffer;
use PHP_CodeSniffer\Config;
use PhpParser\Parser;
use PhpParser\ParserFactory;
use PhpParser\PrettyPrinterAbstract;

final class Context implements CodyContext
{
    public EventSourcingAnalyzer $analyzer;

    public Parser $parser;
    public PrettyPrinter $printer;

    public function __construct(EventSourcingAnalyzer $analyzer, array $csConfig = [])
    {
        $this->analyzer = $analyzer;

        $cs = new PhpCodeSniffer(new Config(array_merge(['inline'], $csConfig), false));

        $this->parser = (new ParserFactory())->create(ParserFactory::ONLY_PHP7);
        $this->printer = new CodeSnifferPrinter(
            new Standard(['shortArraySyntax' => true]),
            $cs
        );
    }

    public function getPrinter(): PrettyPrinterAbstract
    {
        return $this->printer->getPrettyPrinter();
    }

    public function isFullSyncRequired(): bool
    {
        return $this->analyzer->graph()->count() === 0;
    }

    public function clearGraph(): void
    {
        $this->analyzer->clearGraph();
    }

    public function analyzerStats(float $time): string
    {
        $mem = \round(\memory_get_usage() / 1048576, 2) . ' MB';
        $memPeak = \round(\memory_get_peak_usage() / 1048576, 2) . ' MB';

        return "\n⌛ Generated in $time s, memory usage $mem / $memPeak\n" .
            "Commands: {$this->analyzer->commandMap()->count()} | " .
            "Aggregates: {$this->analyzer->aggregateMap()->count()} | " .
            "Events: {$this->analyzer->eventMap()->count()} | " .
            "Documents: {$this->analyzer->documentMap()->count()}\n" .
            "Policies: {$this->analyzer->policyMap()->count()} | " .
            "External Systems: {$this->analyzer->externalSystemMap()->count()} | " .
            "UIs/APIs: {$this->analyzer->uiMap()->count()}\n" .
            "Features: {$this->analyzer->featureMap()->count()} | " .
            "Bounded Contexts: {$this->analyzer->boundedContextMap()->count()}\n" .
            "Total: {$this->analyzer->graph()->count()}";
    }

    public function microtimeFloat(): float
    {
        [$usec, $sec] = \explode(' ', \microtime());

        return (float) $usec + (float) $sec;
    }
}
