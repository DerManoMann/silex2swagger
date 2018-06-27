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

/**
 * A custom Swagger property
 *
 * @Annotation
 */
class SwaggerProperty
{

    /** @var string */
    public $name;

    /** @var mixed */
    public $value;
}
