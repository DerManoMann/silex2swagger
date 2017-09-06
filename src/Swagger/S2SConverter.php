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

use SplObjectStorage;
use Psr\Log\LoggerInterface;
use Swagger\Analyser;
use Swagger\Context;
use Swagger\Annotations\AbstractAnnotation;
use Swagger\Annotations\Parameter;
use Swagger\Annotations\Response;
use Swagger\Annotations as SWG;
use DDesrosiers\SilexAnnotations\Annotations as SLX;
use Radebatz\Silex2Swagger\Swagger\Annotations as S2S;

/**
 * Silex 2 Swagger converter.
 *
 * Supported options:
 * - requestFilter: Callable to filter request annotations.
 * - autoResponse:  Boolean flag to enable/disable generation of default response annotations.
 */
class S2SConverter
{
    protected $app;
    protected $options;
    protected $logger;
    protected $processed;
    protected $classAnnotations;

    /**
     * @param \Silex\Application $app Silex application instance required for processing annotations.
     * @param array $options Additional options.
     */
    public function __construct($app, array $options = [], LoggerInterface $logger = null)
    {
        $this->app = $app;
        $this->options = array_merge([
                'requestFilter' => null,
                'autoResponse' => false,
                'autoDescription' => false,
                'autoSummary' => true,
                'extraCallback' => function ($context, $method, $path) {
                    return [];
                },
            ],
            $options
        );
        $this->logger = $logger;

        $this->processed = new SplObjectStorage();
        $this->classAnnotations = new SplObjectStorage();

        Analyser::$whitelist[] = 'DDesrosiers\SilexAnnotations\Annotations';
        Analyser::$whitelist[] = 'Radebatz\Silex2Swagger\Swagger\Annotations';
    }

    /**
     * Migrate a non swagger annotation.
     *
     * @param S2S\CustomAnnotation $customAnnotation A custom annotation.
     *
     * @return array List of swagger annotations.
     */
    public function migrateAnnotation(S2S\CustomAnnotation $customAnnotation)
    {
        $migrated = [];

        $annotation = $customAnnotation->getAnnotation();
        $context = $customAnnotation->_context;
        if (!$this->processed->contains($annotation)) {
            $this->processed->attach($context, $annotation);

            // a controller as expected by Silex
            $controller = [
                // chop leading '\'
                substr($context->fullyQualifiedName($context->class), 1),
                $context->method,
            ];

            if (($annotation instanceof SLX\Controller) || ($annotation instanceof SLX\Controller)) {
                // these are processed *before* any controller methods
                $this->classAnnotations->attach($context, $annotation);
            } elseif ($annotation instanceof SLX\Route) {
                $migrated = $this->handleRoute($annotation, $context, $controller);
            } elseif ($annotation instanceof SLX\Request) {
                $migrated = $this->migrateRequest($annotation, $context, [], $this->getExtras($annotation));
            } elseif ($annotation instanceof AbstractAnnotation) {
                // hhmmm; not sure why this happens
                $migrated = [$annotation];
            } else {
                if ($this->logger) {
                    $this->logger->warning(sprintf('Skipping loose annotation: %s', get_class($annotation)));
                }
            }
        }

        return $migrated;
    }

    /**
     * Handle a Silex route annotation.
     *
     * @param SLX\Route $route A Silex route annotation.
     * @param mixed $context Swagger context.
     * @param mixed $controller The controller
     * @param array $swgAnnotations Nested swagger annotations,
     *
     * @return array List of swagger annotations.
     */
    protected function handleRoute(SLX\Route $route, Context $context, $controller)
    {
        $migrated = [];

        // process Silex annotation
        $route->process($this->app['controllers_factory'], $controller, $this->app);

        // get nested SWG annotations
        $swgAnnotations = ['parameters' => []];
        foreach ($route as $property => $value) {
            if (is_array($value) && $value && $value[0] instanceof AbstractAnnotation) {
                $swgAnnotations[$property] = $value;
            }
        }

        $extras = $this->getExtras($route);

        if (property_exists($route, 'request') && $route->request) {
            foreach ($route->request as $request) {
                $migrated = array_merge($migrated, $this->migrateRequest($request, $context, $swgAnnotations, $extras));
            }
        }

        return $migrated;
    }

