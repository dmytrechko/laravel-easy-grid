<?php namespace EasyGrid\EasyGrid\Adapters;

use EasyGrid\EasyGrid\Contracts\ParamsAdapterInterface;

class RequestParams implements ParamsAdapterInterface
{
    protected $defaults = [];
    protected $current = [];
    
    public function __construct(array $defaults = [])
    {
        $this->defaults = $defaults;
    }
    
    public function get($name)
    {
      return array_get($this->current, $name, request($name,array_get($this->defaults,$name)));
    }
    
    public function set($name,$value)
    {
        array_set($this->current,$name,$value);
    }
    
}