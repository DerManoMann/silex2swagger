<?php

/*
* This file is part of the silex2swagger library.
*
* (c) Martin Rademacher <mano@radebatz.net>
*
* For the full copyright and license information, please view the LICENSE
* file that was distributed with this source code.
*/

namespace Radebatz\Silex2Swagger\Swagger;

use Psr\Log\AbstractLogger;
use Psr\Log\LogLevel;
use Swagger\Logger;

/**
 * Simple Psr logger wrapper around the Swagger logger.
 */
class PsrLogger extends AbstractLogger
{
    protected $logger;

    /**
     */
    public function __construct(Logger $logger)
    {
        $this->logger = $logger;
    }

    /**
     * {@inheritdoc}
     */
    public function log($level, $message, array $context = [])
    {
        if (in_array($level, [LogLevel::NOTICE, LogLevel::INFO])) {
            $this->logger->notice($message);
        } else {
            $this->logger->warning($message);
        }
    }
}
