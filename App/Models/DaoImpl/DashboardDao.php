<?php

	namespace App\Models\DaoImpl;	

	use MF\Model\ModelDao;
	use App\Models\Dao\InterfaceDashboardDao;

    class DashboardDao extends ModelDao implements InterfaceDashboardDao{		

        public function idadeDoSistema(){

			$query = "SELECT MAX(data) AS data_ultima_consulta, MIN(data) AS data_primeira_consulta 
			FROM consultas 
			WHERE consultas.status != 'bloqueado';";

			$stmt = $this->db->prepare($query);
			$stmt->execute();

			return $stmt->fetch(\PDO::FETCH_ASSOC);
		}

		public function dadosMes($mes_referencia){

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