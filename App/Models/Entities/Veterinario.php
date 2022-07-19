<?php 

	namespace App\Models\Entities;		

	use App\Models\Entities\Usuario;

	class Veterinario extends Usuario{
		
		private $endereco;		
		private $crmv;		

		public function __construct($cpf, $nome, $telefone, $email, $senha, $endereco, $crmv){
			parent::__construct($cpf, $nome, $telefone, $email, $senha);
			$this->endereco = $endereco;			
			$this->crmv = $crmv;
		}	
		
		public function __get($atributo){
			return $this->$atributo;
		}

		public function __set($atributo, $valor){
			$this->$atributo = $valor;
		}
	}

?>