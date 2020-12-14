**Breeze**, https://missallsunday.com

The software is being license under [MPL 2.0 license](https://www.mozilla.org/MPL/).

###### What is Breeze:

Breeze is powerful social mod for your SMF forum, it allows your users to turn their profile pages into a dynamic wall where they can publish new status and comment on other user's status.

###### Requirements:

- SMF 2.1.x
- PHP 7.4.0

###### Features:

- No file edits, works with all themes.
- Users individual settings, each user defines her/his own settings.
- Mentions for status and comments.
- Notifications for several features.
- General wall page for displaying your buddie's latest activity.

The mod uses the following scripts:

- [Vue](https://vuejs.org/)
- [Axios](https://github.com/axios/axios)
- [moment.js](https://momentjs.com/)
- [vue-toast-notification](https://github.com/ankurk91/vue-toast-notification)
- [DOMPurify](https://github.com/cure53/DOMPurify)
- [Sun editor](http://suneditor.com/)

##### Tests
```bash
composer test
```

##### Generate an optimized SMF package
```bash
composer install --no-ansi --no-dev --no-interaction --no-plugins --no-progress --no-scripts --no-suggest --optimize-autoloader &&
zip -r Breeze breezeVendor/ Sources/ Themes/ tasks/ hooks.php install.php License package-info.xml README.txt remove.php
``` 

###### Notes:

Feel free to fork this repository and make your desired changes.

Please see the [Developer's Certificate of Origin](https://github.com/MissAllSunday/Breeze/blob/master/DCO.txt) in the repository:
by signing off your contributions, you acknowledge that you can and do license your submissions under the license of the project.

###### Branches organization:
* ***master*** - is the main branch, only used to merge in a "final release"
* ***development*** - is the branch where the development of the "next" version/s happens
