# Sistema VetPet
[![NPM](https://img.shields.io/npm/l/react)](https://github.com/leogomes88/sistema_vetpet/blob/main/LICENSE) 

Entrada de clientes: http://leodeswebvet.epizy.com/

Entrada de veterinários: http://leodeswebvet.epizy.com/login_vet

# Sobre o projeto

  Sistema VetPet é uma aplicação full stack web totalmente responsiva para uma clínica veterinária fictícia, construída com o intuito de demonstrar conhecimentos em desenvolvimento web.
  
A aplicação engloba o site de divulgação da clínica, cadastro e agendamento de consultas para os clientes, controle de agenda e controle de consultas para os veterinários, além de um dashboard do sistema para um veterinário usuário especial.

O software conta com páginas dinâmicas, boa resposta aos usuários, filtro nos dados, tratamento de erros, entre outros requisitos necessários para as aplicações modernas. 

## Alguns requisitos funcionais empregados

- Cadastro de clientes com controle de cpf e email já cadastrados no sistema, cpf válido e utilização de criptografia na senha criada
- Cadastro de pets com controle de nome já cadastrado pelo cliente
- Cadastro de veterinários permitido apenas para um veterinário usuário especial. Com controle de cpf, email e crmv já cadastrados no sistema, cpf válido e utilização de criptografia na senha criada
- Agendamento de consultas com até 60 dias de antecedência, e criação de um lembrete da data e horário da consulta para o cliente
- Agendamento de consultas não é permitido nos finais de semana
- Controle de agenda para os veterinários, com acesso as consultas marcadas, horários livres e bloqueados dos próximos 30 dias
- O sistema deve possibilitar a execução de consultas de emergência a qualquer horário dentro dos plantões, inclusive nos finais de semana
- O sistema deve permitir o cancelamento de consultas pelo veterinário apenas para consultas em que faltam mais de 24h para execução da mesma, além de gerar um aviso para o cliente que teve a consulta cancelada
- O sistema deve permitir o bloqueio de horários livres, ou seja, sem consultas marcadas, por parte dos veterinários para eventuais compromissos
- O sistema deve permitir o desbloqueio de horários pelos veterinários, assim liberando o horário para o agendamento de nova consulta
- Entre outros 


## Exemplos do layout mobile
![Mobile 1](https://github.com/leogomes88/sistema_vetpet/blob/main/assets/mobile1.png) ![Mobile 2](https://github.com/leogomes88/sistema_vetpet/blob/main/assets/mobile2.png)

![Mobile 3](https://github.com/leogomes88/sistema_vetpet/blob/main/assets/mobile3.png) ![Mobile 4](https://github.com/leogomes88/sistema_vetpet/blob/main/assets/mobile4.png)

![Mobile 5](https://github.com/leogomes88/sistema_vetpet/blob/main/assets/mobile5.png) ![Mobile 6](https://github.com/leogomes88/sistema_vetpet/blob/main/assets/mobile6.png)

![Mobile 7](https://github.com/leogomes88/sistema_vetpet/blob/main/assets/mobile7.png) ![Mobile 8](https://github.com/leogomes88/sistema_vetpet/blob/main/assets/mobile8.png)


## Exemplos do layout web
![Web 1](https://github.com/leogomes88/sistema_vetpet/blob/main/assets/web1.png)

![Web 2](https://github.com/leogomes88/sistema_vetpet/blob/main/assets/web2.png)

![Web 3](https://github.com/leogomes88/sistema_vetpet/blob/main/assets/web3.png)

![Web 4](https://github.com/leogomes88/sistema_vetpet/blob/main/assets/web4.png)

![Web 5](https://github.com/leogomes88/sistema_vetpet/blob/main/assets/web5.png)

![Web 6](https://github.com/leogomes88/sistema_vetpet/blob/main/assets/web6.png)

![Web 7](https://github.com/leogomes88/sistema_vetpet/blob/main/assets/web7.png)

![Web 8](https://github.com/leogomes88/sistema_vetpet/blob/main/assets/web8.png)

![Web 9](https://github.com/leogomes88/sistema_vetpet/blob/main/assets/web9.png)

## Modelo conceitual
![Modelo Conceitual](https://github.com/leogomes88/sistema_vetpet/blob/main/assets/conceito_diagrama_classes.png)

# Tecnologias utilizadas

O sistema foi desenvolvido com o padrão de arquitetura MVC, construído sobre um mini-framework que utiliza o gerenciador de dependências Composer. O banco de dados fornecido é composto de dados fictícios e aleatórios, e para maior análise acompanha o arquivo 'Emails_senhas_clientes_veterinarios.txt', com os usuários e suas respectivas senhas. 

## Back end
- PHP
- Banco de dados MySQL
## Front end
- HTML
- CSS
- JavaScript
- Bootstrap
## Pré-requisitos para execução da aplicação
- PHP 7.0 ou versão mais atual


# Autor

Leonardo Henrique Gomes

https://www.linkedin.com/in/leohgomes/
