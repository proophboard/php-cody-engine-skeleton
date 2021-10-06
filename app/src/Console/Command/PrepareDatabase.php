<?php

declare(strict_types=1);

namespace Acme\Console\Command;

use EventEngine\DocumentStore\Index;
use EventEngine\DocumentStore\Postgres\Metadata\MetadataColumnIndex;
use EventEngine\EventEngine;
use EventEngine\Persistence\MultiModelStore;
use Prooph\EventStore\Exception\StreamExistsAlready;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

final class PrepareDatabase extends Command
{
    /** @var MultiModelStore */
    private $multiModelStore;

    /** @var EventEngine */
    private $eventEngine;

    public function __construct(MultiModelStore $multiModelStore, EventEngine $eventEngine)
    {
        $this->multiModelStore = $multiModelStore;
        $this->eventEngine     = $eventEngine;
        parent::__construct(null);
    }

    protected function configure()
    {
        $this
            ->setName('database:prepare')
            ->setDescription('Creates Event Engine streams and collections');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $streams = [
            $this->eventEngine->writeModelStreamName(),
        ];

        foreach ($streams as $stream) {
            try {
                $output->writeln('Create Event Engine Stream: ' . $stream);

                $this->multiModelStore->createStream($stream);

                $output->writeln('Stream created successfully');
            } catch (StreamExistsAlready $err) {
                $output->writeln('Stream exists already.');
            }
        }

        $output->writeln('Create Aggregate State Collections ...');

        $collections = [];

        foreach ($collections as $collection => $indexes) {
            if (! $this->multiModelStore->hasCollection($collection)) {
                $this->multiModelStore->addCollection($collection, ...$indexes);
                $output->writeln("$collection created successfully");
            } else {
                /** @var Index $index */
                foreach ($indexes as $index) {
                    if ($index instanceof MetadataColumnIndex) {
                        $indexName = $index->indexCmd()->toArray()['name'];
                    } else {
                        $indexName = $index->toArray()['name'];
                    }

                    if (null !== $indexName && ! $this->multiModelStore->hasCollectionIndex($collection, $indexName)) {
                        $this->multiModelStore->addCollectionIndex($collection, $index);
                        $output->writeln("Index $indexName added to collection $collection");
                    }
                }
            }
        }

        $output->writeln('All done!');
        return 0;
    }
}
