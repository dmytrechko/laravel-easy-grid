<?php
/**
 * Created by PhpStorm.
 * User: Nope
 * Date: 13.01.2017
 * Time: 23:08
 */

namespace EasyGrid\EasyGrid\Facades;

use Illuminate\Support\Facades\Facade;

class Grid extends Facade
{
    protected static function getFacadeAccessor()
    {
        return 'easygrid';
    }
    
    
}

