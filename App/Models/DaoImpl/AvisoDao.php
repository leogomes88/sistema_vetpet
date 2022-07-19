<?php 

	namespace App\Models\DaoImpl;	

	use MF\Model\ModelDao;
	use App\Models\Dao\InterfaceAvisoDao;

	class AvisoDao extends ModelDao implements InterfaceAvisoDao{			

		public function gerarAviso($dados_consulta){

			$dados_consulta['data'] = strtotime($dados_consulta['data']); 
			$dados_consulta['data'] = date("d/m/Y", $dados_consulta['data']);

			$descricao = 'Lamentamos informar que a consulta do seu pet na data ' . $dados_consulta['data'] . ' e no horário '. $dados_consulta['horario'] . ' foi cancelada por motivo de força maior, esperamos que em breve entre em contato e marque uma nova consulta.';				
				
			//criação de aviso de cancelamento para o cliente
			//(o aviso poderia vir por mensagem de texto no celular, através de uma API)
			$query = "INSERT INTO avisos(cpf_dono, crmv_vet, descricao, status)VALUES(:cpf_dono, :crmv_vet, :descricao, 'criado')";

			$stmt = $this->db->prepare($query);			
			$stmt->bindValue(':cpf_dono', $dados_consulta['cpf']);
			$stmt->bindValue(':crmv_vet', $dados_consulta['crmv_vet']);	
			$stmt->bindValue(':descricao', $descricao);	
			$stmt->execute();
		}
		
		public function recuperarAvisos($cpf){

			$query = "SELECT id_aviso, descricao FROM avisos WHERE cpf_dono = :cpf_dono AND status = 'criado';";

			$stmt = $this->db->prepare($query);			
			$stmt->bindValue(':cpf_dono', $cpf);
			$stmt->execute();

			return $stmt->fetchAll(\PDO::FETCH_ASSOC);
		}		
		
		public function avisosVisualizados($avisos){             

            for ($i=0; $i < count($avisos); $i++) {                 					
					
				$query = "UPDATE avisos SET status = 'visualizado' WHERE id_aviso = :id_aviso;";

				$stmt = $this->db->prepare($query);				
				$stmt->bindValue(':id_aviso', $avisos[$i]);	
				$stmt->execute();					
            }

			return true;	
		}		
	}

?>