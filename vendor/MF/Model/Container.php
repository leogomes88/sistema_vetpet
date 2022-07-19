<?php 

	namespace MF\Model;

	use App\Connection;

	class Container{

		public static function getModelDao($type){
			
			$class = "\\App\\Models\\DaoImpl\\" . ucfirst($type) . "Dao";
			return new $class(Connection::getDb());
		}
	}
?>