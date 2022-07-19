<?php 

	namespace App\Models\Entities;	

	class Aviso{

		private $id_aviso;		        
		private $descricao;
		private $status;			

		public function __construct($id_aviso, $descricao, $status){			
			$this->id_aviso = $id_aviso;
			$this->descricao = $descricao;
			$this->status = $status;			
		}	

		public function __get($atributo){
			return $this->$atributo;
		}

		public function __set($atributo, $valor){
			$this->$atributo = $valor;
		}				
	}

?>