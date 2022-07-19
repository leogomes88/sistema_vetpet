<?php 

	namespace App\Models\Entities;	

	class Pet{

		private $id;
		private $nome;        
		private $data_nasc;
		private $raca;
        private $especie;
        private $porte;

		private $cliente;

		public function __construct($id, $nome, $data_nasc, $raca, $especie, $porte, $cliente){
			$this->id = $id;
			$this->nome = $nome;
			$this->data_nasc = $data_nasc;
			$this->raca = $raca;
			$this->especie = $especie;
			$this->porte = $porte;
			$this->cliente = $cliente;
		}	
		
		public function __get($atributo){
			return $this->$atributo;
		}

		public function __set($atributo, $valor){
			$this->$atributo = $valor;
		}
	}

?>