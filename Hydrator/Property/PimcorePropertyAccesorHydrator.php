<?php

namespace Piszczek\PimcoreFixturesBundle\Hydrator\Property;

use Nelmio\Alice\Definition\Object\SimpleObject;
use Nelmio\Alice\Definition\Property;
use Nelmio\Alice\Generator\GenerationContext;
use Nelmio\Alice\Generator\Hydrator\PropertyHydratorInterface;
use Nelmio\Alice\ObjectInterface;
use Pimcore\Model\Asset;
use Pimcore\Model\Document;
use Pimcore\Model\Document\Tag;
use Pimcore\Model\Document\Tag\Areablock;

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
        $propertyName = $property->getName();
        $value = $property->getValue();

        $handled = true;

        switch (true) {
            //set file to Asset Object
            case $propertyName === 'sourcePath' && $model instanceof Asset:
                $model->setData(file_get_contents($value));

                break;

            case $propertyName === 'brickId' && $model instanceof Areablock:
                    $document = Document::getById($model->getDocumentId());
                    //todo:
                    $key = 1;
                    $data = ['key' => $key, 'type' => $value];

                    $model->setDataFromEditmode([$data]);
                    $model->current = $key;

                    $document->setElement($value, $model);
                    $document->save();
                break;

            case $propertyName === 'block' && $model instanceof Tag:
                $document = Document::getById($value->getDocumentId());

                $key = $value->current;

                $model->setRealName($model->getName());

                $name = $value->getName().':'.$key.'.'.$model->getName();
                $model->setName($name);

                $document->setElement($name, $model);
                $document->save();
                break;
            default:
                $handled = false;
        }

        if (false === $handled) {
            return $this->hydrator->hydrate($object, $property, $context);
        }

        return new SimpleObject($object->getId(), $model);
    }
}
