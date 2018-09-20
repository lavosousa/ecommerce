<?php

use \Hcode\Page;

$app->get('/', function() {      //Quando chamarem o site da pasta raiz(loja)...
    
	$page = new Page();  //Chama o construct e adiciona o header

	$page->setTpl("index");  // Carrge o conteúdo 
	//O destrutor vai mostrar o footer
});


?>