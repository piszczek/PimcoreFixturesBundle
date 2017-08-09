<?php

declare(strict_types=1);

namespace Piszczek\PimcoreFixturesBundle\Instantiator\Chainable;

use Nelmio\Alice\Definition\MethodCall\SimpleMethodCall;
use Nelmio\Alice\Definition\Object\SimpleObject;
use Nelmio\Alice\Definition\Property;
use Nelmio\Alice\Definition\PropertyBag;
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
        $className = $fixture->getClassName();
        $properties = $fixture->getSpecs()->getProperties();

        $getProperty = function ($propertyName) {
            if (isset($this->properties[$propertyName])) {
                return $this->properties[$propertyName];
            }
            return false;
        };

        $appendProperty = function (Property $property, bool $override = false) {
            if (false === $override && array_key_exists($property->getName(), $this->properties)) {
                return;
            }

            $this->properties[] = $property;
        };

        //append save method to private variables
        $appendSave =  function ($caller) {
            $this->methodCalls[] = $caller;
        };

        if (is_subclass_of($className, Asset::class)) {
            //append default data if not set

            $appendProperty->call($properties, new Property('parentId', 1));
        }

        if (is_subclass_of($className, Document::class)) {
            //check if document with that name exist
            $property = $getProperty->call($properties, 'key');

            if ($property) {
                $document = Document::getByPath('/' . $property->getValue());

                if ($document) {
                    $objects = $fixtureSet->getObjects()->with(
                        new SimpleObject(
                            $fixture->getId(),
                            $document
                        )
                    );

                    return $fixtureSet->withObjects($objects);
                }
            }
        }


        $appendSave->call($fixture->getSpecs()->getMethodCalls(), new SimpleMethodCall('save'));


        return $this->instantiator->instantiate($fixture, $fixtureSet, $context);
    }

    /**
     * @inheritdoc
     */
    protected function createInstance(FixtureInterface $fixture)
    {
    }
}
