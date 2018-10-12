<?php

use \Hcode\Model\User;

function formatPrice($vlprice) 
{

	return number_format($vlprice, 2, ",", ".");

}

function checkLogin($inadmin = true)
{

	return User::checkLogin($inadmin);   // manda pra classe User::CheckLogin

}

function getUserName()
{

	$user = User::getFromSession();

	return $user->getdesperson();

}

?>