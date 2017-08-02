<?php

declare(strict_types=1);

namespace Piszczek\PimcoreFixturesBundle\Processor;

/**
 * @author Jordi Boggiano <j.boggiano@seld.be>
 */
interface ProcessorInterface
{
    /**
     * Prepare object.
     *
     * @param object $object
     */
    public function process($object);
}
