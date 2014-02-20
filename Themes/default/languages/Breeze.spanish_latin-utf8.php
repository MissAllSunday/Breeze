<?php

/**
 * Breeze.spanish_latin-utf8
 *
 * @package Breeze mod
 * @version 1.0
 * @author Jessica González <suki@missallsunday.com>
 * @copyright Copyright (c) 2011, 2014 Jessica González
 * @license http://www.mozilla.org/MPL/MPL-1.1.html
 */

global $txt;

// Public/General strings
$txt['Breeze_general_wall'] = 'Muro';
$txt['Breeze_general_wall_page'] = 'página';
$txt['Breeze_general_summary'] = 'Resumen';
$txt['Breeze_load_more'] = 'Mostrar más';
$txt['Breeze_admin'] = 'Panel de administración';
$txt['Breeze_general_my_wall'] = 'Mi muro';
$txt['Breeze_general_my_wall_settings'] = 'Configuración de mi muro';
$txt['Breeze_general_loading'] = 'Cargando...';
$txt['Breeze_general_like'] = 'Me gusta';
$txt['Breeze_general_last_view'] = 'Última visita';
$txt['Breeze_general_delete'] = 'Borrar';
$txt['Breeze_general_unlike'] = 'No me gusta';
$txt['Breeze_general_plural'] = '(s)';
$txt['Breeze_general_activity'] = 'Última actividad de mis amigos';
$txt['Breeze_general_latest_buddy_status'] = 'Últimos mensajes de mis amigos';
$txt['Breeze_general_posted_on'] = 'Escrito en el muro de %s';

