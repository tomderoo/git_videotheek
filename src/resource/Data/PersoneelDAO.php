<?php

namespace resource\Data;

use \PDO;
use \Exception;
use resource\Data\dbConfig;
use resource\Entities\Personeel;
use Doctrine\Common\ClassLoader;
require_once("Doctrine/Common/ClassLoader.php");
$classLoader = new ClassLoader("resource", "src");
$classLoader->register();

class PersoneelDAO {
    
    public function getAll() {
        $userLijst = array();
        $dbh = new PDO(dbConfig::$db_conn, dbConfig::$db_user, dbConfig::$db_pass);
        $sql = "SELECT id, naam, paswoord FROM personeel ORDER BY naam ASC";
        $resultSet = $dbh->query($sql);
        foreach ($resultSet as $rij) {
            $thisUser = Personeel::create($rij["id"], $rij["naam"], $rij["paswoord"]);
            array_push($userLijst, $thisUser);
        }
        $dbh = null;
        return $userLijst;
    }
    
    public function getById($id) {
        $dbh = new PDO(dbConfig::$db_conn, dbConfig::$db_user, dbConfig::$db_pass);
        $sql = "SELECT naam, paswoord FROM personeel WHERE id=" . $id;
        $resultSet = $dbh->query($sql);
        $rij = $resultSet->fetch();
        $foundUser = Personeel::create($id, $rij["naam"], $rij["paswoord"]);
        $dbh = null;
        return $foundUser;
    }
    
    public function getByNaam($naam) {
        $dbh = new PDO(dbConfig::$db_conn, dbConfig::$db_user, dbConfig::$db_pass);
        $sql = "SELECT id, paswoord FROM personeel WHERE naam='" . $naam . "'";
        $resultSet = $dbh->query($sql);
        if ($resultSet->rowCount() > 0) {
            $rij = $resultSet->fetch();
            $foundUser = Personeel::create($rij["id"], $naam, $rij["paswoord"]);
            return $foundUser;
        } else {
            return false;
        }
        $dbh = null;
    }
    
    public function createPersoneel($naam, $paswoord) {
        // Voorbereiden van paswoord
        // A higher "cost" is more secure but consumes more processing power
        $crypt_cost = 5;
        $crypt_salt = strtr(base64_encode(mcrypt_create_iv(16, MCRYPT_DEV_URANDOM)), '+', '.');
        // Prefix information about the hash so PHP knows how to verify it later.
        // "$2a$" Means we're using the Blowfish algorithm. The following two digits are the cost parameter.
        $crypt_salt = sprintf("$2a$%02d$", $crypt_cost) . $crypt_salt;
        // Hash the password with the salt
        $crypt_hash = crypt($paswoord, $crypt_salt);
        // Variabele is klaar om in de database te steken
        $dbh = new PDO(dbConfig::$db_conn, dbConfig::$db_user, dbConfig::$db_pass);
        $sql = $dbh->prepare("INSERT INTO personeel (naam, paswoord) VALUES (:naam, :paswoord)");
        $sql->bindParam(":naam", $naam);
        $sql->bindParam(":paswoord", $crypt_hash);
        $sql->execute();
        //$dbh->exec($sql);
        $dbh = null;
    }
    
    public function updatePersoneel($id, $naam, $paswoord) {
        // Voorbereiden van paswoord
        // A higher "cost" is more secure but consumes more processing power
        $crypt_cost = 5;
        $crypt_salt = strtr(base64_encode(mcrypt_create_iv(16, MCRYPT_DEV_URANDOM)), '+', '.');
        // Prefix information about the hash so PHP knows how to verify it later.
        // "$2a$" Means we're using the Blowfish algorithm. The following two digits are the cost parameter.
        $crypt_salt = sprintf("$2a$%02d$", $crypt_cost) . $crypt_salt;
        // Hash the password with the salt
        $crypt_hash = crypt($paswoord, $crypt_salt);
        // Variabele is klaar om in de database te steken
        $dbh = new PDO(dbConfig::$db_conn, dbConfig::$db_user, dbConfig::$db_pass);
        $sql = $dbh->prepare("UPDATE personeel SET naam = :naam, paswoord = :paswoord WHERE id = :id");
        $sql->bindParam(":naam", $naam);
        $sql->bindParam(":paswoord", $crypt_hash);
        $sql->bindParam(":id", $id);
        $sql->execute();
        $dbh = null;
    }
    
    public function deletePersoneel($naam) {
        $dbh = new PDO(dbConfig::$db_conn, dbConfig::$db_user, dbConfig::$db_pass);
        $sql = $dbh->prepare("DELETE FROM personeel WHERE naam = :naam");
        $sql->bindParam(":naam", $naam);
        $sql->execute();
        $dbh = null;
    }
    
    public function loginPersoneel($naam, $paswoord) {
        $dbh = new PDO(dbConfig::$db_conn, dbConfig::$db_user, dbConfig::$db_pass);
        $sql = $dbh->prepare("SELECT paswoord FROM personeel WHERE naam = :naam LIMIT 1");
        $sql->bindParam(":naam", $naam);
        $sql->execute();
        if ($sql->rowCount() == 0) { throw new Exception(); }
        $personeel = $sql->fetch(PDO::FETCH_OBJ);
        if(crypt($paswoord, $personeel->paswoord) === $personeel->paswoord) {
            return true;
        } else {
            throw new Exception();
        }
    }
}