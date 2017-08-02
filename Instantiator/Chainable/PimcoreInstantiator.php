<?php

declare(strict_types=1);

namespace Piszczek\PimcoreFixturesBundle\Instantiator\Chainable;

use Nelmio\Alice\Definition\MethodCall\SimpleMethodCall;
use Nelmio\Alice\FixtureInterface;
use Nelmio\Alice\Generator\GenerationContext;
use Nelmio\Alice\Generator\Instantiator\Chainable\AbstractChainableInstantiator;
use Nelmio\Alice\Generator\InstantiatorInterface;
use Nelmio\Alice\Generator\ResolvedFixtureSet;
use Nelmio\Alice\Throwable\Exception\Generator\Instantiator\InstantiationException;
use Pimcore\Model\Asset;
use Pimcore\Model\Document;

final class PimcoreInstantiator extends AbstractChainableInstantiator
{
    private $instantiator;

    public function __construct(InstantiatorInterface $decoratedInstantiator)
    {
        $this->instantiator = $decoratedInstantiator;
    }

    /**
     * @inheritDoc
     */
    public function canInstantiate(FixtureInterface $fixture): bool
    {
        $className = $fixture->getClassName();
        return
            is_subclass_of($className, Asset::class)
            ||
            is_subclass_of($className, Document::class)
        ;
    }

    /**
     * {@inheritdoc}
     *
     * @throws InstantiationException
     */
    public function instantiate(
        FixtureInterface $fixture,
        ResolvedFixtureSet $fixtureSet,
        GenerationContext $context
    ): ResolvedFixtureSet {
        $caller = new SimpleMethodCall('save');

        //append save method to private variables
        $appendSave =  function () use ($caller) {
            $this->methodCalls[] = $caller;
        };

        $appendSave->call($fixture->getSpecs()->getMethodCalls());


        return $this->instantiator->instantiate($fixture, $fixtureSet, $context);
    }

    /**
     * @inheritdoc
     */
    protected function createInstance(FixtureInterface $fixture)
    {
    }
}