    /**
     * Migrate a single request annotation.
     *
     * @param SLX\Request $request A Silex request annotation.
     * @param mixed $context Swagger context.
     * @param array $swgAnnotation Optional nested swagger annotations.
     * @param array $extras Optional extra configuration from elsewhere.
     *
     * @return array List of swagger annotations.
     */
    protected function migrateRequest(SLX\Request $request, Context $context, array $swgAnnotations = [], array $extras = [])
    {
        $migrated = [];

        if (is_callable($this->options['requestFilter']) && !call_user_func($this->options['requestFilter'], $request)) {
            return $migrated;
        }

        $this->applyClassAnnotation($context, [$request]);

        // merge in some defaults
        $swgAnnotations = array_merge(['parameters' => []], $swgAnnotations);
        $extras = array_merge(['requirements' => []], $extras);

        // extract parameters from uri
        // ie /foo/{id} and try to find matching requirements
        preg_match_all('/{([^}]*)}/', $request->uri, $matches);
        if ($matches) {
            foreach ($matches[1] as $name) {
                $properties = [
                    '_context' => $context,
                    'parameter' => $name,
                    'name' => $name,
                    'in' => 'path',
                    'required' => true,
                    'type' => 'string',
                ];

                // add extra requirements
                if (array_key_exists($name, $extras['requirements'])) {
                    $properties['pattern'] = $extras['requirements'][$name];
                }

                $swgAnnotations['parameters'][] = new Parameter($properties);
            }
        }
        if ($extras['schemes']) {
            $swgAnnotations['schemes'] = $extras['schemes'];
        }
//        var_dump($request);
        if ($request instanceof S2S\Request) {
            $keys = array_keys(SWG\Operation::$_types);
            foreach ($request->swaggerProperties as $sp) {
                if (in_array($sp->name, $keys)) {
                    $extras['properties'][$sp->name] = $sp->value;
                }
            }
        }

        // Silex allows method to be something like GET|POST
        $methods = strtolower($request->method);
        // MATCH matches all
        $methods = 'match' != $methods ? $methods : 'get|post|put|delete|options|head|patch';

        foreach (explode('|', $methods) as $method) {
            $method = trim($method);
            // for now we need this...
            $path = str_replace('//', '/', '/' . $request->uri);

            $swgClass = 'Swagger\\Annotations\\' . ucfirst($method);
            /** @var SWG\Operation $swgOperation */
            $swgOperation = new $swgClass(array_merge([
                    '_context' => $context,
                    'operationId' => $extras['bind'] ?: $context->method,
                    'method' => $method,
                    'path' => $path,
                ],
                $extras['properties'],
                call_user_func($this->options['extraCallback'], $context, $method, $path)
            ));

            if ((!property_exists($swgOperation, 'description') || null === $swgOperation->description) && $this->options['autoDescription']) {
                $swgOperation->description = sprintf('%s:%s', strtoupper($method), $swgOperation->path);
            }
            if ((!property_exists($swgOperation, 'summary') || null === $swgOperation->summary) && $this->options['autoSummary']) {
                $swgOperation->summary = $swgOperation->description ?: sprintf('%s:%s', strtoupper($method), $swgOperation->path);
            }

            // add nested SWG anotations back where they are expected - using plural in cases..
            foreach ($swgAnnotations as $property => $value) {
                $properties = $property . 's';
                $key = null;
                if (property_exists($swgOperation, $property)) {
                    $key = $property;
                } elseif (property_exists($swgOperation, $properties)) {
                    $key = $properties;
                }

                if (is_array($swgOperation->$key) && is_array($value) && 'parameters' == $key) {
                    // TODO: check for matching values; ie matching property names and then merge on value level
                    foreach ($value as $parameter) {
                        // does that parameter already exist?
                        $merged = false;
                        foreach ($swgOperation->$key as $cp) {
                            if ($cp->name == $parameter->name) {
                                $merged = true;
                                // todo: merge!
                                break;
                            }
                        }
                        if (!$merged) {
                            $swgOperation->{$key}[] = $parameter;
                        }
                    }
                } else {
                    $swgOperation->$key = $value;
                }
            }

            if (!$swgOperation->responses && $this->options['autoResponse']) {
                // add default response to make swagger happier :/
                $swgOperation->responses = [new Response([
                    'response' => 'default',
                    'description' => sprintf('%s:%s', strtoupper($method), $swgOperation->path),
                ])];
            }

            $migrated[] = $swgOperation;
        }

        return $migrated;
    }

    /**
     * Apply class annotation to given request for matching context.
     *
     * @param mixed $context Swagger context.
     * @param array $requests List of Silex request annotations.
     *
     * @return SLX\Controller|null
     */
    protected function applyClassAnnotation(Context $context, array $requests)
    {
        foreach ($this->classAnnotations as $classContext) {
            if ($classContext->class == $context->class) {
                $classAnnotation = $this->classAnnotations->offsetGet($classContext);
                // deal with prefix
                foreach ($requests as $request) {
                    $request->uri = $classAnnotation->prefix . '/' . $request->uri;
                    // keep relative
                    if ('/' == $request->uri[0]) {
                        $request->uri = substr($request->uri, 1);
                    }
                }

                return $classAnnotation;
            }
        }

        return;
    }

    /**
     * Extract extra information from root annotation.
     *
     * NOTE: Assuming that we can have nested RouteAnnotations in either Route or Request.
     *
     * @param SLX\Route|SLX\Request $root The root annotation.
     *
     * @return array
     */
    protected function getExtras($root)
    {
        $extras = [
            'requirements' => [],
            'schemes' => [],
            'bind' => null,
            'properties' => [],
        ];

        // get all nested annotations and pick what we can use
        if ($root instanceof SLX\Route || $root instanceof SLX\Request) {
            foreach ($root as $annotations) {
                if (is_array($annotations)) {
                    foreach ($annotations as $annotation) {
                        if ($annotation instanceof SLX\Modifier) {
                            if ('addRequirements' == $annotation->method) {
                                $extras['requirements'] = $annotation->args[0];
                            }
                        } elseif ($annotation instanceof SLX\RequireHttp) {
                            $extras['schemes'][] = 'http';
                        } elseif ($annotation instanceof SLX\RequireHttps) {
                            $extras['schemes'][] = 'https';
                        } elseif ($annotation instanceof SLX\Bind) {
                            $extras['bind'] = $annotation->routeName;
                        }
                    }
                }
            }
        }

        return $extras;
    }
}
