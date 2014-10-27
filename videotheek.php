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
    
    $twigvars["errormessage"] = null;
    $twigvars["info"] = null;
    
    if (isset($_GET["loginfail"])) {
        $twigvars["errormessage"] = "Login mislukt. Gebruiker en/of paswoord onbekend.";
    }
    if (isset($_SESSION["info"])) {
        $twigvars["info"] = $_SESSION["info"];
        unset($_SESSION["info"]);
    }
    
    $twigvars["timer"] = $service->ScriptTimer();
    
    $view = $twig->render("Login.twig", $twigvars);
    print($view);
    
} else {
    
    if (isset($_GET["logout"])) {
        unset($_SESSION["login"]);
        $_SESSION["info"] = "U bent succesvol uitgelogd";
        header("location: videotheek.php");
    }
    
    $twigvars["errormessage"] = null;
    $twigvars["info"] = null;
    $twigvars["filmlijst"] = null;
    
    if (isset($_SESSION["info"])) {
        $twigvars["info"] = $_SESSION["info"];
        unset($_SESSION["info"]);
    }
    
    $twigvars["gebruiker"] = $_SESSION["login"];
    $twigvars["timer"] = $service->ScriptTimer();
    
    $view = $twig->render("Videotheek.twig", $twigvars);
    print($view);    
    
}