<?php

namespace Piszczek\PimcoreFixturesBundle\Hydrator\Property;

use AppBundle\Document\Areabrick\AbstractAreabrick;
use Nelmio\Alice\Definition\Object\SimpleObject;
use Nelmio\Alice\Definition\Property;
use Nelmio\Alice\Generator\GenerationContext;
use Nelmio\Alice\Generator\Hydrator\PropertyHydratorInterface;
use Nelmio\Alice\ObjectInterface;
use Pimcore\Model\Asset;
use Pimcore\Model\Document;
use Pimcore\Model\Document\Tag;
use Pimcore\Model\Document\Tag\Areablock;
use Pimcore\Model\Document\Tag\BlockInterface;
use Pimcore\Model\Object\ClassDefinition\Data;
use Pimcore\Model\Object\Concrete;

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
            //add default accesskey to navigation
            case $propertyName === 'key' && $model instanceof Document\Page:
                $model->setKey($value);
                $model->setProperty('navigation_accesskey', 'text', $value, false, false);
                break;

            //handle nested blocks
            case $propertyName === 'elements' && $model instanceof Areablock:
                $document = Document::getById($model->getDocumentId());

                $this->handleBlockElement($document, $model, $value);

                break;

            case $propertyName === 'values' && $model instanceof Concrete:
                $data = $value;

                foreach ($data as $key => $value) {
                    $fd = $model->getClass()->getFieldDefinition($key);
                    if ($fd instanceof Data) {
                        //todo: localized fields

                        $model->setValue($key, $fd->getDataFromEditmode($value, $model));
                    }
                }
                break;

            default:
                $handled = false;
        }

        if (false === $handled) {
            return $this->hydrator->hydrate($object, $property, $context);
        }

        return new SimpleObject($object->getId(), $model);
    }

    private function handleBlockElement(Document $document, BlockInterface $block, $value)
    {
        //if block name isn't set (for recurive area block)
        $blockName = $block->getName()??'';
        //area data
        $data = [];
        $key = 1;

        if ($blockName) {
            foreach ($value as $areaBrickName => $tags) {
                $areaBrickName = preg_replace('/_\d+$/', '', $areaBrickName);

                foreach ($tags as $tagName => $tag) {
                    // if element isn't instance of tag, then create it
                    if (!$tag instanceof Tag) {
                        $tagData = is_array($tag) ? $tag['data'] : $tag;
                        $class = is_array($tag) ? $tag['class'] : Tag\Input::class;
                        $tag = new $class;
                        $tag->setDataFromResource($tagData);
                    }

                    $tag->setRealName($tagName);
                    $tag->setName($blockName . ':' . $key . '.' . $tagName);

                    if ($tag instanceof Tag\Block || $tag instanceof Tag\Areablock) {
                        $this->handleBlockElement($document, $tag, $tag->indices);
                        continue;
                    }

                    $document->setElement($tag->getName(), $tag);
                }
                //in recursive call
                if ($block instanceof Tag\Block) {
                    $data[] = $key++;
                } else {
                    $data[] = ['key' => (string)$key++, 'type' => $areaBrickName];
                }
            }

            $block->setDataFromEditmode($data);

            $document->setElement($blockName, $block);
            $document->save();
        } else {
            //for recursive call
            $block->indices = $value;
        }
    }

}
