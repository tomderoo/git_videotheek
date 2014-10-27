<?php

namespace resource\Entities;

class Dvd_nummer {
    
    private static $idMap = array();
    
    private $id;
    private $dvd_id;
    private $leenstatus;

    private function __construct($id, $dvd_id, $leenstatus) {
        $this->id = $id;
        $this->dvd_id = $dvd_id;
        $this->leenstatus = $leenstatus;
    }
    
    public static function create($id, $dvd_id, $leenstatus) {
        if (!isset(self::$idMap[$id])) {
            self::$idMap[$id] = new Dvd_nummer($id, $dvd_id, $leenstatus);
        }
        return self::$idMap[$id];
    }
    
    public function getId() {
        return $this->id;
    }
    
    public function getDvdId() {
        return $this->dvd_id;
    }
    
    public function getLeenstatus() {
        return $this->leenstatus;
    }
    
}