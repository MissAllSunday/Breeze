<?php

/**
 * BreezeAdmin.Portuguese_Brazilian
 * PT-BR Translation by: FreitasA
 * @package Breeze mod
 * @version 1.0.13
 * @author Jessica Gonzalez <suki@missallsunday.com>
 * @copyright Copyright (c) 2011 - 2018 Jessica Gonzalez
 * @license //www.mozilla.org/MPL/MPL-1.1.html
 */

global $txt;

// Admin Settings
$txt['Breeze_page_panel'] = 'Breeze Painel de administra&#231;&#227;o';
$txt['Breeze_page_welcome'] = 'Este &#233; seu painel de administra&#231;&#227;o.  Apartir daqui pode realizar diversas configura&#231;&#245;es, se tiver algum problema pode <a href="'. Breeze::$supportSite .'" target="_blank" class="new_win">solicitar soporte</a> no site de suporte do autor.';
$txt['Breeze_page_main'] = 'P&#225;gina principal do painel administrativo';
$txt['Breeze_page_permissions'] = 'Permiss&#245;es';
$txt['Breeze_page_permissions_desc'] = 'Apartir daqui voc&#234; pode add/remove especificas do Breeze Mod.';
$txt['Breeze_page_settings'] = 'Configura&#231;&#227;o geral';
$txt['Breeze_page_settings_desc'] = 'Esta &#233; a p&#225;gina de configura&#231;&#227;o geral, como seu nome indica, apartir daqu&#237; pode configurar as diversas op&#231;&#245;es que existem no mod.';
$txt['Breeze_page_donate'] = 'Doar';
$txt['Breeze_page_donate_desc'] = 'Uma p&#225;gina in&#250;til que teve a curiosidade de ver e agora que fez isso jamais vai voltar a visita-la :P';
$txt['Breeze_page_donate_exp'] = 'Breeze &#233; uma modifica&#231;&#227;o totalmente gratuita feita por uma entusiasta em seu tempo livre.<p />Se gostou desta modifica&#231;&#227;o e quer mostrar sua gratid&#227;o, por favor concidere fazer uma <a href="'. Breeze::$supportSite .'">doa&#231;&#227;o</a>. Sua doa&#231;&#227;o servir&#225; para cubrir os gastos de servi&#231;o de hospedagem web ou para comprar sapatos. Os sapatos mantem fel&#237;z a criadora e se ela &#233; fel&#237;z ent&#227;o haver&#225; mais e melhores vers&#245;es do mod ;)<p />Tambem pode mostrar sua gratid&#227;o visitando meu site e  deixando alguma mensagem de agradecimiento e de b&#244;nus mostrar seu f&#243;rum melhorado com Breeze.';
$txt['Breeze_page_credits'] = 'Creditos';
$txt['Breeze_page_credits_decs'] = 'Breeze usa os seguintes icones e scripts:';
$txt['Breeze_enable_general_wall'] = 'Ativar a p&#225;gina "mural geral"';
$txt['Breeze_enable_general_wall_sub'] = 'Se &#233; ativada, esta op&#231;&#227;o mostrar&#225; umaa nova p&#225;gina onde se mostrar&#225; aos usuario os ultimos status a atividade recente de seus amigos.';
$txt['Breeze_menu_position'] = 'Selecione a posi&#231;&#227;o para o bot&#227;o da p&#225;gina geral.';
$txt['Breeze_menu_position_sub'] = 'Por padr&#227;o se coloca ao lado do bot&#227;o "Home".';
$txt['Breeze_master'] = 'Ativar o mod';
$txt['Breeze_master_sub'] = 'A op&#231;&#227;o principal, tem que estar ativada para que o mod funcione corretamente.';
$txt['Breeze_force_enable'] = 'For&#231;ar a ativa&#231;&#227;o do mural em todos os perfis de usuarios.';
$txt['Breeze_force_enable_sub'] = 'Por padr&#227;o, o mural de cada usuario est&#225; desativado at&#233; que eles o ativem, se selecionar esta opc&#231;&#227;o todos os murais de todos seus usuarios registrados at&#233; o momento estaram ativados, tenha em mente que esta op&#231;&#227;o ativa o mural de todos seus usuarios incluindo os usuarios inativos, os bots o os spammers.<br /> seus usuarios ainda podem desativar seu propio mural se assim desejarem, esta op&#231;&#227;o s&#243; ativa seu mural porem n&#227;o for&#231;a a deixa-lo sempre ativado.';
$txt['Breeze_force_enable_on'] = 'Ativar';
$txt['Breeze_force_enable_off'] = 'Desativar';
$txt['Breeze_notifications'] = 'Habilitar notifica&#231;&#245;es';
$txt['Breeze_notifications_sub'] = 'Seus usuarios podem ativar suas proprias notifica&#231;&#245;es para eventos espec&#237;ficos.';
$txt['Breeze_parseBBC'] = 'Ativar o parser de SMF';
$txt['Breeze_parseBBC_sub'] = 'Se ativar, seus usuarios poderam usar c&#243;digo BBC em seus status e comentarios.<br />Tenha em mente que ter ativada esta op&#231;&#227;o ppde gerar problemas em sites muito ativos.';
$txt['Breeze_mention'] = 'Ativar as men&#231;&#245;es.';
$txt['Breeze_mention_sub'] = 'Seus usuarios podem mencionar a outros usuarios em seus status e comentarios.';
$txt['Breeze_mention_limit'] = 'Quantos usuarios pode-se mencionar em uma s&#243; mensagem?';
$txt['Breeze_mention_limit_sub'] = 'Se o usuario mencionar mais usuarios do que permitidos s&#243; ser&#227;o mencionados a mesma quantidade de usuarios que voc&#234; configurou.';
$txt['Breeze_version'] = 'Breeze version';
$txt['Breeze_live'] = 'Conectado com o site de suporte...';
$txt['Breeze_allowed_actions'] = 'escreva as a&#231;&#245;es onde quer que apare&#231;&#227;o as notifica&#231;&#245;es.';
$txt['Breeze_allowed_actions_sub'] = 'Por padr&#227;o as notifica&#231;&#245;es apareceram nas siguintes a&#231;&#245;es '. implode(', ', Breeze::$_allowedActions) .'. tambem do &#237;ndice de for&#250;ns, o &#237;ndice de mensagens  nos t&#243;picos e nos f&#243;runs. <br /> por favor adicione suas a&#231;&#245;es separadas por uma virgula, exemplo, a&#231;&#227;o, a&#231;&#227;o, a&#231;&#227;o, a&#231;&#227;o';
$txt['Breeze_feed_error_message'] = 'Breeze n&#227;o consegiu se conectar ao site de suporte.';
$txt['Breeze_allowed_maxlength_aboutMe'] = 'O numero m&#225;ximo de caracteres que o bloco "sobre mim" pode conter';
$txt['Breeze_allowed_maxlength_aboutMe_sub'] = 'Se deixar vazio, se usar&#225; o valor por padr&#227;o: 1024';
