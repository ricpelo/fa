<?php

namespace mio;

class A
{
    public function __construct()
    {
        echo 'Hola, soy A';
    }
}

function strlen($x)
{
    return 0;
}

echo strlen('hola') . PHP_EOL;
