<?php 
session_start();   // inicia o uso de sessão
require_once("vendor/autoload.php");

use \Slim\Slim;
use \Hcode\Page;
use \Hcode\PageAdmin;
use \Hcode\Model\User;
use \Hcode\Model\Category;

$app = new Slim();

$app->config('debug', true);

$app->get('/', function() {      //Quando chamarem o site da pasta raiz(loja)...
    
	$page = new Page();  //Chama o construct e adiciona o header

	$page->setTpl("index");  // Carrge o conteúdo 
	//O destrutor vai mostrar o footer
});

$app->get('/admin', function() {    // rota pagina admin 

	User::verifyLogin();
    
	$page = new PageAdmin();  

	$page->setTpl("index");  
	
});

$app->get('/admin/login', function() {      // Login get
    
	$page = new PageAdmin([
		"header" => false,
		"footer" => false
	]); 

	$page->setTpl("login");   
	
});

$app->post('/admin/login', function() {	   // Login post
    
	User::login($_POST["login"], $_POST["password"]);

	header("Location: /admin");
	exit;   
	
});

$app->get('/admin/logout', function() {		 // Logout get
    
	User::logout();

	header("Location: /admin/login");
	exit;   
	
});

$app->get('/admin/users', function() {    // rota pagina admin / usuários

	User::verifyLogin();    // ja valida se está logado e se é admin

	$users = User::listAll();  // traz array com todos usuários
    
	$page = new PageAdmin();  // carrega pagina padrão com header e footer (normal) 

	$page->setTpl("users", array (
		"users"=>$users
	));

	$page->setTpl("users");  
	
});

$app->get('/admin/users/create', function() {   
	User::verifyLogin();    
    
	$page = new PageAdmin();  

	$page->setTpl("users-create");  	
});

$app->get('/admin/users/:iduser/delete', function($iduser) {    // tem que vir antes pois se vir depois do prox nunca será executado.

	User::verifyLogin();

	$user = new User(); // Carregar o usuário pra saber que ainda exite no bd;

	$user->get((int)$iduser);

	$user->delete();

	header("Location: /admin/users");
	exit;

});

$app->get('/admin/users/:iduser', function($iduser) {    // rota pagina admin / usuários

	User::verifyLogin();

	$user = new User();

	$user->get((int)$iduser);
    
	$page = new PageAdmin();  

	$page->setTpl("users-update", array(
		"user"=>$user->getValues()
	));  
	
});

$app->post('/admin/users/create', function() {      // create via post para gravar

	User::verifyLogin();

	$user = new User();

	$_POST["inadmin"] = (isset($_POST["inadmin"]))?1:0;  

 	$_POST['despassword'] = password_hash($_POST["despassword"], PASSWORD_DEFAULT, [
 		"cost"=>12 
 	]);

	$user->setData($_POST);
		   
	$user->save();

	header("Location: /admin/users");	
	exit;
	
});

$app->post('/admin/users/:iduser', function($iduser) {    // create via post para gravar, agora recebendo

	User::verifyLogin();   

	$user = new User();

	$_POST["inadmin"] = (isset($_POST["inadmin"]))?1:0;

	$user->get((int)$iduser);

	$user->setData($_POST);

	$user->update();

	header("Location: /admin/users");	
	exit;

});

$app->get('/admin/forgot', function() {    

	$page = new PageAdmin([
		"header" => false,
		"footer" => false
	]);

	$page->setTpl("forgot");
});


$app->post('/admin/forgot', function() {    

	$user = User::getForgot($_POST["email"]);

	header("Location: /admin/forgot/sent");	
	exit;

});

$app->get('/admin/forgot/sent', function() {    

	$page = new PageAdmin([
		"header" => false,
		"footer" => false
	]);

	$page->setTpl("forgot-sent");

});

$app->get('/admin/forgot/reset', function() {

	$user = User::validForgotDecrypt($_GET["code"]);   // recebe o codigo via get

	$page = new PageAdmin([
		"header" => false,
		"footer" => false
	]);

	$page->setTpl("forgot-reset", array(
		"name"=>$user["desperson"],
		"code"=>$_GET["code"]
	));

});

$app->post('/admin/forgot/reset', function() {

	$forgot = User::validForgotDecrypt($_POST["code"]);   // recebe o codigo via get

	User::setForgotUsed($forgot["idrecovery"]); // PAra invalidar o código apos uso.

	$user = new User();

	$user->get((int)$forgot["iduser"]);

	$password = password_hash($_POST["password"], PASSWORD_DEFAULT, [
		"cost"=>12
	]);

	$user->setPassword($password);

	$page = new PageAdmin([
		"header" => false,
		"footer" => false
	]);	

	$page->setTpl("forgot-reset-success");

});

$app->get('/admin/categories', function() {

	User::verifyLogin();   

	$categories = Category::listAll();

	$page = new PageAdmin();

	$page->SetTpl("categories",[
		"categories"=>$categories
	]);

});

$app->get('/admin/categories/create', function() {

	User::verifyLogin();  

	$page = new PageAdmin();

	$page->SetTpl("categories-create");

});

$app->post('/admin/categories/create', function() {

	User::verifyLogin();  

	$category = new Category();

	$category->setdata($_POST);

	$category->save();

	header("location: /admin/categories");
	exit;
});

$app->get('/admin/categories/:idcategory/delete', function($idcategory) {

	User::verifyLogin();

	$category = new Category();

	$category->get((int)$idcategory);

	$category->delete();

	header("Location: /admin/categories");
	exit;

});

$app->get('/admin/categories/:idcategory', function($idcategory) {

	User::verifyLogin();  

	$category = new Category();

	$category->get((int)$idcategory);

	$page = new PageAdmin();

	$page->SetTpl("categories-update", [
		"category"=>$category->getValues()
	]);

});

$app->post('/admin/categories/:idcategory', function($idcategory) {

	User::verifyLogin();  

	$category = new Category();

	$category->get((int)$idcategory);

	$category->setData($_POST);

	$category->save();

	header("Location: /admin/categories");
	exit;

});

$app->run();

 ?>