<?php 

	namespace App\Controllers;

	//recursos do miniframework
	use MF\Controller\Action;
	use MF\Model\Container;

	class AuthController extends Action{	
		
		public function cadastrar_cli(){	

			$this->view->cliente = array(
				
				'nome' => '', 
				'cpf' => '',
				'telefone' => '',
				'email' => '', 
				'senha' => ''
			);
			
			$this->view->erroCadastro = false;

			$this->render('cadastrar_cli');
		}

		public function cadastrar_cli2(){	

			if(!isset($_POST['nome']) || !isset($_POST['cpf']) || !isset($_POST['telefone']) || !isset($_POST['email'])
			|| !isset($_POST['senha'])){

				header('Location: /cadastrar_cli');
			}

			try{				
				
				$cliente = Container::getModel('cliente');

				//elimina a mascara do cpf
				$cpf = preg_replace("/[^0-9]/", "", $_POST['cpf']);
				$cpf = str_pad($cpf, 11, '0', STR_PAD_LEFT);

				$cliente->__set('nome', $_POST['nome']);
				$cliente->__set('cpf', $cpf);
				$cliente->__set('telefone', $_POST['telefone']);
				$cliente->__set('email', $_POST['email']);
				$cliente->__set('senha', $_POST['senha']);

				if($cliente->validarCadastro()){
					
					$cliente->salvar();
									
					$this->render('cadastro_cli_sucesso');				

				}else{
					
					$this->view->cliente = array(
						
						'nome' => $_POST['nome'], 
						'cpf' => $_POST['cpf'],
						'telefone' => $_POST['telefone'],
						'email' => $_POST['email'], 
						'senha' => $_POST['senha']
					);

					$this->view->erroCadastro = true;

					$this->render('cadastrar_cli');
				}		
			
			}catch(\Error){

				header('Location: /erro_banco');
			}
		}		

		public function login_cli(){

			$this->view->usuario = array(				
				
				'email' => '', 
				'senha' => ''
			);

			$this->view->erro = false;
			$this->view->login_cliente = true;
			$this->view->titulo = 'Entre para agendar';
			$this->view->rota = '/autenticar_cli';

			$this->render('login');
		}

		public function autenticar_cli(){

			if(!isset($_POST['email']) || !isset($_POST['senha'])){

				header('Location: /login_cli');
			}

			try{

				$cliente = Container::getModel('cliente');

				$cliente->__set('email', $_POST['email']);
				$cliente->__set('senha', md5($_POST['senha']));			

				$cliente->autenticar();					

				if(!empty($cliente->__get('cpf'))){

					session_start();

					session_destroy();

					session_start();					

					$_SESSION['cpf_cli'] = $cliente->__get('cpf');
					$_SESSION['nome_cli'] = $cliente->__get('nome');

					header('Location: /painel_cli');

				}else{

					$this->view->usuario = array(						
						
						'email' => $_POST['email'], 
						'senha' => $_POST['senha']
					);

					$this->view->erro = true;
					$this->view->login_cliente = true;
					$this->view->titulo = 'Entre para agendar';
					$this->view->rota = '/autenticar_cli';

					$this->render('login');
				}		

			}catch(\Error){

				header('Location: /erro_banco');
			}
		}		

		public function login_vet(){

			$this->view->usuario = array(				
				
				'email' => '', 
				'senha' => ''
			);

			$this->view->erro = false;
			$this->view->login_cliente = false;
			$this->view->titulo = 'Login | Veterinário';
			$this->view->rota = '/autenticar_vet';

			$this->render('login', 'layout_cabecalho_limpo');
		}

		public function autenticar_vet(){

			if(!isset($_POST['email']) || !isset($_POST['senha'])){

				header('Location: /login_vet');
			}

			try{

				$veterinario = Container::getModel('veterinario');

				$veterinario->__set('email', $_POST['email']);
				$veterinario->__set('senha', md5($_POST['senha']));

				$veterinario->autenticar();

				if(!empty($veterinario->__get('cpf'))){

					session_start();

					session_destroy();

					session_start();

					$_SESSION['cpf_vet'] = $veterinario->__get('cpf');
					$_SESSION['nome_vet'] = $veterinario->__get('nome');

					header('Location: /painel_vet');

				}else{

					$this->view->usuario = array(				
					
						'email' => $_POST['email'], 
						'senha' => $_POST['senha']
					);
		
					$this->view->erro = true;
					$this->view->login_cliente = false;
					$this->view->titulo = 'Login | Veterinário';
					$this->view->rota = '/autenticar_vet';
		
					$this->render('login', 'layout_cabecalho_limpo');
				}

			}catch(\Error){

				header('Location: /erro_banco');
			}
		}		

		public function sair(){

			session_start();

			session_destroy();

			header('Location: /');
		}

		public function erro_banco(){

			session_start();

			session_destroy();

			$this->render('erro_banco', 'layout_cabecalho_limpo');
		}
	}	

?>