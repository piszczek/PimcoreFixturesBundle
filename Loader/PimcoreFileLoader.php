<?php

namespace Piszczek\PimcoreFixturesBundle\Loader;

use Nelmio\Alice\DataLoaderInterface;
use Nelmio\Alice\FileLoaderInterface;
use Nelmio\Alice\IsAServiceTrait;
use Nelmio\Alice\ObjectSet;
use Nelmio\Alice\Parser\IncludeProcessor\IncludeDataMerger;
use Nelmio\Alice\ParserInterface;

class PimcoreFileLoader implements FileLoaderInterface
{
    use IsAServiceTrait;

    /**
     * @var DataLoaderInterface
     */
    private $dataLoader;

    /**
     * @var ParserInterface
     */
    private $parser;

    private $dataMerger;

    public function __construct(ParserInterface $parser, DataLoaderInterface $dataLoader)
    {
        $this->parser = $parser;
        $this->dataLoader = $dataLoader;
        $this->dataMerger = new IncludeDataMerger();
    }

    /**
     * @inheritdoc
     */
    public function loadFile(string $file, array $parameters = [], array $objects = []): ObjectSet
    {
        $data = $this->parser->parse($file);

        $data = $this->dataMerger->mergeInclude(
            $data,
            $this->parser->parse(__DIR__ . '/../Resources/fixtures/base-page.yml')
        );

        return $this->dataLoader->loadData($data, $parameters, $objects);
    }
}
