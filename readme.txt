=== By this Author ===
Contributors: racanu
Tags: overview, generate, list
Requires at least: 4.1
Tested up to: 5.0
Stable tag: 1.2.0
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

Allows inserting in a post a list of articles by or attributed to an author.

== Description ==

Sometimes in a website the articles are not authored by the respective registered users but still need to be attributed to them somehow.

This plugin addresses this specific situation and offers a way to deal with it. 

The idea is that articles are tagged with the names of people to which they should be attributed.
Thus, when making an author's bio page a shortcode can be inserted that will show a list of articles authored by or attributed to a registered user.
If the user later registers using the same name and starts publishing his/her own posts, they will auto-magically be inserted in his bio page.

The shortcode is:

    [by-this-author name="name of the author" post_types="authored_by, attributed_to" posts_per_page=#]
    
*post_types* is optional and may contain one of or both keywords
*posts_per_page* is optional and specifies how many posts to display

There is no support for pagination so the only way of limiting very long pages is to use the *posts_per_page* parameter.

The plugin solves a very specific problem of a very specific website. It may be useful to someone else too.

A single translation file is provided for it_IT.

There are two other, unrelated, shortcodes included.

Age calculation

    [get-age ref_date="reference date" end_date="end date"]

Calculates the age (in years) since a reference date. The age is calculated until the current moment, unless *end_date* is specified.
This is meant to show ages of people (possibly at the time of their death) but may also be used for other events that have a start and maybe an end date.

*ref_date* is the date (typically in the past) to which the age refers
*end_date* is optional and represents the date up to which the age is calculated (typically in the past too but may also be in the future)

Time machine

    [time-machine ref_time="reference moment" future_text="text to show before ref_time" past_text="text to show after ref_time"]

Allows altering the text of the post based on the current moment relative to a reference moment.

Suppose a post is published before an event, is should refer to that event in terms of the future.
However, after the time of the event (the reference time) has passed, the same post should refer to the event in terms of the past.
This simple change avoids the post looking stale until you manually edit it to maybe add post-event information.

*ref_time* is the date and time of the event; up until midnight after the event it will show the future_text, then the past_text
*future_text* is the text to show while the event is still in the future
*past_text* is the text to show when the event is in the past

== Installation ==

1. Download the zip file, and use WordPress' plugin installation page in the dashboard
2. Activate the plugin through the 'Plugins' menu in WordPress
3. Place the shortcode(s) you need in your post

== Changelog ==

= 1.0 =
* First release.

= 1.1.1 =
* Updated "tested to" version to 4.5.3

