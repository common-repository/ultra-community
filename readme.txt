===Ultra Community===
Contributors: ultrateam, mihche
Tags: community, membership, members directory, user profile, login form, registration form
Requires at least: 4.6
Requires PHP: 5.6
Tested up to: 5.4
Stable tag: 2.1.2
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

Ultra Community is a powerful community plugin for WordPress that takes your site beyond the blog.

== Description ==
A complete bundle to create your community site without having any advanced knowledge of programming.
It includes all of the features you’ve come to expect from any online community, like stunning user profiles, login and registration forms, notifications, and many more.

See Ultra Community in action: [Ultra Community Demo Site](http://demo.ultracommunity.com/)

= Translation =

Currently Ultra Community is translated into the following languages:

* German
* Italian
* Swedish

We'd really appreciate if you could help [translating Ultra Community into your language here](https://translate.wordpress.org/projects/wp-plugins/ultra-community/).


= Summary of features =

**Front end Login**
Ultra Community offers an interactive front-end user login, with error handling and custom redirection support.

**Front end Registration**
Create multiple fully customizable front-end registration forms for your users

**User Profile**
Create amazing user profiles which can be fully customized to your site’s specific requirements

**User Roles**
Ultra Community allows you to create multiple user roles and give each role its own permissions and capabilities

**Groups**
Allow users to create and join groups

**Members Directory**
List all the registered users filtering them by User Role and setup which user roles can access the directory

**Groups Directory**
List all groups filtering them by group type (public, private, secret)


**Email Notifications**
Allows you to setup and customize email notifications that can be sent to users after a certain event happens. Each email type can be activated or deactivated


= Compatibility =
On a **WP Multisite** you can either activate the plugin network wide or on a single site.


> **Note: This plugin requires PHP 5.3 or higher to be activated.**

== Changelog ==
= 2.1.2 =
* Fixed Members Directory
* Prevent duplicating User Roles
* New Extension [User Notifications](https://ultracommunity.com/downloads/user-notifications/)
* New Extension [User Letters Avatar](https://ultracommunity.com/downloads/user-letters-avatar/) Extension version 1.1


= 2.1.1 =
* Fixed page background color settings
* Fixed Groups Directory list
* Fixed Login popup layout
* Allow username with spaces

= 2.0.25 =
* Added languages folder and ultra-community.pot file

= 2.0.24 =
* Added New User Role Capability - Users can delete their activity posts


= 2.0.22 =
* Fixed Groups Directories Pagination
* Fixed Error when creating a new Members Directory
* Allow Admin to manually approve "Awaiting email Confirmation" user status


= 2.0.21 =
* Fixed Directories Pages - integration with WPML

= 2.0.20 =
* Fixed - forms ajax request
* Added new filter UltraCommHooks::FILTER_MEMBERS_DIRECTORY_QUERY_ARGUMENTS to dynamically sort members directory


= 2.0.19 =
* Fixed - Multiple pair of fields added for default forms

= 2.0.18 =
* Fixed Minimum and Maximum number of characters allowed for form fields
* Added Browser Page Title
* Added Registration Url in Login Forms
* CSS minor fixes

= 2.0.17 =
* Better detection of post thumbnails
* Prevent UltraCommunity to overwrite user registration date
* Fixed Members Directory pagination

= 2.0.16 =
* Added a new option to enable using built in page template
* Added a shortcode to embed user profile page [ultracomm_user_profile]
* Fixed user profile form submission by section
* Fixed Divider Form Field properties
* CSS and JS fixes


= 2.0.15 =
* Compatibility with [User Post Submissions](https://ultracommunity.com/downloads/user-post-submissions/) Extension version 1.1
* Compatibility with [Extended Activity](https://ultracommunity.com/downloads/extended-activity/) Extension version 1.1



= 2.0.14 =
* Fixed Form Checkboxes
* Fixed mapped form fields to user meta keys
* Added Radio Buttons form fields
* Improved user settings wp routing



= 2.0.13 =
* Fixed User Avatar URL for Post Comments
* Fixed User Activity StatusId display
* Added the following filters constants in UltraCommHooks class: FILTER_FRONT_END_CAN_USE_GLOBAL_TEMPLATE,  FILTER_USER_CAN_JOIN_GROUP, FILTER_USER_CAN_CREATE_GROUP
* Added the following functions in uc_functions.php file : uc_user_can_join_group, uc_user_can_create_groups, uc_count_user_all_groups, uc_count_user_created_groups


= 2.0.12 =
* Introducing [User Post Submissions](https://ultracommunity.com/extensions/) Extension

= 2.0.11 =
* Fixed Group Statistics on Groups Directory

= 2.0.10 =
* Introducing [Social Share Extension](https://ultracommunity.com/extensions/)
* Added support for Group and User activities url
* Fixed login popup appearance
* CSS and JavaScript fixes


= 2.0.9 =
* Added option to enable User Gravatar URL
* Added Green and Purple color schemes


= 2.0.8 =
* Fixed forms short codes

= 2.0.7 =
* Improved User Roles section
* Minor CSS and JavaScript fixes

= 2.0.6 =
* Introducing [Custom Tabs Extension](https://ultracommunity.com/extensions/)
* Fixed compatibility with  [bbPress Extension](https://ultracommunity.com/extensions/)
* Minor CSS and JavaScript fixes

= 2.0.5 =
* Fixed Save Forms admin issue
* Fixed Private Groups show content issue
* Added Groups tab section on User profiles
* Added Amazon and iTunes on Social Networks
* Added Page Custom CSS option
* Added Redirect Author User URL to profile option
* Added Redirect Comment User URL to profile option


= 2.0.4 =
* Fixed User Registration redirect
* Fixed Members Directory list for existing users
* CSS fixes
* JavaScript fixes

= 2.0.3 =
* Introducing [Ultra Community Extensions](https://ultracommunity.com/extensions/)

= 2.0.1 =
* Bug fixes

= 2.0 =
* Initial release

