<?php

use FastD\Http\JsonResponse;
use FastD\Http\Response;
use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Database\Connection;

if (!function_exists('eloquent_db')) {
    /**
     * @param null $name
     *
     * @return Connection
     */
    function eloquent_db($name = null)
    {
        return app()->get('eloquent')->getConnection($name);
    }
}

if (!function_exists('eloquent')) {
    /**
     * @param Arrayable $resource
     * @param int       $statusCode
     *
     * @return JsonResponse
     */
    function eloquent(Arrayable $resource, $statusCode = Response::HTTP_OK)
    {
        return new JsonResponse($resource->toArray(), $statusCode);
    }
}

if (!function_exists('per_page')) {
    /**
     * @param string $name
     * @param int    $default
     *
     * @return int
     */
    function per_page($name = 'per_page', $default = 15)
    {
        $perPage = intval(request()->getQueryParams()[$name] ?? $default);

        $perPage < 0 && $perPage = 15;

        return $perPage;
    }
}

if (!function_exists('event')) {
    /**
     * @return \Illuminate\Events\Dispatcher
     */
    function event()
    {
        return app()->get('event');
    }
}
