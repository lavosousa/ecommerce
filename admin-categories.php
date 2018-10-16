<?php

use \Hcode\PageAdmin;
use \Hcode\Model\User;
use \Hcode\Model\Category;
use \Hcode\Model\Product;


$app->get('/admin/categories', function() {

	User::verifyLogin();   

	$search = (isset($_GET['search'])) ? $_GET['search'] : "";
	$page = (isset($_GET['page'])) ? (int)$_GET['page'] : 1;

	if ($search != '') {     // tem pesquisa

		$pagination = Category::getPageSearch($search, $page); 


	} else {                // Não tem pesquisa

		$pagination = Category::getPage($page); 

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

	$page = new PageAdmin();

	$page->SetTpl("categories",[		
		"categories"=>$pagination['data'],
		"search"=>$search,
		"pages"=>$pages
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


$app->get("/admin/categories/:idcategory/products", function($idcategory) {

	User::verifyLogin();  

	$category = new Category();

	$category->get((int)$idcategory);

	$page = new PageAdmin();

	$page->SetTpl("categories-products", [
		"category"=>$category->getValues(),
		"productsRelated"=>$category->getProducts(),
		"productsNotRelated"=>$category->getProducts(false)
	]);

});

$app->get("/admin/categories/:idcategory/products/:idproduct/add", function($idcategory, $idproduct) {

	User::verifyLogin();  

	$category = new Category();

	$category->get((int)$idcategory);

	$products = new Product();

	$products->get((int)$idproduct);

	$category->addProduct($products);

	header("Location: /admin/categories/".$idcategory."/products");
	exit;

});

$app->get("/admin/categories/:idcategory/products/:idproduct/remove", function($idcategory, $idproduct) {

	User::verifyLogin();  

	$category = new Category();

	$category->get((int)$idcategory);

	$products = new Product();

	$products->get((int)$idproduct);

	$category->removeProduct($products);

	header("Location: /admin/categories/".$idcategory."/products");
	exit;

});

?>