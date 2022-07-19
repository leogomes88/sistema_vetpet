<?php 

	namespace App\Models\Dao;

	interface InterfaceConsultaDao {		

		//AGENDAMENTO DE CONSULTAS 

		public function verificarDatasHorariosDisponiveis($data, $lista_vets);
		public function verificarVeterinariosDisponiveis($data, $horario, $lista_vets);
		public function validarCadastro($consulta);	
		public function salvar($consulta);

		//REALIZAÇÃO DE CONSULTAS

		public function realizarConsulta($consulta);
		public function realizarConsultaNaoAgendada($consulta);

		//CONTROLE DE AGENDA DOS VETERINÁRIOS			

		public function recuperarConsultas($intervalo, $crmv);
		public function recuperarHorariosLivres($intervalo, $crmv);
		public function recuperarHorariosBloqueados($intervalo, $crmv);
		public function cancelarConsultas($consultas);
		public function bloquearHorarios($datas_horarios, $crmv);
		public function liberarHorarios($horarios_bloqueados);				
	}
?>