<?php 

	namespace App\Models\DaoImpl;	

	use MF\Model\ModelDao;
	use App\Models\Dao\InterfacePetDao;

	class PetDao extends ModelDao implements InterfacePetDao{		
		
		public function salvar($pet){
			
			$query = "INSERT INTO pets(nome, cpf_dono, data_nasc, raca, especie, porte)VALUES(:nome, :cpf_dono, :data_nasc, :raca, :especie, :porte);";

			$stmt = $this->db->prepare($query);
			$stmt->bindValue(':nome', $pet->__get('nome'));
			$stmt->bindValue(':cpf_dono', $pet->__get('cliente')->__get('cpf'));
			$stmt->bindValue(':data_nasc', $pet->__get('data_nasc'));
			$stmt->bindValue(':raca', $pet->__get('raca'));
            $stmt->bindValue(':especie', $pet->__get('especie'));
			$stmt->bindValue(':porte', $pet->__get('porte'));
			$stmt->execute();
		}		

		private function verificarNomeDuplicado($pet){

			$query = "SELECT nome FROM pets WHERE nome = :nome AND cpf_dono = :cpf_dono;";

			$stmt = $this->db->prepare($query);			
			$stmt->bindValue(':nome', $pet->__get('nome'));
			$stmt->bindValue(':cpf_dono', $pet->__get('cliente')->__get('cpf'));
			$stmt->execute();	

			return $stmt->fetch(\PDO::FETCH_ASSOC);			
		}
		
		public function validarCadastro($pet){

			$valido = true;

			if(strlen($pet->__get('nome')) < 2){
				$valido = false;
			}	
			
			if(strlen($pet->__get('data_nasc')) < 10){
				$valido = false;
			}

			if(strlen($pet->__get('raca')) < 3){
				$valido = false;
			}

			if(strlen($pet->__get('especie')) < 3){
				$valido = false;
			}  
			
			if(empty($pet->__get('porte'))){
				$valido = false;
			}

			if(!empty($this->verificarNomeDuplicado($pet))){
				$valido = false;
			}

			return $valido;
		}	

		public function recuperarPetsCli($cpf){
			
			$query = "SELECT id_pet, nome FROM pets WHERE cpf_dono = :cpf;";

			$stmt = $this->db->prepare($query);			
			$stmt->bindValue(':cpf', $cpf);		
			$stmt->execute();

			return $stmt->fetchAll(\PDO::FETCH_ASSOC);
		}
		
		public function pesquisarPets($filtro){

			$query = "SELECT 
			pets.id_pet, pets.nome AS nome_pet, pets.data_nasc, pets.raca, pets.especie, pets.porte, 
			clientes.nome AS nome_tutor 
			FROM pets 
			INNER JOIN clientes ON (pets.cpf_dono = clientes.cpf)
			WHERE pets.nome like :nome OR clientes.nome like :nome 
			ORDER BY pets.nome;";
			
			$stmt = $this->db->prepare($query);			
			$stmt->bindValue(':nome', '%'. $filtro . '%');	
			$stmt->execute();

			return $stmt->fetchAll(\PDO::FETCH_ASSOC);
		}

		public function recuperarHistorico($id_pet){

			$query = "SELECT 			
			consultas.id_consulta, consultas.data, consultas.horario, consultas.descricao,
			veterinarios.nome AS nome_vet
			FROM consultas
			INNER JOIN veterinarios ON (consultas.crmv_vet = veterinarios.crmv)
			WHERE consultas.id_pet = :id_pet
			AND consultas.status IN ('realizada', 'realizada sem marcar')
			ORDER BY consultas.data, STR_TO_DATE(consultas.horario, '%H:%i');";

			$stmt = $this->db->prepare($query);			
			$stmt->bindValue(':id_pet', $id_pet);	
			$stmt->execute();				
			
			return $stmt->fetchAll(\PDO::FETCH_ASSOC);
		}
	}

?>