// User Individual Settings
$txt['Breeze_profile'] = 'Configuración de mi muro';
$txt['Breeze_user_settings_name'] = 'Configuración de mi muro';
$txt['Breeze_user_settings_name_desc'] = 'Configura tu muro y otras opciones personales.';
$txt['Breeze_user_buddysettings_name'] = 'Solicitudes de amistad';
$txt['Breeze_user_single_status'] = 'Mensaje';
$txt['Breeze_user_buddyrequestmessage_name'] = 'Solicitud enviada';
$txt['Breeze_user_notisettings_name'] = 'Mis notificaciones';
$txt['Breeze_user_notilogs_name'] = 'Mi registro de actividad';
$txt['Breeze_user_settings_name_settings'] = 'Configuración de notificaciones';
$txt['Breeze_user_settings_name_settings_desc'] = 'Puedes configurar las distintas opciones para tus notificciones.';
$txt['Breeze_user_settings_kick_ignored'] = 'No mostrar mi muro a los usuarios en mi lita de ignorados';
$txt['Breeze_user_settings_kick_ignored_sub'] = 'Si seleccionas esta opción los usuarios en tu lista de ignorados no podrán ver tu muro ni escribir en el.';
$txt['Breeze_user_settings_load_more'] = 'Activar el botón: "Mostrar más"';
$txt['Breeze_user_settings_load_more_sub'] = 'Al activar esta opción podrás reemplazar la paginación normal por una basada en AJAX, al dar click en el botón se mostrarán los siguientes mensajes sin necesidad de cambiar de página.';
$txt['Breeze_user_settings_pagination_number'] = '¿Cuantos mensajes se mostrarán por página?';
$txt['Breeze_user_settings_pagination_number_sub'] = 'Esta opción controla cuantos mensajes serán mostrados en cada página o cuantos mensajes se cargarán al presionar el botón de "mostrar más". Por defecto se muestran 5 mensajes';
$txt['Breeze_user_settings_general_wall'] = 'Activar el muro general';
$txt['Breeze_user_settings_general_wall_sub'] = 'El muro general es una página única en donde se muestra la actividad reciente de todos tus amigos.';
$txt['Breeze_user_settings_wall'] = 'Activar mi muro';
$txt['Breeze_user_settings_wall_sub'] = 'Permite mostrar tu muro a otros usuarios.';
$txt['Breeze_user_settings_visitors'] = '¿Activar el bloque de visitas?';
$txt['Breeze_user_settings_visitors_sub'] = 'Este bloque mostrará las ultimas visitas registradas a tu muro.';
$txt['Breeze_user_settings_how_many_mentions'] = '¿Cuantos usuarios se mostrarán en la lista de menciones?';
$txt['Breeze_user_settings_how_many_mentions_sub'] = 'Si la lista de posibles usuarios mencionables es mayor a la cantidad que pongas, sólo se mostrarán la misma cantidad que tu hayas elegido.';
$txt['Breeze_user_settings_clean_visitors'] = 'Limpiar el registro de visitas';
$txt['Breeze_user_settings_clean_visitors_sub'] = 'Borra tu actual lista de visitantes.';
$txt['Breeze_user_settings_clear_noti'] = '¿Durante cuantos segundos se mostrarán las notificaciones antes de ser automáticamente cerradas?';
$txt['Breeze_user_settings_clear_noti_sub'] = 'Las notificaciones sólo serán cerradas cuando vayas a otra página volverán a aparecer hasta que las marques como leídas o las borres, si dejas esta opción vacia las notificaciones nunca se verrarán y deberás de cerrarlas manualmente.';
$txt['Breeze_user_settings_noti_on_status'] = 'Notifícame cuando alguien escribe un nuevo mensaje en mi muro.';
$txt['Breeze_user_settings_noti_on_status_sub'] = 'Cualquier usuario que puede escribir en mi muro.';
$txt['Breeze_user_settings_noti_on_comment'] = 'Notifícame cuando alguien comenta en alguno de mis mensajes.';
$txt['Breeze_user_settings_noti_on_comment_sub'] = 'En cualquier mensaje hecho en cualquier muro.';
$txt['Breeze_user_settings_noti_on_mention'] = 'Notifícame cuando alguien me mencione.';
$txt['Breeze_user_settings_noti_on_mention_sub'] = 'En cualquier lugar en donde se apliquen las menciones.';
$txt['Breeze_user_settings_aboutMe'] = 'Activa tu bloque "acerca de mi"';
$txt['Breeze_user_settings_aboutMe_sub'] = 'Déjalo vacio para desactivarlo, puedes usar código BBC.';
$txt['Breeze_user_settings_activity'] = 'Mostrar mi actividad';
$txt['Breeze_user_settings_activity_sub'] = 'Esa opción mostrará una nueva pestaña en donde se mostrarán tus ultimas actividades registradas.';
$txt['Breeze_user_settings_buddies'] = 'Activar el  bloque de amigos.';
$txt['Breeze_user_settings_buddies_sub'] = 'Mostrará todos tus amigos y su información de usuario.';
$txt['Breeze_user_settings_how_many_buddies'] = 'Cuantos amigos se van a mostrar';
$txt['Breeze_user_settings_how_many_buddies_sub'] = 'Si se deja vacio se mostrará el valor definido por el administrador..';
$txt['Breeze_user_settings_activityLog'] = 'Activar tu registro de actividades.';
$txt['Breeze_user_settings_activityLog_sub'] = 'Esta opción registrará algunas de tus actividades en el foro.';
$txt['Breeze_user_settings_visitors'] = 'Activar el bloque de ultimas visitas.';
$txt['Breeze_user_settings_visitors_sub'] = 'Mostrará un bloque con los ultimos usuarios que han visitado tu muro.';
$txt['Breeze_user_settings_how_many_visitors'] = '¿Cuantos visitantes se van a mostrar?';
$txt['Breeze_user_settings_how_many_visitors_sub'] = 'Si se deja vacio se usará el valor por defecto 10, el máximo de usuarios a mostrar es 30.';
$txt['Breeze_user_settings_show_last_visit'] = 'Mostrar cuando fué la ultima vez que un usuario visitó tu muro';
$txt['Breeze_user_permissions_name'] = 'Permisos';
$txt['Breeze_user_modules_name'] = 'Bloques';
$txt['Breeze_user_modules_visitors'] = 'visitas: ';
$txt['Breeze_user_modules_visitors_none'] = 'No hay visitas recientes';
$txt['Breeze_user_modules_buddies_none'] = 'Este usuario no tiene ninguna solicitud de amistad confirmada.';
$txt['Breeze_visitors_timeframe_hour'] = 'Hora';
$txt['Breeze_visitors_timeframe_day'] = 'Día';
$txt['Breeze_visitors_timeframe_week'] = 'Semana';
$txt['Breeze_visitors_timeframe_month'] = 'Mes';

