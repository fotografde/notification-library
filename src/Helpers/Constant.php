<?php

namespace GetPhoto\Helpers;

class Constant
{

    protected $name;
    protected $value;

    public function __construct($name, $value)
    {
        $this->name = $name;
        $this->value = $value;
    }

    public function __toString()
    {
        return $this->name . '::' . $this->value;
    }

}
