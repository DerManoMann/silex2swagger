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
use Swagger\Annotations as SWG;

/**
 * A Silex <code>Controller</code> annotation with support for shared parameters.
 *
 * @Annotation
 */
class Controller extends SLX\Controller
{

    /** @var SWG\Parameter[] */
    public $parameters = [];

    /** @var SWG\Response[] */
    public $responses = [];

    /**
     * Controller constructor.
     *
     * @param array $values
     */
    public function __construct(array $values = [])
    {
        foreach ($values as $propertyName => $value) {
            if ('value' === $propertyName) {
                // might be array or single value...
                $values = is_array($value) ? $value : [$value];
                foreach ($values as $innerValue) {
                    $classPath = explode("\\", get_class($innerValue));
                    $propertyName = lcfirst(array_pop($classPath)) . 's';
                    $this->{$propertyName}[] = $innerValue;
                }
            } else {
                $this->{$propertyName} = $value;
            }
        }
    }

}
