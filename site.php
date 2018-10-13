<?php

use \Hcode\Page;
use \Hcode\Model\Product;
use \Hcode\Model\Category;
use \Hcode\Model\Cart;
use \Hcode\Model\Address;
use \Hcode\Model\User;


$app->get('/', function() {      //Quando chamarem o site da pasta raiz(loja)...

	$products = Product::listAll();
    
	$page = new Page();  //Chama o construct e adiciona o header

	$page->setTpl("index", [
		"products"=>Product::checkList($products)
	]); // Carrge o conteúdo 
	//O destrutor vai mostrar o footer
});

$app->get("/categories/:idcategory", function($idcategory) {

	$page = (isset($_GET['page'])) ? (int)$_GET['page'] : 1;

	$category = new Category();

	$category->get((int)$idcategory);

	$pagination = $category->getProductsPage($page);

	$pages = [];

	for ($i=1; $i <= $pagination['pages']; $i++) { 
		array_push($pages, [
			'link'=>'/categories/'.$category->getidcategory().'?page='.$i,
			'page'=>$i
		]);
	}

	$page = new Page();	

	$page->setTpl("category", array(
		"category"=>$category->getValues(),
		"products"=>$pagination["data"],
		'pages'=>$pages
	));

});

$app->get("/products/:desurl", function($desurl){

	$product = new Product();

	$product->getFromURL($desurl);
	$page = new Page();
	$page->setTpl("product-detail", [
		'product'=>$product->getValues(),
		'categories'=>$product->getCategories()
	]);

});


$app->get("/cart", function() {

	$cart = Cart::getFromSession();

	$page = new Page();

	//var_dump($cart->getValues());
	//exit;

	$page->setTpl("cart", [
		'cart'=>$cart->getValues(),
		'products'=>$cart->getProducts(),
		'error' =>Cart::getMsgError()
	]);
});


$app->get("/cart/:idproduct/add", function($idproduct) {

	$product = new Product();

	$product->get((int)$idproduct);

	$cart = Cart::getFromSession();  // Pega o carrinho da sessão ou cria um novo

	$qtd = (isset($_GET['qtd'])) ? (int)$_GET['qtd'] : 1 ;  // Se no parametro de quantidades de produtos veio mais de um... se não fica 1.

	for ($i = 0; $i < $qtd; $i++) {   // Adiciona o produto de acordo com a quantidade passada.

		$cart->addProduct($product);

	}

	header("Location: /cart");
	exit;
});

$app->get("/cart/:idproduct/minus", function($idproduct) {

	$product = new Product();

	$product->get((int)$idproduct);

	$cart = Cart::getFromSession();  // Pega o carrinho da sessão ou cria um novo

	$cart->removeProduct($product);

	header("Location: /cart");
	exit;
});

$app->get("/cart/:idproduct/remove", function($idproduct) {

	$product = new Product();

	$product->get((int)$idproduct);

	$cart = Cart::getFromSession();  // Pega o carrinho da sessão ou cria um novo

	$cart->removeProduct($product, true);   // true para remover todos

	header("Location: /cart");
	exit;
});

$app->post("/cart/freight", function() {

	$cart = Cart::getFromSession();

	$cart->setFreight($_POST['zipcode']);

	header("Location: /cart");
	exit;
});

$app->get("/checkout", function() {

	User::verifyLogin(false);   // para uma rota que nao é para admin

	$cart = Cart::getFromSession();   // ja pega o carrinho da sessão

	$address = new Address();

	$page = new Page();

	$page->setTpl("checkout", [
			'cart'=>$cart->getValues(),
			'address'=>$address->getValues()
	]);

});

$app->get("/login", function() {

	$page = new Page();

	$page->setTpl("login", [
		'error'=>User::getError(),
		'errorRegister'=>User::getErrorRegister(),
		'registerValues'=>(isset($_SESSION['registerValues'])) ? $_SESSION['registerValues'] : ['name'=>'', 'email'=>'','phone'=>'']
	]);

});

$app->post("/login", function() {

	try {

		User::login($_POST['login'], $_POST['password']);

	} catch(Exception $e) {

		User::setError($e->getMessage());

	}

	header("Location: /checkout");
	exit;

});

$app->get('/logout', function() {		 // Logout get
    
	User::logout();

	header("Location: /login");
	exit;   
	
});

$app->post('/register', function() {

    $_SESSION['registerValues'] = $_POST;

	if (!isset($_POST['name']) || $_POST['name'] == '') {
	
		User::setErrorRegister("Preencha o seu nome.");
		header("Location: /login");
		exit;

	}
   
	if (!isset($_POST['email']) || $_POST['email'] == '') {
	
		User::setErrorRegister("Preencha o seu e-mail.");
		header("Location: /login");
		exit;
		
	}

	if (!isset($_POST['password']) || $_POST['password'] == '') {
	
		User::setErrorRegister("Preencha o sua senha.");
		header("Location: /login");
		exit;
		
	}

	if (User::checkLoginExist($_POST['email']) == true ) {

		User::setErrorRegister("Este endereço de e-mail já está sendo usado por outro usuário.");
		header("Location: /login");
		exit;
		
	}


	$user = new User();

	$user->setData([
		'inadmin'=>0,
		'deslogin'=>$_POST['email'],
		'desperson'=>$_POST['name'],
		'desemail'=>$_POST['email'],
		'despassword'=>$_POST['password'],
		'nrphone'=>$_POST['phone']
	]); 
	
	$user->save();

	User::login($_POST['email'], $_POST['password']);

	header("Location: /checkout");
	exit;

});

$app->get('/forgot', function() {    

	$page = new Page();

	$page->setTpl("forgot");
});


$app->post('/forgot', function() {    

	$user = User::getForgot($_POST["email"], false);

	header("Location: /forgot/sent");	
	exit;

});

$app->get('/forgot/sent', function() {    

	$page = new Page();

	$page->setTpl("forgot-sent");

});

$app->get('/forgot/reset', function() {

	$user = User::validForgotDecrypt($_GET["code"]);   // recebe o codigo via get

	$page = new Page();

	$page->setTpl("forgot-reset", array(
		"name"=>$user["desperson"],
		"code"=>$_GET["code"]
	));

});

$app->post('/forgot/reset', function() {

	$forgot = User::validForgotDecrypt($_POST["code"]);   // recebe o codigo via get

	User::setForgotUsed($forgot["idrecovery"]); // PAra invalidar o código apos uso.

	$user = new User();

	$user->get((int)$forgot["iduser"]);

	$password = password_hash($_POST["password"], PASSWORD_DEFAULT, [
		"cost"=>12
	]);

	$user->setPassword($password);

	$page = new Page();	

	$page->setTpl("forgot-reset-success");

});




?>