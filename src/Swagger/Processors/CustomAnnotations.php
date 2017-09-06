<?php

/*
* This file is part of the silex2swagger library.
*
* (c) Martin Rademacher <mano@radebatz.net>
*
* For the full copyright and license information, please view the LICENSE
* file that was distributed with this source code.
*/

namespace Radebatz\Silex2Swagger\Swagger\Processors;

use SplObjectStorage;
use Swagger\Analysis;
use Radebatz\Silex2Swagger\Swagger\S2SConverter;
use Radebatz\Silex2Swagger\Swagger\Annotations\CustomAnnotation;

/**
 * Process custom annotations.
 */
class CustomAnnotations
{
    protected $s2sConverter;

    /**
     * Create instance.
     */
    public function __construct(S2SConverter $s2sConverter)
    {
        $this->s2sConverter = $s2sConverter;
    }

    /**
     * Invoke processor.
     */
    public function __invoke(Analysis $analysis)
    {
        $add = new SplObjectStorage();
        $remove = new SplObjectStorage();
        foreach ($analysis->annotations as $annotation) {
            if ($annotation instanceof CustomAnnotation) {
                // migrate & replace
                foreach ($this->s2sConverter->migrateAnnotation($annotation) as $migrated) {
                    $add->attach($migrated);
                }

                // remove custom annotation
                $remove->attach($annotation);

            }
        }

        $analysis->annotations->removeAll($remove);
        $analysis->annotations->addAll($add);
    }
}
