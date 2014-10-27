<?php

namespace resource\Data;

use \PDO;
use \stdClass;
use resource\Data\dbConfig;
use resource\Entities\Film;
use resource\Entities\Dvd_nummer;
use resource\Exception\NummerBestaatAlException;
use resource\Exception\OngeldigNummerException;
use resource\Exception\OnvindbaarException;
use Doctrine\Common\ClassLoader;
require_once("Doctrine/Common/ClassLoader.php");
$classLoader = new ClassLoader("resource", "src");
$classLoader->register();

class FilmDAO {
    
    public function getAll() {
        $filmLijst = array();
        $dbh = new PDO(dbConfig::$db_conn, dbConfig::$db_user, dbConfig::$db_pass);
        $film_sql = $dbh->prepare("SELECT id, titel, imdb_id FROM films ORDER BY titel ASC");
        $film_sql->execute();
        $film_result = $film_sql->fetchAll(PDO::FETCH_ASSOC);
        $dvd_sql = $dbh->prepare("SELECT id, film_id, leenstatus FROM dvd_nummers ORDER BY film_id, id");
        $dvd_sql->execute();
        $dvd_result = $dvd_sql->fetchAll(PDO::FETCH_ASSOC);
        foreach ($film_result as $film) {
            $thisFilm = Film::create($film["id"], $film["titel"], $film["imdb_id"], array());
            $filmLijst[$film["id"]] = $thisFilm;
        }
        foreach ($dvd_result as $dvd) {
            $thisDVD = Dvd_nummer::create($dvd["id"], $dvd["film_id"], $dvd["leenstatus"]);
            $filmLijst[$dvd["film_id"]]->pushDVD($thisDVD);
        }
        return $filmLijst;
        $dbh = null;
    }
    
    public function getById($id) {
        $dbh = new PDO(dbConfig::$db_conn, dbConfig::$db_user, dbConfig::$db_pass);
        $sql = $dbh->prepare("SELECT id, titel, imdb_id FROM films WHERE id = :id");
        $sql->bindParam(":id", $id);
        $sql->execute();
        $rij = $sql->fetch(PDO::FETCH_ASSOC);
        if ($sql->rowCount() > 0) {
            $dvd_sql = "SELECT * FROM dvd_nummers WHERE film_id = " . $rij["id"] . " ORDER BY id";
            $dvd_resultSet = $dbh->query($dvd_sql);
            $dvd_lijst = array();
            if(!empty($dvd_resultSet)) {
                foreach ($dvd_resultSet as $dvd_rij) {
                    $thisDVD = Dvd_nummer::create($dvd_rij["id"], $rij["id"], $dvd_rij["leenstatus"]);
                    array_push($dvd_lijst, $thisDVD);
                }
            } else {
                $dvd_lijst = null;
            }
            $thisFilm = Film::create($rij["id"], $rij["titel"], $rij["imdb_id"], $dvd_lijst);
            return $thisFilm;
        } else {
            return false;
        }
        $dbh = null;
    }
    
    public function getByDVD($dvd_id) {
        if($dvd_id < 0) { throw new OngeldigNummerException(); }
        $filmLijst = array();
        $dbh = new PDO(dbConfig::$db_conn, dbConfig::$db_user, dbConfig::$db_pass);
        $id_sql = $dbh->prepare("SELECT film_id FROM dvd_nummers WHERE id = :dvd_id");
        $id_sql->bindParam(":dvd_id", $dvd_id);
        $id_sql->execute();
        if($id_sql->rowCount() == 0) { throw new OnvindbaarException(); }
        $id_rij = $id_sql->fetch(PDO::FETCH_ASSOC);
        $id = $id_rij["film_id"];
        array_push($filmLijst, FilmDAO::getById($id));
        return $filmLijst;
        $dbh = null;
    }
    
    public function getByTitle($titel) {
        $filmLijst = array();
        $dbh = new PDO(dbConfig::$db_conn, dbConfig::$db_user, dbConfig::$db_pass);
        $sql = $dbh->prepare("SELECT id FROM films WHERE titel LIKE :titel");
        $titel = str_replace("*", "%", $titel);
        if (strpos($titel, "%") === false) {
            $titel = "%" . $titel . "%";
        }
        $sql->bindParam(":titel", $titel);
        $sql->execute();
        if($sql->rowCount() == 0) { throw new OnvindbaarException(); }
        $result = $sql->fetchAll(PDO::FETCH_ASSOC);
        foreach ($result as $rij) {
            $id = $rij["id"];
            array_push($filmLijst, FilmDAO::getById($id));
        }
        return $filmLijst;
        $dbh = null;
    }
    
    public function createFilm($titel, $imdb_id) {
        $dbh = new PDO(dbConfig::$db_conn, dbConfig::$db_user, dbConfig::$db_pass);
        $check_sql = $dbh->prepare("SELECT id, imdb_id FROM films WHERE titel = :titel AND imdb_id = :imdb_id");
        $check_sql->bindParam(":titel", $titel);
        $check_sql->bindParam(":imdb_id", $imdb_id);
        $check_sql->execute();
        if ($check_sql->rowCount() > 0) { throw new NummerBestaatAlException(); }
        $sql = $dbh->prepare("INSERT INTO films (titel, imdb_id) VALUES (:titel, :imdb_id)");
        $sql->bindParam(":titel", $titel);
        $sql->bindParam(":imdb_id", $imdb_id);
        $sql->execute();
        $dbh = null;
    }
    
