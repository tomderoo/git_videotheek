<?php
use resource\Service\VideoService;
//use resource\Data\FilmDAO;
use Doctrine\Common\ClassLoader;
require_once("Doctrine/Common/ClassLoader.php");
$classLoader = new ClassLoader("resource", "src");
$classLoader->register();

session_start();

$service = new VideoService();

// Valuegrabber

if (isset($_GET["act"])) {
    $action = $_GET["act"];
} elseif (isset($_POST["act"])) {
    $action = $_POST["act"];
} else {
    header:("location: videotheek.php");
}

// Event switcher

switch ($action) {
    case "login":
        $naam = $_POST["naam"];
        $paswoord = $_POST["paswoord"];
        try {
            $service->verifyLogin($naam, $paswoord);
            $_SESSION["login"] = $naam;
            header("location: videotheek.php");
        } catch (Exception $ex) {
            header("location: videotheek.php?loginfail");
        }
        break;
        
    case "logout":
        unset($_SESSION["login"]);
        $_SESSION["info"] = "U bent succesvol uitgelogd";
        header("location: videotheek.php");
        break;
    
    case "newuser":
        $naam = $_POST["naam"];
        $paswoord = $_POST["paswoord"];
        $service->nieuwPersoneel($naam, $paswoord);
        $_SESSION["info"] = "Gebruiker succesvol aangepast / toegevoegd";
        header("location: videotheek.php");
        break;
    
    case "viewall":
        $_SESSION["filmlijst"] = $service->alleFilms();
        header("location: filmlijst.php");
        break;
    
    case "zoekfilm":
        try {
            $_SESSION["filmlijst"] = $service->geefFilmOpExemplaarnummer($_POST["nummer"]);
        } catch (resource\Exception\OngeldigNummerException $ex) {
            $_SESSION["error"] = "Niet zoeken op negatieve nummers, probeer opnieuw";
        } catch (resource\Exception\OnvindbaarException $ex) {
            $_SESSION["error"] = "Dit filmnummer is onvindbaar, probeer opnieuw";
        }
        header("location: filmlijst.php");
        break;
    
    case "zoektitel":
        try {
            $_SESSION["filmlijst"] = $service->geefFilmOpTitel($_POST["titel"]);
        } catch (resource\Exception\OnvindbaarException $ex) {
            $_SESSION["error"] = "Geen gelijkaardige filmtitels gevonden, probeer opnieuw";
        }
        header("location: filmlijst.php");
        break;
    
    case "nieuwetitel":
        $film = $_POST["film"];
        $imdb = $_POST["imdb"];
        try {
            $service->nieuweFilm($film, $imdb);
            $_SESSION["info"] = "Titel '" . $film . "' werd succesvol toegevoegd";
        } catch (resource\Exception\NummerBestaatAlException $ex) {
            $_SESSION["error"] = "Deze titel bestaat al, probeer opnieuw";
        }
        
        header("location: filmlijst.php");
        break;
    
    case "deletefilm":
        if (!isset($_POST["confirm"])) {
            $_SESSION["confirm"] = 1;
            $_SESSION["filmlijst"] = $service->geefFilmOpId($_POST["filmid"]);
            header("location: filmlijst.php");
        } else {
            $service->verwijderFilm($_POST["filmid"]);
            $_SESSION["info"] = "Film succesvol verwijderd...";
            header("location: filmlijst.php");
        }
        break;
    
    case "focusdvd":
        $_SESSION["selectid"] = $_POST["selectid"];
        header("location: dvdlijst.php");
        break;
    
    case "nieuwedvd":
        try {
            $service->nieuweDVD($_POST["filmid"], $_POST["nummer"]);
            $_SESSION["info"] = "DVD-exemplaar nr. " . $_POST["nummer"] . " succesvol toegevoegd...";
        } catch (resource\Exception\NummerBestaatAlException $nba) {
            $_SESSION["error"] = "Ongeldige bewerking: dit DVD-nummer bestaat reeds";
        } catch (resource\Exception\OngeldigNummerException $ong) {
            $_SESSION["error"] = "Ongeldige bewerking: ongeldig nummerformaat";
        }
        header("location: dvdlijst.php");
        break;
    
    case "zoekdvd":
        if (isset($_POST["id"]) && is_numeric($_POST["id"])) {
            $dvdid = $_POST["id"];
        } elseif (isset($_GET["id"]) && is_numeric($_GET["id"])) {
            $dvdid = $_GET["id"];
        }
        try {
            $_SESSION["dvdlijst"] = $service->geefDVDExemplaarOpNummer($dvdid);
        } catch (resource\Exception\OngeldigNummerException $ex) {
            $_SESSION["error"] = "Geen negatieve nummers in de database, zoek opnieuw";
        } catch (resource\Exception\OnvindbaarException $ex) {
            $_SESSION["error"] = "Nummer onvindbaar, zoek opnieuw";
        }
        
        header("location: dvdlijst.php");
        break;
    
    case "retour":
        $service->veranderStatus($_POST["id"], 1);
        if (isset($_POST["single"])) {
            $dvdid = $_POST["single"];
            $_SESSION["dvdlijst"] = $service->geefDVDExemplaarOpNummer($dvdid);
        }
        header("location: dvdlijst.php");
        break;
    
    case "ajaxretour":
        $service->veranderStatus($_POST["id"], 1);
        break;
        
    case "leen":
        $service->veranderStatus($_POST["id"], 0);
        if (isset($_POST["single"])) {
            $dvdid = $_POST["single"];
            $_SESSION["dvdlijst"] = $service->geefDVDExemplaarOpNummer($dvdid);
        }
        header("location: dvdlijst.php");
        break;
        
    case "ajaxleen":
        $service->veranderStatus($_POST["id"], 0);
        break;
    
    case "kill":
        if (!isset($_POST["confirm"])) {
            $_SESSION["confirm"] = 1;
            $_SESSION["dvdlijst"] = $service->geefDVDExemplaarOpNummer($_POST["id"]);
            header("location: dvdlijst.php");
        } else {
            $service->verwijderDVD($_POST["id"]);
            $_SESSION["info"] = "DVD-exemplaar succesvol verwijderd...";
            header("location: dvdlijst.php");
        }
        break;
    
    default:
        header("location: videotheek.php");
        break;
}