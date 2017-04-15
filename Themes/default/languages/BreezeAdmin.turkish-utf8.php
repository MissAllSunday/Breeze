<?php

/**
 * BreezeAdmin.turkish-utf8
 *
 * @package Breeze mod
 * @version 1.0.11
 * @author Jessica Gonzalez <suki@missallsunday.com>
 * @copyright Copyright (c) 2011 - 2017 Jessica Gonzalez
 * @license http://www.mozilla.org/MPL/MPL-1.1.html
 */
/**
 *  @türkçe çeviri snrj
 *  @http://smf.konusal.com/
 */
global $txt;

// Admin Settings
$txt['Breeze_page_panel'] = 'Breeze modunu etkinleştir';
$txt['Breeze_page_welcome'] = '&quot;Breeze Yönetici Paneli&quot;.   Buradan Breeze ayarlarını düzenleyebilirsiniz. Herhangi bir sorunuz varsa <a href="', Breeze::$supportSite ,'" target="_blank" class="new_win">Yazarın sitesinden</a> destek istemekten çekinmeyin.';
$txt['Breeze_page_main'] = 'Breeze Yönetim Merkezi';
$txt['Breeze_page_permissions'] = 'İzinler';
$txt['Breeze_page_permissions_desc'] = 'Buradan belirli Breeze izinlerini ekleyebilir / kaldırabilirsiniz.';
$txt['Breeze_page_settings'] = 'Genel Ayarlar';
$txt['Breeze_page_settings_desc'] = 'Bu genel ayarlar sayfasıdır, buradan modu etkinleştirebileceğiniz / devre dışı bırakabileceğiniz gibi genel ayarları yapılandırmanız da mümkündür.';
$txt['Breeze_page_donate'] = 'Bağış';
$txt['Breeze_page_donate_desc'] = 'Boring stuff you were curious about it but you\'ll never gonna see it again :P';
$txt['Breeze_page_donate_exp'] = 'Breeze, serbest zamanında bir PHP meraklısı tarafından getirilen ücretsiz bir SMF modifikasyonudur.<p /> Bu modifikasyonu beğenirseniz ve takdirinizi göstermek istiyorsanız, lütfen bir <a href="', Breeze::$supportSite ,'">bağış yapın</a>.  Bağışınız sunucu giderlerini karşılamak ve / veya ayakkabı satın almak,
Ayakkabı geliştiriciyi mutlu ediyor ve eğer mutluysa daha fazla güncelleme olacak;)<p />Forumunuzda Breezeyi kullandığınızdan bana haber verin, merhaba de, Breezeden güç alan profil sayfalarınızı bana gösterin, teşekkürlerinizi gösterin..';
$txt['Breeze_page_credits'] = 'Yapımcılar';
$txt['Breeze_page_credits_decs'] = 'Breeze size aşağıdaki kişiler ve / veya scriptler tarafından getirilir:';
$txt['Breeze_enable_general_wall'] = 'Genel Duvarı Etkinleştir';
$txt['Breeze_enable_general_wall_sub'] = 'Etkinleştirilirse, genel bir duvar ortaya çıkacak ve bu genel duvarda kullanıcı arkadaşının durumunu ve son etkinliğini görebilecektir';
$txt['Breeze_menu_position'] = 'Genel Duvar düğmesinin konumunu seçin.';
$txt['Breeze_menu_position_sub'] = 'Varsayılan olarak anasayfa düğmesinin yanında.';
$txt['Breeze_master'] = 'Breeze modunu etkinleştir';
$txt['Breeze_master_sub'] = 'Ana ayar, modun düzgün çalışabilmesi için bu etkinleştirilmelidir.';
$txt['Breeze_force_enable'] = 'Duvarların etkinleştirilmesini zorunlu kılmak için işaretleyin.';
$txt['Breeze_force_enable_sub'] = 'Varsayılan olarak duvar devre dışıdır ve kullanıcıların elle etkinleştirmesi gerekir; eğer bu seçeneği işaretlerseniz duvarları etkinleştirilir, unutulmamalıdır ki bu seçenek aktif olmayan üyeler ve botlar da dahil olmak üzere herkesin duvarını etkinleştirir<br /> İstenirse yine de duvarlarını manuel olarak devre dışı bırakabilirler, bu seçenek yalnızca onu etkinleştirir ancak duvarın her zaman etkinleştirilmesini zorlamaz..';
$txt['Breeze_force_enable_on'] = 'Etkinleştir';
$txt['Breeze_force_enable_off'] = 'Devre dışı bırak';
$txt['Breeze_notifications'] = 'Bildirimleri etkinleştir';
$txt['Breeze_notifications_sub'] = 'Etkinleştirilirse, kullanıcılarınıza bildirim alabilecek ve uygun gördükleri şekilde onları etkinleştirebilecek / devre dışı bırakabilecek.';
$txt['Breeze_parseBBC'] = 'BBCyi ayrıştırmayı etkinleştir';
$txt['Breeze_parseBBC_sub'] = 'Etkinleştirirseniz, kullanıcılarınız durumları / yorumları konusunda BBC kodunu kullanabilecektir. <br /> Çok meşgul forumlarda bu seçeneğin etkinleştirilmesinin sunucunuzu yavaşlatabileceğine dikkat edin.';
$txt['Breeze_mention'] = 'Mention (Bahsetme) özelliğini etkinleştir.';
$txt['Breeze_mention_sub'] = 'İnsanlara diğer kullanıcılardan durumları ve yorumlarında Mention(Bahsetme) belirtmelerini istiyorsanız bu özelliği açın.';
$txt['Breeze_mention_limit'] = 'Tek bir mesajda kaç kullanıcıdan Bahsedebilir?';
$txt['Breeze_mention_limit_sub'] = 'Kullanıcı izin verilen sayıdan daha fazla kullanıcıdan bahsetmeye çalışırsa,
Yalnızca ilk X sözlere dönüştürülecektir, burada X belirttiğiniz sayıdır. Eğer boş bırakılırsa,Mod varsayılan değeri kullanacaktır: 10';
$txt['Breeze_posts_for_mention'] = 'Mention (Bahsetme) listesinde kaç adet yayın bulunması istiyorsunuz?';
$txt['Breeze_posts_for_mention_sub'] = 'Sözleşme listesinde spamcı / bot hesaplarının görünmesini önlemek için bir kullanıcının söz edilebilir olabilmesi için kaç mesaj yayınlayacağını ayarlayabilir, bu ayarı boş bırakırsanız varsayılan değeri kullanır: 1. <br /> Sunucu yüküyle birlikte yardımcı olması için, bahis listesinde önbellek alınır; bu ayarı değiştirirseniz sonuçlarını görmek için forum önbelleğinizi temizlediğinizden emin olun..';
$txt['Breeze_version'] = 'Breeze versiyon';
$txt['Breeze_live'] = 'Canlı destek forumundan...';
$txt['Breeze_allowed_actions'] = 'Bildirim sisteminin gösterilmesini istediğiniz eylemleri yazın';
$txt['Breeze_allowed_actions_sub'] = 'Varsayılan olarak, bildirim sistemi aşağıdaki işlemlerde görünür: '. implode(', ', Breeze::$_allowedActions) .'. Artı BoardIndex, MessageIndex, Konu ve Kurul sayfaları. <br /> Lütfen hareketlerinizi virgülle ayrılmış bir listeye ekleyin, örneğin: eylem, eylem, eylem, eylem';
$txt['Breeze_feed_error_message'] = 'Breeze destek sitesine bağlanamadı.';
$txt['Breeze_allowed_maxlength_aboutMe'] = '"Hakkımda" bloğunun maksimum uzunluğu ';
$txt['Breeze_allowed_maxlength_aboutMe_sub'] = 'Eğer boş bırakılırsa, mod varsayılan değeri kullanacaktır: 1024';
$txt['Breeze_allowed_max_num_users'] = 'Bir kullanıcı ziyaretçileri ve arkadaşları engellediğinde kaç kullanıcı gösterebilir?';
$txt['Breeze_allowed_max_num_users_sub'] = 'Kullanıcının belirtilen ayardan daha fazla kullanıcısı varsa, tüm listeleri daha kompakt bir bağlantı listesine dönüştürülecektir. Bu seçeneği devre dışı bırakmak için 0 değerinde bırakın.';