// Time
$txt['Breeze_time_just_now'] = 'ahora.';
$txt['Breeze_time_second'] = 'segundo';
$txt['Breeze_time_ago'] = 'ago.';
$txt['Breeze_time_minute'] = 'minuto';
$txt['Breeze_time_hour'] = 'hora';
$txt['Breeze_time_day'] = 'día';
$txt['Breeze_time_week'] = 'semana';
$txt['Breeze_time_month'] = 'mes';
$txt['Breeze_time_year'] = 'año';

// Permissions strings
$txt['cannot_view_general_wall'] = 'Lo siento, no estás autorizado(a) para ver este muro.';
$txt['permissiongroup_simple_breeze_per_simple'] = 'Breeze mod permisos';
$txt['permissiongroup_breeze_per_classic'] = 'Breeze mod permisos';
$txt['permissionname_breeze_canMention'] = 'Poder mencionar<br /><span class="smalltext">Tener la capacidad de crear menciones a otras personas</span>';
$txt['permissionname_breeze_beMentioned'] = 'Aparecer en la lista de mencionable<br /><span class="smalltext">Si el usuairo puede o no aparecer en la lista de posibles mencionables de otros usuarios</span>';
$txt['permissionname_breeze_deleteStatus'] = 'Borrar todos los mensajes de cualquier muro<br /><span class="smalltext">Este permiso reemplaza cualquier otro permiso que el usuario puede tener. Borrar un mensaje también borra todos los comentarios asociados a ese mensaje.</span>';
$txt['permissionname_breeze_deleteComments'] = 'Borrar cualquier comentario en cualquier muro<br /><span class="smalltext">El usuario podrá borrar los comentarios hechos por cualquier otro usuario.</span>';
$txt['permissionname_breeze_deleteOwnStatus'] = 'Borrar sus propios status.<br /><span class="smalltext">Sin importar en cuál muro fueron hechos. Al hacerlo también se borrarán todos los comentarios asociados a ese status.</span>';
$txt['permissionname_breeze_deleteOwnComments'] = 'Borrar sus propios comentarios.<br /><span class="smalltext">Sin importar en dónde han sido escritos</span>';
$txt['permissionname_breeze_deleteProfileStatus'] = 'Borrar mensajes hechos en sus propios muros.<br /><span class="smalltext">Sin importar quién los escribió. Esto también borrará cualquier comentario asociado a ese status.</span>';
$txt['permissionname_breeze_deleteProfileComments'] = 'Borrar comentarios hechos en su propio muro.<br /><span class="smalltext">Sin importar quién los escribió.</span>';
$txt['permissionname_breeze_postStatus'] = 'Escribir nuevos mensajes en cualquier muro<br /><span class="smalltext">Por defecto, el dueño de el muro siempre tiene la posibilidad de escribir en su propio muro.</span>';
$txt['permissionname_breeze_postComments'] = 'Escribir nuevos comentarios en cualquier muro<br /><span class="smalltext">Por defecto, el dueño del muro siempre tiene la capacidad de escribir comentarios en sus propios muros.</span>';

