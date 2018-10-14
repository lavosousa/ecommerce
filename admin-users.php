<?php

use \Hcode\PageAdmin;
use \Hcode\Model\User;

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

?>