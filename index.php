<?php

require '/./libs/Slim/Slim.php';
require '/./libs/rb.php';

\Slim\Slim::registerAutoloader();

// set up database connection
R::setup('mysql:host=localhost;dbname=doctornowv1','root','');

//R::freeze(true);
$app = new \Slim\Slim();
$app->contentType('application/json');


$app->get('/test', function() use ($app) {
  
  echo "working";
 
});


$app->run();

?>