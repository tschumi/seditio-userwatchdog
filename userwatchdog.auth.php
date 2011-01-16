<?PHP

/* ====================
Seditio - Website engine
Copyright Neocrome
http://www.neocrome.net

[BEGIN_SED]
File=plugins/userwatchdog/userwatchdog.auth.php
Version=121
Updated=2008-jan-18
Type=Plugin
Author=riptide
Description=Delete users which stayed off your website too long automaticaly
[END_SED]

[BEGIN_SED_EXTPLUGIN]
Code=userwatchdog
Part=users.auth.check.done
File=userwatchdog.auth
Hooks=users.auth.check.done
Tags=
Minlevel=0
Order=10
[END_SED_EXTPLUGIN]

==================== */

if ( !defined('SED_CODE') ) { die("Wrong URL."); }

if($cfg['plugin']['userwatchdog']['uwd_enableplugin'] == 'yes')
  {
  $uwd_reprieveslot = $cfg['plugin']['userwatchdog']['uwd_reprieveslot'];

  if ($rusername != '')
    {
    //check if it is a user with a reprieve and if yes, clear the reprieve date
    $uwd_sql = sed_sql_query("UPDATE $db_users SET ".$uwd_reprieveslot."='' WHERE ".$uwd_reprieveslot.">'0' AND user_name='".sed_sql_prep($rusername)."' LIMIT 1");
    $uwd_backagain = sed_sql_affectedrows($uwd_sql);

    if ($uwd_backagain>0)
    	{ sed_log("User watchdog plugin welcomes back: ".sed_sql_prep($rusername)."",'adm'); }

    unset($uwd_backagain);
    }
  }
?>