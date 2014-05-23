<?php

/**
 * BreezeAdmin.spanish_latin
 *
 * @package Breeze mod
 * @version 1.0
 * @author Jessica Gonzalez <suki@missallsunday.com>
 * @copyright Copyright (c) 2011, 2014 Jessica Gonzalez
 * @license http://www.mozilla.org/MPL/MPL-1.1.html
 */

global $txt;

// Admin Settings
$txt['Breeze_page_panel'] = 'Breeze Panel de administraci&oacute;n';
$txt['Breeze_page_welcome'] = 'Este es tu panel de administraci&oacute;n.  Desde aqu&iacute; puedes editar las diversas configuraciones, si tienes alg&uacute;n problema puedes <a href="http://missallsunday.com" target="_blank" class="new_win">solicitar soporte</a> en el sitio de soporte del autor.';
$txt['Breeze_page_main'] = 'P&aacute;gina principal panel de administraci&oacute;n';
$txt['Breeze_page_permissions'] = 'Permisos';
$txt['Breeze_page_permissions_desc'] = 'Desde aqu&iacute; puedes From here you can add/remove specific Breeze permissions.';
$txt['Breeze_page_settings'] = 'Configuraci&oacute;n general';
$txt['Breeze_page_settings_desc'] = 'Esta es la p&aacute;gina de configuraci&oacute;n general, como su nombre lo indica, desde aqu&iacute; puedes configurar las diversas opciones que tienenel mod.';
$txt['Breeze_page_donate'] = 'Donar';
$txt['Breeze_page_donate_desc'] = 'Una p&aacute;gina aburrida que ten&iacute;as curiosidad por ver y ahora que ya lo has hecho jam&aacute;s vas a volver a verla :P';
$txt['Breeze_page_donate_exp'] = 'Breeze es una modificaci&oacute;n totalmente gratuita hecha por una entusiasta en su tiempo libre.<p />Si te gust&oacute; esta modificaci&oacute;n y quieres mostrar tu apreciaci&oacute;n, por favor concidera hacer una <a href="http://missallsunday.com/">donaci&oacute;n</a>. Tu donaci&oacute;n servir&aacute; para cubrir los gastos del servicio de hospedaje web o para comprar zapatos. Los zapatos mantienen fel&iacute;z a la desarrolladora y si ella es fel&iacute;z entonces habr&aacute; m&aacute;s y mejores versiones del mod ;)<p />Tambi&eacute;n puedes mostrar tu apreciaci&oacute;n visitando mi sitio y  dejando alg&uacute;n mensaje de agradecimiento y de paso mostrarme tu flamante foro mejorado con Breeze.';
$txt['Breeze_page_credits'] = 'Creditos';
$txt['Breeze_page_credits_decs'] = 'Breeze usa los siguientes &iacute;conos o scripts:';
$txt['Breeze_enable_general_wall'] = 'Activar la p&aacute;gina "muro general"';
$txt['Breeze_enable_general_wall_sub'] = 'Si es activada, esta opci&oacute;n mostrar&aacute; una nueva p&aacute;gina en donde se le mostrar&aacute; a el usuario los ultimos status y la actividad reciente de sus amigos.';
$txt['Breeze_menu_position'] = 'Selecciona la posici&oacute;n para el bot&oacute;n de la p&aacute;gina general.';
$txt['Breeze_menu_position_sub'] = 'Por defecto se coloca a un lado del bot&oacute;n "Home".';
$txt['Breeze_master'] = 'Activar el mod';
$txt['Breeze_master_sub'] = 'La opci&oacute;n principal, tene que estar activada para que el mod funcione correctamente.';
$txt['Breeze_force_enable'] = 'Forzar la activaci&oacute;n del muro en todos los perfiles de usuario.';
$txt['Breeze_force_enable_sub'] = 'Por defecto, el muro de cada usuario est&aacute; desactivado hasta que ellos lo activen, si seleccionas esta opci&oacute;n todos los muros de todos tus usuarios registrados hasta el momento estar&aacute;n activados, ten en cuenta que esta opci&oacute;n activa el muro de todos tus usuario incluidos los usuarios inactivos, los bots o los spammers.<br /> Tus usuarios a&uacute;n pueden desactivar su propio muro si as&iacute; lo desean, esta opci&oacute;n s&oacute;lo activa su muro pero no fuerza a tenerlo siempre activado.';
$txt['Breeze_force_enable_on'] = 'Activar';
$txt['Breeze_force_enable_off'] = 'Desactivar';
$txt['Breeze_likes'] = 'Activar la opci&oacute;n "me gusta" en los status y comentarios.';
$txt['Breeze_likes_sub'] = 'Tus usuarios podr&aacute;n usar la funci&oacute;n en cualquier estatus o comentario y si los usuarios tienen activadas sus preferencias de alertas, recibir&aacute;n una alerta cada vez que alguien le guste un comentario o estatus.';
$txt['Breeze_notifications'] = 'Habilitar notificaciones';
$txt['Breeze_notifications_sub'] = 'Tus usuarios podr&aacute;n activar sus propias notificaciones para eventos espec&iacute;ficos.';
$txt['Breeze_parseBBC'] = 'Activar el parser de SMF';
$txt['Breeze_parseBBC_sub'] = 'Si se activa, tus usuarios podr&aacute;n usar c&oacute;digo BBC en sus status y comentarios.<br />Ten en cuenta que tener activada esta opci&oacute;n puede generar problmas en sitios muy activos.';
$txt['Breeze_mention'] = 'Activar las menciones.';
$txt['Breeze_mention_sub'] = 'Tus usuarios podr&aacute;n mencionar a otros usuarios en sus status y comentarios.';
$txt['Breeze_mention_limit'] = '&iquest;Cuantos usuarios se pueden mencionar en un solo mensaje?';
$txt['Breeze_mention_limit_sub'] = 'Si el usuario trata de mencionar a m&aacute;s usuarios de los permitidos solo ser&aacute;n mencionados la misma cantidad de usuarios que tu elijas.';
$txt['Breeze_version'] = 'Breeze version';
$txt['Breeze_live'] = 'En vivo desde el sitio de soporte...';
$txt['Breeze_allowed_actions'] = 'escribe las acciones en donde quieres que aparezcan las notificaciones.';
$txt['Breeze_allowed_actions_sub'] = 'Por defecto las notificaciones aparecer&aacute;n en las siguientes acciones: '. implode(', ', Breeze::$_allowedActions) .'. adem&aacute;s de el &iacute;ndice de foros, el &iacute;ndice de mensajes en los temas y en los foros. <br /> por favor agrega tus acciones separadas por una coma, ejemplo, acci&oacute;n, acci&oacute;n, acci&oacute;n, acci&oacute;n';
$txt['Breeze_feed_error_message'] = 'Breeze no pudo conectarse con el sitio de soporte.';
$txt['Breeze_allowed_maxlength_aboutMe'] = 'El m&aacute;ximo n&uacute;mero de caracteres que el bloque "acerca de mi" puede contener';
$txt['Breeze_allowed_maxlength_aboutMe_sub'] = 'Si se deja vacio, se usar&aacute; el valor por defecto: 1024';

