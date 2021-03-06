<?php

namespace Piszczek\PimcoreFixturesBundle\Loader;

use Faker\Generator as FakerGenerator;
use Nelmio\Alice\FileLoaderInterface;
use Nelmio\Alice\Generator\Hydrator\PropertyHydratorInterface;
use Nelmio\Alice\Generator\Instantiator\Chainable\NoCallerMethodCallInstantiator;
use Nelmio\Alice\Generator\Instantiator\Chainable\NoMethodCallInstantiator;
use Nelmio\Alice\Generator\Instantiator\Chainable\NullConstructorInstantiator;
use Nelmio\Alice\Generator\Instantiator\Chainable\StaticFactoryInstantiator;
use Nelmio\Alice\Generator\Instantiator\ExistingInstanceInstantiator;
use Nelmio\Alice\Generator\Instantiator\InstantiatorRegistry;
use Nelmio\Alice\Generator\Instantiator\InstantiatorResolver;
use Nelmio\Alice\Generator\InstantiatorInterface;
use Nelmio\Alice\Loader\NativeLoader as BaseNativeLoader;
use Nelmio\Alice\ObjectSet;
use Piszczek\PimcoreFixturesBundle\Hydrator\Property\PimcorePropertyAccesorHydrator;
use Piszczek\PimcoreFixturesBundle\Instantiator\Chainable\PimcoreInstantiator;
use Piszczek\PimcoreFixturesBundle\Processor\PimcoreProcessor;
use Piszczek\PimcoreFixturesBundle\Provider\PimcoreProvider;

class NativeLoader extends BaseNativeLoader
{
    public function createFakerGenerator(): FakerGenerator
    {
        $fakerGenerator = parent::createFakerGenerator();

        $fakerGenerator->addProvider(new PimcoreProvider());

        return $fakerGenerator;
    }

    public function createFileLoader(): FileLoaderInterface
    {
        return new PimcoreFileLoader(
            $this->getParser(),
            $this->getDataLoader()
        );
    }

    protected function createPropertyHydrator(): PropertyHydratorInterface
    {
        return new PimcorePropertyAccesorHydrator(
            parent::createPropertyHydrator()
        );
    }

    protected function createInstantiator(): InstantiatorInterface
    {
        return new ExistingInstanceInstantiator(
            new InstantiatorResolver(
                new InstantiatorRegistry([
                    new PimcoreInstantiator(new NullConstructorInstantiator()),
                    new NoCallerMethodCallInstantiator(),
                    new NullConstructorInstantiator(),
                    new NoMethodCallInstantiator(),
                    new StaticFactoryInstantiator(),
                ])
            )
        );
    }

    /**
     * @return PimcoreProcessor
     */
    public function getPersister(): PimcoreProcessor
    {
        return new PimcoreProcessor();
    }

    public function loadFile(string $file, array $parameters = [], array $objects = []): ObjectSet
    {
        $persister = $this->getPersister();

        $objectsSet = parent::loadFile($file, $parameters, $objects);


        foreach ($objectsSet->getObjects() as $object) {
            $persister->process($object);
        }

        return $objectsSet;
    }
}