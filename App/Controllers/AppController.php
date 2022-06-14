<?php 

	namespace App\Controllers;

	//recursos do miniframework
	use MF\Controller\Action;
	use MF\Model\Container;

	class AppController extends Action{

		//FUNCIONALIDADES PARA OS VETERINÁRIOS

		public function painel_vet(){

			$this->verificaConexaoBanco();

			$this->validaAutenticacao('veterinario');	

			$this->set_layout_painel('veterinario');

			$this->view->usuario_especial = false;

			if($_SESSION['cpf_vet'] == '32222215021'){

				$this->view->usuario_especial = true;	 			
			}

			$this->render('painel_vet', 'layout_painel');		
		}

		public function controlar_agenda(){

			$this->verificaConexaoBanco();			
				
			$this->validaAutenticacao('veterinario');	
	
			$this->set_layout_painel('veterinario');

			$this->render('controlar_agenda', 'layout_painel');				
		}

		public function recuperar_consultas(){			

			$this->validaAutenticacao('veterinario');

			$intervalo = $_GET['intervalo'];			

			$data_atual = date('Y-m-d');
			
			$cpf_vet = $_SESSION['cpf_vet'];

			$consulta = Container::getModel('consulta');

			$consulta->__set('data', $data_atual);			

			$consultas = $consulta->recuperar_consultas($intervalo, $cpf_vet);

			echo json_encode($consultas);			
		}

		public function recuperar_horarios_livres(){			

			$this->validaAutenticacao('veterinario');

			$intervalo = $_GET['intervalo'];			

			$data_atual = date('Y-m-d');
			
			$cpf_vet = $_SESSION['cpf_vet'];

			$consulta = Container::getModel('consulta');

			$consulta->__set('data', $data_atual);			

			$horarios_livres = $consulta->recuperar_horarios_livres($intervalo, $cpf_vet);				

			echo json_encode($horarios_livres);			
		}

		public function recuperar_horarios_bloqueados(){			

			$this->validaAutenticacao('veterinario');

			$intervalo = $_GET['intervalo'];			

			$data_atual = date('Y-m-d');
			
			$cpf_vet = $_SESSION['cpf_vet'];

			$consulta = Container::getModel('consulta');

			$consulta->__set('data', $data_atual);			

			$horarios_bloqueados = $consulta->recuperar_horarios_bloqueados($intervalo, $cpf_vet);				

			echo json_encode($horarios_bloqueados);			
		}

		public function cancelar_consultas(){			

			$this->validaAutenticacao('veterinario');

			$consultas = explode(',', $_POST['consultas']);		

			$consulta = Container::getModel('consulta');						
			
			$resposta = $consulta->cancelar_consultas($consultas);				

			echo json_encode($resposta);			
		}		

		public function bloquear_horarios_livres(){			

			$this->validaAutenticacao('veterinario');

			$cpf_vet = $_SESSION['cpf_vet'];

			$datas_horarios = explode(',', $_POST['datas_horarios_livres']);
			
			$datas_horarios_bloquear = array();

			foreach ($datas_horarios as $data_horario) {
				
				$data_horario_bloquear = explode(' ', $data_horario);

				$datas_horarios_bloquear[] = ['data' => $data_horario_bloquear[0],  'horario' => $data_horario_bloquear[1]];
			}			

			$consulta = Container::getModel('consulta');					

			$resposta = $consulta->bloquear_horarios($datas_horarios_bloquear, $cpf_vet);				

			echo json_encode($resposta);
		}

		public function liberar_horarios_bloqueados(){			

			$this->validaAutenticacao('veterinario');

			$horarios_bloqueados = explode(',', $_POST['horarios_bloqueados']);		

			$consulta = Container::getModel('consulta');						

			$resposta = $consulta->liberar_horarios($horarios_bloqueados);				

			echo json_encode($resposta);			
		}

		public function controlar_consultas(){

			$this->verificaConexaoBanco();

			$this->validaAutenticacao('veterinario');
			
			$this->set_layout_painel('veterinario');

			$this->render('controlar_consultas', 'layout_painel');
		}

		public function pesquisar_pets(){			

			$this->validaAutenticacao('veterinario');
			
			$pesquisa = $_GET['pesquisa'];			
			
			$pet = Container::getModel('pet');						

			$resultado = $pet->pesquisa_pets($pesquisa);				

			echo json_encode($resultado);			
		}

		public function prontuario(){

			$this->validaAutenticacao('veterinario');
				
			$this->set_layout_painel('veterinario');

			if(!isset($_GET['id_pet']) || !isset($_GET['nome_pet']) || !isset($_GET['nome_tutor']) || !isset($_GET['raca']) || 
			!isset($_GET['especie']) || !isset($_GET['porte']) || !isset($_GET['data_nasc'])){

				header('Location: /controlar_consultas');
			}

			try{				

				$data_nasc = explode('-', $_GET['data_nasc']);
				$data_nasc = $data_nasc[2] . '/' . $data_nasc[1] . '/' . $data_nasc[0];

				$this->view->pet = array(

					'id_pet' => $_GET['id_pet'],
					'nome_pet' => $_GET['nome_pet'], 
					'nome_tutor' => $_GET['nome_tutor'],
					'raca' => $_GET['raca'],
					'especie' => $_GET['especie'],
					'porte' => $_GET['porte'], 
					'data_nasc' => $data_nasc
				);			
				
				$pet = Container::getModel('pet');	
				
				$pet->__set('id', $_GET['id_pet']);

				$this->view->historico = $pet->recupera_historico();

				$this->render('prontuario', 'layout_painel');	
				
			}catch(\Error){

				header('Location: /erro_banco');
			}
		}

		public function realizar_consulta(){			

			$this->validaAutenticacao('veterinario');			
				
			$data_atual = date('Y-m-d');
			$hora_atual = date('H:i');
			
			$cpf_vet = $_SESSION['cpf_vet'];

			$consulta = Container::getModel('consulta');	
			$pet = Container::getModel('pet');
			
			$consulta->__set('id_pet', $_POST['id_pet']);
			$consulta->__set('descricao', $_POST['descricao']);
			$consulta->__set('data', $data_atual);
			$consulta->__set('horario', $hora_atual);
			$pet->__set('id', $_POST['id_pet']);

			//realizar_consulta() insere a descrição da consulta caso a mesma já exista e esteja marcada para o dia atual
			if($consulta->realizar_consulta($cpf_vet)){	

				$historico_atualizado = $pet->recupera_historico();

				echo json_encode($historico_atualizado);

			}else{//realizar_consulta_nao_agendada() cria uma nova consulta
				
				$consulta->realizar_consulta_nao_agendada($cpf_vet);

				$historico_atualizado = $pet->recupera_historico();

				echo json_encode($historico_atualizado);
			}					
		}

		//FUNCIONALIDADES EXCLUSIVAS PARA O VETERINÁRIO USUÁRIO ESPECIAL, COM CPF FAKE '32222215021'

		public function cadastrar_vet(){

			$this->verificaConexaoBanco();			
			
			$this->validaAutenticacaoEspecial();

			$this->set_layout_painel('veterinario');
			
			$this->view->veterinario = array(

				'nome' => '', 
				'cpf' => '',
				'telefone' => '',
				'endereco' => '',
				'email' => '', 
				'crmv' => '',
				'senha' => ''
			);
			
			$this->view->erroCadastro = false;

			$this->render('cadastrar_vet', 'layout_painel');
		}

		public function cadastrar_vet2(){		

			$this->validaAutenticacaoEspecial();
				
			$this->set_layout_painel('veterinario');

			if(!isset($_POST['nome']) || !isset($_POST['cpf']) || !isset($_POST['telefone']) || !isset($_POST['endereco'])
			|| !isset($_POST['email']) || !isset($_POST['crmv']) || !isset($_POST['senha'])){

				header('Location: /cadastrar_vet');
			}
			
			try{				
				
				//elimina a máscara do cpf
				$cpf = preg_replace("/[^0-9]/", "", $_POST['cpf']);
				$cpf = str_pad($cpf, 11, '0', STR_PAD_LEFT);
				
				$veterinario = Container::getModel('veterinario');

				$veterinario->__set('nome', $_POST['nome']);
				$veterinario->__set('cpf',$cpf);
				$veterinario->__set('telefone', $_POST['telefone']);
				$veterinario->__set('endereco', $_POST['endereco']);
				$veterinario->__set('email', $_POST['email']);
				$veterinario->__set('crmv', $_POST['crmv']);
				$veterinario->__set('senha', $_POST['senha']);

				if($veterinario->validarCadastro()){
					
					$veterinario->salvar();						
					
					$this->view->mensagem_sucesso = 'Veterinário(a) cadastrado com sucesso!';

					$this->render('cadastro_sucesso', 'layout_painel');				

				}else{
					
					$this->view->veterinario = array(

						'nome' => $_POST['nome'], 
						'cpf' => $_POST['cpf'],
						'telefone' => $_POST['telefone'],
						'endereco' => $_POST['endereco'],
						'email' => $_POST['email'], 
						'crmv' => $_POST['crmv'],
						'senha' => $_POST['senha']
					);

					$this->view->erroCadastro = true;

					$this->render('cadastrar_vet', 'layout_painel');
				}		
				
			}catch(\Error){

				header('Location: /erro_banco');
			}
		}	

		public function dashboard(){		
			
			try{

				$this->validaAutenticacaoEspecial();
				
				$this->set_layout_painel('veterinario');

				//recuperar meses de referência para análise no dashboard

				$consulta = Container::getModel('consulta');

				$idade_do_sistema = $consulta->idade_do_sistema();	
				
				$data_ultima_consulta = $idade_do_sistema['data_ultima_consulta'];
				$data_consulta = $idade_do_sistema['data_primeira_consulta'];

				$lista_mes_ano = [];		

				$lista_mes_ano[] = date('Y-m', strtotime($data_consulta));				
				
				while(date('Y-m', strtotime($data_ultima_consulta)) != date('Y-m', strtotime($data_consulta))){				
					
					$data_consulta = date('Y-m-d', strtotime($data_consulta . " + 1 month"));	

					$lista_mes_ano[] = date('Y-m', strtotime($data_consulta));				
				}

				$this->view->meses_referencia = $lista_mes_ano;

				//recuperar dados dos veterinários

				$vet = Container::getModel('veterinario');				

				$vets = $vet->recuperar_vets();			

				$this->view->vets = $vets;
				
				$this->render('dashboard', 'layout_painel');
				
			}catch(\Error){

				header('Location: /erro_banco');
			}
		}			

		public function dados_mes(){			

			$this->validaAutenticacaoEspecial();

			$mes_referencia = $_GET['mes_referencia'];

			$consulta = Container::getModel('consulta');

			$dados = $consulta->dados_mes($mes_referencia);

			echo json_encode($dados);			
		}

		public function recuperar_clientes(){			

			$this->validaAutenticacaoEspecial();

			$filtro = $_GET['filtro'];

			$cliente = Container::getModel('cliente');				

			$clientes = $cliente->recuperar_clientes($filtro);			

			echo json_encode($clientes);			
		}

		//FUNCIONALIDADES PARA OS CLIENTES

		public function painel_cli(){

			try{

				$this->validaAutenticacao('cliente');		
				
				$this->set_layout_painel('cliente');

				$this->view->pet_cadastrado = true;

				$this->view->consultas_marcadas = '';
				
				$cliente = Container::getModel('cliente');

				$cliente->__set('cpf', $_SESSION['cpf_cli']);

				if(empty($cliente->recuperarPetsCli())){
					
					$this->view->pet_cadastrado = false;
				}

				if(!empty($cliente->verifica_consultas_marcadas())){

					$this->view->consultas_marcadas = $cliente->verifica_consultas_marcadas();
				}

				$this->render('painel_cli', 'layout_painel');	
				
			}catch(\Error){

				header('Location: /erro_banco');
			}
		}
		
		public function verifica_avisos(){			

			$this->validaAutenticacao('cliente');

			$aviso = Container::getModel('aviso');

			$aviso->__set('cpf_dono', $_SESSION['cpf_cli']);

			echo json_encode($aviso->recuperar_avisos());			
		}

		public function avisos_visualizados(){			

			$this->validaAutenticacao('cliente');

			$avisos_visualizados = explode(',', $_POST['avisos_visualizados']);

			$aviso = Container::getModel('aviso');

			$resposta = $aviso->avisos_visualizados($avisos_visualizados);				

			echo json_encode($resposta);			
		}

		public function cadastrar_pet(){

			$this->verificaConexaoBanco();

			$this->validaAutenticacao('cliente');	
			
			$this->set_layout_painel('cliente');
			
			$this->view->pet = array(

				'nome' => '', 
				'data_nasc' => '',
				'raca' => '',
				'especie' => '',
				'porte' => ''
			);
			
			$this->view->erroCadastro = false;			

			$this->render('cadastrar_pet', 'layout_painel');
		}

		public function cadastrar_pet2(){	

			$this->validaAutenticacao('cliente');

			$this->set_layout_painel('cliente');

			if(!isset($_POST['nome']) || !isset($_POST['data_nasc']) || !isset($_POST['raca']) || !isset($_POST['especie'])
			|| !isset($_POST['porte'])){

				header('Location: /cadastrar_pet');
			}

			try{														
				
				$pet = Container::getModel('pet');

				$data_nasc = $_POST['data_nasc'];			

				list($dia, $mes, $ano) = explode("/", $data_nasc);

				$data_nasc = $ano . '-' . $mes . '-' . $dia;

				$pet->__set('nome', $_POST['nome']);
				$pet->__set('cpf_dono', $_SESSION['cpf_cli']);
				$pet->__set('data_nasc', $data_nasc);
				$pet->__set('raca', $_POST['raca']);
				$pet->__set('especie', $_POST['especie']);
				$pet->__set('porte', $_POST['porte']);

				if($pet->validarCadastro()){
					
					$pet->salvar();

					$this->view->mensagem_sucesso = 'Pet cadastrado com sucesso!';
					
					$this->render('cadastro_sucesso', 'layout_painel');				

				}else{
					
					$this->view->pet = array(

						'nome' => $_POST['nome'], 
						'data_nasc' => $_POST['data_nasc'],
						'raca' => $_POST['raca'],
						'especie' => $_POST['especie'],
						'porte' => $_POST['porte']
					);

					$this->view->erroCadastro = true;

					$this->render('cadastrar_pet', 'layout_painel');
				}	
				
			}catch(\Error){

				header('Location: /erro_banco');
			}
		}		

		public function verifica_data_horario(){			

			$this->validaAutenticacao('cliente');

			$consulta = Container::getModel('consulta');

			$consulta->__set('data', $_GET['data']);

			$horarios_disponiveis = $consulta->verificar_datas_horarios_disponiveis();				

			echo json_encode($horarios_disponiveis);			
		}

		public function verifica_veterinarios(){			

			$this->validaAutenticacao('cliente');

			$consulta = Container::getModel('consulta');			

			$consulta->__set('data', $_GET['data']);
			$consulta->__set('horario', $_GET['horario']);

			$vets_disponiveis = $consulta->verificar_veterinarios_disponiveis();				

			echo json_encode($vets_disponiveis);			
		}

		public function agendar_consulta(){

			try{

				$this->validaAutenticacao('cliente');

				$this->set_layout_painel('cliente');
				
				$this->view->erro = isset($_GET['erro']) ? $_GET['erro'] : false;
				
				$cliente = Container::getModel('cliente');

				$cliente->__set('cpf', $_SESSION['cpf_cli']);			 

				$this->view->data_atual = date('Y-m-d');

				//só é possivel marcar uma consulta com no máximo 60 dias de antecedência
				$intervalo_dias = 60;			

				$this->view->data_maxima = date('Y-m-d', strtotime($this->view->data_atual . "+{$intervalo_dias} days"));		

				$this->view->pets = $cliente->recuperarPetsCli();			

				$this->render('agendar_consulta', 'layout_painel');		
				
			}catch(\Error){

				header('Location: /erro_banco');
			}
		}

		public function agendar_consulta2(){	

			try{

				$this->validaAutenticacao('cliente');	

				$this->set_layout_painel('cliente');			
				
				$consulta = Container::getModel('consulta');		

				$consulta->__set('data', $_POST['data_consulta']);
				$consulta->__set('horario', $_POST['horario_consulta']);
				$consulta->__set('crmv_vet', $_POST['veterinarios_d']);
				$consulta->__set('id_pet', $_POST['pet']);		
				
				if($consulta->validarCadastro()){

					$consulta->salvar();

					$this->view->mensagem_sucesso = 'Consulta marcada com sucesso!';
					
					$this->render('cadastro_sucesso', 'layout_painel');

				}else{

					header('Location:/agendar_consulta?erro=true');
				}	
				
			}catch(\Error){

				header('Location: /erro_banco');
			}
		}		

		//UTILIDADES PARA O SISTEMA
		
		public function validaAutenticacao($usuario){

			session_start();

			if($usuario == 'cliente'){

				if(empty($_SESSION['cpf_cli'])){

					header('Location: /');
				}

			}else if($usuario == 'veterinario'){

				if(empty($_SESSION['cpf_vet'])){

					header('Location: /');
				}
			}			
		}			

		public function validaAutenticacaoEspecial(){	
			
			session_start();

			if($_SESSION['cpf_vet'] != '32222215021'){

				header('Location: /');
			}
		}

		public function verificaConexaoBanco(){	

			try{
			
				$cliente = Container::getModel('cliente');

			}catch(\Error){

				header('Location: /erro_banco');
			}
		}

		public function set_layout_painel($usuario){			

			if($usuario == 'cliente'){

				$nome = $_SESSION['nome_cli'];
				$nome = strstr($nome, ' ', true);

				$this->view->link_menu = '/painel_cli';
				$this->view->saudacao = 'Olá ' . $nome . '!';
			}

			if($usuario == 'veterinario'){

				$nome = $_SESSION['nome_vet'];
				$nome = strstr($nome, ' ', true);

				$this->view->link_menu = '/painel_vet';
				$this->view->saudacao = 'Olá Dr(a) ' . $nome . '!';
			}
		}
	}

?>