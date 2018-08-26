<?php

/**
 * BreezeAdmin.Portuguese_Brazilian-utf8
 * PT-BR Translation by: FreitasA
 * @package Breeze mod
 * @version 1.0.14
 * @author Jessica Gonzalez <suki@missallsunday.com>
 * @copyright Copyright (c) 2011 - 2018 Jessica Gonzalez
 * @license //www.mozilla.org/MPL/MPL-1.1.html
 */

global $txt;

// Admin Settings
$txt['Breeze_page_panel'] = 'Breeze Painel de administração';
$txt['Breeze_page_welcome'] = 'Este é seu painel de administração.  Apartir daqui pode realizar diversas configurações, se tiver algum problema pode <a href="'. Breeze::$supportSite .'" target="_blank" class="new_win">solicitar soporte</a> no site de suporte do autor.';
$txt['Breeze_page_main'] = 'Página principal do painel administrativo';
$txt['Breeze_page_permissions'] = 'Permissões';
$txt['Breeze_page_permissions_desc'] = 'Apartir daqui você pode add/remove especificas do Breeze Mod.';
$txt['Breeze_page_settings'] = 'Configuração geral';
$txt['Breeze_page_settings_desc'] = 'Esta é a página de configuração geral, como seu nome indica, apartir daquí pode configurar as diversas opções que existem no mod.';
$txt['Breeze_page_donate'] = 'Doar';
$txt['Breeze_page_donate_desc'] = 'Uma página inútil que teve a curiosidade de ver e agora que fez isso jamais vai voltar a visita-la :P';
$txt['Breeze_page_donate_exp'] = 'Breeze é uma modificação totalmente gratuita feita por uma entusiasta em seu tempo livre.<p />Se gostou desta modificação e quer mostrar sua gratidão, por favor concidere fazer uma <a href="'. Breeze::$supportSite .'">doação</a>. Sua doação servirá para cubrir os gastos de serviço de hospedagem web ou para comprar sapatos. Os sapatos mantem felíz a criadora e se ela é felíz então haverá mais e melhores versões do mod ;)<p />Tambem pode mostrar sua gratidão visitando meu site e  deixando alguma mensagem de agradecimiento e de bônus mostrar seu fórum melhorado com Breeze.';
$txt['Breeze_page_credits'] = 'Creditos';
$txt['Breeze_page_credits_decs'] = 'Breeze usa os seguintes icones e scripts:';
$txt['Breeze_enable_general_wall'] = 'Ativar a página "mural geral"';
$txt['Breeze_enable_general_wall_sub'] = 'Se é ativada, esta opção mostrará umaa nova página onde se mostrará aos usuario os ultimos status a atividade recente de seus amigos.';
$txt['Breeze_menu_position'] = 'Selecione a posição para o botão da página geral.';
$txt['Breeze_menu_position_sub'] = 'Por padrão se coloca ao lado do botão "Home".';
$txt['Breeze_master'] = 'Ativar o mod';
$txt['Breeze_master_sub'] = 'A opção principal, tem que estar ativada para que o mod funcione corretamente.';
$txt['Breeze_force_enable'] = 'Forçar a ativação do mural em todos os perfis de usuarios.';
$txt['Breeze_force_enable_sub'] = 'Por padrão, o mural de cada usuario está desativado até que eles o ativem, se selecionar esta opcção todos os murais de todos seus usuarios registrados até o momento estaram ativados, tenha em mente que esta opção ativa o mural de todos seus usuarios incluindo os usuarios inativos, os bots o os spammers.<br /> seus usuarios ainda podem desativar seu propio mural se assim desejarem, esta opção só ativa seu mural porem não força a deixa-lo sempre ativado.';
$txt['Breeze_force_enable_on'] = 'Ativar';
$txt['Breeze_force_enable_off'] = 'Desativar';
$txt['Breeze_notifications'] = 'Habilitar notificações';
$txt['Breeze_notifications_sub'] = 'Seus usuarios podem ativar suas proprias notificações para eventos específicos.';
$txt['Breeze_parseBBC'] = 'Ativar o parser de SMF';
$txt['Breeze_parseBBC_sub'] = 'Se ativar, seus usuarios poderam usar código BBC em seus status e comentarios.<br />Tenha em mente que ter ativada esta opção ppde gerar problemas em sites muito ativos.';
$txt['Breeze_mention'] = 'Ativar as menções.';
$txt['Breeze_mention_sub'] = 'Seus usuarios podem mencionar a outros usuarios em seus status e comentarios.';
$txt['Breeze_mention_limit'] = 'Quantos usuarios pode-se mencionar em uma só mensagem?';
$txt['Breeze_mention_limit_sub'] = 'Se o usuario mencionar mais usuarios do que permitidos só serão mencionados a mesma quantidade de usuarios que você configurou.';
$txt['Breeze_version'] = 'Breeze version';
$txt['Breeze_live'] = 'Conectado com o site de suporte...';
$txt['Breeze_allowed_actions'] = 'escreva as ações onde quer que apareção as notificações.';
$txt['Breeze_allowed_actions_sub'] = 'Por padrão as notificações apareceram nas siguintes ações '. implode(', ', Breeze::$_allowedActions) .'. tambem do índice de forúns, o índice de mensagens  nos tópicos e nos fóruns. <br /> por favor adicione suas ações separadas por uma virgula, exemplo, ação, ação, ação, ação';
$txt['Breeze_feed_error_message'] = 'Breeze não consegiu se conectar ao site de suporte.';
$txt['Breeze_allowed_maxlength_aboutMe'] = 'O numero máximo de caracteres que o bloco "sobre mim" pode conter';
$txt['Breeze_allowed_maxlength_aboutMe_sub'] = 'Se deixar vazio, se usará o valor por padrão: 1024';
