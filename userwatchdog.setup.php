<?PHP

/* ====================
Seditio - Website engine
Copyright Neocrome
http://www.neocrome.net

[BEGIN_SED]
File=plugins/userwatchdog/userwatchdog.setup.php
Version=121
Updated=2008-jan-18
Type=Plugin
Author=riptide
Description=Delete users which stayed off your website too long automaticaly
[END_SED]

[BEGIN_SED_EXTPLUGIN]
Code=userwatchdog
Name=User Watchdog
Description=Delete users which stayed off your website too long automaticaly
Version=121
Date=2008/01/18
Author=riptide
Copyright=
Notes=Cleaning performed each time the admin panel is opened.<br />If you use the default values - it will take totally 100 days before the user is deleted.
SQL=
Auth_guests=R
Lock_guests=W12345A
Auth_members=R
Lock_members=12345A
[END_SED_EXTPLUGIN]

[BEGIN_SED_EXTPLUGIN_CONFIG]
uwd_enableplugin=01:select:yes,no:no:Enable the plugin (save your desired config values first)
uwd_timelimit=02:string::90:Days after users without visit should be deleted (Default 90)
uwd_reprieve=03:string::10:Days they have time to come back before they will be deleted (Default 10)
uwd_reprieveslot=04:select:user_extra1,user_extra2,user_extra3,user_extra4,user_extra5:user_extra5:Extra field where the reprieve date will be stored (Default user_extra5)
uwd_dontcleangroup=05:string::5:Groups that should be excluded from cleaning (comma seperated)  (Default 5)
uwd_delpfs=06:select:yes,no:yes:Delete user PFS (Default Yes)
[END_SED_EXTPLUGIN_CONFIG]

==================== */

if ( !defined('SED_CODE') ) { die("Wrong URL."); }

?>