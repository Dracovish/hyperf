<?php

declare(strict_types=1);
/**
 * This file is part of Hyperf.
 *
 * @link     https://hyperf.org
 * @document https://wiki.hyperf.org
 * @contact  group@hyperf.org
 * @license  https://github.com/hyperf-cloud/hyperf/blob/master/LICENSE
 */

namespace Hyperf\Di\Aop;

use Closure;
use Hyperf\Di\ReflectionManager;
use Hyperf\Framework\ApplicationContext;
use Hyperf\Di\Annotation\AspectCollector;
use Hyperf\Di\Annotation\AnnotationCollector;

trait ProxyTrait
{
    protected static function __proxyCall(
        string $originalClassName,
        string $method,
        array $arguments,
        Closure $closure
    ) {
        $proceedingJoinPoint = new ProceedingJoinPoint($closure, $originalClassName, $method, $arguments);
        $result = self::handleArround($proceedingJoinPoint);
        unset($proceedingJoinPoint);
        return $result;
    }

    /**
     * @TODO This method will be called everytime, should optimize it later.
     */
    protected static function getParamsMap(string $className, string $method, array $args): array
    {
        $map = [
            'keys' => [],
            'order' => [],
        ];
        $reflectMethod = ReflectionManager::reflectMethod($className, $method);
        $reflectParameters = $reflectMethod->getParameters();
        foreach ($reflectParameters as $key => $reflectionParameter) {
            if (! isset($args[$key])) {
                $args[$key] = $reflectionParameter->getDefaultValue();
            }
            $map['keys'][$reflectionParameter->getName()] = $args[$key];
            $map['order'][] = $reflectionParameter->getName();
        }
        return $map;
    }

    private static function handleArround(ProceedingJoinPoint $proceedingJoinPoint)
    {
        $aspects = self::parseAspects($proceedingJoinPoint->className, $proceedingJoinPoint->methodName);
        $annotationAspects = self::getAnnotationAspects($proceedingJoinPoint->className, $proceedingJoinPoint->methodName);
        $aspects = array_replace($aspects, $annotationAspects);
        if (empty($aspects)) {
            return $proceedingJoinPoint->processOriginalMethod();
        }

        $container = ApplicationContext::getContainer();
        if (method_exists($container, 'make')) {
            $pipeline = $container->make(Pipeline::class);
        } else {
            $pipeline = new Pipeline($container);
        }
        return $pipeline->via('process')
            ->through($aspects)
            ->send($proceedingJoinPoint)
            ->then(function (ProceedingJoinPoint $proceedingJoinPoint) {
                return $proceedingJoinPoint->processOriginalMethod();
            });
    }

    private static function parseAspects(string $className, string $method): array
    {
        $aspects = AspectCollector::get('classes', []);
        $matchAspect = [];
        foreach ($aspects as $aspect => $rules) {
            foreach ($rules as $rule) {
                if ($rule === $className) {
                    $matchAspect[] = $aspect;
                    break;
                }
                if (strpos($rule, '::') !== false) {
                    [$expectedClass, $expectedMethod] = explode('::', $rule);
                    if ($expectedClass === $className && $expectedMethod === $method) {
                        $matchAspect[] = $aspect;
                        break;
                    }
                }
                if (strpos($rule, '*') !== false) {
                    $preg = str_replace(['*', '\\'], ['.*', '\\\\'], $rule);
                    $pattern = "/^${preg}$/";
                    if (preg_match($pattern, $className)) {
                        $matchAspect[] = $aspect;
                        break;
                    }
                }
            }
        }
        return array_unique($matchAspect);
    }

    private static function getAnnotationAspects(string $className, string $method): array
    {
        $matchAspect = $annotations = $rules = [];

        $classAnnotations = AnnotationCollector::get($className . '._c', []);
        $methodAnnotations = AnnotationCollector::get($className . '._m.' . $method, []);
        $annotations = array_unique(array_merge(array_keys($classAnnotations), array_keys($methodAnnotations)));
        if (! $annotations) {
            return $matchAspect;
        }

        $aspects = AspectCollector::get('annotations', []);
        foreach ($aspects as $aspect => $rules) {
            foreach ($rules as $rule) {
                foreach ($annotations as $annotation) {
                    if (strpos($rule, '*') !== false) {
                        $preg = str_replace(['*', '\\'], ['.*', '\\\\'], $rule);
                        $pattern = "/^${preg}$/";
                        if (! preg_match($pattern, $annotation)) {
                            continue;
                        }
                    } elseif ($rule !== $annotation) {
                        continue;
                    }
                    $matchAspect[] = $aspect;
                }
            }
        }
        return $matchAspect;
    }
}
