======================================================================
  BREEZE 2.0.0-beta.1  -  Social Wall Mod for SMF 2.1
  https://missallsunday.com
  License: Mozilla Public License 2.0
======================================================================

Breeze adds a social wall to every member profile on your forum.
Members can post status updates, leave comments, give likes, and
mention each other. Works with any SMF theme, no file edits required.

----------------------------------------------------------------------
  REQUIREMENTS
----------------------------------------------------------------------

  * SMF 2.1.x
  * PHP 8.3 or higher

----------------------------------------------------------------------
  USER FEATURES
----------------------------------------------------------------------

  [ Personal Wall ]

    Every member gets their own wall on their profile page. It is
    opt-in: each user decides whether to activate it. When off, the
    profile falls back to the standard SMF summary page.

  [ Status & Comments ]

    Members post short status updates on any wall. Other members can
    reply with comments. The editor supports BBC.

  [ Action Bar ]

    Every status and comment displays a compact action bar with inline
    buttons for likes, toggling the comment section, and deleting the
    post. Buttons are shown or hidden based on the member's permissions.

  [ Likes ]

    Any status or comment can be liked. Each member can only like a
    given post once. The like count updates in real time.

  [ General Wall ]

    A dedicated "Wall" page in the main navigation aggregates the
    latest posts from all of a member's buddies in one feed.
    Visibility is controlled by a Breeze permission (see below).

  [ About Me tab ]

    Users can write a short bio on their profile using full BBC
    support. The tab is hidden automatically when left empty.

  [ Buddies tab ]

    An optional tab on the profile page that lists all buddies with
    quick add/remove buttons.

  [ Block List ]

    Each user can maintain a private block list. Blocked members
    cannot view the wall owner's profile wall, and the wall owner
    will not see blocked members' walls either (symmetric blocking).

  [ Post Confirmation ]

    Users can opt in to a confirmation dialog that appears before
    every status or comment submission, preventing accidental posts.

  [ Mentions ]

    Type @ in the editor to search for and mention other members.
    A real-time suggestion dropdown appears as you type. Mentioned
    members receive an SMF alert linking directly to the status or
    comment where they were mentioned.

  [ Alerts ]

    Breeze plugs into the standard SMF alert system. Each user can
    tune their preferences independently. Supported alerts:

      - Someone posted a status on my wall
      - Someone commented on a status I made
      - Someone commented on a status posted on my wall
      - Someone liked one of my status or comments
      - Someone mentioned me in a status or comment

----------------------------------------------------------------------
  ADMIN FEATURES
----------------------------------------------------------------------

  [ Flood Control ]

    Limit how many status updates and comments a member can post
    within a rolling time window. Both the message count and the
    window length are configurable. Defaults to 10 messages per
    5 minutes across all walls combined.

  [ Permissions ]

    Breeze registers its own permission group alongside the standard
    SMF permissions. Available permissions:

      - Post new status on any wall
      - Post new comments on any wall
      - Delete own status (anywhere)
      - Delete own comments (anywhere)
      - Delete any status on any wall        (for moderators)
      - Delete any comment on any wall       (for moderators)
      - Delete status posted on their own profile wall
      - Delete comments posted on their own profile wall
      - Access the general wall feed

  [ Maintenance Tools ]

    Built-in tools to detect and repair data inconsistencies:
    orphan comments (whose parent status was deleted), orphan likes
    (pointing to content that no longer exists), and a bulk action
    to enable the personal wall for every member at once.

----------------------------------------------------------------------
  SUPPORT
----------------------------------------------------------------------

  Questions, bug reports, or feedback:
    https://missallsunday.com

  Breeze is open-source (MPL 2.0). Source code:
    https://github.com/MissAllSunday/Breeze

======================================================================
