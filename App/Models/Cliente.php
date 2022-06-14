<?php 

	namespace App\Models;

	use MF\Model\Model;

	class Cliente extends Model{

		private $cpf;
		private $nome;
        private $telefone;
		private $email;
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

		private function verificaCpfEmailDuplicados(){

			$query = "SELECT nome FROM clientes WHERE email = :email OR cpf = :cpf;";

			$stmt = $this->db->prepare($query);
			
			$stmt->bindValue(':email', $this->__get('email'));
			$stmt->bindValue(':cpf', $this->__get('cpf'));		

			$stmt->execute();	

			return $stmt->fetch(\PDO::FETCH_ASSOC);
		}

		public function salvar(){			

			$query = "INSERT INTO clientes(cpf, nome, telefone, email, senha)VALUES(:cpf, :nome, :telefone, :email, :senha);";

			$stmt = $this->db->prepare($query);

			$stmt->bindValue(':cpf', $this->__get('cpf'));
			$stmt->bindValue(':nome', $this->__get('nome'));
			$stmt->bindValue(':telefone', $this->__get('telefone'));
			$stmt->bindValue(':email', $this->__get('email'));
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

			if(strlen($this->__get('email')) < 8){

				$valido = false;
			}

			if(strlen($this->__get('senha')) < 4 || strlen($this->__get('senha')) > 8){

				$valido = false;
			}

			if(!empty($this->verificaCpfEmailDuplicados())){

				$valido = false;
			}

			return $valido;
		}

		public function autenticar(){

			$query = "SELECT cpf, nome, email FROM clientes WHERE email = :email AND senha = :senha;";

			$stmt = $this->db->prepare($query);
			
			$stmt->bindValue(':email', $this->__get('email'));	
			$stmt->bindValue(':senha', $this->__get('senha'));	

			$stmt->execute();

			$cliente = $stmt->fetch(\PDO::FETCH_ASSOC);

			if(!empty($cliente['cpf'])){

				$this->__set('cpf', $cliente['cpf']);
				$this->__set('nome', $cliente['nome']);	
			}

			return $this;				
		}
		
		public function recuperarPetsCli(){
			
			$query = "SELECT id_pet, nome FROM pets WHERE cpf_dono = :cpf;";

			$stmt = $this->db->prepare($query);
			
			$stmt->bindValue(':cpf', $this->__get('cpf'));		

			$stmt->execute();

			return $stmt->fetchAll(\PDO::FETCH_ASSOC);
		}

		public function recuperar_clientes($filtro){

			if($filtro != ''){

				$query = "SELECT 
				clientes.cpf, clientes.nome AS nome_cliente, clientes.telefone, clientes.email, pets.nome AS nome_pet
				FROM clientes 
				LEFT JOIN pets ON (clientes.cpf = pets.cpf_dono)
				WHERE clientes.nome like :nome 
				ORDER BY clientes.cpf;";
				
				$stmt = $this->db->prepare($query);
				
				$stmt->bindValue(':nome', '%'. $filtro . '%');	

				$stmt->execute();
				
				$clientes = $stmt->fetchAll(\PDO::FETCH_ASSOC);								

			}else{
				
				$query = "SELECT 
				clientes.cpf, clientes.nome AS nome_cliente, clientes.telefone, clientes.email, pets.nome AS nome_pet
				FROM clientes 
				LEFT JOIN pets ON (clientes.cpf = pets.cpf_dono)
				ORDER BY clientes.cpf;";
				
				$stmt = $this->db->prepare($query);		
				
				$stmt->execute();
				
				$clientes = $stmt->fetchAll(\PDO::FETCH_ASSOC);	
			}

			foreach ($clientes as $id => $cliente){
					
				if(isset($clientes[$id + 1]) && $clientes[$id]['cpf'] == $clientes[$id + 1]['cpf']){						
					
					$clientes[$id + 1]['nome_pet'] .= ', ' . $clientes[$id]['nome_pet'];						
				}
			}	

			foreach($clientes as $id => $cliente){

				if(!empty($clientes[$id + 1]['cpf']) && $clientes[$id]['cpf'] == $clientes[$id + 1]['cpf']){						
					
					unset($clientes[$id]);
				}
			}
			
			$clientes = array_values($clientes);

			$nome_cliente = array();

			foreach ($clientes as $key => $cliente){

				$nome_cliente[$key] = $cliente['nome_cliente'];				
			}			

			array_multisort($nome_cliente, SORT_ASC, $clientes);
			
			return $clientes;
		}

		public function verifica_consultas_marcadas(){

			$query = "SELECT consultas.data, consultas.horario, pets.nome AS nome_pet FROM consultas
			INNER JOIN pets ON (consultas.id_pet = pets.id_pet)
			INNER JOIN clientes ON (pets.cpf_dono = clientes.cpf)
			WHERE clientes.cpf = :cpf 
			AND consultas.data >= CURDATE()
			ORDER BY consultas.data, STR_TO_DATE(consultas.horario, '%H:%i');";

			$stmt = $this->db->prepare($query);
			
			$stmt->bindValue(':cpf', $this->__get('cpf'));		

			$stmt->execute();

			return $stmt->fetchAll(\PDO::FETCH_ASSOC);
		}
	}

?>