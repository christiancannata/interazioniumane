=== Disable User Gravatar ===
Contributors: netweblogic
Tags: avatar, gravatar, activity stream, wordpress, wordpress mu, wpmu, BuddyPress, wire, activity
Requires at least: 2.7
Tested up to: 5.6
Stable tag: 3.1

Stops WordPress from grabbing a user avatar using their registrated email from gravatar.com.

== Description ==

This is a very simple and lightweight plugin that anonymizes default avatars and prevents the user's gravatar being automatically obtained from gravatar.com based on their registered email. This would be useful for sites where users require an extra layer of privacy, or if you just want to prevent potentially silly or embarrasing avatar accidents.

If you're using Identicons or any other generated default avatar, the user should keep a consistent avatar unless they change their registered email.

You can also disable Gravatar completely and choose a default image to display.

This plugin is also compatible with other avatar customization plugins such as [Avatar Manager](https://wordpress.org/plugins/avatar-manager/), [BuddyPress](https://wordpress.org/plugins/buddypress/) or [Add New Default Avatar](https://wordpress.org/plugins/add-new-default-avatar/), since this plugin specifically prevents the gravatar of a specific user email being used and reverts to the default or user-defined avatar.

**Important 3.0 Update - BuddyPress users should visit the Settings > Discussion page on your dashboard and choose one of the Disable Gravatar options to restore previous behavior.**

If you have any issues or suggestions, please visit our [support forums](https://wordpress.org/support/plugin/disable-user-gravatar).

If you find this plugin useful and would like to say thanks, please leave us a [5 star review](https://wordpress.org/support/view/plugin-reviews/disable-user-gravatar?filter=5)!

== Installation ==

1. Upload this plugin to the `/wp-content/plugins/` directory and unzip it, or simply upload the zip file within your wordpress installation.

2. Activate the plugin through the 'Plugins' menu in WordPress

3. Gravatars should now automatically be automatically anonymized.

4. Visit Settings > Discussion in the dashboard to disable Gravatar avatars completely and choose your own default image.

Alternatively, download the plugin and copy disable-user-gravatar.php to your [Must Use Plugins folder](https://codex.wordpress.org/Must_Use_Plugins), it'll activate automatically.

== Screenshots ==

1. When activated, gravatar.com avatars become the default avatar 

== ChangeLog ==
= 3.1 =
* fixed BuddyPress compatability issue

= 3.0 =
* added options to disable gravatar connection completely and use default image instead
* fixed avatar display issues when BuddyPress is enabled
* added further BuddyPress support to use default image or use BP's gravatar disabling option
* Notice : BuddyPress users should visit the Settings > Discussion page on your dashboard and choose one of the Disable Gravatar options to restore previous behavior.

= 2.2 =
* added filter for BuddyPress fallback on Gravatar (kudos to @baptx)

= 2.1 =
* changed object structure to static and updated to be PHP 7 compatible
* fixed bp_core_fetch_avatar showing original avatar when only requesting the URL
* fixed avatars for post author not taken into account in certain situations
* changed member.%USER%@mydomain.com to member.%USER%@somerandomdomain.com for added randomness
* added filter to remove 'You can change your profile picture on Gravatar' on profile page