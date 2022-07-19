<?php 

	namespace App\Controllers;
	
	use MF\Controller\Action;
	use MF\Model\Container;
	use App\Models\Entities\Veterinario;
	use App\Models\Entities\Cliente;
	use App\Models\Entities\Pet;
	use App\Models\Entities\Consulta;

	class AppController extends Action{

		//FUNCIONALIDADES PARA OS VETERINÁRIOS

		public function painel_vet(){

			$this->verificaConexaoBanco();
			$this->validaAutenticacao('veterinario');	
			$this->set_layout_painel('veterinario');

			$this->view->usuario_especial = false;

			if(unserialize($_SESSION['veterinario'])->__get('cpf') == '32222215021'){
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
			$crmv = unserialize($_SESSION['veterinario'])->__get('crmv');

			$consultaDao = Container::getModelDao('consulta');
			echo json_encode($consultaDao->recuperarConsultas($intervalo, $crmv));			
		}

		public function recuperar_horarios_livres(){			

			$this->validaAutenticacao('veterinario');

			$intervalo = $_GET['intervalo'];			
			$crmv = unserialize($_SESSION['veterinario'])->__get('crmv');

			$consultaDao = Container::getModelDao('consulta');
			echo json_encode($consultaDao->recuperarHorariosLivres($intervalo, $crmv));			
		}

		public function recuperar_horarios_bloqueados(){			

			$this->validaAutenticacao('veterinario');

			$intervalo = $_GET['intervalo'];				
			$crmv = unserialize($_SESSION['veterinario'])->__get('crmv');

			$consultaDao = Container::getModelDao('consulta');
			echo json_encode($consultaDao->recuperarHorariosBloqueados($intervalo, $crmv));			
		}

		public function cancelar_consultas(){			

			$this->validaAutenticacao('veterinario');

			$consultas = explode(',', $_POST['consultas']);		

			$consultaDao = Container::getModelDao('consulta');	
			echo json_encode($consultaDao->cancelarConsultas($consultas));			
		}		

		public function bloquear_horarios_livres(){			

			$this->validaAutenticacao('veterinario');

			$crmv = unserialize($_SESSION['veterinario'])->__get('crmv');

			$datas_horarios = explode(',', $_POST['datas_horarios_livres']);			
			$datas_horarios_bloquear = array();

			foreach ($datas_horarios as $data_horario) {				
				$data_horario_bloquear = explode(' ', $data_horario);
				$datas_horarios_bloquear[] = ['data' => $data_horario_bloquear[0],  'horario' => $data_horario_bloquear[1]];
			}			

			$consultaDao = Container::getModelDao('consulta');	
			echo json_encode($consultaDao->bloquearHorarios($datas_horarios_bloquear, $crmv));
		}

		public function liberar_horarios_bloqueados(){			

			$this->validaAutenticacao('veterinario');

			$horarios_bloqueados = explode(',', $_POST['horarios_bloqueados']);		

			$consultaDao = Container::getModelDao('consulta');	
			echo json_encode($consultaDao->liberarHorarios($horarios_bloqueados));			
		}

		public function controlar_consultas(){

			$this->verificaConexaoBanco();
			$this->validaAutenticacao('veterinario');			
			$this->set_layout_painel('veterinario');
			$this->render('controlar_consultas', 'layout_painel');
		}

		public function pesquisar_pets(){			

			$this->validaAutenticacao('veterinario');
			
			$filtro = $_GET['pesquisa'];			
			
			$petDao = Container::getModelDao('pet');	
			echo json_encode($petDao->pesquisarPets($filtro));			
		}

		public function prontuario(){
			
			$this->validaAutenticacao('veterinario');				
			$this->set_layout_painel('veterinario');

			try{	
	
				if(!isset($_GET['id_pet']) || !isset($_GET['nome_pet']) || !isset($_GET['nome_tutor']) || !isset($_GET['raca']) || 
				!isset($_GET['especie']) || !isset($_GET['porte']) || !isset($_GET['data_nasc'])){
					header('Location: /controlar_consultas');
				}

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
				
				$id_pet = $_GET['id_pet'];

				$petDao = Container::getModelDao('pet');			

				$this->view->historico = $petDao->recuperarHistorico($id_pet);
				$this->render('prontuario', 'layout_painel');	
				
			}catch(\Error){
				header('Location: /erro_banco');
			}
		}

		public function realizar_consulta(){		
			
			$this->validaAutenticacao('veterinario');	
			
			$veterinario = unserialize($_SESSION['veterinario']);
			$pet = new Pet($_POST['id_pet'], null, null, null, null, null, null);
			$consulta = new Consulta(null, date('Y-m-d'), date('H:i'), $_POST['descricao'], 'realizada', $veterinario, $pet);

			$consultaDao = Container::getModelDao('consulta');	
			$petDao = Container::getModelDao('pet');			

			//realizar_consulta() insere a descrição da consulta caso a mesma já exista e esteja marcada para o dia atual
			if(!$consultaDao->realizarConsulta($consulta)){	
				//realizar_consulta_nao_agendada() cria uma nova consulta
				$consulta->__set('status', 'realizada sem marcar');
				$consultaDao->realizarConsultaNaoAgendada($consulta);
			}

			echo json_encode($petDao->recuperarHistorico($pet->__get('id')));
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

			try{	
				if(!isset($_POST['nome']) || !isset($_POST['cpf']) || !isset($_POST['telefone']) || !isset($_POST['endereco'])
				|| !isset($_POST['email']) || !isset($_POST['crmv']) || !isset($_POST['senha'])){
					header('Location: /cadastrar_vet');
				}

				$cpf = preg_replace("/[^0-9]/", "", $_POST['cpf']);
				$cpf = str_pad($cpf, 11, '0', STR_PAD_LEFT);

				$veterinario = new Veterinario($cpf, $_POST['nome'], $_POST['telefone'], $_POST['email'], $_POST['senha'], $_POST['endereco'], $_POST['crmv']);
				
				$veterinarioDao = Container::getModelDao('veterinario');				

				if($veterinarioDao->validarCadastro($veterinario)){					
					$veterinarioDao->salvar($veterinario);						
					
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
			
			$this->validaAutenticacaoEspecial();				
			$this->set_layout_painel('veterinario');
			
			try{				
				//recuperar meses de referência para análise no dashboard
				$dashboardDao = Container::getModelDao('dashboard');

				$idade_do_sistema = $dashboardDao->idadeDoSistema();				
				$data_ultima_consulta = $idade_do_sistema['data_ultima_consulta'];
				$data_consulta = $idade_do_sistema['data_primeira_consulta'];

				$lista_mes_ano = [];
				$lista_mes_ano[] = date('Y-m', strtotime($data_consulta));				
				
				while(date('Y-m', strtotime($data_ultima_consulta)) != date('Y-m', strtotime($data_consulta))){						
					$data_consulta = date('Y-m-d', strtotime($data_consulta . " + 1 month"));	
					$lista_mes_ano[] = date('Y-m', strtotime($data_consulta));				
				}

				$this->view->meses_referencia = $lista_mes_ano;
				
				$vetDao = Container::getModelDao('veterinario');

				$this->view->vets = $vetDao->recuperarVets();				
				$this->render('dashboard', 'layout_painel');
				
			}catch(\Error){
				header('Location: /erro_banco');
			}
		}			

		public function dados_mes(){			

			$this->validaAutenticacaoEspecial();

			$mes_referencia = $_GET['mes_referencia'];

			$dashboardDao = Container::getModelDao('dashboard');			
			echo json_encode($dashboardDao->dadosMes($mes_referencia));			
		}

		public function recuperar_clientes(){			

			$this->validaAutenticacaoEspecial();

			$filtro = $_GET['filtro'];

			$clienteDao = Container::getModelDao('cliente');
			echo json_encode($clienteDao->recuperarClientes($filtro));			
		}

		//FUNCIONALIDADES PARA OS CLIENTES

		public function painel_cli(){			
			
			$this->validaAutenticacao('cliente');
			$this->set_layout_painel('cliente');

			try{
				$this->view->pet_cadastrado = true;
				
				$petDao = Container::getModelDao('pet');
				$clienteDao = Container::getModelDao('cliente');

				$cpf = unserialize($_SESSION['cliente'])->__get('cpf');

				if(empty($petDao->recuperarPetsCli($cpf))){					
					$this->view->pet_cadastrado = false;
				}

				$consultas_marcadas = $clienteDao->verificarConsultasMarcadas($cpf);
				$this->view->consultas_marcadas = !empty($consultas_marcadas) ? $consultas_marcadas : '';
				$this->render('painel_cli', 'layout_painel');	
				
			}catch(\Error){
				header('Location: /erro_banco');
			}
		}
		
		public function verifica_avisos(){			

			$this->validaAutenticacao('cliente');

			$avisoDao = Container::getModelDao('aviso');
			echo json_encode($avisoDao->recuperarAvisos(unserialize($_SESSION['cliente'])->__get('cpf')));			
		}

		public function avisos_visualizados(){			

			$this->validaAutenticacao('cliente');

			$avisos_visualizados = explode(',', $_POST['avisos_visualizados']);

			$avisoDao = Container::getModelDao('aviso');
			echo json_encode($avisoDao->avisosVisualizados($avisos_visualizados));			
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

			try{		
				if(!isset($_POST['nome']) || !isset($_POST['data_nasc']) || !isset($_POST['raca']) || !isset($_POST['especie'])
				|| !isset($_POST['porte'])){
					header('Location: /cadastrar_pet');
				}

				$data_nasc = $_POST['data_nasc'];
				list($dia, $mes, $ano) = explode("/", $data_nasc);
				$data_nasc = $ano . '-' . $mes . '-' . $dia;

				$cliente = unserialize($_SESSION['cliente']);				
				$pet = new Pet(null, $_POST['nome'], $data_nasc, $_POST['raca'], $_POST['especie'], $_POST['porte'], $cliente);
				
				$petDao = Container::getModelDao('pet');

				if($petDao->validarCadastro($pet)){					
					$petDao->salvar($pet);
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

			$consultaDao = Container::getModelDao('consulta');
			$veterinarioDao = Container::getModelDao('veterinario');			
			echo json_encode($consultaDao->verificarDatasHorariosDisponiveis($_GET['data'], $veterinarioDao->recuperarVets()));			
		}

		public function verifica_veterinarios(){			

			$this->validaAutenticacao('cliente');

			$consultaDao = Container::getModelDao('consulta');	
			$veterinarioDao = Container::getModelDao('veterinario');
			echo json_encode($consultaDao->verificarVeterinariosDisponiveis($_GET['data'], $_GET['horario'], $veterinarioDao->recuperarVets()));			
		}

		public function agendar_consulta(){

			$this->validaAutenticacao('cliente');
			$this->set_layout_painel('cliente');	

			try{
				$this->view->erro = isset($_GET['erro']) ? $_GET['erro'] : false;				
				$this->view->data_atual = date('Y-m-d');

				//só é possivel marcar uma consulta com no máximo 60 dias de antecedência
				$intervalo_dias = 60;				
				$this->view->data_maxima = date('Y-m-d', strtotime($this->view->data_atual . "+{$intervalo_dias} days"));	

				$petDao = Container::getModelDao('pet');		 
				$this->view->pets = $petDao->recuperarPetsCli(unserialize($_SESSION['cliente'])->__get('cpf'));		
				$this->render('agendar_consulta', 'layout_painel');		
				
			}catch(\Error){
				header('Location: /erro_banco');
			}
		}

		public function agendar_consulta2(){	

			$this->validaAutenticacao('cliente');
			$this->set_layout_painel('cliente');

			try{

				$pet = new Pet($_POST['pet'], null, null, null, null, null, null);			
				$veterinario = new Veterinario(null, null, null, null, null, null, $_POST['veterinarios_d']);			
				$consulta = new Consulta(null, $_POST['data_consulta'], $_POST['horario_consulta'], null, null, $veterinario, $pet);			
				
				$consultaDao = Container::getModelDao('consulta');
				
				if($consultaDao->validarCadastro($consulta)){
					$consultaDao->salvar($consulta);
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

			if(!isset($_SESSION[$usuario])){
				header('Location: /');				
			}						
		}			

		public function validaAutenticacaoEspecial(){	
			
			session_start();

			if(!isset($_SESSION['veterinario']) || unserialize($_SESSION['veterinario'])->__get('cpf') != '32222215021'){
				header('Location: /');
			}
		}

		public function verificaConexaoBanco(){	

			try{			
				$clienteDao = Container::getModelDao('cliente');

			}catch(\Error){
				header('Location: /erro_banco');
			}
		}

		public function set_layout_painel($usuario){	
			
			$nome = unserialize($_SESSION[$usuario])->__get('nome');
			$nome = strstr($nome, ' ', true);

			if($usuario == 'cliente'){
				$this->view->link_menu = '/painel_cli';
				$this->view->saudacao = 'Olá ' . $nome . '!';

			}else if($usuario == 'veterinario'){
				$this->view->link_menu = '/painel_vet';
				$this->view->saudacao = 'Olá Dr(a) ' . $nome . '!';
			}			
		}
	}

?>