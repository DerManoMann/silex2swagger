<?php

namespace Radebatz\Silex2Swagger\Tests\Controller2\sub1;

use DDesrosiers\SilexAnnotations\Annotations as SLX;
use Radebatz\Silex2Swagger\Swagger\Annotations as S2S;
use Swagger\Annotations as SWG;

/**
 * @S2S\Controller(prefix="/silex1",
 *   @SWG\Response(
 *     response=401,
 *     description="not authorized"
 *   )
 * )
 */
class SilexPrefixController
{
    /**
     * @SLX\Route(
     *   @SLX\Request(method="GET", uri="get1"),
     *   @SLX\RequireHttp,
     *   @SWG\Response(response=200, description="GET")
     * )
     */
    public function testGetRequestSilex()
    {
        return '';
    }
}
