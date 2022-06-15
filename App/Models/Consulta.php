<?php 

	namespace App\Models;

	use MF\Model\Model;
	use MF\Model\Container;

	class Consulta extends Model{

		private $id;
		private $data;
		private $horario;
        private $crmv_vet;
		private $id_pet;
		private $descricao;
        private $status;  		
		private $horarios_disponiveis = array('8:00', '9:00', '10:00', '11:00', '13:00', '14:00', '15:00', '16:00');

		public function __get($atributo){

			return $this->$atributo;
		}

		public function __set($atributo, $valor){

			$this->$atributo = $valor;
		}

		//AGENDAMENTO DE CONSULTAS 

		public function verificar_datas_horarios_disponiveis(){

			$num_vets = count(Veterinario::getCrmvVet());			

			$horarios_disponiveis = $this->horarios_disponiveis;			

			$query = "SELECT horario FROM consultas WHERE data = :data;";

			$stmt = $this->db->prepare($query);
			
			$stmt->bindValue(':data', $this->__get('data'));		

			$stmt->execute();				
			
			$horarios_assoc = $stmt->fetchAll(\PDO::FETCH_ASSOC);

			//array auxiliar para posteriormente utilizar o comando 'array_count_values()'			
			$horarios = [];

			foreach($horarios_assoc as $horario_assoc){				

				$horarios[] = $horario_assoc['horario'];
			}

			//array que guarda os horários e os respectivos números de consultas marcadas neles
			$horarios_numeroOcorrencias = array_count_values($horarios);

			foreach($horarios_numeroOcorrencias as $horario => $numero_ocorrencias){
				
				//se o número de consultas marcadas no horário for igual ao número de veterinários,
				//retira o horário do array
				if($numero_ocorrencias == $num_vets){

					$horario_ocupado = array($horario);

					$horarios_disponiveis = array_diff($horarios_disponiveis, $horario_ocupado);
				}				
			}			
			
			//se a data escolhida for a data atual, retira os horários já passados do array
			if(strtotime($this->__get('data')) == strtotime(date('Y-m-d'))){

				foreach ($horarios_disponiveis as $id => $horario_disponivel){
					
					if(strtotime($horario_disponivel) < strtotime(date('H:i'))){

						unset($horarios_disponiveis[$id]);
					}
				}
			}			
			
			return array_values($horarios_disponiveis);		
		}

		public function verificar_veterinarios_disponiveis(){

			$lista_vets = Veterinario::getCrmvVet();								

			$query = "SELECT crmv_vet FROM consultas WHERE data = :data AND horario = :horario;";

			$stmt = $this->db->prepare($query);
			
			$stmt->bindValue(':data', $this->__get('data'));
			$stmt->bindValue(':horario', $this->__get('horario'));		

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

		private function verifica_consulta_mesmo_dia(){

			$query = "SELECT 
			id_consulta 
			FROM consultas 
			WHERE data = :data 
			AND id_pet = :id_pet
			AND status = 'marcada';";

			$stmt = $this->db->prepare($query);
			
			$stmt->bindValue(':data', $this->__get('data'));
			$stmt->bindValue(':id_pet', $this->__get('id_pet'));

			$stmt->execute();				
			
			return $stmt->fetch(\PDO::FETCH_ASSOC);
		}

		private function validaDataHorarioVet(){

			$query = "SELECT 
			id_consulta 
			FROM consultas 
			WHERE data = :data 
			AND horario = :horario
			AND crmv_vet = :crmv_vet;";

			$stmt = $this->db->prepare($query);
			
			$stmt->bindValue(':data', $this->__get('data'));
			$stmt->bindValue(':horario', $this->__get('horario'));
			$stmt->bindValue(':crmv_vet', $this->__get('crmv_vet'));

			$stmt->execute();				
			
			return $stmt->fetch(\PDO::FETCH_ASSOC);
		}
		
		public function validarCadastro(){

			$valido = true;
			
			if(empty($this->__get('data'))){

				$valido = false;
			}

			if(empty($this->__get('horario'))){

				$valido = false;
			}

			if(empty($this->__get('crmv_vet'))){

				$valido = false;
			}

			if(empty($this->__get('id_pet'))){

				$valido = false;
			}		

			if(!empty($this->validaDataHorarioVet())){

				$valido = false;
			}

			if(!empty($this->verifica_consulta_mesmo_dia())){

				$valido = false;
			}

			return $valido;
		}

		public function salvar(){

			$query = "INSERT INTO consultas(data, horario, crmv_vet, id_pet, status)VALUES(:data, :horario, :crmv_vet, :id_pet, 'marcada')";

			$stmt = $this->db->prepare($query);
			$stmt->bindValue(':data', $this->__get('data'));
			$stmt->bindValue(':horario', $this->__get('horario'));
			$stmt->bindValue(':crmv_vet', $this->__get('crmv_vet'));
			$stmt->bindValue(':id_pet', $this->__get('id_pet'));

			$stmt->execute();

			return $this;
		}

		//REALIZAÇÃO DE CONSULTAS

		public function realizar_consulta($cpf_vet){

			$resposta = true;			

			if(!empty($this->verifica_consulta_mesmo_dia())){

				$id_consulta = (array) $this->verifica_consulta_mesmo_dia();

				$crmv_vet = (array) Veterinario::getCrmvPorCpf($cpf_vet);				
				
				$query = "UPDATE consultas SET crmv_vet = :crmv_vet, descricao = :descricao, status = 'realizada' WHERE id_consulta = :id_consulta;";

				$stmt = $this->db->prepare($query);
				
				$stmt->bindValue(':crmv_vet', $crmv_vet['crmv']);					
				$stmt->bindValue(':id_consulta', $id_consulta['id_consulta']);
				$stmt->bindValue(':descricao', $this->__get('descricao'));

				$stmt->execute();

			}else{

				$resposta = false;
			}	

			return $resposta;
		}

		public function realizar_consulta_nao_agendada($cpf_vet){

			$crmv_vet = (array) Veterinario::getCrmvPorCpf($cpf_vet);			

			$query = "INSERT INTO consultas(data, horario, crmv_vet, id_pet, descricao, status)VALUES(:data, :horario, :crmv_vet, :id_pet, :descricao, 'realizada sem marcar')";

			$stmt = $this->db->prepare($query);
			$stmt->bindValue(':data', $this->__get('data'));
			$stmt->bindValue(':horario', $this->__get('horario'));
			$stmt->bindValue(':crmv_vet', $crmv_vet['crmv']);
			$stmt->bindValue(':id_pet', $this->__get('id_pet'));
			$stmt->bindValue(':descricao', $this->__get('descricao'));

			$stmt->execute();

			return $this;
		}

		//CONTROLE DE AGENDA DOS VETERINÁRIOS	

		private function aux_recuperar_consultas($data_inicial, $data_final, $cpf_vet){

			$query = "SELECT 
			consultas.id_consulta, consultas.data, consultas.horario, 
			pets.nome AS nome_pet, clientes.nome AS nome_tutor, consultas.descricao, consultas.status 
			FROM consultas 
			INNER JOIN veterinarios ON (consultas.crmv_vet = veterinarios.crmv) 
			INNER JOIN pets ON (consultas.id_pet = pets.id_pet)
			INNER JOIN clientes ON (pets.cpf_dono = clientes.cpf) 
			WHERE data BETWEEN (:data_inicial) AND (:data_final) 
			AND veterinarios.cpf = :cpf
			AND consultas.status NOT IN ('bloqueado', 'realizada sem marcar')
			ORDER BY data, STR_TO_DATE(horario, '%H:%i');";

			$stmt = $this->db->prepare($query);
			
			$stmt->bindValue(':data_inicial', $data_inicial);
			$stmt->bindValue(':data_final', $data_final);	
			$stmt->bindValue(':cpf', $cpf_vet);		

			$stmt->execute();				
			
			return $stmt->fetchAll(\PDO::FETCH_ASSOC);
		}

		public function recuperar_consultas($intervalo, $cpf_vet){
							
			if($intervalo == 'dia'){

				$data_inicial = $this->__get('data');
				$data_final = $this->__get('data');

				return $this->aux_recuperar_consultas($data_inicial, $data_final, $cpf_vet);			
			}	
			
			if($intervalo == 'quinzena'){

				$data_inicial = $this->__get('data');
				$intervalo_dias = 15;		
				$data_final = date('Y-m-d', strtotime($data_inicial . "+{$intervalo_dias} days"));

				return $this->aux_recuperar_consultas($data_inicial, $data_final, $cpf_vet);				
			}

			if($intervalo == 'mês'){

				$data_inicial = $this->__get('data');
				$intervalo_dias = 30;	
				$data_final = date('Y-m-d', strtotime($data_inicial . "+{$intervalo_dias} days"));

				return $this->aux_recuperar_consultas($data_inicial, $data_final, $cpf_vet);	
			}					
		}

		private function aux_recuperar_horarios_livres($intervalo_dias, $data_inicial, $data_final, $cpf_vet){

			$horarios_disponiveis = $this->horarios_disponiveis;

			$query = "SELECT 
			data, horario
			FROM consultas 
			INNER JOIN veterinarios ON (consultas.crmv_vet = veterinarios.crmv)
			WHERE data BETWEEN (:data_inicial) AND (:data_final) 
			AND veterinarios.cpf = :cpf
			AND consultas.status != 'realizada sem marcar'
			ORDER BY data, STR_TO_DATE(horario, '%H:%i');";

			$stmt = $this->db->prepare($query);
			
			$stmt->bindValue(':data_inicial', $data_inicial);
			$stmt->bindValue(':data_final', $data_final);	
			$stmt->bindValue(':cpf', $cpf_vet);		

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

		public function recuperar_horarios_livres($intervalo, $cpf_vet){			
							
			if($intervalo == 'dia'){

				$intervalo_dias = 1;
				$data_inicial = $this->__get('data');
				$data_final = $this->__get('data');

				return $this->aux_recuperar_horarios_livres($intervalo_dias, $data_inicial, $data_final, $cpf_vet);
			}	
			
			if($intervalo == 'quinzena'){

				$intervalo_dias = 15;
				$data_inicial = $this->__get('data');
				$data_final = date('Y-m-d', strtotime($data_inicial . "+{$intervalo_dias} days"));

				return $this->aux_recuperar_horarios_livres($intervalo_dias, $data_inicial, $data_final, $cpf_vet);
			}

			if($intervalo == 'mês'){

				$intervalo_dias = 30;	
				$data_inicial = $this->__get('data');
				$data_final = date('Y-m-d', strtotime($data_inicial . "+{$intervalo_dias} days"));

				return $this->aux_recuperar_horarios_livres($intervalo_dias, $data_inicial, $data_final, $cpf_vet);				
			}					
		}

		private function aux_recuperar_horarios_bloqueados($data_inicial, $data_final, $cpf_vet){

			$query = "SELECT 
			consultas.id_consulta, consultas.data, consultas.horario
			FROM consultas 
			INNER JOIN veterinarios ON (consultas.crmv_vet = veterinarios.crmv)
			WHERE data BETWEEN (:data_inicial) AND (:data_final) 
			AND veterinarios.cpf = :cpf
			AND consultas.status = 'bloqueado'
			ORDER BY data, STR_TO_DATE(horario, '%H:%i');";

			$stmt = $this->db->prepare($query);
			
			$stmt->bindValue(':data_inicial', $data_inicial);
			$stmt->bindValue(':data_final', $data_final);	
			$stmt->bindValue(':cpf', $cpf_vet);		

			$stmt->execute();				
			
			return $stmt->fetchAll(\PDO::FETCH_ASSOC);
		}

		public function recuperar_horarios_bloqueados($intervalo, $cpf_vet){
							
			if($intervalo == 'dia'){

				$data_inicial = $this->__get('data');
				$data_final = $this->__get('data');

				return $this->aux_recuperar_horarios_bloqueados($data_inicial, $data_final, $cpf_vet);				
			}	
			
			if($intervalo == 'quinzena'){

				$data_inicial = $this->__get('data');
				$intervalo_dias = 15;	
				$data_final = date('Y-m-d', strtotime($data_inicial . "+{$intervalo_dias} days"));

				return $this->aux_recuperar_horarios_bloqueados($data_inicial, $data_final, $cpf_vet);					
			}

			if($intervalo == 'mês'){

				$data_inicial = $this->__get('data');
				$intervalo_dias = 30;	
				$data_final = date('Y-m-d', strtotime($data_inicial . "+{$intervalo_dias} days"));

				return $this->aux_recuperar_horarios_bloqueados($data_inicial, $data_final, $cpf_vet);
			}					
		}

		private function aux_cancelar_consultas($consulta){							
			
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

		public function cancelar_consultas($consultas){			
							
			foreach($consultas as $consulta){				

				$dados_consulta = (array) $this->aux_cancelar_consultas($consulta);				
				
				$aviso = Container::getModel('aviso');

				$aviso->gerar_aviso($dados_consulta);				
				
				//atualização da consulta para horario bloqueado
				$query = "UPDATE consultas SET id_pet = NULL, status = 'bloqueado' WHERE id_consulta = :id_consulta;";

				$stmt = $this->db->prepare($query);
				
				$stmt->bindValue(':id_consulta', $consulta);					

				$stmt->execute();					
			}	
			
			return true;
		}		

		public function bloquear_horarios($datas_horarios, $cpf_vet){

			$crmv = (array) Veterinario::getCrmvPorCpf($cpf_vet);						
							
			foreach($datas_horarios as $data_horario){				
					
				$query = "INSERT INTO consultas(data, horario, crmv_vet, status)VALUES(:data, :horario, :crmv_vet, 'bloqueado')";

				$stmt = $this->db->prepare($query);
				
				$stmt->bindValue(':data', $data_horario['data']);
				$stmt->bindValue(':horario', $data_horario['horario']);	
				$stmt->bindValue(':crmv_vet', $crmv['crmv']);		

				$stmt->execute();										
			}		
			
			return true;
		}

		public function liberar_horarios($horarios_bloqueados){
							
			foreach($horarios_bloqueados as $horario_bloqueado){				
					
				$query = "DELETE FROM consultas WHERE id_consulta = :id_consulta";

				$stmt = $this->db->prepare($query);
				
				$stmt->bindValue(':id_consulta', $horario_bloqueado);	

				$stmt->execute();								
			}	

			return true;			
		}	

		//FUNÇÕES PARA O DASHBOARD DO SISTEMA

		public function idade_do_sistema(){

			$query = "SELECT 
			MAX(data) AS data_ultima_consulta, 
			MIN(data) AS data_primeira_consulta 
			FROM consultas 
			WHERE consultas.status != 'bloqueado';";

			$stmt = $this->db->prepare($query);					

			$stmt->execute();

			return $stmt->fetch(\PDO::FETCH_ASSOC);
		}

		public function dados_mes($mes_referencia){

			//para o mês de referência
			list($ano, $mes) = explode("-", $mes_referencia);

			$dias_do_mes = cal_days_in_month(CAL_GREGORIAN, $mes, $ano);

			$data_inicial = $mes_referencia . '-01';
			$data_final = $mes_referencia . '-' . $dias_do_mes;

			//para o mês anterior ao da referência
			$data_inicial_mes_passado = $mes_referencia . '-01';
			$data_inicial_mes_passado = date('Y-m-d', strtotime($data_inicial_mes_passado . " - 1 month"));

			list($ano, $mes, $dia) = explode("-", $data_inicial_mes_passado);

			$dias_do_mes = cal_days_in_month(CAL_GREGORIAN, $mes, $ano);

			$data_final_mes_passado = $ano . '-' . $mes . '-' . $dias_do_mes;

			//SELECTS ENCADEADOS

			//total de consultas realizadas no mês
			$query = "SELECT 
			COUNT(*) AS total_consultas_realizadas, 

			/*total de consultas realizadas no mês anterior*/
			(SELECT COUNT(*) 
			FROM consultas 
			WHERE consultas.data BETWEEN (:data_inicial_mes_passado) AND (:data_final_mes_passado) AND
			consultas.status NOT IN ('bloqueado', 'marcada')) AS total_consultas_realizadas_mes_anterior,

			/*total de consultas agendadas no mês*/
			(SELECT COUNT(*)
			FROM consultas 
			WHERE consultas.data BETWEEN (:data_inicial) AND (:data_final) AND
			consultas.status IN ('marcada', 'realizada')) AS total_consultas_agendadas,

			/*consultas realizadas com agendamento*/
			(SELECT COUNT(*)
			FROM consultas 
			WHERE consultas.data BETWEEN (:data_inicial) AND (:data_final) AND
			consultas.status = 'realizada') AS realizadas_com_agendamento,

			/*consultas realizadas sem agendamento*/
			(SELECT COUNT(*)
			FROM consultas 
			WHERE consultas.data BETWEEN (:data_inicial) AND (:data_final) AND 
			consultas.status = 'realizada sem marcar') AS realizadas_sem_agendamento,

			/*veterinário com mais consultas no mês*/			
			(SELECT nome_vet FROM 
			(SELECT veterinarios.nome AS nome_vet, COUNT(*) AS numero_consultas 
			FROM consultas 
			INNER JOIN veterinarios ON (consultas.crmv_vet = veterinarios.crmv) 
			WHERE consultas.data BETWEEN (:data_inicial) AND (:data_final) 
			AND consultas.status NOT IN ('bloqueado', 'marcada') 
			GROUP BY veterinarios.nome) consultas 
			ORDER BY numero_consultas DESC LIMIT 1) AS vet_mais_consultas,

			/*veterinário com menos consultas no mês*/
			(SELECT nome_vet FROM 
			(SELECT veterinarios.nome AS nome_vet, COUNT(*) AS numero_consultas 
			FROM consultas 
			INNER JOIN veterinarios ON (consultas.crmv_vet = veterinarios.crmv) 
			WHERE consultas.data BETWEEN (:data_inicial) AND (:data_final) 
			AND consultas.status NOT IN ('bloqueado', 'marcada') 
			GROUP BY veterinarios.nome) consultas 
			ORDER BY numero_consultas ASC LIMIT 1) AS vet_menos_consultas

			/*total de consultas realizadas no mês*/
			FROM consultas 
			WHERE consultas.data BETWEEN (:data_inicial) AND (:data_final) AND
			consultas.status NOT IN ('bloqueado', 'marcada');";

			$stmt = $this->db->prepare($query);	
			
			$stmt->bindValue(':data_inicial', $data_inicial);
			$stmt->bindValue(':data_final', $data_final);
			$stmt->bindValue(':data_inicial_mes_passado', $data_inicial_mes_passado);
			$stmt->bindValue(':data_final_mes_passado', $data_final_mes_passado);

			$stmt->execute();

			return $stmt->fetch(\PDO::FETCH_ASSOC);
		}		
	}
?>