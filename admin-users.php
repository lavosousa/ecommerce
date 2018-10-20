<?php

use \Hcode\PageAdmin;
use \Hcode\Model\User;

$app->get('/admin/users/:iduser/password', function($iduser) {

	User::verifyLogin();    // ja valida se está logado e se é admin

	$user = new User();

	$user->get((int)$iduser);

	$page = new PageAdmin();

	$page->setTpl("users-password", [
		"user"=>$user->getValues(),
		"msgError"=>User::getError(),
		"msgSuccess"=>User::getSuccess()
	]);

});

$app->post('/admin/users/:iduser/password', function($iduser) {

	User::verifyLogin();    // ja valida se está logado e se é admin

	if (!isset($_POST['despassword']) || $_POST['despassword'] === '' ) {

		User::setError("Perencha a nova senha.");
		header("Location: /admin/users/$iduser/password");
		exit;

	}

	if (!isset($_POST['despassword-confirm']) || $_POST['despassword-confirm'] === '' ) {

		User::setError("Perencha a confirmação da nova senha.");
		header("Location: /admin/users/$iduser/password");
		exit;
				
	}

	if ($_POST['despassword'] !== $_POST['despassword-confirm']) {

		User::setError("Confirme corretamente as senhas.");
		header("Location: /admin/users/$iduser/password");
		exit;
				
	}	

	$user = new User();

	$user->get((int)$iduser);

	$user->setPassword(User::getPasswordHash($_POST['despassword']));

	User::setSuccess("Senha alterada com sucesso.");
	header("Location: /admin/users/$iduser/password");
	exit;
	
});

$app->get('/admin/users', function() {    // rota pagina admin / usuários

	User::verifyLogin();    // ja valida se está logado e se é admin

	$search = (isset($_GET['search'])) ? $_GET['search'] : "";
	$page = (isset($_GET['page'])) ? (int)$_GET['page'] : 1;

	if ($search != '') {     // tem pesquisa

		$pagination = User::getPageSearch($search, $page); 


	} else {                // Não tem pesquisa

		$pagination = User::getPage($page); 

	}	

	$pages = []; 

	for ($x = 0; $x < $pagination['pages'] ; $x++) 
	{ 

		array_push($pages, [
			'href' => '/admin/users?'.http_build_query([
				'page' => $x+1,
				'search' => $search
			]),
			'text'=>$x+1
		]);
	}
    
	$page = new PageAdmin();  // carrega pagina padrão com header e footer (normal) 

	$page->setTpl("users", array (
		"users"=>$pagination['data'],
		"search"=>$search,
		"pages"=>$pages
	));

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