<?php 

	namespace App\Models\Entities;	

	abstract class Usuario {

		protected $cpf;
		protected $nome;
        protected $telefone;
		protected $email;
		protected $senha;

		public function __construct($cpf, $nome, $telefone, $email, $senha){
			$this->cpf = $cpf;
			$this->nome = $nome;
			$this->telefone = $telefone;
			$this->email = $email;
			$this->senha = $senha;
		}	
		
		public function __get($atributo){
			return $this->$atributo;
		}

		public function __set($atributo, $valor){
			$this->$atributo = $valor;
		}

		public function validarCPF() {			
			
			if ($this->cpf == '00000000000' || 
				$this->cpf == '11111111111' || 
				$this->cpf == '22222222222' || 
				$this->cpf == '33333333333' || 
				$this->cpf == '44444444444' || 
				$this->cpf == '55555555555' || 
				$this->cpf == '66666666666' || 
				$this->cpf == '77777777777' || 
				$this->cpf == '88888888888' || 
				$this->cpf == '99999999999') {

				return false;

			} else {  					
				for ($t = 9; $t < 11; $t++) {					
					for ($d = 0, $c = 0; $c < $t; $c++) {
						$d += $this->cpf[$c] * (($t + 1) - $c);
					}
					
					$d = ((10 * $d) % 11) % 10;

					if ($this->cpf[$c] != $d) {
						return false;
					}
				}
		
				return true;
			}
		}					
	}

?>