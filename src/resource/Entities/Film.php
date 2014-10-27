<?php

namespace resource\Entities;

class Film {
    
    private static $idMap = array();
    
    private $id;
    private $titel;
    private $imdb_id;
    private $dvd = array();    // DVD-nummers toevoegen is vals spelen! Eigenlijk zijn deze geen onderdeel van de database-entiteit, en dus mogen ze ook geen onderdeel uitmaken van de php-entiteit; dit is enkel maar toegevoegd om de data te kunnen tonen, en daar moet je stdClasses voor gebruiken!!!
    
    private function __construct($id, $titel, $imdb_id, $dvd) {
        $this->id = $id;
        $this->titel = $titel;
        $this->imdb_id = $imdb_id;
        $this->dvd = $dvd;
    }
    
    public static function create($id, $titel, $imdb_id, $dvd) {
        if (!isset(self::$idMap[$id])) {
            self::$idMap[$id] = new Film($id, $titel, $imdb_id, $dvd);
        }
        return self::$idMap[$id];
    }
    
    public function getId() {
        return $this->id;
    }
    
    public function getTitel() {
        return $this->titel;
    }
    
    public function getImdb() {
        return $this->imdb_id;
    }
    
    public function getDVD() {
        return $this->dvd;
    }
    
    public function setDVD($dvd) {
        $this->dvd = $dvd;
    }
    
    // VALSSPEELFUNCTIE! Dit moet ik er bij zetten omdat ik vals heb gespeeld met het toewijzen van eigenschappen aan deze Entitity 'Film' (nl. een dvd-lijst) om films te kunnen tonen => dit tonen moet in de plaats daarvan worden gedaan met stdClasses!!!
    public function pushDVD($dvd) {
        array_push($this->dvd, $dvd);
    }
}