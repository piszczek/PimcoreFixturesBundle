<?php

declare(strict_types=1);

namespace Piszczek\PimcoreFixturesBundle\Provider;

use Pimcore\Db;
use Pimcore\Model\Document\Tag\Checkbox;
use Pimcore\Model\Document\Tag\Href;
use Pimcore\Model\Document\Tag\Image;
use Pimcore\Model\Document\Tag\Link;
use Pimcore\Model\Document\Tag\Select;
use Pimcore\Model\Document\Tag\Textarea;
use Pimcore\Model\Document\Tag\Wysiwyg;
use Pimcore\Model\Object\ClassDefinition;
use Pimcore\Model\Object\ClassDefinition\Service;

class PimcoreProvider
{
    /**
     * @param $sourcePath
     * @return string
     */
    public function path($sourcePath): string
    {
        $rootDir = \Pimcore::getKernel()->getRootDir();

        return $rootDir . DIRECTORY_SEPARATOR . $sourcePath;
    }

    public function imageAsset(string $sourcePath)
    {
        if (!file_exists($sourcePath)) {
            $sourcePath = $this->path($sourcePath);
        }
        $filename = basename($sourcePath);

        $imageAsset = \Pimcore\Model\Asset\Image::getByPath('/' . $filename);
        if (null === $imageAsset) {
            $imageAsset = new \Pimcore\Model\Asset\Image();
            $imageAsset->setFilename($filename);
            $imageAsset->setParentId(1);
            $imageAsset->setData(file_get_contents($sourcePath));
            $imageAsset->save();
        }

        return $imageAsset;
    }

    public function imageTag(string $sourcePath): Image
    {
        $imageAsset = $this->imageAsset($sourcePath);

        $imageTag = new Image();
        $imageTag->setImage($imageAsset);
        $imageTag->setCropPercent('');


        return $imageTag;
    }


    public function textareaTag(string $text): Textarea
    {
        $textareaTag = new Textarea();
        $textareaTag->setDataFromResource($text);

        return $textareaTag;
    }

    public function checkboxTag($checked): Checkbox
    {
        $checkboxTag = new Checkbox();
        $checkboxTag->setDataFromResource($checked);

        return $checkboxTag;
    }

    public function wysiwygTag(string $html): Wysiwyg
    {
        $tag = new Wysiwyg();
        $tag->setDataFromResource($html);

        return $tag;
    }

    public function selectTag($data): Select
    {
        $tag = new Select();
        $tag->setDataFromResource($data);

        return $tag;
    }

    public function linkTag(string $path, string $text): Link
    {
        $tag = new Link();
        $tag->setDataFromEditmode(['path' => $path, 'text' => $text]);

        return $tag;
    }

    public function loadSQL(string $path)
    {
        $path = $this->path($path);

        $db = Db::get();

        $db->query(file_get_contents($path));
    }

    public function hrefTag(int $id, string $type = 'object', $subType = 'object')
    {
        $tag = new Href();
        $tag->setDataFromEditmode(['id' => $id, 'type' => $type, 'subType' => $subType]);

        return $tag;
    }

    public function importClass(string $className, string $path)
    {
        $classDefinition = new ClassDefinition();
        $classDefinition->setName($className);

        $path = $this->path($path);

        $success = Service::importClassDefinitionFromJson($classDefinition, file_get_contents($path));


        return $success;
    }
}
