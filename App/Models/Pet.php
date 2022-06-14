<?php 

	namespace App\Models;

	use MF\Model\Model;

	class Pet extends Model{

		private $id;
		private $nome;
        private $cpf_dono;
		private $data_nasc;
		private $raca;
        private $especie;
        private $porte;

		public function __get($atributo){

			return $this->$atributo;
		}

		public function __set($atributo, $valor){

			$this->$atributo = $valor;
		}
		
		public function salvar(){
			
			$query = "INSERT INTO pets(nome, cpf_dono, data_nasc, raca, especie, porte)VALUES(:nome, :cpf_dono, :data_nasc, :raca, :especie, :porte);";

			$stmt = $this->db->prepare($query);
			$stmt->bindValue(':nome', $this->__get('nome'));
			$stmt->bindValue(':cpf_dono', $this->__get('cpf_dono'));
			$stmt->bindValue(':data_nasc', $this->__get('data_nasc'));
			$stmt->bindValue(':raca', $this->__get('raca'));
            $stmt->bindValue(':especie', $this->__get('especie'));
			$stmt->bindValue(':porte', $this->__get('porte'));

			$stmt->execute();

			return $this;
		}

		private function verificaNomeDuplicado(){

			$query = "SELECT nome FROM pets WHERE nome = :nome AND cpf_dono = :cpf_dono;";

			$stmt = $this->db->prepare($query);
			
			$stmt->bindValue(':nome', $this->__get('nome'));
			$stmt->bindValue(':cpf_dono', $this->__get('cpf_dono'));

			$stmt->execute();	

			return $stmt->fetch(\PDO::FETCH_ASSOC);			
		}
		
		public function validarCadastro(){

			$valido = true;

			if(strlen($this->__get('nome')) < 2){

				$valido = false;
			}	
			
			if(strlen($this->__get('data_nasc')) < 10){

				$valido = false;
			}

			if(strlen($this->__get('raca')) < 3){

				$valido = false;
			}

			if(strlen($this->__get('especie')) < 3){

				$valido = false;
			}  
			
			if(empty($this->__get('porte'))){

				$valido = false;
			}

			if(!empty($this->verificaNomeDuplicado())){

				$valido = false;
			}

			return $valido;
		}	
		
		public function pesquisa_pets($pesquisa){

			$query = "SELECT 
			pets.id_pet, pets.nome AS nome_pet, pets.data_nasc, pets.raca, pets.especie, pets.porte, 
			clientes.nome AS nome_tutor 
			FROM pets 
			INNER JOIN clientes ON (pets.cpf_dono = clientes.cpf)
			WHERE pets.nome like :nome OR clientes.nome like :nome 
			ORDER BY pets.nome;";
			
			$stmt = $this->db->prepare($query);
			
			$stmt->bindValue(':nome', '%'. $pesquisa . '%');		

			$stmt->execute();

			return $stmt->fetchAll(\PDO::FETCH_ASSOC);
		}

		public function recupera_historico(){

			$query = "SELECT 			
			consultas.id_consulta, consultas.data, consultas.horario, consultas.descricao,
			veterinarios.nome AS nome_vet
			FROM consultas
			INNER JOIN veterinarios ON (consultas.crmv_vet = veterinarios.crmv)
			WHERE consultas.id_pet = :id_pet
			AND consultas.status IN ('realizada', 'realizada sem marcar')
			ORDER BY consultas.data, STR_TO_DATE(consultas.horario, '%H:%i');";

			$stmt = $this->db->prepare($query);
			
			$stmt->bindValue(':id_pet', $this->__get('id'));	

			$stmt->execute();				
			
			return $stmt->fetchAll(\PDO::FETCH_ASSOC);
		}
	}

?>