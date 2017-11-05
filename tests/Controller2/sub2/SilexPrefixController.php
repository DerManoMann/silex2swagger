<?php

namespace Radebatz\Silex2Swagger\Tests\Controller2\sub2;

use DDesrosiers\SilexAnnotations\Annotations as SLX;
use Radebatz\Silex2Swagger\Swagger\Annotations as S2S;
use Swagger\Annotations as SWG;

/**
 * @S2S\Controller(prefix="/silex2",
 *   @SWG\Parameter(
 *     name="x-api-version",
 *     in="header",
 *     required=true,
 *     type="string"
 *   )
 * )
 */
class SilexPrefixController
{
    /**
     * @SLX\Route(
     *   @SLX\Request(method="GET", uri="get2"),
     *   @SWG\Response(response=200, description="GET")
     * )
     */
    public function testGetRequestSilex()
    {
        return '';
    }
}
