<?php

declare(strict_types=1);

/**
 * @license http://www.mozilla.org/MPL/ MPL 2.0
 */

global $txt;

$txt['permissiongroup_simple_breeze_per_simple'] = 'Breeze mod permissions';
$txt['permissiongroup_breeze_per_classic'] = 'Breeze mod permissions';

$txt['permissionname_breeze_canCover'] = 'Be able to upload an image as cover for their own wall <br />
<span class="smalltext">  The master setting needs to be enable first.</span>';
$txt['permissionname_breeze_deleteStatus'] = 'Delete all status on any wall<br />
<span class="smalltext">  This overwrites any other delete permission the user might have.
Deleting a status also deletes all comments associated with it.</span>';
$txt['permissionname_breeze_deleteComments'] = 'Delete all comments on any wall<br />
<span class="smalltext">  This overwrites ant other delete comment permission the user might have.</span>';
$txt['permissionname_breeze_deleteOwnStatus'] = 'Delete their own status.<br />
<span class="smalltext">
	Regardless of where it has been posted. Deleting a status also deletes all comments associated with it.
</span>';
$txt['permissionname_breeze_deleteOwnComments'] = 'Delete their own comments.<br />
<span class="smalltext">  Regardless of where it has been posted</span>';
$txt['permissionname_breeze_deleteProfileStatus'] = 'Delete status made on their own profile.<br />
<span class="smalltext">  Regardless of who posted them.</span>';
$txt['permissionname_breeze_deleteProfileComments'] = 'Delete comments made on their own profile.<br />
<span class="smalltext">  Regardless of who posted them.</span>';
$txt['permissionname_breeze_postStatus'] = 'Post new Status on any wall<br />
<span class="smalltext">  By default, the profile owner always has the ability to post on their own wall.</span>';
$txt['permissionname_breeze_postComments'] = 'Post new Comments on any wall<br />
<span class="smalltext">  By default, the profile owner always has the ability to post on their own wall.</span>';
$txt['permissionname_breeze_viewGeneralWall'] = 'Access the general wall feed<br />
<span class="smalltext">  Controls which member groups can visit the general wall page and have activity surfaced in their feed.
  Grant this permission to every group that should be able to see the general wall.
  Members who are not granted this permission will not see the general wall menu entry and will receive an access-denied error if they navigate to it directly.</span>';