    public function deleteFilm($id) {
        $dbh = new PDO(dbConfig::$db_conn, dbConfig::$db_user, dbConfig::$db_pass);
        $film_sql = $dbh->prepare("DELETE FROM films WHERE id = :id");
        $film_sql->bindParam(":id", $id);
        $film_sql->execute();
        // Onderstaande niet nodig want FOREIGN KEY ingevoerd in database met ON DELETE CASCADE
        /*$dvd_sql = $dbh->prepare("DELETE FROM dvd_nummers WHERE film_id = :id");
        $dvd_sql->bindParam(":id", $id);
        $dvd_sql->execute();*/
        $dbh = null;
    }
    
    public function nextNumber($limit) {
        $dbh = new PDO(dbConfig::$db_conn, dbConfig::$db_user, dbConfig::$db_pass);
        $dvd_sql = $dbh->prepare("SELECT id FROM dvd_nummers ORDER BY id");
        $dvd_sql->execute();
        $resultSet = $dvd_sql->fetchAll(PDO::FETCH_ASSOC);
        $key = 0;
        $emptynumber = 0;
        $i = 0;
        while ($i <= $limit) {
            if(!isset($resultSet[$key]["id"]) or ($resultSet[$key]["id"] <> $key)) {
                $emptynumber = $key;
                break;
            }
            $key++;
        }
        return $emptynumber;
    }
    
    public function createDVD($filmid, $nummer) {
        $dbh = new PDO(dbConfig::$db_conn, dbConfig::$db_user, dbConfig::$db_pass);
        $check_sql = $dbh->prepare("SELECT * FROM dvd_nummers WHERE id = :id");
        $check_sql->bindParam(":id", $nummer);
        $check_sql->execute();
        $checked = $check_sql->rowCount();
        if ($checked != 0) {throw new NummerBestaatAlException();}
        if ($nummer < 0) {throw new OngeldigNummerException();}
        $sql = $dbh->prepare("INSERT INTO dvd_nummers (id, film_id) VALUES (:id, :film_id)");
        $sql->bindParam(":id", $nummer);
        $sql->bindParam(":film_id", $filmid);
        $sql->execute();
    }
    
    public function getAllDVD() {
        $dvdLijst = array();
        $dbh = new PDO(dbConfig::$db_conn, dbConfig::$db_user, dbConfig::$db_pass);
        $sql = $dbh->prepare("SELECT dvd_nummers.id AS id, films.titel AS titel, films.imdb_id AS imdb_id, dvd_nummers.leenstatus AS leenstatus FROM dvd_nummers, films WHERE dvd_nummers.film_id = films.id ORDER BY id ASC");
        $sql->execute();
        $resultSet = $sql->fetchAll(PDO::FETCH_ASSOC);
        foreach ($resultSet as $rij) {
            $thisDVD = new stdClass();
            $thisDVD->id = $rij["id"];
            $thisDVD->titel = $rij["titel"];
            $thisDVD->imdb_id = $rij["imdb_id"];
            $thisDVD->leenstatus = $rij["leenstatus"];
            array_push($dvdLijst, $thisDVD);
        }
        $dbh = null;
        return $dvdLijst;
    }
    
    public function deleteDVD($id) {
        $dbh = new PDO(dbConfig::$db_conn, dbConfig::$db_user, dbConfig::$db_pass);
        $sql = $dbh->prepare("DELETE FROM dvd_nummers WHERE id = :id");
        $sql->bindParam(":id", $id);
        $sql->execute();
        $dbh = null;
    }
    
    public function updateDVDStatus($id, $change) {
        $dbh = new PDO(dbConfig::$db_conn, dbConfig::$db_user, dbConfig::$db_pass);
        $sql = $dbh->prepare("UPDATE dvd_nummers SET leenstatus = :leenstatus WHERE id = :id");
        $sql->bindParam(":leenstatus", $change);
        $sql->bindParam(":id", $id);
        $sql->execute();
        $dbh = null;
    }
    
    public function getDVDById($id) {
        if ($id < 0) { throw new OngeldigNummerException(); }
        $dvdLijst = array();
        $dbh = new PDO(dbConfig::$db_conn, dbConfig::$db_user, dbConfig::$db_pass);
        $sql = $dbh->prepare("SELECT dvd_nummers.id AS id, films.titel AS titel, dvd_nummers.leenstatus AS leenstatus FROM dvd_nummers, films WHERE dvd_nummers.id = :id AND dvd_nummers.film_id = films.id LIMIT 1");
        $sql->bindParam(":id", $id);
        $sql->execute();
        $rij = $sql->fetch(PDO::FETCH_ASSOC);
        if ($sql->rowCount() <= 0) { throw new OnvindbaarException(); }
        $thisDVD = new stdClass();
        $thisDVD->id = $rij["id"];
        $thisDVD->titel = $rij["titel"];
        $thisDVD->leenstatus = $rij["leenstatus"];
        array_push($dvdLijst, $thisDVD);
        return $dvdLijst;
    }
}