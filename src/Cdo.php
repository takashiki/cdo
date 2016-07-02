<?php

namespace Mis;

class Cdo
{
    public $routes = [];
    public $methods = [
        'get',
        'post',
        'put',
        'delete',
        'options',
        'patch',
        'head',
    ];

    protected $requestUrl;
    protected $requestMethod;
    protected $requestParams = [];
    protected $defaultCallback;

    protected static $instance = null;

    public function __construct()
    {
        $this->requestUrl = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
        $this->requestMethod = $_SERVER['REQUEST_METHOD'];
        $this->defaultCallback = [$this, 'notFound'];
    }

    public function __call($method, $params)
    {
        if (
            in_array($method, $this->methods) &&
            is_string($params[0]) &&
            is_callable($params[1])
        ) {
            $this->_map(strtoupper($method), $params[0], $params[1]);
        } else {
            call_user_func_array([$this, '_'.$method], $params);
        }
    }

    public static function __callStatic($name, $params)
    {
        if (static::$instance === null) {
            static::$instance = new static();
        }

        call_user_func_array([static::$instance, $name], $params);
    }

    protected function routeKey($method, $route)
    {
        return $method.' '.$route;
    }

    protected function match($route)
    {
        list($methods, $pattern) = explode(' ', $route);

        return $this->matchMethod($methods) && $this->matchRoute($pattern);
    }

    protected function matchMethod($methods)
    {
        return in_array($this->requestMethod, explode('|', strtoupper($methods)));
    }

    protected function matchRoute($pattern)
    {
        if (preg_match_all("#^{$pattern}$#", $this->requestUrl, $matches, PREG_SET_ORDER)) {
            $this->requestParams = array_slice($matches[0], 1);

            return true;
        }

        return false;
    }

    protected function getParameters(callable $callable)
    {
        $reflection = is_array($callable) ?
            new \ReflectionMethod($callable[0], $callable[1]) :
            new \ReflectionFunction($callable);

        $params = [];
        $i = 0;
        foreach ($reflection->getParameters() as $param) {
            if (isset($this->requestParams[$param->name])) {
                $params[] = $this->requestParams[$param->name];
            } else {
                $params[] = $this->requestParams[$i];
            }
            ++$i;
        }

        return $params;
    }

    protected function _map($method, $route, $callback)
    {
        $this->routes[$this->routeKey($method, $route)] = $callback;
    }

    protected function _any($route, $callback)
    {
        $this->_map(implode('|', $this->methods), $route, $callback);
    }

    protected function _run()
    {
        $handler = $this->defaultCallback;
        foreach ($this->routes as $route => $callback) {
            if ($this->match($route)) {
                $handler = $callback;
                break;
            }
        }

        return call_user_func_array($handler, $this->getParameters($handler));
    }

    public function setNotFound(callable $callback)
    {
        $this->defaultCallback = $callback;
    }

    public function notFound()
    {
        header($_SERVER['SERVER_PROTOCOL'].' 404 Not Found');
        echo '404';
    }
}
