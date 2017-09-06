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

use DDesrosiers\SilexAnnotations\Annotations as SLX;

/**
 * A Silex <code>Request</code> annotation with support for arbitrary swagger properties.
 *
 * @Annotation
 */
class Request extends SLX\Request
{

    /** @var \Radebatz\Silex2Swagger\Swagger\Annotations\SwaggerProperty[] */
    public $swaggerProperties = [];
}
