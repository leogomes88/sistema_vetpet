<?php 	

	namespace App\Models\Dao;

	interface InterfaceAvisoDao {		

		public function gerarAviso($dados_consulta);		
		public function recuperarAvisos($cpf);		
		public function avisosVisualizados($avisos);
	}

?>