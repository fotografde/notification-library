<?php

namespace GetPhoto\Helpers;

class Method
{

    protected $method;
    protected $parameters;

    public function __construct($method, $parameters)
    {
        $this->method = $method;
        $this->parameters = $parameters;
    }

    public function __toString()
    {
        return '{{' . $this->method . '||' . implode('||', $this->parameters) . '}}';
    }

}
