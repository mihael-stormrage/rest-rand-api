<?php
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\Factory\AppFactory;
use Slim\Views\PhpRenderer;
use Lazer\Classes\Database as Lazer;

require __DIR__ . '/../vendor/autoload.php';

define('LAZER_DATA_PATH', realpath(__DIR__).'/../data/'); //Path to folder with tables
$db = Lazer::table('db');

// Instantiate App
$app = AppFactory::create();

// Add error middleware
$app->addErrorMiddleware(true, true, true);

$app->get('/', function ($request, $response) {
    $phpView = new PhpRenderer('../templates');
    return $phpView->render($response, 'about.phtml');
});

function getById($id) {
    global $db;
    $record = $db->where('id', '=', $id)->findAll()->asArray();
    $payload = json_encode($record);
    return $payload;
};

$app->map(['GET', 'POST'], '/generate', function (Request $request, Response $response) {
    global $db;
    $db->number = rand();
    $db->save();

    $last = $db->lastId();
    $lastRecord = getById($last);
    $response->getBody()->write($lastRecord);
    return $response
          ->withHeader('Content-Type', 'application/json')
          ->withStatus(201);
});

$app->get('/retrieve/{id}', function (Request $request, Response $response, $args) {
    global $db;
    $id = $args['id'];
    $found = getById($id);
    $response->getBody()->write($found);
    return $response
          ->withHeader('Content-Type', 'application/json');
});

$app->run();