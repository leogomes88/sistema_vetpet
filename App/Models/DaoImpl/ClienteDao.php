<?php 

	namespace App\Models\DaoImpl;	
	
	use MF\Model\ModelDao;
	use App\Models\Dao\InterfaceClienteDao;

	class ClienteDao extends ModelDao implements InterfaceClienteDao{			

		private function verificarCpfEmailDuplicados($cliente){

			$query = "SELECT nome FROM clientes WHERE email = :email OR cpf = :cpf;";

			$stmt = $this->db->prepare($query);			
			$stmt->bindValue(':email', $cliente->__get('email'));
			$stmt->bindValue(':cpf', $cliente->__get('cpf'));
			$stmt->execute();	

			return $stmt->fetch(\PDO::FETCH_ASSOC);
		}

		public function salvar($cliente){			

			$query = "INSERT INTO clientes(cpf, nome, telefone, email, senha)VALUES(:cpf, :nome, :telefone, :email, :senha);";

			$stmt = $this->db->prepare($query);
			$stmt->bindValue(':cpf', $cliente->__get('cpf'));
			$stmt->bindValue(':nome', $cliente->__get('nome'));
			$stmt->bindValue(':telefone', $cliente->__get('telefone'));
			$stmt->bindValue(':email', $cliente->__get('email'));
			$stmt->bindValue(':senha', md5($cliente->__get('senha')));
			$stmt->execute();			
		}
		
		public function validarCadastro($cliente){

			$valido = true;

			if(strlen($cliente->__get('nome')) < 7){
				$valido = false;
			}

			if(!$cliente->validarCPF()){
				$valido = false;
			}

			if(strlen($cliente->__get('telefone')) < 10){
				$valido = false;
			}

			if(strlen($cliente->__get('email')) < 8){
				$valido = false;
			}

			if(strlen($cliente->__get('senha')) < 4 || strlen($cliente->__get('senha')) > 8){
				$valido = false;
			}

			if(!empty($this->verificarCpfEmailDuplicados($cliente))){
				$valido = false;
			}

			return $valido;
		}

		public function autenticar($cliente){

			$query = "SELECT cpf, nome, telefone FROM clientes WHERE email = :email AND senha = :senha;";

			$stmt = $this->db->prepare($query);			
			$stmt->bindValue(':email', $cliente->__get('email'));	
			$stmt->bindValue(':senha', $cliente->__get('senha'));	
			$stmt->execute();

			$resposta_dados_cliente = $stmt->fetch(\PDO::FETCH_ASSOC);

			if(!empty($resposta_dados_cliente['cpf'])){
				$cliente->__set('cpf', $resposta_dados_cliente['cpf']);
				$cliente->__set('nome', $resposta_dados_cliente['nome']);	
				$cliente->__set('telefone', $resposta_dados_cliente['telefone']);	
			}							
		}		

		public function recuperarClientes($filtro){

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

		public function verificarConsultasMarcadas($cpf){

			$query = "SELECT consultas.data, consultas.horario, pets.nome AS nome_pet FROM consultas
			INNER JOIN pets ON (consultas.id_pet = pets.id_pet)
			INNER JOIN clientes ON (pets.cpf_dono = clientes.cpf)
			WHERE clientes.cpf = :cpf 
			AND consultas.data >= CURDATE()
			ORDER BY consultas.data, STR_TO_DATE(consultas.horario, '%H:%i');";

			$stmt = $this->db->prepare($query);			
			$stmt->bindValue(':cpf', $cpf);	
			$stmt->execute();

			return $stmt->fetchAll(\PDO::FETCH_ASSOC);
		}
	}

?>