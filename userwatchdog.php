<?PHP

/* ====================
Seditio - Website engine
Copyright Neocrome
http://www.neocrome.net

[BEGIN_SED]
File=plugins/userwatchdog/userwatchdog.php
Version=121
Updated=2008-jan-18
Type=Plugin
Author=riptide
Description=Delete users which stayed off your website too long automaticaly
[END_SED]

[BEGIN_SED_EXTPLUGIN]
Code=userwatchdog
Part=main
File=userwatchdog
Hooks=admin.main
Tags=
Minlevel=0
Order=10
[END_SED_EXTPLUGIN]

==================== */

if ( !defined('SED_CODE') ) { die("Wrong URL."); }

if($cfg['plugin']['userwatchdog']['uwd_enableplugin'] == 'yes')
  {
  require('plugins/userwatchdog/lang/userwatchdog.'.$usr['lang'].'.lang.php');

  $uwd_reprieveslot = $cfg['plugin']['userwatchdog']['uwd_reprieveslot'];

  $uwd_timeago = $sys['now'] - ($cfg['plugin']['userwatchdog']['uwd_timelimit']*24*3600); // X days (X x 24 x 3600 seconds)
  $uwd_reprieve = $sys['now'] - ($cfg['plugin']['userwatchdog']['uwd_reprieve']*24*3600); // X days (X x 24 x 3600 seconds)
  $uwd_now = $sys['now'];
  
  //check if there are users who stayed off your website too long
  $sql = sed_sql_query("SELECT user_id,user_email FROM $db_users WHERE ".$uwd_reprieveslot."='' AND user_banexpire='0' AND user_lastlog>'0' AND user_lastlog<'$uwd_timeago'");

  $uwd_title = $L['plu_acmailtitle'];
  $uwd_message = sprintf($L['plu_acmaimessage'], $cfg['plugin']['userwatchdog']['uwd_timelimit'], $cfg['plugin']['userwatchdog']['uwd_reprieve']);

  $uwd_counter = 0;

  while ($row=sed_sql_fetcharray($sql))
      {
      $uwd_userid = $row['user_id'];
      $uwd_email = $row['user_email'];
      
      //check if the user is member of an excluded group
      if ($cfg['plugin']['userwatchdog']['uwd_dontcleangroup'] != '')
        {
        $uwd_dontcleangroup = explode(",", $cfg['plugin']['userwatchdog']['uwd_dontcleangroup']);
        
        foreach($uwd_dontcleangroup as $k => $v)
          {
		  //build sql query to check groups
          if($uwd_buildsqlargs != '') { $uwd_buildsqlargs .= ' AND '; }
          $uwd_buildsqlargs .= "gru_groupid <> '$v'";
		  
		  //build sql query to check main group
          if($uwd_buildsqlargs2 != '') { $uwd_buildsqlargs2 .= ' AND '; }
          $uwd_buildsqlargs2 .= "user_maingrp <> '$v'";
          }

		//do the query for groups
        if($uwd_buildsqlargs != '') { $uwd_sqlargs = ' AND ('.$uwd_buildsqlargs.')'; }
        $uwd_sqlgroups = sed_sql_query("SELECT gru_groupid FROM $db_groups_users WHERE gru_userid='$uwd_userid' ".$uwd_sqlargs."");
		$uwd_sqlnum = sed_sql_numrows($uwd_sqlgroups);
		
		//do the query for main group
        if($uwd_buildsqlargs2 != '') { $uwd_sqlargs2 = ' AND ('.$uwd_buildsqlargs2.')'; }
        $uwd_sqlgroups2 = sed_sql_query("SELECT user_maingrp FROM $db_users WHERE user_id='$uwd_userid' ".$uwd_sqlargs2."");		
		$uwd_sqlnum2 = sed_sql_numrows($uwd_sqlgroups2);
		
        $uwd_setreprievedate = ($uwd_sqlnum+$uwd_sqlnum2>0) ? TRUE : FALSE;
        }
      else
        {
		//there is no group to exclude
        $uwd_setreprievedate = TRUE;
        }
        
      if ($uwd_setreprievedate == TRUE)
        {
         //send them a mail to give them a chance to come back
        sed_mail($uwd_email,$uwd_title,$uwd_message);

        //save the reprieve date in the banexpire field
        $sqltmp = sed_sql_query("UPDATE $db_users SET ".$uwd_reprieveslot."='$uwd_now' WHERE user_id='$uwd_userid'");

        $uwd_counter++;
        }
      }

  if ($uwd_counter>0)
  	{ sed_log("User watchdog plugin sent ".$uwd_counter." inactivity warning(s)",'adm'); }

  //delete user which reached the reprieve date
  $sql = sed_sql_query("SELECT user_id, user_name FROM $db_users WHERE ".$uwd_reprieveslot.">'0' AND ".$uwd_reprieveslot."<'$uwd_reprieve'");

  while ($row = sed_sql_fetcharray($sql))
    {
	//if there is the trashcan enabled, we put it there
    if ($cfg['trash_user'])
      {
      $sqltrash = sed_sql_query("SELECT * FROM $db_users WHERE user_id='".$row['user_id']."'");
      if ($rowtrash = sed_sql_fetchassoc($sqltrash))
        { sed_trash_put('user', $L['User']." #".$rowtrash['user_id']." ".$rowtrash['user_name'], $rowtrash['user_id'], $rowtrash); }
      }
    $sqldel = sed_sql_query("DELETE FROM $db_users WHERE user_id='".$row['user_id']."'");
    $sqldel = sed_sql_query("DELETE FROM $db_groups_users WHERE gru_userid='".$row['user_id']."'");
    if ($cfg['plugin']['userwatchdog']['uwd_delpfs'])
      {
      sed_pfs_deleteall($row['user_id']);
      //Avatar, Photo, Signature cleaning idea by Kilandor
      $uwd_avatar = $cfg['av_dir'] .$row['user_id']."-avatar.gif";
      $uwd_photo = $cfg['photos_dir'].$row['user_id']."-photo.gif";
      $uwd_signature = $cfg['sig_dir'].$row."-signature.gif";
      @unlink($avatar);
      @unlink($photo);
      @unlink($signature);
      }
    if ($cfg['trash_user'])
      { sed_log("User Watchdog trashed #".$row['user_id']." ".$row['user_name'],'adm'); }
    else
      { sed_log("User Watchdog deleted #".$row['user_id']." ".$row['user_name'],'adm'); }
    }
  }
?>