<?php
// Affiche les erreurs directement dans la page
init_set('display_errors', 1);
error_reporting(E_ALL);

//Inclure l'autoloader
require_once __DIR__ . '/vendor/autoload.php';

//Import des classes
use App\Config\Config;
use App\Utils\Response;

//Démarrer une session ou reprend la session existante
session_start();

//Charger nos varibles d'environnement
Config::load();

//Définir des routes avec la bibliothèque FastRoute (dépendance qui va nous aider à faire les routes) (association entre URL)
$dispatcher = FastRoute\simpleDispatcher(function(FastRoute\RouteCollector $r){
    //méthode http ce que l'on va faire soit envoyer données reupere donnes modifier donnees

    $r->addRoute('GET', '/', [App\Controllers\HomeController::class, 'index']);
    $r->addRoute('GET', '/login', [App\Controllers\AuthController::class, 'showLogin']);//AuthController gere l'authentification
    $r->addRoute('POST', '/login', [App\Controllers\AuthController::class, 'login']);//AuthController gere l'authentification
    $r->addRoute('POST', '/logout', [App\Controllers\AuthController::class, 'logout']);//AuthController gere l'authentification
     $r->addRoute('GET', '/cars', [App\Controllers\CarController::class, 'index']);//AuthController gere l'authentification
});

//Traitement de la requête

//récupérer la méthode HTTP (GET, POST, PUT, PATCH) et l'URI(/login, /car/l)
$httpMethod = $_SERVER['REQUEST_METHOD'];
$uri = rawurldecode(parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH));

//Dispatcher FastRoute 
$routeInfo = $dispatcher->dispatch($httpMethod, $uri);
$response = new Response();

//Analyser le résultat du dispatching
switch ($routeInfo[0]) {
    case FastRoute\Dispatcher::NOT_FOUND:
        $response->error("404 - Page non trouvée", 404);
        break;
    case FastRoute\Dispatcher::METHOD_NOT_ALLOWED:
        $response->error("405 - Méthode non autorisée", 405);
        break;
    case FastRoute\Dispatcher::FOUND:
        [$controllerClass, $method] = $routeInfo[1];
        $var = $routeInfo[2];
        try{
            $controller = new $controllerClass();
            call_user_func_array([$controller, $method], $vars);
        }catch(\Exception $e){
            if(Config::get('APP_DEBUG') === 'true'){
                $response->error("Erreur 500 : " . $e->getMessage(). " dans " .$e->getFile() . ":" .$e->getLine(), 500);
            }else{
                (new \App\Utils\Logger())->log('ERROR', 'Erreur Serveur :' .$e->getMessage());
                $response->error("Une ereur interne est survenue.", 500);
            }
        }
        break;
}