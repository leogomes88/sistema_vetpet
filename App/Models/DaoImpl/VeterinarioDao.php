<?php 

	namespace App\Models\DaoImpl;	

	use MF\Model\ModelDao;
	use App\Models\Dao\InterfaceVeterinarioDao;

	class VeterinarioDao extends ModelDao implements InterfaceVeterinarioDao{						
		
		private function verificarCpfEmailCrmvDuplicados($veterinario){

			$query = "SELECT nome FROM veterinarios WHERE email = :email OR cpf = :cpf OR crmv = :crmv;";

			$stmt = $this->db->prepare($query);			
			$stmt->bindValue(':email', $veterinario->__get('email'));
			$stmt->bindValue(':cpf', $veterinario->__get('cpf'));
			$stmt->bindValue(':crmv', $veterinario->__get('crmv'));	
			$stmt->execute();
			
			return $stmt->fetchAll(\PDO::FETCH_ASSOC);
		}
		
		public function salvar($veterinario){

			$query = "INSERT INTO veterinarios(cpf, nome, telefone, endereco, email, crmv, senha)VALUES(:cpf, :nome, :telefone, :endereco, :email, :crmv, :senha);";

			$stmt = $this->db->prepare($query);
			$stmt->bindValue(':cpf', $veterinario->__get('cpf'));
			$stmt->bindValue(':nome', $veterinario->__get('nome'));
			$stmt->bindValue(':telefone', $veterinario->__get('telefone'));
			$stmt->bindValue(':endereco', $veterinario->__get('endereco'));
			$stmt->bindValue(':email', $veterinario->__get('email'));
			$stmt->bindValue(':crmv', $veterinario->__get('crmv'));
			$stmt->bindValue(':senha', md5($veterinario->__get('senha')));
			$stmt->execute();

			return $veterinario;
		}
		
		public function validarCadastro($veterinario){

			$valido = true;

			if(strlen($veterinario->__get('nome')) < 7){
				$valido = false;
			}

			if(!$veterinario->validarCPF()){
				$valido = false;
			}

			if(strlen($veterinario->__get('telefone')) < 10){
				$valido = false;
			}

			if(strlen($veterinario->__get('endereco')) < 10){
				$valido = false;
			}

			if(strlen($veterinario->__get('email')) < 8){
				$valido = false;
			}

			if(strlen($veterinario->__get('crmv')) < 4){
				$valido = false;
			}

			if(strlen($veterinario->__get('senha')) < 4 || strlen($veterinario->__get('senha')) > 8){
				$valido = false;
			}

			if(!empty($this->verificarCpfEmailCrmvDuplicados($veterinario))){
				$valido = false;
			}

			return $valido;
		}				

		public function autenticar($veterinario){

			$query = "SELECT cpf, nome, telefone, endereco, crmv FROM veterinarios WHERE email = :email AND senha = :senha;";

			$stmt = $this->db->prepare($query);			
			$stmt->bindValue(':email', $veterinario->__get('email'));	
			$stmt->bindValue(':senha', $veterinario->__get('senha'));
			$stmt->execute();

			$resposta_dados_veterinario = $stmt->fetch(\PDO::FETCH_ASSOC);

			if(!empty($resposta_dados_veterinario['cpf'])){
				$veterinario->__set('cpf', $resposta_dados_veterinario['cpf']);
				$veterinario->__set('nome', $resposta_dados_veterinario['nome']);	
				$veterinario->__set('telefone', $resposta_dados_veterinario['telefone']);	
				$veterinario->__set('endereco', $resposta_dados_veterinario['endereco']);	
				$veterinario->__set('crmv', $resposta_dados_veterinario['crmv']);	
			}					
		}	
		
		public function recuperarVets(){

			$query = "SELECT nome, telefone, email, endereco, crmv
			FROM veterinarios ORDER BY nome;";
			
			$stmt = $this->db->prepare($query);	
			$stmt->execute();
			
			return $stmt->fetchAll(\PDO::FETCH_ASSOC);	
		}				
	}

?>