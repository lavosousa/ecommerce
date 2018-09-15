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
				$this->values[$fieldName];   // quando for get ele pega
			break;

			case "set":
				$this->values[$fieldName] = $args[0];   // quando ser ele atribui
			break;

		}

	}

	public function setData($data = array())
	{

		foreach ($data as $key => $value) {

				$this->{"set".$key}($value);

		}

	}

	public function getValues()
	{

		return $this->values;
		
	}

}




?>