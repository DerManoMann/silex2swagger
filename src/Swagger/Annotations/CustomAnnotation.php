<?php

/*
* This file is part of the silex2swagger library.
*
* (c) Martin Rademacher <mano@radebatz.net>
*
* For the full copyright and license information, please view the LICENSE
* file that was distributed with this source code.
*/

namespace Radebatz\Silex2Swagger\Swagger\Annotations;

use Swagger\Annotations\AbstractAnnotation;

/**
 * A wrapper for custom annotation.
 *
 * @Annotation
 */
class CustomAnnotation extends AbstractAnnotation
{
    /**
     * The custom annotation.
     *
     * @var mixed
     */
    protected $annotation;

    /**
     * @param array $properties
     * @param mixed $annotation The custom annotation.
     */
    public function __construct($properties, $annotation)
    {
        parent::__construct($properties);
        $this->annotation = $annotation;
    }

    /**
     * Get the custom annotation.
     *
     * @return mixed
     */
    public function getAnnotation()
    {
        return $this->annotation;
    }
}
