Bridge to generate swagger documentation from Silex Annotations
===============================================================

## Introduction
[Silex Annotations](https://github.com/danadesrosiers/silex-annotation-provider) are an easy way to configure
routes in Silex.
With this bridge, in combination with [Swagger-PHP](https://github.com/zircote/swagger-php), it is easy to generate basic swagger documentation from these annotations.

Typically the Swagger annotations are added on top of existing Silex annotations to complement/complete the definitions.


## Example
````
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


## Generating Swagger
### Using the CL
````
./bin/silex2swagger silex2swagger:build --path=[src] --file=swagger.json
````

### Using (Simple) Code
```php
<?php
require 'vendor/autoload.php';

use Radebatz\Silex\Swagger\SilexSwaggerAnalysis;

$swagger = \Swagger\scan('./src', ['analysis' => new Silex2SwaggerAnalysis([], null, new Silex2SwaggerConverter(new Application()))]);
echo $swagger
```

For a more complete example have a look at the included Symfony Console command.


## Gotchas
* All annotation classes need to be in the class path (visible by the auto loader).
* In order to accurately merge/group annotations it is necessary to use the `@SLX\Route`
  Example:
````
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
