<?php 

	namespace App\Models;

	use MF\Model\Model;

	class veterinario extends Model{

		private $cpf;
		private $nome;
        private $telefone;
		private $endereco;
		private $email;
		private $crmv;
		private $senha;

		public function __get($atributo){

			return $this->$atributo;
		}

		public function __set($atributo, $valor){

			$this->$atributo = $valor;
		}

		private static function validaCPF($cpf = null) {
			
			if ($cpf == '00000000000' || 
				$cpf == '11111111111' || 
				$cpf == '22222222222' || 
				$cpf == '33333333333' || 
				$cpf == '44444444444' || 
				$cpf == '55555555555' || 
				$cpf == '66666666666' || 
				$cpf == '77777777777' || 
				$cpf == '88888888888' || 
				$cpf == '99999999999') {

				return false;

			} else {   
				
				//calcula os digitos verificadores para verificar se o CPF é válido
				for ($t = 9; $t < 11; $t++) {
					
					for ($d = 0, $c = 0; $c < $t; $c++) {

						$d += $cpf[$c] * (($t + 1) - $c);

					}
					
					$d = ((10 * $d) % 11) % 10;

					if ($cpf[$c] != $d) {

						return false;
					}
				}
		
				return true;
			}
		}	
		
		private function verificaCpfEmailCrmvDuplicados(){

			$query = "SELECT nome FROM veterinarios WHERE email = :email OR cpf = :cpf OR crmv = :crmv;";

			$stmt = $this->db->prepare($query);
			
			$stmt->bindValue(':email', $this->__get('email'));
			$stmt->bindValue(':cpf', $this->__get('cpf'));
			$stmt->bindValue(':crmv', $this->__get('crmv'));		

			$stmt->execute();
			
			return $stmt->fetchAll(\PDO::FETCH_ASSOC);
		}
		
		public function salvar(){

			$query = "INSERT INTO veterinarios(cpf, nome, telefone, endereco, email, crmv, senha)VALUES(:cpf, :nome, :telefone, :endereco, :email, :crmv, :senha);";

			$stmt = $this->db->prepare($query);
			$stmt->bindValue(':cpf', $this->__get('cpf'));
			$stmt->bindValue(':nome', $this->__get('nome'));
			$stmt->bindValue(':telefone', $this->__get('telefone'));
			$stmt->bindValue(':endereco', $this->__get('endereco'));
			$stmt->bindValue(':email', $this->__get('email'));
			$stmt->bindValue(':crmv', $this->__get('crmv'));
			$stmt->bindValue(':senha', md5($this->__get('senha')));

			$stmt->execute();

			return $this;
		}
		
		public function validarCadastro(){

			$valido = true;

			if(strlen($this->__get('nome')) < 7){

				$valido = false;
			}

			if(!$this->validaCPF($this->__get('cpf'))){

				$valido = false;
			}

			if(strlen($this->__get('telefone')) < 10){

				$valido = false;
			}

			if(strlen($this->__get('endereco')) < 10){

				$valido = false;
			}

			if(strlen($this->__get('email')) < 8){

				$valido = false;
			}

			if(strlen($this->__get('crmv')) < 4){

				$valido = false;
			}

			if(strlen($this->__get('senha')) < 4 || strlen($this->__get('senha')) > 8){

				$valido = false;
			}

			if(!empty($this->verificaCpfEmailCrmvDuplicados())){

				$valido = false;
			}

			return $valido;
		}				

		public function autenticar(){

			$query = "SELECT cpf, nome, email FROM veterinarios WHERE email = :email AND senha = :senha;";

			$stmt = $this->db->prepare($query);
			
			$stmt->bindValue(':email', $this->__get('email'));	
			$stmt->bindValue(':senha', $this->__get('senha'));	

			$stmt->execute();

			$veterinario = $stmt->fetch(\PDO::FETCH_ASSOC);

			if(!empty($veterinario['cpf'])){

				$this->__set('cpf', $veterinario['cpf']);
				$this->__set('nome', $veterinario['nome']);	
			}

			return $this;			
		}	
		
		public function recuperar_vets(){

			$query = "SELECT 
			veterinarios.nome, veterinarios.telefone, veterinarios.email, veterinarios.endereco, veterinarios.crmv
			FROM veterinarios 
			ORDER BY veterinarios.nome;";
			
			$stmt = $this->db->prepare($query);	

			$stmt->execute();
			
			return $stmt->fetchAll(\PDO::FETCH_ASSOC);	
		}
	}

?>