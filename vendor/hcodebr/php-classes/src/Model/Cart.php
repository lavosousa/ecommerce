<?php

namespace Hcode\Model;

use \Hcode\DB\Sql;
use \Hcode\Model;
use \Hcode\Mailer;
use \Hcode\Model\Cart;

class Cart extends Model {

	const SESSION = "Cart";
	const SESSION_ERROR = "CartError";

	public static function getFromSession()
	{

		$cart = new Cart();
		// Se a sessão já existir e o carrinho for > 0, já existe o carrinho no banco e na sessao. Então carregue.
		if(isset($_SESSION[Cart::SESSION]) && (int)$_SESSION[Cart::SESSION]['idcart'] > 0 )  {

			$cart->get((int)$_SESSION[Cart::SESSION]['idcart']);
			//echo "Carrinho ja existe";
			//var_dump($cart);
			//exit;

		} else {      //  ou então procura o cart a partir da sessão do usuário.

			echo "Carrinho nao existe";
			//exit;

			$cart->getFromSessionID();

			if (!(int)$cart->getidcart() > 0) {

				$data = [
					'dessessionid' => session_id()
				];

				if (User::checkLogin(false)) {       // false pq não ta indo pra rota de admin)

					$user = User::getFromSession();

					$data['iduser'] = $user->getiduser();
				}

				$cart->setData($data);

				$cart->save();

				$cart->setToSession();

			}
		}

        return $cart;

	}

	public function setToSession()
	{

		$_SESSION[Cart::SESSION] = $this->getValues();

	}



	public function getFromSessionID()
	{

		$sql = new Sql();

		$results = $sql->select("SELECT * FROM tb_carts WHERE dessessionid = :dessessionid", [
			':dessessionid'=>session_id()
		]);

		if (count($results) > 0) {

			$this->setData($results[0]);

		}

	}


	public function get(int $idcart)
	{

		$sql = new Sql();

		$results = $sql->select("SELECT * FROM tb_carts WHERE idcart = :idcart", [
			':idcart'=>$idcart
		]);

		if (count($results) > 0) {

			$this->setData($results[0]);

		}

	}


	public function save()
	{

		$sql = new Sql();

		$results = $sql->select("CALL sp_carts_save(:idcart, :dessessionid, :iduser, :deszipcode, :vlfreight, :nrdays)", [
			':idcart'=>$this->getidcart(),
			':dessessionid'=>$this->getdessessionid(),
			':iduser'=>$this->getiduser(),
			':deszipcode'=>$this->getdeszipcode(),
			':vlfreight'=>$this->getvlfreight(),
			':nrdays'=>$this->getnrdays()
		]);

		$this->setData($results[0]);

	}


	public function addProduct(Product $product)
	{

		$sql = new Sql();
		//var_dump($product);exit;
		//var_dump($this->getidproduct());exit;
		//var_dump($this->getidcart());exit;
		$results = $sql->query("INSERT INTO tb_cartsproducts (idcart, idproduct) VALUES (:idcart, :idproduct)", [
			':idcart'=>$this->getidcart(),
			':idproduct'=>$product->getidproduct()
		]);

		$this->getCalculateTotal();

	}

