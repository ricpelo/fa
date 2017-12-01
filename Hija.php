<?php

require 'Prueba.php';

class Hija extends Prueba
{
    public $otra;

    public function __construct($otra = 'otra', $publica = self::PUBLICA_DEFAULT)
    {
        parent::__construct($publica);
        $this->otra = $otra;
    }

    public function mostrar()
    {
        echo "Vengo de Hija\n";
    }
}
