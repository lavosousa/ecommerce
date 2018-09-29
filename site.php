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

//Página de Categorias
$app->get('/categories/:idcategory', function ($idcategory) use ($app) {
    //Verifica se há uma pagina no GET, se não houver, começa na primeira
    $pageNum = (isset($_GET['page'])) ? (int)$_GET['page'] : 1;

    //Instancia e inicializa os produtos por pagina
    $category = new Category();
    $category->get((int)$idcategory);

    //Calcula e Divide o numero de produtos por pagina
    $pagination = $category->getProductsPage($pageNum);

    //Inicializa um array de Pages Vazio
    $pages = [];
    for($i = 1; $i <= $pagination['pages']; $i++) {
        array_push($pages, [
            'link' => '/categories/'.$category->getidcategory().'?page='.$i,
            'page' => $i
        ]);
    }
    $page = new Page();
    $page->setTpl("category" , array(
            "category" => $category->getValues(),
            "products" => $pagination['data'],
            "pages"    => $pages)
    );
});

?>