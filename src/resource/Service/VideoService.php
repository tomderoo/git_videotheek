<?php

namespace resource\Service;

use stdClass;
use resource\Data\PersoneelDAO;
use resource\Data\FilmDAO;
use Doctrine\Common\ClassLoader;
require_once("Doctrine/Common/ClassLoader.php");
$classLoader = new ClassLoader("resource", "src");
$classLoader->register();

class VideoService {
    
    /* * * * * Scripttimer * * * * */
    
    public function ScriptTimer() {
        $timer = new stdClass();
        $processtime = round((microtime(true) - $_SERVER["REQUEST_TIME_FLOAT"]) * 1000);
        if ($processtime <= 50) {
            $frontcolor = "green";
            $backcolor = "#CCFFCC";
        } elseif ($processtime > 50 && $processtime <= 100) {
            $frontcolor = "#999966";
            $backcolor = "#FFFFAA";
        } elseif ($processtime > 100 && $processtime <= 200) {
            $frontcolor = "orange";
            $backcolor = "#FFDD55";
        } elseif ($processtime > 200) {
            $frontcolor = "red";
            $backcolor = "#FFCCCC";
        }
        $timer->time = $processtime;
        $timer->fgcolor = $frontcolor;
        $timer->bgcolor = $backcolor;
        return $timer;
    }
    
    /* * * * * Login-functies * * * * */
    
    public function verifyLogin($user, $pass) {
        $verify = PersoneelDAO::loginPersoneel($user, $pass);
        return $verify;
    }
    
    public function nieuwPersoneel($naam, $paswoord) {
        $personeel = PersoneelDAO::getByNaam($naam);
        if($personeel == false) {
            PersoneelDAO::createPersoneel($naam, $paswoord);
        } else {
            $id = $personeel->getId();
            PersoneelDAO::updatePersoneel($id, $naam, $paswoord);
        }
    }
    
    /* * * * * Filmfuncties * * * * */
    
    public function alleFilms() {
        $filmlijst = FilmDAO::getAll();
        return $filmlijst;
    }
    
    public function geefFilmOpExemplaarnummer($id) {
        $filmlijst = FilmDAO::getByDVD($id);
        return $filmlijst;
    }
    
    public function geefFilmOpTitel($titel) {
        $filmlijst = FilmDAO::getByTitle($titel);
        return $filmlijst;
    }
    
    public function geefFilmOpId($id) {
        $filmlijst = array();
        array_push($filmlijst, FilmDAO::getById($id));
        return $filmlijst;
    }
    
    public function nieuweFilm($titel, $imdb) {
        FilmDAO::createFilm($titel, $imdb);
    }
    
    public function verwijderFilm($filmid) {
        FilmDAO::deleteFilm($filmid);
    }
    
    public function laagsteVrijeNummer($limit) {
        $nummer = FilmDAO::nextNumber($limit);
        return $nummer;
    }
    
    public function nieuweDVD($filmid, $nummer) {
        FilmDAO::createDVD($filmid, $nummer);
    }
    
    public function alleDVDs() {
        $dvdlijst = FilmDAO::getAllDVD();
        return $dvdlijst;
    }
    
    public function verwijderDVD($id) {
        FilmDAO::deleteDVD($id);
    }
    
    public function veranderStatus($id, $status) {
        FilmDAO::updateDVDStatus($id, $status);
    }
    
    public function geefDVDExemplaarOpNummer($id) {
        $dvdlijst = FilmDAO::getDVDById($id);
        return $dvdlijst;
    }
}