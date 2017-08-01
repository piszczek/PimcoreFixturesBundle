<?php

declare(strict_types=1);

namespace Piszczek\PimcoreFixturesBundle\Instantiator\Chainable;

use Nelmio\Alice\Definition\MethodCall\NoMethodCall;
use Nelmio\Alice\Definition\Object\SimpleObject;
use Nelmio\Alice\Definition\Property;
use Nelmio\Alice\Definition\ServiceReference\StaticReference;
use Nelmio\Alice\FixtureInterface;
use Nelmio\Alice\FixtureSet;
use Nelmio\Alice\Generator\GenerationContext;
use Nelmio\Alice\Generator\Instantiator\Chainable\AbstractChainableInstantiator;
use Nelmio\Alice\Generator\ResolvedFixtureSet;
use Nelmio\Alice\Throwable\Exception\Generator\Instantiator\InstantiationException;
use Nelmio\Alice\Throwable\Exception\Generator\Instantiator\InstantiationExceptionFactory;
use Nelmio\Alice\Throwable\InstantiationThrowable;
use Pimcore\Model\Document\Tag;

final class PimcoreInstantiator extends AbstractChainableInstantiator
{
    /**
     * @inheritDoc
     */
    public function canInstantiate(FixtureInterface $fixture): bool
    {
        return false;
//        return is_subclass_of($fixture->getClassName(), Tag::class);
    }

    /**
     * @inheritdoc
     */
    protected function createInstance(FixtureInterface $fixture)
    {
        //todo:
    }
}
