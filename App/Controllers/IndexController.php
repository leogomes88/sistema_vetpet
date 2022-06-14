<?php 

	namespace App\Controllers;

	//recursos do miniframework
	use MF\Controller\Action;
	use MF\Model\Container;	

	class IndexController extends Action{		

		public function index(){				
			
			$this->render('index');
		}			

		public function quem_somos(){				
			
			$this->render('quem_somos');
		}	

		public function servicos(){				
			
			$this->render('servicos');
		}	

		public function contato_endereco(){				
			
			$this->render('contato_endereco');
		}			
	}
?>