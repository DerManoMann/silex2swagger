<?php

namespace Radebatz\Silex2Swagger\Tests\Controller;

use DDesrosiers\SilexAnnotations\Annotations as SLX;
use Swagger\Annotations as SWG;
use Radebatz\Silex2Swagger\Swagger\Annotations as S2S;

/**
 * @SLX\Controller(prefix="/request")
 */
class CustomRequestController
{
    /**
     * @SLX\Route(
     *   @S2S\Request(method="GET", uri="get",
     *     @S2S\SwaggerProperty(name="consumes", value={"application/x-www-form-urlencoded"})
     *   ),
     *   @SWG\Response(response=200, description="GET")
     * )
     */
    public function testCustomRequest()
    {
        return '';
    }
}
