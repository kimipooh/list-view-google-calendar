=== Google Calendar List View ===
Contributors: kimipooh
Donate link: 
Tags: Google Calendar
Requires at least: 4.0
Requires PHP: 5.6
Tested up to: 5.7
Stable tag: 6.0
License: GPL v2  or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

The plugin is to create a shortcode for displaying the list view of public Google Calendars.
 
== Description ==

The plugin is to create a shortcode for displaying the list view of public Google Calendars.

= Document =

Please see the [documentation](https://info.cseas.kyoto-u.ac.jp/en/links-en/plugin-en/wordpress-dev-info-en/google-calendar-list-view) in detail. [Japanese documentation](https://info.cseas.kyoto-u.ac.jp/links-ja/plugin-ja/wordpress-dev-info/google-calendar-list-view)

== Installation ==

1. Upload the plugin folder to /wp-content/plugins/ directory.
2. Activate the plugin in your WordPress admin panel.

== Frequently Asked Questions ==
= How to get past events. =
Please set up "ALL" or past date to "Start Date" in the setting menu.
Default setting is to get from now to future events.

= Cannot get Google Calendar event? =
Please check "Google Calendar API Key" or "Google Calendar ID". If their setting is right, please wait for a day because the API queries limitation may be beyond. (Reference: https://developers.google.com/google-apps/calendar/pricing) 

= Can get events in multi Google Calendar? =
Yes. You can set Google Calendar ID and API Key in the shortcode.

= How to remove the settings =
Deactivate the plugin.

= How to hide a specific event =
By setting up hash tag (#display none or #display off) in an event description, so the event isn’t displayed.

== Screenshots ==
1. List View of a public Google Calendar
2. How to use the Shortcode
3. Sample of public Google Calendars
4. How to use it
5. Goolge Calendar API Settings
6. General Settngs
7. Feature Expansion & Other notice

== Changelog ==
= 6.0 = 
* Add the hash tag (#display). by setting up hash tag (#display none or #display off) in an event description, so the event isn’t displayed.
"lvgc_each_output_data" hook.
* If there are no events, "there are no events." message is displayed.
* Tested up WordPress 5.7 and php 8.0.0.

= 5.9.1 = 
* Fixed the warnings when "WP_DEBUG" is true.
* Tested up WordPress 5.6.2 and php 8.0.0.

= 5.9 = 
* Added the Google Calendar original values to the "lvgc_each_output_data" hook. The values is referred in https://developers.google.com/calendar/v3/reference/events#resource.
* Tested up WordPress 5.6 and php 7.4.2.

= 5.8 = 
* Added the response of CSRF (Cross-Site Request Forgery) vulnerability for this plugin's settings.
* Tested up WordPress 5.3.2 and php 7.4.2.

= 5.7 = 
* Added "view_location" shortcode option. If the value is not empty, location data is displayed with the title.
* Added "$gc_location" variable in the hook.

= 5.6 = 
* Tested up WordPress 5.3.2 and php 7.4.1.
* Fixed warning messages in case that "start_date" and "end_date" shortcode options are not set.
* Fixed the issue that it referred to Google Calendar even if the default "Google Calendar ID" or "API Key" aren't set. 

= 5.5 = 
* Fixed the issue that "orderbysort" setting in the admin setting menu isn't applied.
* Fixed the warnings when some Google calendar values, such as a description, are empty.

= 5.4 = 
* Fixed the hook "lvgc_each_output_data" issue.
* Added "$end_date_num" variable in the hook.

= 5.3 = 
* Tested up WordPress 5.3 and php 7.3.
* Fixed the sort issue for the multi-calendar.

= 5.2 = 
* Tested up WordPress 5.2.4 and php 7.3.
* Fixed descending sort issue.
* Improve the argument of load_plugin_textdomain function for WordPress 3.7 or later. 

= 5.1 = 
* Tested up WordPress 5.2.2 and php 7.3.
* Fixed the array check issue in get_google_calendar_contents function.
* Fixed the error message issue in case that there are not any shortcode options.
* Fixed the warning message issue  for file_get_contents function.

= 5.0 = 
* Supported the strtotime date format for shortcut option "start_date" and "end_date". Various date formats for strtotime function, such as "now", "+1 days", "-2 days", "yesterday", "-1 week", and so on can be used.
* Added new shortcode option "max_display". "max_view" is for getting the number of items from Google Calendars. "max_display" is for displaying the number of items. If "max_display" isn't set, "max_display" is automatically set the value of "max_view" value. If there are mixed events (1.2.3. events in Japanese, 5.6.7. events in English) and set "max_view=5", lang="en", 5. and 6. events are only displayed. Therefore, you need to set "max_view" is 6 or more and "max_display" is 5. By these shortcut options, the plugin gets 6 ore more events and picks up 5 events in English among 6 or more events. 
* Fixed the issue which could not get a date if the WordPress timezone setting is "Offset" time (+9, -1, etc.).
* Fixed the processing issue regarding max_view (maxResults) value in case of multi Google calendars.


= 4.6 = 
* Allowed the html tag on "description" of Google Calendar.

= 4.5.1 = 
* Fixed help message for "start_date", "end_date" shortcode's date format on the setting menu.

= 4.5 = 
* Fixed the processing of "start_date", "end_date" shortcode's options.
* Fixed the default value of "orderbysort" shortcode's option (default: descending)

= 4.4 = 
* Added the value of "gc_description" on the hook.

= 4.3 = 
* Fixed current_time function error.
* Tested up WordPress 5.0 and php 7.2.

= 4.2 = 
* Tested up WordPress 4.9.

= 4.1 = 
* Added target="_blank" in a link tag.

= 4.0 = 
* Support of Multi Google Calendars.
* Fixed getting local timestamp in case of not set "start_date" option.
* Fixed the processing for #organizer hash tag in case of including a space in the value. 
* Changed the default for orderbysort is descending.

= 3.1 = 
* re-uploaded library/tags/lip.php template.

= 3.0 =
* Added the hash tag "#organizer". If you set the hash tag, you can use $hash_tags_organizer_value or $hash_tags['organizer']['value'] on the hook.
* Added shortcode option "enable_view_category". If you want to display the category (#type and #organizer), please set this value to "true" or not empty value (default is "")
* Deleted "html_tag_date_class" and "html_tag_title_class" for migrating "html_tag_class".
* Reduced the code of the template tag files (library/tags/) for more easily use.

= 2.2 =
* Moved get_select_lang_data function to the extended class.
* Create gclv_hash_tags class for getting the hash tags in the description field on Google Calendar
* Added the template html_tag "lip".
* Fixed CSS name.
* Added the attribution "hash_tags" and "hash_tags_type_title" for the hook.

= 2.11 =
* Fixed to display a debug message in case of using lang shortcode.

= 2.1 =
* Added shortcode option "lang". 

= 2.0 =
* Removed "lvgc_output_data" and "lvgc_gc_data" hooks for the security reason.
* Fixed the setting name.
* Added the secure option "hook_secret_key" on the shortcode for "lvgc_each_output_data" hook.


= 1.51 =
* Added the translation for the documentation link.

= 1.5 =
* Fixed the translation issue.
* Fixed the documentation.

= 1.43 =
* Tested up WordPress 4.8 and PHP 7.1.

= 1.42 =

* fixed. "lvgc_each_output_data" hook. 

= 1.41 =

* fixed. "lvgc_each_output_data" hook. 

= 1.4 =

* added "lvgc_each_output_data" hook for handling each output data. 

= 1.3 =

* fixed default html_tag setting and added shortcode option "id".

= 1.2 =

* added two hooks of "lvgc_output_data" and "lvgc_gc_data".
"lvgc_output_data" hook can handle for the output data. "lvgc_gc_data" hook can handled for getting Google Calendar data.

* Fixed timezone issue by using current_time and get_date_from_gmt function instead of date function.

* Added to a class "list-view-google-calendar-holding" to html tag for a holding event.

= 1.1 =

* added 4 shortcode options (max_view, html_tag_class, html_tag_date_class, html_tag_title_class)

= 1.0 =

* First Released.


