<?php

namespace Hcode\Model;

use \Hcode\DB\Sql;
use \Hcode\Model;
use \Hcode\Mailer;

class User extends Model {

	const SESSION = "User";
	const SECRET = "HcodePhp7_Secret";
	const ERROR = "UserError";
	const ERROR_REGISTER = "UserErrorRegister";
	const SUCESS = "UserSucess";

	public static function getFromSession()
	{

		$user = new User();
		// Se tem sessao e está logado.
		if (isset($_SESSION[User::SESSION]) && (int)$_SESSION[User::SESSION]['iduser'] > 0) {

			$user->setData($_SESSION[User::SESSION]);
		}

		return $user;
	}


	public static function checkLogin($inadmin = true)
	{

		if (
			!isset($_SESSION[User::SESSION])    // se a sessão nao for definida, logo não ta logado...
			||
			!$_SESSION[User::SESSION]           // se for defina porém for vazia, não ta logado...
			||
			!(int)$_SESSION[User::SESSION]["iduser"] > 0   // se for definida, não for vazia porém o ID usuário bão for > que 0, também não ta logado.  
		) {
			// Atendido algo acima, quer dizer que não está logado, logo
			//echo "Sessão inválida, não logado";
			//exit;
			return false;

		} else {
			// Está logado, mas vamos ver se é um acesso para rota de admin ou usuário normal.
			//echo "Alguem logado";

			if ($inadmin === true && (bool)$_SESSION[User::SESSION]['inadmin'] === true ) {    // admin 
				//echo "Usuario Admin logado";
				//exit;
				return true;

			} else if ($inadmin === false) {   //normal
				//echo "Usuário normal logado";
				///exit;
				return true;

			} else {
				//echo "Nenhuma das opções";
				//exit;
				return false;

			}


		}
	}


	public static function login($login, $password) 
	{

		$sql = new Sql();

		$results =  $sql->select("SELECT * FROM tb_users a INNER JOIN tb_persons b ON a.idperson = b.idperson WHERE deslogin = :LOGIN", array(
				":LOGIN"=>$login
		));

		if (count($results) === 0)
		{
			throw new \Exception("Usuário inexistente ou senha inválida.");
		}

		$data = $results[0];

		if (password_verify($password, $data["despassword"]) === true)
		{

			$user = new User();

			$data["desperson"] = utf8_encode($data["desperson"]);

			$user->setData($data);
			$_SESSION[User::SESSION] = $user->getValues();

			return $user;

		} else {
			throw new \Exception("senha inválida.", 1);			
		}

	}

	public static function verifyLogin($inadmin = true)
	{

		if (!User::checkLogin($inadmin)) {

			if($inadmin) {	
				header("Location: /admin/login");
			} else {
				header("Location: /login");
			}
			exit;

		}

	}


	public static function logout()	
	{

		$_SESSION[User::SESSION] = NULL;

	}

	public static function listAll()
	{

		$sql = new Sql();
		return $sql->select("SELECT * FROM tb_users a INNER JOIN tb_persons b USING(idperson) ORDER BY b.desperson");

	}

	public function save()
	{
		$sql = new Sql();
	
		$results = $sql->select("CALL sp_users_save(:desperson, :deslogin, :despassword, :desemail, :nrphone, :inadmin)", array(
			":desperson"=>utf8_decode($this->getdesperson()),
			":deslogin"=>$this->getdeslogin(),
			":despassword"=>User::getPasswordHash($this->getdespassword()), 
			//":despassword"=>$this->getdespassword(), 
			":desemail"=>$this->getdesemail(),
			":nrphone"=>$this->getnrphone(),
			":inadmin"=>$this->getinadmin()
		));

		$this->setData($results[0]);

	}

	public function get($iduser)
	{
		$sql = new Sql();

		$results = $sql->select("SELECT * FROM tb_users a INNER JOIN tb_persons b USING(idperson) WHERE a.iduser = :iduser", array(
			"iduser"=>$iduser
		));

		$data = $results[0];

		$data['desperson'] = utf8_encode($data['desperson']);

		$this->setData($data);

	}	

	public function update()
	{
		$sql = new Sql();
	
		$results = $sql->select("CALL sp_usersupdate_save(:iduser, :desperson, :deslogin, :despassword, :desemail, :nrphone, :inadmin)", array(
			":iduser"=>$this->getiduser(),
			":desperson"=>utf8_decode($this->getdesperson()),
			":deslogin"=>$this->getdeslogin(),
			":despassword"=>User::getPasswordHash($this->getdespassword()), 			
			//":despassword"=>$this->getdespassword(), 
			":desemail"=>$this->getdesemail(),
			":nrphone"=>$this->getnrphone(),
			":inadmin"=>$this->getinadmin()
		));

		$this->setData($results[0]);

	}

	public function delete()
	{
		$sql = new Sql();
	
		$results = $sql->select("CALL sp_users_delete(:iduser)", array(
			":iduser"=>$this->getiduser()
		));

	}




	public static function getForgot($email, $inadmin = true)

