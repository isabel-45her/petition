<?php

require '/./libs/Slim/Slim.php';
require '/./libs/rb.php';
\Slim\Slim::registerAutoloader();

R::setup('mysql:host=localhost;dbname=petition','root','');

//R::freeze(true);
$app = new \Slim\Slim();
$app->contentType('application/json');


$app->post('/', function() use ($app) {
session_start(); // Starting Session

$username = $app->request->post('username');
$password = $app->request->post('password');


$article = R::findOne('user', 'username=?', array((string)$username));
    if ($article) { // if found, return JSON response
      $pass_db = (string)$article->password;
      $pass_request = (string)$password;
      if($pass_db === $pass_request)
      {
     
        $_SESSION['id'] = $article->id;
        $_SESSION['username'] = $article->username;
        echo $_SESSION['username'];
        $app->redirect('posts.html');
          }
      else
      {
        echo "wrong password";
      }

    }
    else {
echo "go register first";
    }
});



$app->post('/register', function() use ($app) {

$username = $app->request->post('username');
$password = $app->request->post('password');
    
    $article = R::dispense('user');
    $article->password = (string)$password;
    $article->username = (string)$username;
    $id = R::store($article);
echo "registerd";
$app->redirect('/add');
});



$app->post('/add', function() use ($app) {

if(isset($_SESSION['username'])){


$title = $app->request->post('title');
$details = $app->request->post('details');
    
    $article = R::dispense('petition');
    $article->password = (string)$title;
    $article->username = (string)$details;
    $id = R::store($article);
  }else{$app->redirect('/login')}

});



$app->run();

?>