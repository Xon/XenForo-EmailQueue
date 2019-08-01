<?php

class SV_EmailQueue_Listener
{
    public static function load_class($class, &$extend)
    {
        $extend[] = 'SV_EmailQueue_' . $class;
    }
}
