<?php 

	namespace MF\Model;

	use App\Connection;

	class Container{

		public static function getModel($model){

			//retornar o modelo já instanciado, com a conexão já estabelecidada
			$class = "\\App\\Models\\" . ucfirst($model);

			//instancia de conexao
			$conn = Connection::getDb();

			return new $class($conn);
		}
	}
?>