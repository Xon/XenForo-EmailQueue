<?php

class SV_EmailQueue_Listener
{
    const AddonNameSpace = 'SV_EmailQueue_';

    public static function load_class($class, &$extend)
    {
        $extend[] = self::AddonNameSpace.$class;
    }
}
