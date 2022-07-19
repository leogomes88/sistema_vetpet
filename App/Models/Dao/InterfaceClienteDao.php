<?php 	

	namespace App\Models\Dao;

	interface InterfaceClienteDao {
		
		public function salvar($cliente);
		public function validarCadastro($cliente);
		public function autenticar($cliente);
		public function recuperarClientes($filtro);
		public function verificarConsultasMarcadas($cpf);
	}

?>