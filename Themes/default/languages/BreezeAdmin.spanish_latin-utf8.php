<?php

/**
 * BreezeAdmin.spanish_latin-utf8
 *
 * @package Breeze mod
 * @version 1.0
 * @author Jessica Gonzalez <suki@missallsunday.com>
 * @copyright Copyright (c) 2011, 2014 Jessica Gonzalez
 * @license http://www.mozilla.org/MPL/MPL-1.1.html
 */

global $txt;

// Admin Settings
$txt['Breeze_page_panel'] = 'Breeze Panel de administración';
$txt['Breeze_page_welcome'] = 'Este es tu panel de administración.  Desde aquí puedes editar las diversas configuraciones, si tienes algún problema puedes <a href="http://missallsunday.com" target="_blank" class="new_win">solicitar soporte</a> en el sitio de soporte del autor.';
$txt['Breeze_page_main'] = 'Página principal panel de administración';
$txt['Breeze_page_permissions'] = 'Permisos';
$txt['Breeze_page_permissions_desc'] = 'Desde aquí puedes From here you can add/remove specific Breeze permissions.';
$txt['Breeze_page_settings'] = 'Configuración general';
$txt['Breeze_page_settings_desc'] = 'Esta es la página de configuración general, como su nombre lo indica, desde aquí puedes configurar las diversas opciones que tienenel mod.';
$txt['Breeze_page_donate'] = 'Donar';
$txt['Breeze_page_donate_desc'] = 'Una página aburrida que tenías curiosidad por ver y ahora que ya lo has hecho jamás vas a volver a verla :P';
$txt['Breeze_page_donate_exp'] = 'Breeze es una modificación totalmente gratuita hecha por una entusiasta en su tiempo libre.<p />Si te gustó esta modificación y quieres mostrar tu apreciación, por favor concidera hacer una <a href="http://missallsunday.com/">donación</a>. Tu donación servirá para cubrir los gastos del servicio de hospedaje web o para comprar zapatos. Los zapatos mantienen felíz a la desarrolladora y si ella es felíz entonces habrá más y mejores versiones del mod ;)<p />También puedes mostrar tu apreciación visitando mi sitio y  dejando algún mensaje de agradecimiento y de paso mostrarme tu flamante foro mejorado con Breeze.';
$txt['Breeze_page_credits'] = 'Creditos';
$txt['Breeze_page_credits_decs'] = 'Breeze usa los siguientes íconos o scripts:';
$txt['Breeze_enable_general_wall'] = 'Activar la página "muro general"';
$txt['Breeze_enable_general_wall_sub'] = 'Si es activada, esta opción mostrará una nueva página en donde se le mostrará a el usuario los ultimos status y la actividad reciente de sus amigos.';
$txt['Breeze_menu_position'] = 'Selecciona la posición para el botón de la página general.';
$txt['Breeze_menu_position_sub'] = 'Por defecto se coloca a un lado del botón "Home".';
$txt['Breeze_master'] = 'Activar el mod';
$txt['Breeze_master_sub'] = 'La opción principal, tene que estar activada para que el mod funcione correctamente.';
$txt['Breeze_force_enable'] = 'Forzar la activación del muro en todos los perfiles de usuario.';
$txt['Breeze_force_enable_sub'] = 'Por defecto, el muro de cada usuario está desactivado hasta que ellos lo activen, si seleccionas esta opción todos los muros de todos tus usuarios registrados hasta el momento estarán activados, ten en cuenta que esta opción activa el muro de todos tus usuario incluidos los usuarios inactivos, los bots o los spammers.<br /> Tus usuarios aún pueden desactivar su propio muro si así lo desean, esta opción sólo activa su muro pero no fuerza a tenerlo siempre activado.';
$txt['Breeze_force_enable_on'] = 'Activar';
$txt['Breeze_force_enable_off'] = 'Desactivar';
$txt['Breeze_likes'] = 'Activar la opción "me gusta" en los status y comentarios.';
$txt['Breeze_likes_sub'] = 'Tus usuarios podrán usar la función en cualquier estatus o comentario y si los usuarios tienen activadas sus preferencias de alertas, recibirán una alerta cada vez que alguien le guste un comentario o estatus.';
$txt['Breeze_notifications'] = 'Habilitar notificaciones';
$txt['Breeze_notifications_sub'] = 'Tus usuarios podrán activar sus propias notificaciones para eventos específicos.';
$txt['Breeze_parseBBC'] = 'Activar el parser de SMF';
$txt['Breeze_parseBBC_sub'] = 'Si se activa, tus usuarios podrán usar código BBC en sus status y comentarios.<br />Ten en cuenta que tener activada esta opción puede generar problmas en sitios muy activos.';
$txt['Breeze_mention'] = 'Activar las menciones.';
$txt['Breeze_mention_sub'] = 'Tus usuarios podrán mencionar a otros usuarios en sus status y comentarios.';
$txt['Breeze_mention_limit'] = '¿Cuantos usuarios se pueden mencionar en un solo mensaje?';
$txt['Breeze_mention_limit_sub'] = 'Si el usuario trata de mencionar a más usuarios de los permitidos solo serán mencionados la misma cantidad de usuarios que tu elijas.';
$txt['Breeze_version'] = 'Breeze version';
$txt['Breeze_live'] = 'En vivo desde el sitio de soporte...';
$txt['Breeze_allowed_actions'] = 'escribe las acciones en donde quieres que aparezcan las notificaciones.';
$txt['Breeze_allowed_actions_sub'] = 'Por defecto las notificaciones aparecerán en las siguientes acciones: '. implode(', ', Breeze::$_allowedActions) .'. además de el índice de foros, el índice de mensajes en los temas y en los foros. <br /> por favor agrega tus acciones separadas por una coma, ejemplo, acción, acción, acción, acción';
$txt['Breeze_feed_error_message'] = 'Breeze no pudo conectarse con el sitio de soporte.';
$txt['Breeze_allowed_maxlength_aboutMe'] = 'El máximo número de caracteres que el bloque "acerca de mi" puede contener';
$txt['Breeze_allowed_maxlength_aboutMe_sub'] = 'Si se deja vacio, se usará el valor por defecto: 1024';
