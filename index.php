<?php
session_cache_limiter(false);
session_start(); // Add this to the top of the file

require 'vendor/autoload.php';

require('config.php');
require('lib/db.php');
require('lib/parameters.php');
require('lib/validate_params.php');
require('lib/data_handler.php');

/*
// Automatically load library files
$files = glob('../lib/*.php');
foreach ($files as $file) {
  require_once $file;
}
*/

// Prepare Slim app
$app = new \Slim\Slim(array(
  'templates.path' => 'templates',
  'cache.path' => 'cache',
));

// Set path for template views
$app->hook('slim.before.dispatch', function() use ($app) {
  $app->view()->appendData(array(
    'app_base' => $app->request()->getRootUri()
  ));
});

// Allow Cross Origin Scripting
$res = $app->response();
$res['Access-Control-Allow-Origin'] = '*';

/**
 * Service Homepage
 * Path: /
 */
$app->get('/', function() use ($app) {
  $app->render('homepage.php'); 
});

// Automatically load route files
$files = glob('routes/*.php');
foreach ($files as $file) {
  require_once $file;
}

// Run Slim
$app->run();

?>