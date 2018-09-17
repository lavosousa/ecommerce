<?php 

namespace Hcode;

class Model {

	private $values = [];

	public function __call($name, $args)    // quando get pega quando set atribui
	{

		$method = substr($name, 0, 3);
		$fieldName = substr($name, 3, strlen($name));

		switch ($method)
		{

			case "get":
				return (isset($this->values[$fieldName])) ? $this->values[$fieldName] : NULL;	
			break;

			case "set":
				$this->values[$fieldName] = $args[0];   // quando ser ele atribui			
			break;

		}

	}

	public function setData($data = array())   // setar automaticamente
	{

		//var_dump($data);

		foreach ($data as $key => $value) {

			$this->{"set".$key}($value);    // criação dinamica do set+o campo. ex: setIduser {"set".$key}  

		}

		//var_dump($this);

	}

	public function getValues()
	{

		return $this->values;
		
	}

}




?>