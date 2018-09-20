<?php

namespace Hcode\Model;

use \Hcode\DB\Sql;
use \Hcode\Model;
use \Hcode\Mailer;

class Product extends Model {

	public static function listAll()
	{

		$sql = new Sql();
		return $sql->select("SELECT * FROM tb_products ORDER BY desproduct");

	}

	public function save()
	{

		$sql = new Sql();
	
		$results = $sql->select("CALL sp_products_save(:idproduct, :desproduct, :vlprice, :vlwidth, :vlheight, :vllength, :vlweight, :desurl)", array(
			":idproduct"=>$this->getidproduct(),
			":desproduct"=>$this->getdesproduct(),
			":vlprice"=>$this->getvlprice(),
			":vlwidth"=>$this->getvlwidth(),
			":vlheight"=>$this->getvlheight(),
			":vllength"=>$this->getvllength(),
			":vlweight"=>$this->getvlweight(),
			":desurl"=>$this->getdesurl()
		));

		$this->setData($results[0]);

	}

	public function get($idproduct)
	{

		$sql = new Sql();
	
		$results = $sql->select("SELECT * FROM tb_products WHERE idproduct = :idproduct", array(
			":idproduct"=>$idproduct
		));

		$this->setData($results[0]);

	}

	public function delete()
	{

		$sql = new Sql();
	
		$results = $sql->query("DELETE FROM tb_products WHERE idproduct = :idproduct", array(
			":idproduct"=>$this->getidproduct()
		));

	}

	public function checkPhoto()     // traz o arquivo de foto(url) ref ao numero do registro.
	{
		if (file_exists(
			$_SERVER['DOCUMENT_ROOT'] . DIRECTORY_SEPARATOR . 
			"res" . DIRECTORY_SEPARATOR . 
			"site"  . DIRECTORY_SEPARATOR . 
			'img'  . DIRECTORY_SEPARATOR . 
			'products'  . DIRECTORY_SEPARATOR .
			$this->getidproduct() . ".jpg"
			)) {

			$url = "/res/site/img/products/" . $this->getidproduct() . ".jpg";              // usa barra pq aqui para url. Acima usa o saoarator pq é para o SO.
		
		} else {

			$url = "/res/site/img/product.jpg";

		}

		return $this->setdesphoto($url);

	}


	public function getValues()   // customizado para pegar a foto
	{

		$this->checkPhoto();

		$values = parent::getValues();   // Chama o getValues padrão

		return $values;

	}

	public function setPhoto($file)   // customizado para pegar a foto
	{

		$extension = explode('.', $file['name']);   //cria um array com cujas dimensoes são os splits de stringe cortando pelos pontos

		$extension = end($extension);

		switch ($extension) {
			case 'jpg':
			case 'jpeg':
				$image = imagecreatefromjpeg($file["tmp_name"]);
				break;
			
			case 'gif':
				$image = imagecreatefromgif($file["tmp_name"]);

			case 'png':
				$image = imagecreatefrompng($file["tmp_name"]);
				break;			
		}

		$dist = $_SERVER['DOCUMENT_ROOT'] . DIRECTORY_SEPARATOR . 
			"res" . DIRECTORY_SEPARATOR . 
			"site"  . DIRECTORY_SEPARATOR . 
			'img'  . DIRECTORY_SEPARATOR . 
			'products'  . DIRECTORY_SEPARATOR .
			$this->getidproduct() . ".jpg";

		imagejpeg($image, $dist);

		imagedestroy($image);

		$this->checkPhoto();


	}



}



?>