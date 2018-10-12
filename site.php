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

	$page->setTpl("login", ['error'=>User::getError()] );

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

?>