<?php

namespace Piszczek\PimcoreFixturesBundle\Persister;


use Pimcore\Document\Tag\Block\BlockName;
use Pimcore\Document\Tag\Block\BlockState;
use Pimcore\Model\AbstractModel;
use Pimcore\Model\Document;
use Pimcore\Model\Document\Page;
use Pimcore\Model\Document\PageSnippet;
use Pimcore\Model\Document\Tag;
use Pimcore\Templating\Model\ViewModel;
use function Sabre\Event\Loop\instance;

class PimcorePersister implements PersisterInterface
{
    public function getTag(Tag $object)
    {
        $document = Document::getById($object->getDocumentId());
        $type = $object->getType();
        $inputName = $object->getName();
        $options = $object->getOptions();

        $type = strtolower($type);
        $name = Tag::buildTagName($type, $inputName, $document);

        try {
            $tag = null;

            if ($document instanceof PageSnippet) {
                $view = new ViewModel([
                    'editmode' => false,
                    'document' => $document
                ]);

                $tag = $document->getElement($name);
                if ($tag instanceof Tag && $tag->getType() === $type) {
                    // call the load() method if it exists to reinitialize the data (eg. from serializing, ...)
                    if (method_exists($tag, 'load')) {
                        $tag->load();
                    }

                    $tag->setView($view);
                    $tag->setEditmode(false);
                    $tag->setOptions($options);
                } else {
                    $tag = $object;
                    $tag->setName($name);
//                    $tag = Tag::factory($type, $name, $document->getId(), $options, null, $view);
                    $document->setElement($name, $tag);
                }

                // set the real name of this editable, without the prefixes and suffixes from blocks and areablocks
                $tag->setRealName($inputName);
            }

            return $tag;
        } catch (\Exception $e) {
            throw $e;
        }
    }

    /**
     * TODO inject block state via DI
     *
     * @return BlockState
     */
    protected function getBlockState(): BlockState
    {
        return \Pimcore::getContainer()->get('pimcore.document.tag.block_state_stack')->getCurrentState();
    }

    /**
     * Persists objects into the database.
     *
     * @param object $object
     */
    public function persist($object)
    {
        $blockState = $this->getBlockState();


        switch (true) {
            case $object instanceof Tag\Areablock:
                $extraData = $object->extraData;
                $brickId = $extraData['brick_id'];


                $tag = $this->getTag($object);

                //add block
                $blockState->pushBlock(BlockName::createFromTag($tag));
                $key = 1;
                if ($blockState->hasIndexes()) {
                    //todo:
                    $key = $blockState->popIndex() + 1;
                    $blockState->pushIndex($key);
                } else {
                    $blockState->pushIndex($key);
                }

                $data = $tag->getDataForResource();
                $data[] = ['key' =>  $key, 'type' => $brickId];

                $tag->setDataFromEditmode($data);

                $document = Document::getById($tag->getDocumentId());
                $document->save();
                break;
            case $object instanceof Tag:
                $tag = $this->getTag($object);


                $document = Document::getById($tag->getDocumentId());
                $document->save();
                break;
        }
    }

    public function flush()
    {
    }
}
