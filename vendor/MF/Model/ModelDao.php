<?php 

	namespace MF\Model;

	abstract class ModelDao{

		protected $db;

		public function __construct(\PDO $db){
			$this->db = $db;
		}
	}

?>