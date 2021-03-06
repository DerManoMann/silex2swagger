Bridge to generate swagger documentation from Silex Annotations
===============================================================

[![Build Status](https://travis-ci.org/DerManoMann/silex2swagger.png)](https://travis-ci.org/DerManoMann/silex2swagger)

## Introduction
[Silex Annotations](https://github.com/danadesrosiers/silex-annotation-provider) are an easy way to configure
routes in Silex.
With this bridge, in combination with [Swagger-PHP](https://github.com/zircote/swagger-php), it is easy to generate basic swagger documentation from these annotations.

Typically the Swagger annotations are added on top of existing Silex annotations to complement/complete the definitions.

---
## For OpenApi 3.0 and support for other frameworks use [radebatz/openapi-router](https://packagist.org/packages/radebatz/openapi-router)
---

## Mixing Silex and Swagger annotations

````php
<?php

namespace mycode;

use Silex\Application;
use Symfony\Component\HttpFoundation\Request;
use Swagger\Annotations as SWG;
use DDesrosiers\SilexAnnotations\Annotations as SLX;

class Controller
{

    /**
     * Update.
     *
     * @SLX\Route(
     *   @SLX\Request(method="PUT", uri="/{id}"),
     *
     *   @SWG\Parameter(
     *     name="Pet",
     *     in="body",
     *     description="Pet to update",
     *     required=true,
     *     @SWG\Schema(ref="#/definitions/Pet")
     *   ),
     *
     *   @SWG\Response(response=200, description="The full updated Pet", @SWG\Schema(ref="#/definitions/Pet")),
     *   @SWG\Response(response="default", description="Error", @SWG\Schema(ref="#/definitions/jsonError"))
     * )
     */
    public function update(Application $app, Request $request, $id) {
    ...

````

## Attaching Swagger properties to a Silex ````Request````
Silex2Swagger provides a custom ````Request```` annotation that allows to attach any supported
Swagger property to a request. All that is required is to use a custom implementation of the 
Silex ````Request```` annotation. 

````php
<?php

namespace mycode;

use Silex\Application;
use Symfony\Component\HttpFoundation\Request;
use Swagger\Annotations as SWG;
use DDesrosiers\SilexAnnotations\Annotations as SLX;
use Radebatz\Silex2Swagger\Swagger\Annotations as S2S;

class Controller
{

    /**
     * Update.
     *
     * @SLX\Route(
     *   @S2S\Request(method="POST", uri="/login",
     *     @S2S\SwaggerProperty(name="consumes", value={"application/x-www-form-urlencoded"})
     *   ),
     *
     *   @SWG\Parameter(
     *     name="email",
     *     in="formData",
     *     description="Email address",
     *     required=true,
     *     type="string"
     *   ),
     *   @SWG\Parameter(
     *     name="password",
     *     in="formData",
     *     description="Password",
     *     required=true,
     *     type="string"
     *   )
     * )
     */
    public function update(Application $app, Request $request) 
    {
        ...
    }

````
## Setting up common parameters/responses within a Controller
If you have parameters required on all endpoints in a controller it is possible to 
define those on controller level to avoid code duplication.

In order to do this there is a **custom** ````Controller```` annotation class that allows nested swagger parameters.
All parameters defined that way will be injected into all routes inside the controller class.

````php
<?php

namespace mycode;

use Radebatz\Silex2Swagger\Swagger\Annotations as S2S;
use Swagger\Annotations as SWG;

/**
 * @S2S\Controller(
 *   @SWG\Parameter(
 *     name="x-api-version",
 *     in="header",
 *     required=true,
 *     type="string"
 *   ),
 *   @SWG\Response(
 *     response=401,
 *     description="not authorized"
 *   )
 * )
 */
class MyController {
    ...
}
````

## Generating Swagger
### Using the CL
````
./bin/silex2swagger silex2swagger:build --path=[src] --file=swagger.json
````

### Using (simple) code
````php
<?php

require 'vendor/autoload.php';

use Silex\Application;
use Radebatz\Silex2Swagger\Swagger\S2SAnalysis;
use Radebatz\Silex2Swagger\Swagger\S2SConverter;

$swagger = \Swagger\scan('./src', ['analysis' => new S2SAnalysis([], null, new S2SConverter(new Application()))]);
echo $swagger;
````

For a more complete example have a look at the included Symfony Console command.


## Gotchas
* All annotation classes need to be in the class path (visible by the auto loader).
* In order to accurately merge/group annotations it is necessary to use the `@SLX\Route`
  Example:
````php
    /**
     * @SLX\Route(
     *   @SLX\Request(method="GET", uri="/foo"),
     *   @SLX\RequireHttp,
     *   @SWG\Response()
     * )
     */
````


## Changelog

### v1.0.0
* Initial version

### v1.0.1
* Make command more customizable

### v1.0.2
* Add support for Bind annotation (set as operationId)
* Allow to auto generate description and/or summary if not present

### v1.0.3
* Support callback to collect additional (custom) data to be added (ie: tags, etc.)

### v2.0.0
* Update dependencies for Silex 2

### v2.0.1
* Update dependencies for ddesrosiers/silex-annotation-provider to the official 2.0 version

### v2.0.2
* Fix double slash being created using ````@Controller```` without a prefix

### v3.0.0
* Introduce unique namespace and cleanup
* Add custom Swagger-PHP Request annotation that supports all swagger properties
* Bump PHP requirements to PHP 5.6

### v3.0.1
* Fix bug where controller classes need to be compared using FQCN
* Allow to share parameters and responses within a controller [#9]

### v3.0.2
* Allow to set all public request properties via @S2S\SwaggerProperty   [#12]

### v3.0.3
* Allow to specify multiple source locations on command line [#14]

### v3.0.4
* Finally merge auto-generated and annotated parameter definitions [#16]

### v3.0.5
* Code cleanup
