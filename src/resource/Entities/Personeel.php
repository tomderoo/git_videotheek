<?php

namespace resource\Entities;

class Personeel {
    
    private static $idMap = array();
    
    private $id;
    private $naam;
    private $pass;

    private function __construct($id, $naam, $pass) {
        $this->id = $id;
        $this->naam = $naam;
        $this->pass = $pass;
    }
    
    public static function create($id, $naam, $pass) {
        if (!isset(self::$idMap[$id])) {
            self::$idMap[$id] = new Personeel($id, $naam, $pass);
        }
        return self::$idMap[$id];
    }
    
    public function getId() {
        return $this->id;
    }
    
    public function getNaam() {
        return $this->naam;
    }
    
    public function getPass() {
        return $this->pass;
    }

    
}