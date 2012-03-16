What is Breeze?

Breeze is an Ajax powered social mod for your SMF forum, it allows your users to turn their profile pages into a dymanic wall where they can publish new status and comment on other user's status.


Requirements:

-SMF 2.0.x
-PHP 5.3 or greater.

Features:

-Uses hooks, no template edits are necessary, this mod will work with any theme.
-Global permissions using the SMF permission system to allow member groups to post status on any wall, post comments on any wall, delete status/comments on any wall. By default, users are able to post status, post comments and delete status/comments on their own profile.
The mod still depends on the "can see profile own/any"  permission.
-Modules, modules are tiny blocks of info, for example, a "last visits" one or a "my buddies" block.
-Pagination to show multiple status easily.
-Some user's individual settings:
	-Enable/Disable their own wall.
	-Do not show my wall to users in my ignore list
	-How many status will be displayed per page
	-Module's independent settings


Known issues:

-The visitors module doesn't work quite well.


Things left to do:

-Re-write the buddy system to include confirmation from users when someone marks them as a buddy.
-General Wall, where users can see updates and status from their buddies
-Improve UI
-Like/Unlike system, probably extend it to messages too.
-Notifications
-Simple Gallery and tagging in images
-Build a better module system, to allow any module to easily integrate with Breeze

This mod uses the following scripts:

-Srinivas Tamada's notification plugin http://www.9lessons.info/2011/10/jquery-notification-plugin.html
-Nadia Alramli's confirm plugin http://nadiana.com/jquery-confirm-plugin
-Facebox https://github.com/defunkt/facebox
-zRSSFeeds http://www.zazar.net/developers/jquery/zrssfeed/
-Brandon Aaron's Live query plugin http://brandonaaron.net/code/livequery/docs