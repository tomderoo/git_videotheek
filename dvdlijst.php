<?php
use resource\Service\VideoService;
use Doctrine\Common\ClassLoader;
require_once("Doctrine/Common/ClassLoader.php");
$classLoader = new ClassLoader("resource", "src");
$classLoader->register();

session_start();

$service = new VideoService();

$twigvars = array();

require_once("libraries/Twig/Autoloader.php");
    
Twig_Autoloader::register();
    
$loader = new Twig_Loader_Filesystem("src/resource/Presentation");
$twig = new Twig_Environment($loader);
    

if(!isset($_SESSION["login"])) {
    
    header("location: videotheek.php");
    
} else {
    
    $twigvars["errormessage"] = null;
    $twigvars["info"] = null;
    $twigvars["selectid"] = null;
    $twigvars["popup"] = null;
    
    if (isset($_SESSION["filmlijst"])) {
        $twigvars["filmlijst"] = $_SESSION["filmlijst"];
        unset($_SESSION["filmlijst"]);
    } else {
        $twigvars["filmlijst"] = $service->alleFilms();
    }
    
    if (isset($_SESSION["dvdlijst"])) {
        $twigvars["dvdlijst"] = $_SESSION["dvdlijst"];
        unset($_SESSION["dvdlijst"]);
    } else {
        $twigvars["dvdlijst"] = $service->alleDVDs();
    }
    
    if (isset($_SESSION["info"])) {
        $twigvars["info"] = $_SESSION["info"];
        unset($_SESSION["info"]);
    }
    
    if (isset($_SESSION["error"])) {
        $twigvars["errormessage"] = $_SESSION["error"];
        unset($_SESSION["error"]);
    }
    
    if (isset($_SESSION["selectid"])) {
        $twigvars["selectid"] = $_SESSION["selectid"];
        unset($_SESSION["selectid"]);
    }
    
    if (isset($_SESSION["confirm"])) {
        $twigvars["popup"] = 1;
        unset($_SESSION["confirm"]);
    }
    
    $twigvars["vrijenummer"] = $service->laagsteVrijeNummer(10000);
    $twigvars["gebruiker"] = $_SESSION["login"];
    $twigvars["timer"] = $service->ScriptTimer();
    
    $view = $twig->render("Dvdlijst.twig", $twigvars);
    print($view);    
    
}