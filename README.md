**Breeze**, https://missallsunday.com

The software is being license under [MPL 2.0 license](https://www.mozilla.org/MPL/).

###### What is Breeze:

Breeze is a social mod for your users. It enables a dynamic wall where they can post status and comments.
Comes with the following features:

###### Requirements:

- SMF 2.1.x
- PHP 8.2

###### Features:

- No file edits, works with all themes.
- Users individual settings, each user defines her/his own settings.
- Mentions for status and comments.
- Notifications for several features.
- General wall page for displaying your buddies latest activity.

The mod uses the following scripts:

- [React](https://reactjs.org)

##### Tests
```bash
composer test
```



##### Generate an optimized SMF package
```bash
chmod +x generate_zip.sh && ./generate_zip.sh
```

###### Notes:

Feel free to fork this repository and make your desired changes.

Please see the [Developer's Certificate of Origin](https://github.com/MissAllSunday/Breeze/blob/master/DCO.txt) in the repository:
by signing off your contributions, you acknowledge that you can and do license your submissions under the license of the project.

###### Branches organization:
* ***master*** - is the main branch, only used to merge in a "final release"
* ***development*** - is the branch where the development of the "next" version/s happens
