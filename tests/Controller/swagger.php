<?php

/*
* This file is part of the silex2swagger library.
*
* (c) Martin Rademacher <mano@radebatz.net>
*
* For the full copyright and license information, please view the LICENSE
* file that was distributed with this source code.
*/

namespace Radebatz\Silex2Swagger\Tests;

/**
 * @SWG\Swagger(
 *   basePath="/",
 *   produces={"application/json"},
 *   consumes={"application/json"},
 *   @SWG\Info(
 *     version="1.0.0",
 *     title="Silex Test API"
 *   ),
 *
 *   @SWG\Definition(
 *     definition="jsonError",
 *     required={"code", "message"},
 *     @SWG\Property(
 *       property="code",
 *       type="string"
 *     ),
 *     @SWG\Property(
 *       property="message",
 *       type="string"
 *     )
 *   )
 * )
 */
