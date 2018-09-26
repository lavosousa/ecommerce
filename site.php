<?php

use \Hcode\Page;
use \Hcode\Model\Product;
use \Hcode\Model\Category;


$app->get('/', function() {      //Quando chamarem o site da pasta raiz(loja)...

	$products = Product::listAll();
    
	$page = new Page();  //Chama o construct e adiciona o header

	$page->setTpl("index", [
		"products"=>Product::checkList($products)
	]); // Carrge o conteúdo 
	//O destrutor vai mostrar o footer
});

$app->get("/categories/:idcategory", function($idcategory) {

	$category = new Category();

	$category->get((int)$idcategory);

	$page = new Page();

	$page->SetTpl("category", [
		"category"=>$category->getValues(),
		"products"=>Product::checkList($category->getProducts())
	]);

});

?>