	{

		$sql = new Sql();
		$results = $sql->select(
			"SELECT * 
			FROM tb_persons a 
			INNER JOIN tb_users b USING(idperson) 
			WHERE a.desemail = :email;", array(
			":email"=>$email
		));

		if (count($results) === 0 ){

			throw new \Exception("Não foi possível recuperar a senha.");
			
		} else {

			$data = $results["0"];

			$results2 = $sql->select("CALL sp_userspasswordsrecoveries_create(:iduser, :desip)", array(
					":iduser"=>$data["iduser"],
					":desip"=>$_SERVER["REMOTE_ADDR"]
			));

			if (count($results2) === 0 ) {

				throw new \Exception("Não foi possível recuperar a senha.");
				
			} else {

				$dataRecovery = $results2[0];

				$iv = random_bytes(openssl_cipher_iv_length('aes-256-cbc'));
				$code = openssl_encrypt($dataRecovery["idrecovery"], 'aes-256-cbc', User::SECRET, 0 , $iv);
				$result = base64_encode($iv.$code);

				if ($inadmin === true) {

					$link = "http://www.hcodecommerce.com.br/admin/forgot/reset?code=$result";

				} else {

					$link = "http://www.hcodecommerce.com.br/forgot/reset?code=$result";
					
				}
			
				$mailer = new Mailer($data["desemail"], $data["desperson"], "Redefinir a senha da Hcode Store", "forgot", array(
					"name"=>$data["desperson"],
					"link"=>$link
				));

				$mailer->send();    // retorna true se enviou... pode avaliar depois.TODO

				return $link;

			}

		}
	} // End function getForgot



	public static function validForgotDecrypt($result)
 	{
 		//  echo "aqui está o codigo passado na url: " . $result . "<br/>";
		$result = base64_decode($result);
		$code = mb_substr($result, openssl_cipher_iv_length('aes-256-cbc'), null, '8bit');
		$iv = mb_substr($result, 0, openssl_cipher_iv_length('aes-256-cbc'), '8bit');
		$idrecovery = openssl_decrypt($code, 'aes-256-cbc', User::SECRET, 0, $iv);
		$sql = new Sql();
		$results = $sql->select("
		    SELECT *
		    FROM tb_userspasswordsrecoveries a
		    INNER JOIN tb_users b USING(iduser)
		    INNER JOIN tb_persons c USING(idperson)
		    WHERE
		    a.idrecovery = :idrecovery
		    AND
		    a.dtrecovery IS NULL
		    AND
		    DATE_ADD(a.dtregister, INTERVAL 1 HOUR) >= NOW();
		", array(
		    ":idrecovery"=>$idrecovery
		));
		if (count($results) === 0)
		{
		    throw new \Exception("Não foi possível recuperar a senha.");
		}
		else
		{
		    return $results[0];
		}
	}


	

	public static function setForgotUsed($idrecovery)
	{
		$sql = new Sql();

		$sql->query("UPDATE tb_userspasswordsrecoveries SET dtrecovery = NOW() WHERE idrecovery = :idrecovery", array(
				":idrecovery"=>$idrecovery
		));
	}

	

	public function setPassword($password)
	{
		$sql = new Sql();

		$sql->query("UPDATE tb_users SET despassword = :password WHERE iduser = :iduser", array(
			":password"=>$password,
			":iduser"=>$this->getiduser()
		));
	}

	

	public static function setError($msg)
	{

		$_SESSION[User::ERROR] = $msg;

	}

	public static function getError()
	{

		$msg = (isset($_SESSION[User::ERROR]) && $_SESSION[User::ERROR]) ? $_SESSION[User::ERROR] : '';

		User::clearError();

		return $msg;

	}

	public static function clearError()
	{

		$_SESSION[User::ERROR] = NULL;

	}

	public static function setErrorRegister($msg)
	{

		$_SESSION[User::ERROR_REGISTER] = $msg;

	}

	public static function getErrorRegister()
	{

		$msg = (isset($_SESSION[User::ERROR_REGISTER]) && $_SESSION[User::ERROR_REGISTER]) ? $_SESSION[User::ERROR_REGISTER] : '';

		User::clearErrorRegister();

		return $msg;

	}

	public static function clearErrorRegister()
	{

		$_SESSION[User::ERROR_REGISTER] = NULL;

	}

	public static function checkLoginExist($login)
	{

		$sql = new Sql();

		$results = $sql->select("SELECT * FROM tb_users WHERE deslogin = :deslogin", [
			':deslogin'=>$login
		]);

		return (count($results) > 0);
	}

	public static function getPasswordHash($password)
	{

		return password_hash($password, PASSWORD_DEFAULT, [
			'cost' => 12
		]);

	}

	public static function setSucess($msg)
	{

		$_SESSION[User::SUCESS] = $msg;

	}

	public static function getSucess()
	{

		$msg = (isset($_SESSION[User::SUCESS]) && $_SESSION[User::SUCESS]) ? $_SESSION[User::SUCESS] : '';

		User::clearSucess();

		return $msg;

	}

	public static function clearSucess()
	{

		$_SESSION[User::SUCESS] = NULL;

	}

}



?>