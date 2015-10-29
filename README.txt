[center][color=purple][size=15pt]Breeze Social mod[/size][/color] [/center]


Created by [url=http://missallsunday.com]Suki[/url]

[b]This mod needs PHP 5.3 or greater and SMF 2.0.x or greater[/b]

[color=purple][b][size=12pt]License[/size][/b][/color]
[code]
This Source Code Form is subject to the terms of the Mozilla Public License, v. 1.1.
If a copy of the MPL was not distributed with this file,
You can obtain one at http://mozilla.org/MPL/

The contents of this package are subject to the Mozilla Public License Version
1.1 (the "License"); you may not use this package except in compliance with
the License. You may obtain a copy of the License at
http://www.mozilla.org/MPL/
 *
Software distributed under the License is distributed on an "AS IS" basis,
WITHOUT WARRANTY OF ANY KIND, either express or implied. See the License
for the specific language governing rights and limitations under the
License.[/code]


[size=12pt][color=purple]Introduction[/color][/size]

Breeze is a social mod for your users to turn their dull profile into a dynamic wall where they can post status and comments. Comes with the following features:

- No file edits, works with all themes.
- Users individual settings, each user defines her/his own settings.
- Mentions for status and comments.
- Notifications for several features.
- General wall page for displaying your buddies latest activity.
- Couple of blocks for showing your buddies, visitors, latest activity and about me block.
- Works with or without JavaScript.


[size=12pt][color=purple]Supported Languages[/color][/size]

o English/utf8
o Spanish_latin/utf8

I welcome translations, please post it on the support site ;)


[size=12pt][color=purple]Installation[/color][/size]

Use the package manager to install this modification, Breeze will work with all themes.
Tested on PHP 5.3 and 5.4, Opera12, IE8 and whatever version Firefox was when I tested it.


[size=12pt][color=purple]This mod uses the following scripts[/color][/size]

- [jQuery] (http://jquery.com/)
- [Facebox] (https://github.com/defunkt/facebox)
- [zRSSFeeds] (http://www.zazar.net/developers/jquery/zrssfeed/)
- [needim noty jquery plugin] (http://needim.github.com/noty/)
- [ichord Mentions autocomplete script] (http://ichord.github.com/At.js)
- [ikons from Piotr Kwiatkowski] (http://ikons.piotrkwiatkowski.co.uk)
- [DOMPurify] (https://github.com/cure53/DOMPurify)


[size=12pt][color=purple]Changelog[/color][/size]

[code]
1.0.9 Oct 29, 2015
- Fix a missing semicolon preventing users form using the mentions feature on their walls.
- Replace $this->_smcFunc with global $smcFunc.

1.0.8 Jun 29, 2015
- Fix a security issue allowing users to post status and messages as another user. Thanks to JSX3 for reporting it.

1.0.7 Oct 22, 2014
This release adds a new security layer by implementing the DOMPurify library to both comments and status messages.

It also fixes a wrong version on package-info.xml as well as adding the current version to the version tag on all files.

1.0.6 Jul 3, 2014
- This release fixes a bug introduced in the 1.0.5 version which prevented users from saving their options, it also improves the security fix introduced in the 1.0.5 release.

1.0.5  May 30, 2014
- !Make BreezeData::sanitize() a recursive functions for handling arrays.

1.0.4 May 4, 2014
- Fixed checking a wrong variable on BreezeQuery:getCount() which prevented to get the real count.
- Convert to an array of integers the passed value on BreezeQuery:getCount() to prevent weird servers to give errors.
- The load more feature on the general wall wasn't working because userID wasn't defined.
- On the createTopic log and any other log, use the username instead of the real name.

1.0.3 Apr 12, 2014
- Add BreezeQuery::getStatus() to allow users to get status data directly from the DB
- Fixed silly bugs with error strings
- BreezeQuery::getCount() now work with arrays, each element gets casted as integer before passing the array to avoid errors. It also removes the need to pass sql code, it now needs the column name only.

1.0.2  Mar 23, 2014
- Fixes an issue where users will get notified about new topics on boards they cannot see. Thanks to br360 for the report.
- Add support for accounts that were deleted but still has activity recorded.
- Css files were merged and minified, thanks to Antes
- Other small fixes/improvements.

1.0.1 Mar 2, 2014
- Notifications were pretty broken and with unfinished code.
- Changed the value column in breeze_options to a text field
- Weird css on breeze.css was interfering with the forum's style.
- Missed a text string for permissions and fixed a logic issue as well, users weren't able to post new comments even though they have the appropriated permissions to do so.
- New status were appended to a non-existent div.
- Disabling the wall didn't hide the button on the profile menu.
- Support feed url uses a scheme-less url for servers using https
- Typos and corrections on language strings.
- Fixed a logic issue when posting a new comment, the code wrongly assumed the poster and the profile owner were the same person.
- Added a new notification for the profile owner when someone made a comment on any status on their own wall.

1.0 - Windmill
Initial release

[/code]