	public function removeProduct(Product $product, $all = false)
	{

		$sql = new Sql();

		if ($all) {

			$sql->query("UPDATE tb_cartsproducts SET dtremoved = NOW() WHERE idcart = :idcart AND idproduct = :idproduct AND
				dtremoved IS NULL", [
				':idcart'=>$this->getidcart(),
				':idproduct'=>$product->getidproduct()
			]);

		} else {

			$sql->query("UPDATE tb_cartsproducts SET dtremoved = NOW() WHERE idcart = :idcart AND idproduct = :idproduct AND 
				dtremoved IS NULL LIMIT 1", [
				':idcart'=>$this->getidcart(),
				':idproduct'=>$product->getidproduct()
			]);

		}

		$this->getCalculateTotal();


	}

	public function getProducts()
	{

		$sql = new Sql();

		$rows = $sql->select("
			SELECT b.idproduct, b.desproduct, b.vlprice, b.vlwidth, b.vlheight, b.vllength, b.vlweight, b.desurl, count(*) AS nrqtd, SUM(b.vlprice) AS vltotal 
			FROM tb_cartsproducts a 
			INNER JOIN tb_products b ON a.idproduct = b.idproduct 
			WHERE a.idcart = :idcart AND a.dtremoved IS NULL 
			GROUP BY b.idproduct, b.desproduct, b.vlprice, b.vlwidth, b.vlheight, b.vllength, b.vlweight, b.desurl
			ORDER BY b.desproduct
		", [
			':idcart'=>$this->getidcart()

		]);

		return Product::checkList($rows);

	}


	public function getProductsTotals()
	{

		$sql = new Sql();

		$results = $sql->select("
			SELECT SUM(vlprice) AS vlprice, SUM(vlwidth) AS vlwidth, SUM(vlheight) vlheight, SUM(vllength) AS vllength, SUM(vlweight) as vlweight, COUNT(*) AS nrqtd
			FROM tb_products a INNER JOIN tb_cartsproducts b ON a.idproduct = b.idproduct
			WHERE b.idcart = :idcart AND dtremoved IS NULL;
		", [
			':idcart'=>$this->getidcart()

		]);

		if (count($results) > 0) {
			return $results[0];	
		} else {
			return [];
		}

		

	}

	public function setFreight($nrzipcode) 
	{

		$zipcode = str_replace('-','',$nrzipcode);

		$totals = $this->getProductsTotals();

		if($totals['nrqtd'] > 0) {

			if($totals['vlheight'] < 2 ) $totals['vlheight'] = 2;
			if($totals['vllength'] < 16 ) $totals['vllength'] = 16;

			$qs = http_build_query([
				'nCdEmpresa'=>'',
				'sDsSenha'=>'',
				'nCdServico'=>'40010',      // 40010 SEDEX    40045 SEDEX a Cobrar    40215 SEDEX 10   40290 SEDEX Hoje    41106 PAC 
				'sCepOrigem'=>'09853120',
				'sCepDestino'=>$nrzipcode,
				'nVlPeso'=>$totals['vlweight'],
				'nCdFormato'=>'1',
				'nVlComprimento'=>$totals['vllength'],
				'nVlAltura'=>$totals['vlheight'],
				'nVlLargura'=>$totals['vlwidth'],
				'nVlDiametro'=>'0',
				'sCdMaoPropria'=>'S',
				'nVlValorDeclarado'=>$totals['vlprice'],
				'sCdAvisoRecebimento'=>'S'
			]);

			$xml = simplexml_load_file("http://ws.correios.com.br/calculador/CalcPrecoPrazo.asmx/CalcPrecoPrazo?".$qs);

			//$xml = (array)simplexml_load_file("http://ws.correios.com.br/calculador/CalcPrecoPrazo.asmx/CalcPrecoPrazo?".$qs);
			//echo json_encode($xml);
			//exit;

			$result = $xml->Servicos->cServico;   // Acessa o resultado

			if ($result->MsgErro != '') {

				Cart::setMsgError($result->MsgErro);			

			} else {

				Cart::clearMsgError();

			}

			$this->setnrdays($result->PrazoEntrega);
			$this->setvlfreight(Cart::formatValueToDecimal($result->Valor));
			$this->setdeszipcode($nrzipcode);

			$this->save();

			return $result;

		} else {

		}

	}

	public static function formatValueToDecimal($value):float
	{

		$value = str_replace('.','',$value);
		return str_replace(',','.',$value);	

	}

	public static function setMsgError()
	{

		$_SESSION[Cart::SESSION_ERROR] = $msg;

	}

	public static function getMsgError()
	{

		$msg = (isset($_SESSION[Cart::SESSION_ERROR])) ? $_SESSION[Cart::SESSION_ERROR] : "";

		Cart::clearMsgError();

		return $msg;

	}

	public static function clearMsgError()
	{

		$_SESSION[Cart::SESSION_ERROR] = NULL;
		
	}

	public function updateFreight()	
	{

		if ($this->getdeszipcode() != '') {

			$this->setFreight($this->getdeszipcode());

		}

	}

	public function getValues()
	{

		$this->getCalculateTotal();

		return parent::getValues();

	}

	public function getCalculateTotal()
	{

		$this->updateFreight();
		
		$totals = $this->getProductsTotals();

		$this->setvlsubtotal($totals['vlprice']);
		$this->setvltotal($totals['vlprice'] + $this->getvlfreight());

	}

}

?>