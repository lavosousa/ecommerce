<?php 
session_start();   // inicia o uso de sessão
require_once("vendor/autoload.php");

use \Slim\Slim;
use \Hcode\Page;
use \Hcode\PageAdmin;
use \Hcode\Model\User;

$app = new Slim();

$app->config('debug', true);

$app->get('/', function() {      //Quando chamarem o site da pasta raiz...
    
	$page = new Page();  //Chama o construct e adiciona o header

	$page->setTpl("index");  // Carrge o conteúdo 
	//O destrutor vai mostrar o footer
});

$app->get('/admin', function() {     

	User::verifyLogin();
    
	$page = new PageAdmin();  

	$page->setTpl("index");  
	
});

$app->get('/admin/login', function() {      
    
	$page = new PageAdmin([
		"header" => false,
		"footer" => false
	]); 

	$page->setTpl("login");   
	
});

$app->post('/admin/login', function() {
    
	User::login($_POST["login"], $_POST["password"]);

	header("Location: /admin");
	exit;   
	
});

$app->get('/admin/logout', function() {
    
	User::logout();

	header("Location: /admin/login");
	exit;   
	
});

$app->run();

 ?>