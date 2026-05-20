# Wall Visibility Rules

This page explains how Breeze decides whose posts appear on your wall, and the
reasoning behind those choices.

Two principles drive everything that follows:

1. **Blocking always wins over being buddies.** If someone has blocked you, you
   don't see their posts — full stop — even when you share a buddy in common.
2. **Blocking is mutual.** If you block someone, neither of you sees the other.
   It's not a one-way mute.

The rest of this document walks through *why* each principle exists, gives a
concrete example, and ends with the full checklist Breeze runs for every post.

## Table of Contents
1. [Where these rules apply](#1-where-these-rules-apply)
2. [Why blocking beats being buddies](#2-why-blocking-beats-being-buddies)
3. [Why blocks are mutual](#3-why-blocks-are-mutual)
4. [A worked example](#4-a-worked-example)
5. [The full checklist](#5-the-full-checklist)
6. [Permission tiers](#6-permission-tiers)

---

## 1. Where these rules apply

These rules decide what you see when someone else's post might show up in front
of you — your general wall feed, the buddies activity stream, single-post
views, comments, and notifications.

They also govern **access to profile wall pages themselves**. If a block exists
between you and the person whose wall you are trying to visit — in either
direction — Breeze denies access to the wall page entirely, before any per-post
rules are even considered. In other words, symmetric blocking applies at every
level: the page gate, the feed, the wall, and the comment thread.

They do **not** affect who is allowed to *write* posts. Posting permissions are
a separate topic, handled elsewhere.

## 2. Why blocking beats being buddies

Buddying someone is a social signal: *"I'd like to see what this person is up
to."* Blocking is a safety choice: *"I don't want this person to be able to
reach me."*

These two things are not opinions of the same kind. If a social signal could
override a safety choice, blocking would lose its meaning — anyone you ever
blocked could still reach you through a mutual friend, and the whole point of a
block is that it works no matter who else is in the room.

Every major social network (Facebook, X/Twitter, Instagram, LinkedIn) resolves
this the same way: a block is honored everywhere the blocked person might
otherwise have seen your content — including under a mutual friend's posts.
Breeze follows the same rule.

In short: **the author of a post controls who can see it. A mutual friend
cannot let someone back in.**

## 3. Why blocks are mutual

Earlier versions of Breeze treated the block list as one-directional: "people
I've blocked from visiting my wall." That model has two problems most users
don't expect:

- **It still lets the blocked person watch you.** If you block someone but they
  can still see your posts wherever they appear, the block hasn't really
  protected you — it has just hidden *their* wall from *you*. People who block
  someone expect to disappear from that person's view as well.
- **Mutual friends become a backdoor.** Without mutuality, the person you
  blocked keeps seeing your activity through any friend you share. That is
  exactly the situation a block is meant to end.

The cleaner rule is simpler to remember and easier to trust: **if either of you
has blocked the other, you are simply invisible to one another on Breeze.**

## 4. A worked example

Three people: **Alice**, **Beth**, and **Carlos**.

- Alice writes a post on Beth's wall.
- Carlos and Beth are buddies.
- Alice has blocked Carlos.

Should Carlos see Alice's post in his general wall feed?

**No.** Even though Carlos is buddies with Beth (the wall owner), Alice's block
is the deciding factor. Alice does not want Carlos to see her content, and
being friends with Beth does not undo that.

If it had been Beth (not Alice) who blocked Carlos, the answer would still be
no — Beth controls her own wall, including who gets to see things posted on
it.

## 5. The full checklist

The exact checklist depends on *how* the post is reaching you. The rules are
stricter when Breeze is choosing what to surface in your general feed than
when you are visiting somewhere on purpose.

### Accessing a profile wall page

Before any posts are considered, Breeze checks whether you are even allowed to
open the wall. You are denied access if any of the following is true:

- Your SMF member group does not have the `profile_view` permission.
- The wall owner has you in their block list.
- You have the wall owner in your block list.

The block checks run in both directions. This is the same symmetric rule that
applies to individual posts; it just happens earlier, at the page level.

Guests (not logged in) are subject only to the first two checks — Breeze cannot
look up a guest's block list.

### When a post might appear in your general feed

Before the general wall page loads at all, Breeze checks that your member group
has the `viewGeneralWall` Breeze permission. Without it you cannot open the page
and no feed is built.

For every post Breeze considers surfacing in your general wall feed, it then
runs through these five questions. The post is shown only if the answer to all
five is *yes*:

1. **Is this post connected to one of your buddies?** Either the person who
   wrote it, or the person whose wall it was posted on, has to be in your
   buddy list.
2. **Has the author agreed to appear in general feeds?** Each user can opt out
   of having their own posts surfaced on other people's general feeds.
3. **Is there no block between you and the author**, in either direction?
4. **Is there no block between you and the owner of the wall**, in either
   direction?
5. **Does your member group have the `viewGeneralWall` Breeze permission?**
   This is the forum-level gate that controls access to the general wall
   feature as a whole. It is separate from `profile_view`, which only governs
   access to individual profile wall pages.

### Your own posts

Posts you wrote yourself are an exception to questions 1 and 2. You always
see your own posts in your general feed, regardless of whether the wall
owner is in your buddy list and regardless of your own "don't surface me
in general feeds" setting — you are implicitly part of your own social
graph, and your feed opt-out is about other people's feeds, not yours.

Questions 3 to 5 still apply: if you posted on someone's wall and they
later blocked you, that post disappears from your view too.

### When you're visiting a wall, or following a link to a single post

You went there on purpose, so questions 1 and 2 do not apply — Breeze does
not filter someone's wall down to just your buddies, and "don't surface me
in general feeds" is a feed opt-out, not a wall opt-out.

The safety and permission questions still apply, every time:

- **Does your member group have the `profile_view` SMF permission?** This
  governs access to profile pages and is checked at the page gate, before
  any posts are considered. The `viewGeneralWall` Breeze permission does not
  apply here — that permission is specific to the general wall feed.
- **Is there no block between you and the author**, in either direction?
- **Is there no block between you and the owner of the wall**, in either
  direction?

If a post on the wall fails any of these, it is hidden from you even when
the rest of the wall is visible.

### Comments

Comments follow the post they belong to: if you cannot see the post, you do
not see its comments. If you can see the post, you see every comment on it
*except* comments written by someone you've blocked or who has blocked you.

### A note on per-viewer decisions

If any answer is *no*, the post — along with any of its comments that you'd
otherwise have seen — is hidden from you. Other people whose answers are all
*yes* will still see the same post normally; visibility is decided per
viewer, never globally.

---

## 6. Permission tiers

Breeze uses two separate permission tiers to control who can see what. They
are independent of each other and serve different purposes.

### SMF `profile_view`

This is a standard SMF permission. It controls whether a member group can open
any profile page on the forum. Breeze checks it before allowing a visitor to
view a personal profile wall page. It is not specific to Breeze and is managed
through the normal SMF permissions panel alongside all other profile-related
permissions.

### Breeze `viewGeneralWall`

This is a Breeze-owned permission registered under the Breeze permission group
in the SMF permissions panel. It controls whether a member group can access the
general wall feature — the `?action=wall` page and the buddy-activity feed it
displays.

When a member group does not have this permission:

- The general wall menu entry is hidden from members of that group.
- Navigating to `?action=wall` directly results in an access-denied error.
- No activity is surfaced in the feed for that viewer (gate 5 of the feed
  checklist fails before any post is evaluated).

**This permission defaults to not granted**, consistent with how SMF handles
all permissions. An administrator must explicitly grant it to each member group
that should have access to the general wall.

### At a glance

| Context | Permission checked | Owned by |
|---|---|---|
| Opening a profile wall page | `profile_view` | SMF |
| Accessing the general wall page and feed | `viewGeneralWall` | Breeze |

---

*The engineering plan that turns these rules into code is tracked separately
and is intentionally not part of this document.*
