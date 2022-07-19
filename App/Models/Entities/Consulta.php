<?php 

	namespace App\Models\Entities;	

	class Consulta{

		private $id;
		private $data;
		private $horario;       
		private $descricao;
        private $status;  		

		private $veterinario;
		private $pet;

		public function __construct($id, $data, $horario, $descricao, $status, $veterinario, $pet){
			$this->id = $id;
			$this->data = $data;
			$this->horario = $horario;
			$this->descricao = $descricao;
			$this->status = $status;
			$this->veterinario = $veterinario;
			$this->pet = $pet;
		}	
		
		public function __get($atributo){
			return $this->$atributo;
		}

		public function __set($atributo, $valor){
			$this->$atributo = $valor;
		}
	}
?>