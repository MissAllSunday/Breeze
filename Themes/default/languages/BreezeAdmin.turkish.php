<?php

/**
 * BreezeAdmin.turkish
 *
 * @package Breeze mod
 * @version 1.0.11
 * @author Jessica Gonzalez <suki@missallsunday.com>
 * @copyright Copyright (c) 2011 - 2017 Jessica Gonzalez
 * @license //www.mozilla.org/MPL/MPL-1.1.html
 */
/**
 *  @türkçe çeviri snrj
 *  @http://smf.konusal.com/
 */
global $txt;

// Admin Settings
$txt['Breeze_page_panel'] = 'Breeze modunu etkinleþtir';
$txt['Breeze_page_welcome'] = '&quot;Breeze Yönetici Paneli&quot;.   Buradan Breeze ayarlarýný düzenleyebilirsiniz. Herhangi bir sorunuz varsa <a href="', Breeze::$supportSite ,'" target="_blank" class="new_win">Yazarýn sitesinden</a> destek istemekten çekinmeyin.';
$txt['Breeze_page_main'] = 'Breeze Yönetim Merkezi';
$txt['Breeze_page_permissions'] = 'Ýzinler';
$txt['Breeze_page_permissions_desc'] = 'Buradan belirli Breeze izinlerini ekleyebilir / kaldýrabilirsiniz.';
$txt['Breeze_page_settings'] = 'Genel Ayarlar';
$txt['Breeze_page_settings_desc'] = 'Bu genel ayarlar sayfasýdýr, buradan modu etkinleþtirebileceðiniz / devre dýþý býrakabileceðiniz gibi genel ayarlarý yapýlandýrmanýz da mümkündür.';
$txt['Breeze_page_donate'] = 'Baðýþ';
$txt['Breeze_page_donate_desc'] = 'Boring stuff you were curious about it but you\'ll never gonna see it again :P';
$txt['Breeze_page_donate_exp'] = 'Breeze, serbest zamanýnda bir PHP meraklýsý tarafýndan getirilen ücretsiz bir SMF modifikasyonudur.<p /> Bu modifikasyonu beðenirseniz ve takdirinizi göstermek istiyorsanýz, lütfen bir <a href="', Breeze::$supportSite ,'">baðýþ yapýn</a>.  Baðýþýnýz sunucu giderlerini karþýlamak ve / veya ayakkabý satýn almak,
Ayakkabý geliþtiriciyi mutlu ediyor ve eðer mutluysa daha fazla güncelleme olacak;)<p />Forumunuzda Breezeyi kullandýðýnýzdan bana haber verin, merhaba de, Breezeden güç alan profil sayfalarýnýzý bana gösterin, teþekkürlerinizi gösterin..';
$txt['Breeze_page_credits'] = 'Yapýmcýlar';
$txt['Breeze_page_credits_decs'] = 'Breeze size aþaðýdaki kiþiler ve / veya scriptler tarafýndan getirilir:';
$txt['Breeze_enable_general_wall'] = 'Genel Duvarý Etkinleþtir';
$txt['Breeze_enable_general_wall_sub'] = 'Etkinleþtirilirse, genel bir duvar ortaya çýkacak ve bu genel duvarda kullanýcý arkadaþýnýn durumunu ve son etkinliðini görebilecektir';
$txt['Breeze_menu_position'] = 'Genel Duvar düðmesinin konumunu seçin.';
$txt['Breeze_menu_position_sub'] = 'Varsayýlan olarak anasayfa düðmesinin yanýnda.';
$txt['Breeze_master'] = 'Breeze modunu etkinleþtir';
$txt['Breeze_master_sub'] = 'Ana ayar, modun düzgün çalýþabilmesi için bu etkinleþtirilmelidir.';
$txt['Breeze_force_enable'] = 'Duvarlarýn etkinleþtirilmesini zorunlu kýlmak için iþaretleyin.';
$txt['Breeze_force_enable_sub'] = 'Varsayýlan olarak duvar devre dýþýdýr ve kullanýcýlarýn elle etkinleþtirmesi gerekir; eðer bu seçeneði iþaretlerseniz duvarlarý etkinleþtirilir, unutulmamalýdýr ki bu seçenek aktif olmayan üyeler ve botlar da dahil olmak üzere herkesin duvarýný etkinleþtirir<br /> Ýstenirse yine de duvarlarýný manuel olarak devre dýþý býrakabilirler, bu seçenek yalnýzca onu etkinleþtirir ancak duvarýn her zaman etkinleþtirilmesini zorlamaz..';
$txt['Breeze_force_enable_on'] = 'Etkinleþtir';
$txt['Breeze_force_enable_off'] = 'Devre dýþý býrak';
$txt['Breeze_notifications'] = 'Bildirimleri etkinleþtir';
$txt['Breeze_notifications_sub'] = 'Etkinleþtirilirse, kullanýcýlarýnýza bildirim alabilecek ve uygun gördükleri þekilde onlarý etkinleþtirebilecek / devre dýþý býrakabilecek.';
$txt['Breeze_parseBBC'] = 'BBCyi ayrýþtýrmayý etkinleþtir';
$txt['Breeze_parseBBC_sub'] = 'Etkinleþtirirseniz, kullanýcýlarýnýz durumlarý / yorumlarý konusunda BBC kodunu kullanabilecektir. <br /> Çok meþgul forumlarda bu seçeneðin etkinleþtirilmesinin sunucunuzu yavaþlatabileceðine dikkat edin.';
$txt['Breeze_mention'] = 'Mention (Bahsetme) özelliðini etkinleþtir.';
$txt['Breeze_mention_sub'] = 'Ýnsanlara diðer kullanýcýlardan durumlarý ve yorumlarýnda Mention(Bahsetme) belirtmelerini istiyorsanýz bu özelliði açýn.';
$txt['Breeze_mention_limit'] = 'Tek bir mesajda kaç kullanýcýdan Bahsedebilir?';
$txt['Breeze_mention_limit_sub'] = 'Kullanýcý izin verilen sayýdan daha fazla kullanýcýdan bahsetmeye çalýþýrsa,
Yalnýzca ilk X sözlere dönüþtürülecektir, burada X belirttiðiniz sayýdýr. Eðer boþ býrakýlýrsa,Mod varsayýlan deðeri kullanacaktýr: 10';
$txt['Breeze_posts_for_mention'] = 'Mention (Bahsetme) listesinde kaç adet yayýn bulunmasý istiyorsunuz?';
$txt['Breeze_posts_for_mention_sub'] = 'Sözleþme listesinde spamcý / bot hesaplarýnýn görünmesini önlemek için bir kullanýcýnýn söz edilebilir olabilmesi için kaç mesaj yayýnlayacaðýný ayarlayabilir, bu ayarý boþ býrakýrsanýz varsayýlan deðeri kullanýr: 1. <br /> Sunucu yüküyle birlikte yardýmcý olmasý için, bahis listesinde önbellek alýnýr; bu ayarý deðiþtirirseniz sonuçlarýný görmek için forum önbelleðinizi temizlediðinizden emin olun..';
$txt['Breeze_version'] = 'Breeze versiyon';
$txt['Breeze_live'] = 'Canlý destek forumundan...';
$txt['Breeze_allowed_actions'] = 'Bildirim sisteminin gösterilmesini istediðiniz eylemleri yazýn';
$txt['Breeze_allowed_actions_sub'] = 'Varsayýlan olarak, bildirim sistemi aþaðýdaki iþlemlerde görünür: '. implode(', ', Breeze::$_allowedActions) .'. Artý BoardIndex, MessageIndex, Konu ve Kurul sayfalarý. <br /> Lütfen hareketlerinizi virgülle ayrýlmýþ bir listeye ekleyin, örneðin: eylem, eylem, eylem, eylem';
$txt['Breeze_feed_error_message'] = 'Breeze destek sitesine baðlanamadý.';
$txt['Breeze_allowed_maxlength_aboutMe'] = '"Hakkýmda" bloðunun maksimum uzunluðu ';
$txt['Breeze_allowed_maxlength_aboutMe_sub'] = 'Eðer boþ býrakýlýrsa, mod varsayýlan deðeri kullanacaktýr: 1024';
$txt['Breeze_allowed_max_num_users'] = 'Bir kullanýcý ziyaretçileri ve arkadaþlarý engellediðinde kaç kullanýcý gösterebilir?';
$txt['Breeze_allowed_max_num_users_sub'] = 'Kullanýcýnýn belirtilen ayardan daha fazla kullanýcýsý varsa, tüm listeleri daha kompakt bir baðlantý listesine dönüþtürülecektir. Bu seçeneði devre dýþý býrakmak için 0 deðerinde býrakýn.';