// Ajax strings
$txt['Breeze_success_updated_settings'] = 'Tu configuración se guardo correctamente.';
$txt['Breeze_error_deleteComment'] = 'Lo siento, no se te permite borrar comentarios.';
$txt['Breeze_error_deleteStatus'] = 'Lo siento, no se te permite borrar status.';
$txt['Breeze_error_server'] = 'Hubo un error, por favor intenta de nuevo o contacta a el administrador.';
$txt['Breeze_error_wrong_values'] = 'Datos inválidos, la petición no pudo ser procesada.';
$txt['Breeze_success_published'] = 'Tu mensaje fué correctamente publicado';
$txt['Breeze_success_published_comment'] = 'Tu comentario fué correctamente publicado';
$txt['Breeze_error_empty'] = 'No puedes dejar vacio el cuadro de texto.';
$txt['Breeze_success_delete_status'] = 'El mensaje ha sido borrado';
$txt['Breeze_success_delete_comments'] = 'El comentario ha sido borrado';
$txt['Breeze_confirm_delete'] = '¿Realmente quieres borrar este mensaje?';
$txt['Breeze_confirm_yes'] = 'Si';
$txt['Breeze_confirm_cancel'] = 'Cancelar';
$txt['Breeze_error_already_deleted_status'] = 'Este mensaje ya ha sido borrado. Prueba a refrescar tu navegador.';
$txt['Breeze_error_already_deleted_comment'] = 'Este comentario ya ha sido borrado. Prueba a refrescar tu navegador.';
$txt['Breeze_error_already_deleted_noti'] = 'Esta notificación ya ha sido borrada. Prueba a refrescar tu navegador.';
$txt['Breeze_error_already_marked_noti'] = 'Esta notificación ya ha sido marcada como leída. Prueba a refrescar tu navegador.';
$txt['Breeze_cannot_postStatus'] = 'Lo siento, no puedes crear nuevos status.';
$txt['Breeze_cannot_postComments'] = 'Lo siento, no puedes crear nuevos comentarios.';
$txt['Breeze_error_no_valid_action'] = 'Esta no es una acción válida.';
$txt['Breeze_error_no_property'] = '%s no es una acción válida';
$txt['Breeze_error_no_access'] = 'Lo siento, no tienes acceso a esta sección.';
$txt['Breeze_success_noti_unmarkasread_after'] = 'Has marcado esta notificación como no leída.';
$txt['Breeze_success_noti_markasread_after'] = 'Has marcado esta notificación como leída';
$txt['Breeze_error_noti_markasreaddeleted_after'] = 'Esta notificación ya ha sido borrada o no tiene in ID válido.';
$txt['Breeze_error_noti_markasreaddeleted'] = 'Esta notificación ya ha sido borrada o no tiene in ID válido.';
$txt['Breeze_success_noti_delete_after'] = 'Has borrado esta notificación';
$txt['Breeze_success_noti_visitors_clean'] = 'Has borrado el registro de visitantes';
$txt['Breeze_success_notiMulti_delete_after'] = 'Has borrado todas las notificaciones con éxito';
$txt['Breeze_success_notiMulti_markasread_after'] = 'Has marcado como leídas todas tus notificaciones';
$txt['Breeze_success_notiMulti_unmarkasread_after'] = 'Has marcado como no leídas todas tus notificaciones';

// Errors
$txt['cannot_breeze_postStatus'] = $txt['Breeze_cannot_postStatus'];
$txt['cannot_breeze_postComments'] = $txt['Breeze_cannot_postComments'];
$txt['cannot_breeze_deleteStatus'] = 'Lo siento, no puedes borrar mensajes y/o comentarios.';
$txt['Breeze_cannot_see_general_wall'] = 'Necesitas activar tu muro general desde tu <a href="'. $scripturl .'?action=profile;area=breezesettings">página de administración</a>.';

// Pagination
$txt['Breeze_pag_previous'] = 'previo';
$txt['Breeze_pag_next'] = 'siguiente';
$txt['Breeze_pag_first'] = 'Primero';
$txt['Breeze_pag_last'] = 'Último';
$txt['Breeze_pag_pages'] = 'Páginas :';
$txt['Breeze_pag_page'] = '- pagina ';
$txt['Breeze_profile_of_username'] = 'Pefil de %1$s';
$txt['Breeze_page_loading'] = 'Cargando más status...';
$txt['Breeze_page_loading_end'] = 'No hay más mensajes para mostrar';
$txt['Breeze_page_no_status'] = 'No hay mensajes para mostrar';

// Tabs
$txt['Breeze_tabs_wall'] = 'Muro';
$txt['Breeze_tabs_buddies'] = 'Amigos';
$txt['Breeze_tabs_views'] = 'Visitantes del perfil';
$txt['Breeze_tabs_pinfo'] = 'Información';
$txt['Breeze_tabs_activity'] = 'Actividad reciente';
$txt['Breeze_tabs_activity_none'] = 'Este usuario no tiene nungina actividad registrada recientemente.';
$txt['Breeze_tabs_activity_buddies_none'] = 'Tus amigos no tienen ninguna actividad registrada recientemente.';
$txt['Breeze_tabs_about'] = 'Acerca de mi';
$txt['Breeze_goTop'] = 'Ir arriba';

