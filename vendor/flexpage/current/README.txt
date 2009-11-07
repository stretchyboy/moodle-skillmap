This zip file installs a new, tab based format with a left menu navigation, optional top tabs, and the ability to put blocks and modules in a 3 column view on a single page.
 
About:
The Flexpage format is a new course format for Moodle. It is named flexpage for 'flexible page', as it is a format that enables a content author a great deal of control over how and where content is displayed.

The basic features are:

    * 3 column view: Moodle blocks and modules can be assembled in any order in up to 3 columns on the page.
    * Inline Module display: the format can be extended to display module content inline (in the page). Presently this only has been extended for Forum, Assessment, and Resources, other modules show a link as expected.
    * Back button: When you enter a module, there is a back button to return you to the page you were previously on.
    * Parent-Child pages: a page can have child pages, and child pages can have child pages.
    * Automatic Tabs: A parent page can create a top tab if uses with the flexpage theme.
    * Course Menu: this block used with the Flexpage format provides a multiple menu system for navigating the pages.
    * Roles based menus: there is a 'view menu' role, so menus can be hiddent/shown based on user role.
    * Backup and restore: any course in Flexpage format can be fully or partly backed up and copied to another course


Flexpage was developed with funding from Intel Education. The Flexpage Team: Lead developers: Mark Nielsen, Jeff Graham, associate developers: Michael Avelar, Doug Dixon, Jason Hardin. Interaction design, project managment, Michael Penney.

###System requirements###
PHP 5.1+
Moodle 1.8.2+

All of these new tools are built using the standard Moodle module/block/theme/course format/language API, they are built to install on a standard Moodle 1.8.3 installation. If you have not installed Moodle modules, blocks, etc. before, please read the Moodle documents on installing custom modules before starting this process. This code requires the installation of a block, a theme, a course format, a module, and language files-in addition to the page menu module.

They have not been tested on Moodle 1.7 or 1.9. 

Always test new code thoroughly on your test server before using it on a production server (this code has been tested on Solaris and MacOS X). 

To work correctly the format requires all 5 components to be installed.

To install:

1: Put the blocks/page_module block in your moodle/blocks folder.
2: Put the course/format/page course format in your moodle/course/format/ folder
3: Put the language files in lang/en_utf8/ files in your moodle/lang/en_utf8/folder.
4: Put the mod/pagemenu in your moodle/mod/pagemenu folder
5: Put the theme/page in your moodle/theme/page folder.

Visit your admin/Notifications link to install the database tables.

If there are no errors, switch your site theme to the page theme, create a course and set it to use the page format. 

Visit the tutorial pages here: labs.developer.moodlerooms.com for more information on how to use the page format.

