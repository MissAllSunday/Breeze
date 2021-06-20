<?php

/**
 * BreezeAdmin.turkish
 *
 * @package Breeze mod
 * @version 1.0.15
 * @author Michel Mendiola <suki@missallsunday.com>
 * @copyright Copyright (c) 2011 - 2018 Jessica Gonzalez
 * @license //www.mozilla.org/MPL/MPL-1.1.html
 */
/**
 *  @t�rk�e �eviri snrj
 *  @http://smf.konusal.com/
 */
global $txt;

// Admin Settings
$txt['Breeze_page_panel'] = 'Breeze modunu etkinle�tir';
$txt['Breeze_page_welcome'] = '&quot;Breeze Y�netici Paneli&quot;.   Buradan Breeze ayarlar�n� d�zenleyebilirsiniz. Herhangi bir sorunuz varsa <a href="'. Breeze::$supportSite .'" target="_blank" class="new_win">Yazar�n sitesinden</a> destek istemekten �ekinmeyin.';
$txt['Breeze_page_main'] = 'Breeze Y�netim Merkezi';
$txt['Breeze_page_permissions'] = '�zinler';
$txt['Breeze_page_permissions_desc'] = 'Buradan belirli Breeze izinlerini ekleyebilir / kald�rabilirsiniz.';
$txt['Breeze_page_settings'] = 'Genel Ayarlar';
$txt['Breeze_page_settings_desc'] = 'Bu genel ayarlar sayfas�d�r, buradan modu etkinle�tirebilece�iniz / devre d��� b�rakabilece�iniz gibi genel ayarlar� yap�land�rman�z da m�mk�nd�r.';
$txt['Breeze_page_donate'] = 'Ba���';
$txt['Breeze_page_donate_desc'] = 'Boring stuff you were curious about it but you\'ll never gonna see it again :P';
$txt['Breeze_page_donate_exp'] = 'Breeze, serbest zaman�nda bir PHP merakl�s� taraf�ndan getirilen �cretsiz bir SMF modifikasyonudur.<p /> Bu modifikasyonu be�enirseniz ve takdirinizi g�stermek istiyorsan�z, l�tfen bir <a href="'. Breeze::$supportSite .'">ba��� yap�n</a>.  Ba����n�z sunucu giderlerini kar��lamak ve / veya ayakkab� sat�n almak,
Ayakkab� geli�tiriciyi mutlu ediyor ve e�er mutluysa daha fazla g�ncelleme olacak;)<p />Forumunuzda Breezeyi kulland���n�zdan bana haber verin, merhaba de, Breezeden g�� alan profil sayfalar�n�z� bana g�sterin, te�ekk�rlerinizi g�sterin..';
$txt['Breeze_page_credits'] = 'Yap�mc�lar';
$txt['Breeze_page_credits_decs'] = 'Breeze size a�a��daki ki�iler ve / veya scriptler taraf�ndan getirilir:';
$txt['Breeze_enable_general_wall'] = 'Genel Duvar� Etkinle�tir';
$txt['Breeze_enable_general_wall_sub'] = 'Etkinle�tirilirse, genel bir duvar ortaya ��kacak ve bu genel duvarda kullan�c� arkada��n�n durumunu ve son etkinli�ini g�rebilecektir';
$txt['Breeze_menu_position'] = 'Genel Duvar d��mesinin konumunu se�in.';
$txt['Breeze_menu_position_sub'] = 'Varsay�lan olarak anasayfa d��mesinin yan�nda.';
$txt['Breeze_master'] = 'Breeze modunu etkinle�tir';
$txt['Breeze_master_sub'] = 'Ana ayar, modun d�zg�n �al��abilmesi i�in bu etkinle�tirilmelidir.';
$txt['Breeze_force_enable'] = 'Duvarlar�n etkinle�tirilmesini zorunlu k�lmak i�in i�aretleyin.';
$txt['Breeze_force_enable_sub'] = 'Varsay�lan olarak duvar devre d���d�r ve kullan�c�lar�n elle etkinle�tirmesi gerekir; e�er bu se�ene�i i�aretlerseniz duvarlar� etkinle�tirilir, unutulmamal�d�r ki bu se�enek aktif olmayan �yeler ve botlar da dahil olmak �zere herkesin duvar�n� etkinle�tirir<br /> �stenirse yine de duvarlar�n� manuel olarak devre d��� b�rakabilirler, bu se�enek yaln�zca onu etkinle�tirir ancak duvar�n her zaman etkinle�tirilmesini zorlamaz..';
$txt['Breeze_force_enable_on'] = 'Etkinle�tir';
$txt['Breeze_force_enable_off'] = 'Devre d��� b�rak';
$txt['Breeze_notifications'] = 'Bildirimleri etkinle�tir';
$txt['Breeze_notifications_sub'] = 'Etkinle�tirilirse, kullan�c�lar�n�za bildirim alabilecek ve uygun g�rd�kleri �ekilde onlar� etkinle�tirebilecek / devre d��� b�rakabilecek.';
$txt['Breeze_parseBBC'] = 'BBCyi ayr��t�rmay� etkinle�tir';
$txt['Breeze_parseBBC_sub'] = 'Etkinle�tirirseniz, kullan�c�lar�n�z durumlar� / yorumlar� konusunda BBC kodunu kullanabilecektir. <br /> �ok me�gul forumlarda bu se�ene�in etkinle�tirilmesinin sunucunuzu yava�latabilece�ine dikkat edin.';
$txt['Breeze_mention'] = 'Mention (Bahsetme) �zelli�ini etkinle�tir.';
$txt['Breeze_mention_sub'] = '�nsanlara di�er kullan�c�lardan durumlar� ve yorumlar�nda Mention(Bahsetme) belirtmelerini istiyorsan�z bu �zelli�i a��n.';
$txt['Breeze_mention_limit'] = 'Tek bir mesajda ka� kullan�c�dan Bahsedebilir?';
$txt['Breeze_mention_limit_sub'] = 'Kullan�c� izin verilen say�dan daha fazla kullan�c�dan bahsetmeye �al���rsa,
Yaln�zca ilk X s�zlere d�n��t�r�lecektir, burada X belirtti�iniz say�d�r. E�er bo� b�rak�l�rsa,Mod varsay�lan de�eri kullanacakt�r: 10';
$txt['Breeze_posts_for_mention'] = 'Mention (Bahsetme) listesinde ka� adet yay�n bulunmas� istiyorsunuz?';
$txt['Breeze_posts_for_mention_sub'] = 'S�zle�me listesinde spamc� / bot hesaplar�n�n g�r�nmesini �nlemek i�in bir kullan�c�n�n s�z edilebilir olabilmesi i�in ka� mesaj yay�nlayaca��n� ayarlayabilir, bu ayar� bo� b�rak�rsan�z varsay�lan de�eri kullan�r: 1. <br /> Sunucu y�k�yle birlikte yard�mc� olmas� i�in, bahis listesinde �nbellek al�n�r; bu ayar� de�i�tirirseniz sonu�lar�n� g�rmek i�in forum �nbelle�inizi temizledi�inizden emin olun..';
$txt['Breeze_version'] = 'Breeze versiyon';
$txt['Breeze_live'] = 'Canl� destek forumundan...';
$txt['Breeze_allowed_actions'] = 'Bildirim sisteminin g�sterilmesini istedi�iniz eylemleri yaz�n';
$txt['Breeze_allowed_actions_sub'] = 'Varsay�lan olarak, bildirim sistemi a�a��daki i�lemlerde g�r�n�r: '. implode(', ', Breeze::$_allowedActions) .'. Art� BoardIndex, MessageIndex, Konu ve Kurul sayfalar�. <br /> L�tfen hareketlerinizi virg�lle ayr�lm�� bir listeye ekleyin, �rne�in: eylem, eylem, eylem, eylem';
$txt['Breeze_feed_error_message'] = 'Breeze destek sitesine ba�lanamad�.';
$txt['Breeze_allowed_maxlength_aboutMe'] = '"Hakk�mda" blo�unun maksimum uzunlu�u ';
$txt['Breeze_allowed_maxlength_aboutMe_sub'] = 'E�er bo� b�rak�l�rsa, mod varsay�lan de�eri kullanacakt�r: 1024';
$txt['Breeze_allowed_max_num_users'] = 'Bir kullan�c� ziyaret�ileri ve arkada�lar� engelledi�inde ka� kullan�c� g�sterebilir?';
$txt['Breeze_allowed_max_num_users_sub'] = 'Kullan�c�n�n belirtilen ayardan daha fazla kullan�c�s� varsa, t�m listeleri daha kompakt bir ba�lant� listesine d�n��t�r�lecektir. Bu se�ene�i devre d��� b�rakmak i�in 0 de�erinde b�rak�n.';
