<?php 

	namespace App;

	use MF\Init\Bootstrap;

	class Route extends Bootstrap{		

		protected function initRoutes(){

			//PÁGINAS DE APRESENTAÇÃO

			$routes['home'] = array(

				'route' => '/',
				'controller' => 'indexController',
				'action' => 'index'
			);	

			$routes['quem_somos'] = array(

				'route' => '/quem_somos',
				'controller' => 'indexController',
				'action' => 'quem_somos'
			);

			$routes['servicos'] = array(

				'route' => '/servicos',
				'controller' => 'indexController',
				'action' => 'servicos'
			);	

			$routes['contato_endereco'] = array(

				'route' => '/contato_endereco',
				'controller' => 'indexController',
				'action' => 'contato_endereco'
			);	

			//ROTAS PARA CLIENTES

			$routes['cadastrar_cli'] = array(

				'route' => '/cadastrar_cli',
				'controller' => 'AuthController',
				'action' => 'cadastrar_cli'
			);	
			
			$routes['cadastrar_cli2'] = array(

				'route' => '/cadastrar_cli2',
				'controller' => 'AuthController',
				'action' => 'cadastrar_cli2'
			);

			$routes['login_cli'] = array(

				'route' => '/login_cli',
				'controller' => 'AuthController',
				'action' => 'login_cli'
			);			

			$routes['autenticar_cli'] = array(

				'route' => '/autenticar_cli',
				'controller' => 'AuthController',
				'action' => 'autenticar_cli'
			);

			$routes['painel_cli'] = array(

				'route' => '/painel_cli',
				'controller' => 'AppController',
				'action' => 'painel_cli'
			);

			$routes['verifica_avisos'] = array(

				'route' => '/verifica_avisos',
				'controller' => 'AppController',
				'action' => 'verifica_avisos'
			);

			$routes['avisos_visualizados'] = array(

				'route' => '/avisos_visualizados',
				'controller' => 'AppController',
				'action' => 'avisos_visualizados'
			);

			$routes['cadastrar_pet'] = array(

				'route' => '/cadastrar_pet',
				'controller' => 'AppController',
				'action' => 'cadastrar_pet'
			);	
			
			$routes['cadastrar_pet2'] = array(

				'route' => '/cadastrar_pet2',
				'controller' => 'AppController',
				'action' => 'cadastrar_pet2'
			);			

			$routes['verifica_data_horario'] = array(

				'route' => '/verifica_data_horario',
				'controller' => 'AppController',
				'action' => 'verifica_data_horario'
			);

			$routes['verifica_veterinarios'] = array(

				'route' => '/verifica_veterinarios',
				'controller' => 'AppController',
				'action' => 'verifica_veterinarios'
			);
			
			$routes['agendar_consulta'] = array(

				'route' => '/agendar_consulta',
				'controller' => 'AppController',
				'action' => 'agendar_consulta'
			);

			$routes['agendar_consulta2'] = array(

				'route' => '/agendar_consulta2',
				'controller' => 'AppController',
				'action' => 'agendar_consulta2'
			);

			//ROTAS PARA USUÁRIO VETERINARIO ESPECIAL

			$routes['cadastrar_vet'] = array(

				'route' => '/cadastrar_vet',
				'controller' => 'AppController',
				'action' => 'cadastrar_vet'
			);				
			
			$routes['cadastrar_vet2'] = array(
				
				'route' => '/cadastrar_vet2',
				'controller' => 'AppController',
				'action' => 'cadastrar_vet2'
			);
			
			$routes['dashboard'] = array(

				'route' => '/dashboard',
				'controller' => 'AppController',
				'action' => 'dashboard'
			);			

			$routes['dados_mes'] = array(

				'route' => '/dados_mes',
				'controller' => 'AppController',
				'action' => 'dados_mes'
			);

			$routes['recuperar_clientes'] = array(

				'route' => '/recuperar_clientes',
				'controller' => 'AppController',
				'action' => 'recuperar_clientes'
			);			
			
			//ROTAS PARA VETERINÁRIOS

			$routes['login_vet'] = array(

				'route' => '/login_vet',
				'controller' => 'AuthController',
				'action' => 'login_vet'
			);			

			$routes['autenticar_vet'] = array(

				'route' => '/autenticar_vet',
				'controller' => 'AuthController',
				'action' => 'autenticar_vet'
			);

			$routes['painel_vet'] = array(

				'route' => '/painel_vet',
				'controller' => 'AppController',
				'action' => 'painel_vet'
			);			

			$routes['controlar_agenda'] = array(

				'route' => '/controlar_agenda',
				'controller' => 'AppController',
				'action' => 'controlar_agenda'
			);

			$routes['recuperar_consultas'] = array(

				'route' => '/recuperar_consultas',
				'controller' => 'AppController',
				'action' => 'recuperar_consultas'
			);

			$routes['recuperar_horarios_livres'] = array(

				'route' => '/recuperar_horarios_livres',
				'controller' => 'AppController',
				'action' => 'recuperar_horarios_livres'
			);

			$routes['recuperar_horarios_bloqueados'] = array(

				'route' => '/recuperar_horarios_bloqueados',
				'controller' => 'AppController',
				'action' => 'recuperar_horarios_bloqueados'
			);

			$routes['cancelar_consultas'] = array(

				'route' => '/cancelar_consultas',
				'controller' => 'AppController',
				'action' => 'cancelar_consultas'
			);

			$routes['bloquear_horarios_livres'] = array(

				'route' => '/bloquear_horarios_livres',
				'controller' => 'AppController',
				'action' => 'bloquear_horarios_livres'
			);

			$routes['liberar_horarios_bloqueados'] = array(

				'route' => '/liberar_horarios_bloqueados',
				'controller' => 'AppController',
				'action' => 'liberar_horarios_bloqueados'
			);

			$routes['controlar_consultas'] = array(

				'route' => '/controlar_consultas',
				'controller' => 'AppController',
				'action' => 'controlar_consultas'
			);

			$routes['pesquisar_pets'] = array(

				'route' => '/pesquisar_pets',
				'controller' => 'AppController',
				'action' => 'pesquisar_pets'
			);

			$routes['prontuario'] = array(

				'route' => '/prontuario',
				'controller' => 'AppController',
				'action' => 'prontuario'
			);

			$routes['realizar_consulta'] = array(

				'route' => '/realizar_consulta',
				'controller' => 'AppController',
				'action' => 'realizar_consulta'
			);

			//UTILIDADES PARA O SISTEMA

			$routes['sair'] = array(

				'route' => '/sair',
				'controller' => 'AuthController',
				'action' => 'sair'
			);	

			$routes['erro_banco'] = array(

				'route' => '/erro_banco',
				'controller' => 'AuthController',
				'action' => 'erro_banco'
			);			

			$this->setRoutes($routes);
		}		
	}
?>