// Notifications
$txt['Breeze_noti_title'] = 'Notificaciones';
$txt['Breeze_noti_title_settings'] = 'Configuración de notificaciones';
$txt['Breeze_noti_title_settings_desc'] = 'Activar/Desactivar notificaciones individuales.';
$txt['Breeze_noti_message'] = 'Mensaje';
$txt['Breeze_noti_buddy_title'] = 'Notificación de amistad';
$txt['Breeze_noti_buddy_message'] = 'El usuario(a) %s te ha agregado como su amigo(a), por favor confirma esta solicitud.';
$txt['Breeze_noti_markasread'] = 'Marcar como leído';
$txt['Breeze_noti_markasunread'] = 'Marcar como no leído';
$txt['Breeze_noti_markasread_title'] = 'Marcar como no/leído';
$txt['Breeze_noti_markasread_viewed'] = 'Ya se ha marcado com leído';
$txt['Breeze_noti_close'] = 'Cerrar';
$txt['Breeze_noti_delete'] = 'Borrar';
$txt['Breeze_noti_cancel'] = 'Cancelar';
$txt['Breeze_noti_closeAll'] = 'Cerrar todas las notificaciones';
$txt['Breeze_noti_novalid_after'] = 'No es una notificación válida';
$txt['Breeze_noti_none'] = 'No tienes ninguna notificación';
$txt['Breeze_noti_checkAll'] = 'Marcar todas';
$txt['Breeze_noti_check'] = 'marcar';
$txt['Breeze_noti_selectedOptions'] = 'Hacer lo siguiente con las notificaciones marcadas: ';
$txt['Breeze_noti_send'] = 'Enviar';
$txt['Breeze_noti_gender_his'] = 'su';
$txt['Breeze_noti_gender_her'] = 'su';
$txt['Breeze_noti_gender_his_default'] = 'su';
$txt['Breeze_noti_gender_he'] = 'el';
$txt['Breeze_noti_gender_she'] = 'ella';
$txt['Breeze_noti_gender_he_default'] = 'el/ella';

// Comment notification
$txt['Breeze_noti_comment_message'] = '%1$s comentó en el mensaje hecho por %2$s en el muro de %3$s,<br/> <a href="" class="bbc_link" target="_blank">ver el comentario</a>';
$txt['Breeze_noti_comment_message_statusOwner'] = '%1$s comentó en tu mensaje hecho en el muro de %2$s';
$txt['Breeze_noti_comment_message_wallOwner'] = '%1$s comentó en el mensaje hecho por %2$s en tu muro';

// Someone posted a status on your wall.
$txt['Breeze_noti_posted_wall'] = '%1$s escribió un nuevo mensaje en tu muro: %2$s';

// Someone commented your status on your own wall
$txt['Breeze_noti_posted_comment'] = '%1$s comentó en tu status: %2$s en el muro de %3$s';

// Mentions
$txt['Breeze_mention_message_status'] = '¡<a href="%3$s" class="bbc_link" target="_blank" id="noti_%4$s"> Has sido mencionad(a)</a> por %1$s oen el muro de %2$s!';
$txt['Breeze_mention_message_own_wall_status'] = '<a href="%1$s" class="bbc_link" target="_blank">Has sido mencionado(a)</a> en tu propio muro por %2$s!';
$txt['Breeze_mention_message_comment'] = '¡<a href="%3$s" class="bbc_link" target="_blank" id="noti_%4$s"> Has sido mencionado en un comentario</a> hecho por %1$s e el muro de %2$s!';
$txt['Breeze_mention_message_own_wall_comment'] = '¡<a href="%1$s" class="bbc_link" target="_blank" id="noti_%3$s">Has sido mencionado(a)</a> en un comentario en tu propio muro hecho por %2$s!';

// Single Status
$txt['Breeze_singleStatus_pageTitle'] = 'Mensaje';

// Log
$txt['Breeze_logTopic'] = 'creó un nuevo tema:';
$txt['Breeze_logRegister'] = '¡Se ha registrado!';
$txt['Breeze_logComment'] = 'hizo un nuevo comentario en el muro de %s';
$txt['Breeze_logComment_own_0'] = 'hizo un nuevo comentario en su propio muro.';
$txt['Breeze_logComment_own_1'] = 'hizo un comentario en su propio muro';
$txt['Breeze_logComment_own_2'] = 'hizo un comentario en su propio muro';
$txt['Breeze_logComment_view'] = 'Ver comentario';
$txt['Breeze_logStatus'] = 'creó un nuevo mensaje en el muro de %s';
$txt['Breeze_logStatus_own_0'] = 'creó un nuevo mensaje en su propio muro.';
$txt['Breeze_logStatus_own_1'] = 'creó un nuevo mensaje en su propio muro.';
$txt['Breeze_logStatus_own_2'] = 'creó un nuevo mensaje en su propio muro.';
$txt['Breeze_logStatus_view'] = 'Ver mensaje';
