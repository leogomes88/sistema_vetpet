<?php 

	namespace App\Models\Dao;

	interface InterfacePetDao {		
		
		public function salvar($pet);	
		public function validarCadastro($pet);
		public function recuperarPetsCli($cpf);			
		public function pesquisarPets($filtro);
		public function recuperarHistorico($id_pet);
	}

?>