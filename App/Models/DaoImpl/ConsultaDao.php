<?php 

	namespace App\Models\DaoImpl;	

	use MF\Model\ModelDao;
	use App\Models\Dao\InterfaceConsultaDao;
	use MF\Model\Container;	

	class ConsultaDao extends ModelDao implements InterfaceConsultaDao{
		 		
		private $horarios_disponiveis = array('8:00', '9:00', '10:00', '11:00', '13:00', '14:00', '15:00', '16:00');			

		//AGENDAMENTO DE CONSULTAS 

		public function verificarDatasHorariosDisponiveis($data, $lista_vets){

			$num_vets = count($lista_vets);	
			$horarios_disponiveis = $this->horarios_disponiveis;			

			$query = "SELECT horario FROM consultas WHERE data = :data;";

			$stmt = $this->db->prepare($query);			
			$stmt->bindValue(':data', $data);
			$stmt->execute();				
			
			$horarios_assoc = $stmt->fetchAll(\PDO::FETCH_ASSOC);
						
			$horarios = [];

			foreach($horarios_assoc as $horario_assoc){
				$horarios[] = $horario_assoc['horario'];
			}
			
			$horarios_numeroOcorrencias = array_count_values($horarios);

			foreach($horarios_numeroOcorrencias as $horario => $numero_ocorrencias){				
				if($numero_ocorrencias == $num_vets){
					$horario_ocupado = array($horario);
					$horarios_disponiveis = array_diff($horarios_disponiveis, $horario_ocupado);
				}				
			}			
			
			if(strtotime($data) == strtotime(date('Y-m-d'))){
				foreach ($horarios_disponiveis as $id => $horario_disponivel){					
					if(strtotime($horario_disponivel) < strtotime(date('H:i'))){
						unset($horarios_disponiveis[$id]);
					}
				}
			}			
			
			return array_values($horarios_disponiveis);		
		}

		public function verificarVeterinariosDisponiveis($data, $horario, $lista_vets){

			$query = "SELECT crmv_vet FROM consultas WHERE data = :data AND horario = :horario;";

			$stmt = $this->db->prepare($query);			
			$stmt->bindValue(':data', $data);
			$stmt->bindValue(':horario', $horario);	
			$stmt->execute();				
			
			$vets_indisponiveis = $stmt->fetchAll(\PDO::FETCH_ASSOC);			

			foreach($lista_vets as $id => $vet){
				foreach($vets_indisponiveis as $vet_indisponivel){
					if($vet['crmv'] == $vet_indisponivel['crmv_vet']){
						unset($lista_vets[$id]);						
					}
				}								
			}	
			
			return array_values($lista_vets);		
		}

		private function verificarConsultaMesmoDia($consulta){

			$query = "SELECT id_consulta FROM consultas 
			WHERE data = :data 
			AND id_pet = :id_pet
			AND status = 'marcada';";

			$stmt = $this->db->prepare($query);			
			$stmt->bindValue(':data', $consulta->__get('data'));
			$stmt->bindValue(':id_pet', $consulta->__get('pet')->__get('id'));
			$stmt->execute();				
			
			return $stmt->fetch(\PDO::FETCH_ASSOC);
		}

		private function validarDataHorarioVet($consulta){

			$query = "SELECT id_consulta FROM consultas WHERE data = :data AND horario = :horario AND crmv_vet = :crmv_vet;";

			$stmt = $this->db->prepare($query);			
			$stmt->bindValue(':data', $consulta->__get('data'));
			$stmt->bindValue(':horario', $consulta->__get('horario'));
			$stmt->bindValue(':crmv_vet', $consulta->__get('veterinario')->__get('crmv'));
			$stmt->execute();				
			
			return $stmt->fetch(\PDO::FETCH_ASSOC);
		}
		
		public function validarCadastro($consulta){

			$valido = true;
			
			if(empty($consulta->__get('data'))){
				$valido = false;
			}

			if(empty($consulta->__get('horario'))){
				$valido = false;
			}

			if(empty($consulta->__get('veterinario')->__get('crmv'))){
				$valido = false;
			}

			if(empty($consulta->__get('pet')->__get('id'))){
				$valido = false;
			}		

			if(!empty($this->validarDataHorarioVet($consulta))){
				$valido = false;
			}

			if(!empty($this->verificarConsultaMesmoDia($consulta))){
				$valido = false;
			}

			return $valido;
		}

		public function salvar($consulta){

			$query = "INSERT INTO consultas(data, horario, crmv_vet, id_pet, status)VALUES(:data, :horario, :crmv_vet, :id_pet, 'marcada')";

			$stmt = $this->db->prepare($query);
			$stmt->bindValue(':data', $consulta->__get('data'));
			$stmt->bindValue(':horario', $consulta->__get('horario'));
			$stmt->bindValue(':crmv_vet', $consulta->__get('veterinario')->__get('crmv'));
			$stmt->bindValue(':id_pet', $consulta->__get('pet')->__get('id'));
			$stmt->execute();
		}

		//REALIZAÇÃO DE CONSULTAS		

		public function realizarConsulta($consulta){

			$resposta = true;	

			$id_consulta = $this->verificarConsultaMesmoDia($consulta);	

			if(!empty($id_consulta)){								
				
				$query = "UPDATE consultas SET crmv_vet = :crmv_vet, descricao = :descricao, status = :status WHERE id_consulta = :id_consulta;";

				$stmt = $this->db->prepare($query);				
				$stmt->bindValue(':crmv_vet', $consulta->__get('veterinario')->__get('crmv'));					
				$stmt->bindValue(':id_consulta', $id_consulta['id_consulta']);
				$stmt->bindValue(':status', $consulta->__get('status'));
				$stmt->bindValue(':descricao', $consulta->__get('descricao'));
				$stmt->execute();

			}else{
				$resposta = false;
			}	

			return $resposta;
		}

		public function realizarConsultaNaoAgendada($consulta){			

			$query = "INSERT INTO consultas(data, horario, crmv_vet, id_pet, descricao, status)VALUES(:data, :horario, :crmv_vet, :id_pet, :descricao, :status)";

			$stmt = $this->db->prepare($query);
			$stmt->bindValue(':data', $consulta->__get('data'));
			$stmt->bindValue(':horario', $consulta->__get('horario'));
			$stmt->bindValue(':crmv_vet', $consulta->__get('veterinario')->__get('crmv'));
			$stmt->bindValue(':id_pet', $consulta->__get('pet')->__get('id'));
			$stmt->bindValue(':descricao', $consulta->__get('descricao'));
			$stmt->bindValue(':status', $consulta->__get('status'));
			$stmt->execute();
		}

		//CONTROLE DE AGENDA DOS VETERINÁRIOS				

		public function recuperarConsultas($intervalo, $crmv){

			$data_inicial = date('Y-m-d');
			$intervalo_dias = $intervalo == 'dia' ? 0 : ($intervalo == 'quinzena' ? 15 : 30);			
			$data_final = date('Y-m-d', strtotime($data_inicial . "+{$intervalo_dias} days"));

			$query = "SELECT 
			consultas.id_consulta, consultas.data, consultas.horario, 
			pets.nome AS nome_pet, clientes.nome AS nome_tutor, consultas.descricao, consultas.status 
			FROM consultas 
			INNER JOIN pets ON (consultas.id_pet = pets.id_pet)
			INNER JOIN clientes ON (pets.cpf_dono = clientes.cpf) 
			WHERE consultas.data BETWEEN (:data_inicial) AND (:data_final) 
			AND consultas.crmv_vet = :crmv
			AND consultas.status NOT IN ('bloqueado', 'realizada sem marcar')
			ORDER BY data, STR_TO_DATE(horario, '%H:%i');";

			$stmt = $this->db->prepare($query);			
			$stmt->bindValue(':data_inicial', $data_inicial);
			$stmt->bindValue(':data_final', $data_final);	
			$stmt->bindValue(':crmv', $crmv);	
			$stmt->execute();				
			
			return $stmt->fetchAll(\PDO::FETCH_ASSOC);	
		}		

		public function recuperarHorariosLivres($intervalo, $crmv){			
							
			$data_inicial = date('Y-m-d');
			$intervalo_dias = $intervalo == 'dia' ? 1 : ($intervalo == 'quinzena' ? 15 : 30);
			$data_final = date('Y-m-d', strtotime($data_inicial . "+{$intervalo_dias} days"));			
			$horarios_disponiveis = $this->horarios_disponiveis;

			$query = "SELECT 
			data, horario
			FROM consultas
			WHERE data BETWEEN (:data_inicial) AND (:data_final) 
			AND consultas.crmv_vet = :crmv
			AND consultas.status != 'realizada sem marcar'
			ORDER BY data, STR_TO_DATE(horario, '%H:%i');";

			$stmt = $this->db->prepare($query);			
			$stmt->bindValue(':data_inicial', $data_inicial);
			$stmt->bindValue(':data_final', $data_final);	
			$stmt->bindValue(':crmv', $crmv);	
			$stmt->execute();				
			
			$horarios_indisponiveis = $stmt->fetchAll(\PDO::FETCH_ASSOC);								
			
			for ($i=0; $i < $intervalo_dias; $i++) { 			
				$lista_datas[] = date('Y-m-d', strtotime($data_inicial . "+{$i} days"));
			}	

			//remove os finais de semana
			if($intervalo_dias == 1){					
				if (date('w', strtotime($lista_datas[0])) == 0 || date('w', strtotime($lista_datas[0])) == 6) {					
					return $lista_horarios_livres = array();						
				}					

			}else{
				foreach($lista_datas as $id => $data){
					if (date('w', strtotime($data)) == 0 || date('w', strtotime($data)) == 6) {						
						unset($lista_datas[$id]);
					}								
				}
			}				

			foreach($lista_datas as $data){
				foreach($horarios_disponiveis as $horario_disponivel){
					$lista_horarios_livres[] = ['data' => $data, 'horario' => $horario_disponivel];						
				}								
			}					

			foreach ($lista_horarios_livres as $id => $horario_livre) {
				foreach ($horarios_indisponiveis as $horario_indisponivel) {
					
					//remove os horários indisponíveis
					if ($horario_livre['data'] == $horario_indisponivel['data'] && $horario_livre['horario'] == $horario_indisponivel['horario']) {						
						unset($lista_horarios_livres[$id]);
					}
				}					
			}				
			
			return array_values($lista_horarios_livres);
		}		

		public function recuperarHorariosBloqueados($intervalo, $crmv){
			
			$data_inicial = date('Y-m-d');
			$intervalo_dias = $intervalo == 'dia' ? 0 : ($intervalo == 'quinzena' ? 15 : 30);
			$data_final = date('Y-m-d', strtotime($data_inicial . "+{$intervalo_dias} days"));

			$query = "SELECT 
			consultas.id_consulta, consultas.data, consultas.horario
			FROM consultas
			WHERE data BETWEEN (:data_inicial) AND (:data_final) 
			AND consultas.crmv_vet = :crmv
			AND consultas.status = 'bloqueado'
			ORDER BY data, STR_TO_DATE(horario, '%H:%i');";

			$stmt = $this->db->prepare($query);			
			$stmt->bindValue(':data_inicial', $data_inicial);
			$stmt->bindValue(':data_final', $data_final);	
			$stmt->bindValue(':crmv', $crmv);		
			$stmt->execute();				
			
			return $stmt->fetchAll(\PDO::FETCH_ASSOC);					
		}

		private function auxCancelarConsultas($consulta){							
			
			$query = "SELECT 
			consultas.data, consultas.horario, clientes.cpf, consultas.crmv_vet 
			FROM consultas
			INNER JOIN pets ON (consultas.id_pet = pets.id_pet)
			INNER JOIN clientes ON (pets.cpf_dono = clientes.cpf)
			WHERE id_consulta = :id_consulta;";

			$stmt = $this->db->prepare($query);			
			$stmt->bindValue(':id_consulta', $consulta);
			$stmt->execute();				
			
			return $stmt->fetch(\PDO::FETCH_OBJ);									
		}

		public function cancelarConsultas($consultas){			
							
			foreach($consultas as $consulta){
				$dados_consulta = (array) $this->auxCancelarConsultas($consulta);				
				
				$avisoDao = Container::getModelDao('aviso');
				$avisoDao->gerarAviso($dados_consulta);				
				
				//atualização da consulta para horario bloqueado
				$query = "UPDATE consultas SET id_pet = NULL, status = 'bloqueado' WHERE id_consulta = :id_consulta;";

				$stmt = $this->db->prepare($query);				
				$stmt->bindValue(':id_consulta', $consulta);
				$stmt->execute();					
			}	
			
			return true;
		}		

		public function bloquearHorarios($datas_horarios, $crmv){									
							
			foreach($datas_horarios as $data_horario){				
					
				$query = "INSERT INTO consultas(data, horario, crmv_vet, status)VALUES(:data, :horario, :crmv_vet, 'bloqueado')";

				$stmt = $this->db->prepare($query);				
				$stmt->bindValue(':data', $data_horario['data']);
				$stmt->bindValue(':horario', $data_horario['horario']);	
				$stmt->bindValue(':crmv_vet', $crmv);
				$stmt->execute();										
			}		
			
			return true;
		}

		public function liberarHorarios($horarios_bloqueados){
							
			foreach($horarios_bloqueados as $horario_bloqueado){
									
				$query = "DELETE FROM consultas WHERE id_consulta = :id_consulta";

				$stmt = $this->db->prepare($query);				
				$stmt->bindValue(':id_consulta', $horario_bloqueado);
				$stmt->execute();								
			}	

			return true;			
		}					
	}
?>