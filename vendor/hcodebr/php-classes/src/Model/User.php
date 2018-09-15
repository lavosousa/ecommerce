<?php

namespace Hcode\Model;

use \Hcode\DB\Sql;
use \Hcode\Model;

class User extends Model {

	const SESSION = "User";

	public static function login($login, $password) 
	{

		$sql = new Sql();

		$results = $sql->select("SELECT * FROM tb_users WHERE deslogin = :LOGIN", array(
			":LOGIN" => $login
		));

		if (count($results) === 0)
		{
			throw new \Exception("Usuário inexistente ou senha inválida.");
		}

		$data = $results[0];

		if (password_verify($password, $data["despassword"]) === true)
		{

			$user = new User();

			//$user->setiduser($data["iduser"]);
			$user->setData($data);
			$_SESSION[User::SESSION] = $user->getValues();

			return $user;

		} else {
			throw new \Exception("Usuário inexistente ou senha inválida.", 1);			
		}

	}

	public static function verifyLogin($inadmin = true)
	{

		if (
			!isset($_SESSION[User::SESSION])    // se a sessão nao existir ou...
			||
			!$_SESSION[User::SESSION]           // se for falsa(vazia) ou...
			||
			!(int)$_SESSION[User::SESSION]["iduser"] > 0   // se não for maior que 0 (invalida na conversao int)
			||
			(bool)$_SESSION[User::SESSION]["inadmin"] !== $inadmin  // se pode acessar a admin.
		) {
			// se tudo algum acima ... 
			header("Location: /admin/login");
			exit;   // para naofazer mais nada

		}


	}


	public static function logout()	
	{

		$_SESSION[User::SESSION] = NULL;

	}

}



?>