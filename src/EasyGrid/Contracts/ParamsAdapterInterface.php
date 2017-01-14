<?php
/**
 * Created by PhpStorm.
 * User: Nope
 * Date: 25.12.2016
 * Time: 17:05
 */

namespace EasyGrid\EasyGrid\Contracts;


interface ParamsAdapterInterface
{
    public function __construct(array $defaults = []);
    
    public function get($name);
    
    public function set($name,$value);
    
}