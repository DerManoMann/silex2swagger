<?php

namespace Radebatz\Silex2Swagger\Tests\Controller2;

use DDesrosiers\SilexAnnotations\Annotations as SLX;
use Swagger\Annotations as SWG;

/**
 * @SLX\Controller(prefix="/silex2")
 */
class SilexPrefixController
{
    /**
     * @SLX\Route(
     *   @SLX\Request(method="GET", uri="get"),
     *   @SWG\Response(response=200, description="GET")
     * )
     */
    public function testGetRequestSilex2()
    {
        return '';
    }
}
