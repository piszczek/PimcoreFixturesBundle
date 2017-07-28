<?php

namespace Piszczek\PimcoreFixturesBundle\Hydrator\Property;

use Nelmio\Alice\Definition\Object\SimpleObject;
use Nelmio\Alice\Definition\Property;
use Nelmio\Alice\Generator\GenerationContext;
use Nelmio\Alice\Generator\Hydrator\PropertyHydratorInterface;
use Nelmio\Alice\ObjectInterface;
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
        $document = $object->getInstance();

        if ($document instanceof Page) {
            switch ($property->getName()) {
                case 'parent':
                    $parentPage = $property->getValue();

                    if (! $parentPage instanceof Page) {
                        throw new \Exception('Parent must be instance of ' . Page::class . 'class');
                    }
                    if (!$parentPage->getId()) {
                        $parentPage->save();
                    }

                    $document = $document->setParent($parentPage);

                    return new SimpleObject($object->getId(), $document);
            }
        }

        return $this->hydrator->hydrate($object, $property, $context);
    }
}
