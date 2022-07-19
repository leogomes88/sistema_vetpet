<?php 

	namespace App\Models\Dao;
	
	interface InterfaceVeterinarioDao {			
		
		public function salvar($veterinario);
		public function validarCadastro($veterinario);
		public function autenticar($veterinario);		
		public function recuperarVets();
	}

?>