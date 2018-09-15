<?php 

require_once("vendor/autoload.php");

use \Slim\Slim;
use \Hcode\Page;
use \Hcode\PageAdmin;

$app = new Slim();

$app->config('debug', true);

$app->get('/', function() {      //Quando chamarem o site da pasta raiz...
    
	$page = new Page();  //Chama o construct e adiciona o header

	$page->setTpl("index");  // Carrge o conteúdo 
	//O destrutor vai mostrar o footer
});

$app->get('/admin', function() {      //Quando chamarem o site da pasta raiz...
    
	$page = new PageAdmin();  //Chama o construct e adiciona o header

	$page->setTpl("index");  // Carrge o conteúdo 
	//O destrutor vai mostrar o footer
});

$app->run();

 ?>