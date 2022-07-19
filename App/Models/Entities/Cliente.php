<?php 

	namespace App\Models\Entities;	

	use App\Models\Entities\Usuario;

	class Cliente extends Usuario{		

		public function __construct($cpf, $nome, $telefone, $email, $senha){
			parent::__construct($cpf, $nome, $telefone, $email, $senha);
		}	

		public function __get($atributo){
			return $this->$atributo;
		}

		public function __set($atributo, $valor){
			$this->$atributo = $valor;
		}				
	}

?>