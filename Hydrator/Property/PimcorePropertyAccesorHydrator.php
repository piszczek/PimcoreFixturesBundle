<?php

namespace Piszczek\PimcoreFixturesBundle\Hydrator\Property;

use Nelmio\Alice\Definition\Object\SimpleObject;
use Nelmio\Alice\Definition\Property;
use Nelmio\Alice\Generator\GenerationContext;
use Nelmio\Alice\Generator\Hydrator\PropertyHydratorInterface;
use Nelmio\Alice\ObjectInterface;
use Pimcore\Model\AbstractModel;
use Pimcore\Model\Document\Page;

final class PimcorePropertyAccesorHydrator implements PropertyHydratorInterface
{
    /**
     * @var PropertyHydratorInterface
     */
    private $hydrator;

    public function __construct(PropertyHydratorInterface $decoratedPropertyHydrator)
    {
        $this->hydrator = $decoratedPropertyHydrator;
    }

    /**
     * @inheritdoc
     */
    public function hydrate(ObjectInterface $object, Property $property, GenerationContext $context): ObjectInterface
    {
        $model = $object->getInstance();

        if ($model instanceof AbstractModel) {
            switch ($property->getName()) {
                //Allow add extra information from fixtures
                case 'extra_data':
                    $extraData = $property->getValue();

                    $model->extraData = $extraData;

                    return new SimpleObject($object->getId(), $model);
            }
        }

        return $this->hydrator->hydrate($object, $property, $context);
    }
}
