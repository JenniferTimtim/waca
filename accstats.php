<?php
/**************************************************************
** English Wikipedia Account Request Interface               **
** Wikipedia Account Request Graphic Design by               **
** Charles Melbye is licensed under a Creative               **
** Commons Attribution-Noncommercial-Share Alike             **
** 3.0 United States License. All other code                 **
** released under Public Domain by the ACC                   **
** Development Team.                                         **
**             Developers:                                   **
**  SQL ( http://en.wikipedia.org/User:SQL )                 **
**  Cobi ( http://en.wikipedia.org/User:Cobi )               **
** Cmelbye ( http://en.wikipedia.org/User:cmelbye )          **
**FastLizard4 ( http://en.wikipedia.org/User:FastLizard4 )   **
**Stwalkerster ( http://en.wikipedia.org/User:Stwalkerster ) **
**                                                           **
**************************************************************/

require_once('/home/sql/database.inc');
mysql_connect("sql",$toolserver_username,$toolserver_password);
@mysql_select_db("u_sql") or print mysql_error();

$openq = "select COUNT(*) from acc_pend where pend_status = 'Open';";
$result = mysql_query($openq);
if(!$result) Die("ERROR: No result returned.1");
$open = mysql_fetch_assoc($result);  
$adminq = "select COUNT(*) from acc_pend where pend_status = 'Admin';";  
$result = mysql_query($adminq);
if(!$result) Die("ERROR: No result returned.2");
$admin = mysql_fetch_assoc($result);  
$sadminq = "select COUNT(*) from acc_user where user_level = 'Admin';";  
$result = mysql_query($sadminq);
if(!$result) Die("ERROR: No result returned.3");
$sadmin = mysql_fetch_assoc($result);  
$suserq = "select COUNT(*) from acc_user where user_level = 'User';";  
$result = mysql_query($suserq);
if(!$result) Die("ERROR: No result returned.4");
$suser = mysql_fetch_assoc($result);  
$ssuspq = "select COUNT(*) from acc_user where user_level = 'Suspended';";  
$result = mysql_query($ssuspq);
if(!$result) Die("ERROR: No result returned.5");
$ssusp = mysql_fetch_assoc($result);  
$snewq = "select COUNT(*) from acc_user where user_level = 'New';";  
$result = mysql_query($snewq);
if(!$result) Die("ERROR: No result returned.6");
$snew = mysql_fetch_assoc($result);  

$now = date("Y-m-d");
$now2 = date("Y-m-");
$now3 = date("d");
$now3 = $now3 - 1;
$topqa = "select log_user,count(*) from acc_log where log_action = 'Closed 1' group by log_user ORDER BY count(*) DESC limit 5;";
$result = mysql_query($topqa);
if(!$result) Die("ERROR: No result returned.6");
$top5a = array();
while ($topa = mysql_fetch_assoc($result)) {
        array_push($top5a, $topa);
}
$top5aout .= "\nAll time top 5 account creators:\n";
$top5aout .= "-------------------------------------------------------------\n";
foreach ($top5a as $top1a) {
        $top5aout .= "$top1a[log_user] - " . $top1a['count(*)'] . "\n";
}
$topa5out .= "\n";

$topq = "select log_user,count(*) from acc_log where log_time like '$now2$now3%' and log_action = 'Closed 1' group by log_user ORDER BY count(*) DESC limit 5;";
$result = mysql_query($topq);
if(!$result) Die("ERROR: No result returned.6");
$top5 = array();
while ($top = mysql_fetch_assoc($result)) {
	array_push($top5, $top);
}
$top5out .= "\nTodays top 5 account creators:\n";
$top5out .= "-------------------------------------------------------------\n";
foreach ($top5 as $top1) {
	$top5out .= "$top1[log_user] - " . $top1['count(*)'] . "\n";
}
$top5out .= "\n";
$now = date("Y-m-d",mktime(0,0,0,date(m),date("d")-1));
$logq = "select * from acc_log AS A
	JOIN acc_pend AS B ON log_pend = pend_id
	where log_time RLIKE '^$now.*' AND
	log_action RLIKE '^(Closed.*|Deferred.*)';";
$result = mysql_query($logq);
if(!$result) Die("ERROR: No result returned.7");
$dropped = 0;
$created = 0;
$toosimilar = 0;
$taken = 0;
$usernamevio = 0;
$technical = 0;
$dadmins = 0;
$dusers = 0;
while($log = mysql_fetch_assoc($result)) {
	switch ($log[log_action]) {
	case "Closed 0":
	    $dropped++;
	    break;
	case "Closed 1":
	    $created++;
	    break;
	case "Closed 2":
	    $toosimilar++;
	    break;
	case "Closed 3":
	    $taken++;
	    break;
	case "Closed 4":
	    $usernamevio++;
	    break;
	case "Closed 5":
	    $technical++;
	    break;
	case "Deferred to admins":
	    $dadmins++;
	    break;
	case "Deferred to users":
	    $dusers++;
	    break;
	}
} 
$nopen = $open['COUNT(*)'];
$nadmin = $admin['COUNT(*)'];
$nsadmin = $sadmin['COUNT(*)'];
$nsuser = $suser['COUNT(*)'];
$nssusp = $ssusp['COUNT(*)'];
$nsnew = $snew['COUNT(*)'];
$out = "\n";
$out .= "Tool URL is http://tools.wikimedia.de/~sql/acc/acc.php\n";
$out .= "PLEASE, register if you have not already!\n\n";
$out .= "Site Statistics!\n";
$out .= "-------------------------------------------------------------\n";
$out .= "Open Requests: $nopen\n";
$out .= "Open Requests (admin required): $nadmin\n";
$out .= "Site admins: $nsadmin\n";
$out .= "Site users: $nsuser\n";
$out .= "Site suspended accounts: $nssusp\n";
$out .= "Site users awaiting approval: $nsnew\n\n";
$out .= "Todays statistics!\n";
$out .= "-------------------------------------------------------------\n";
$out .= "Account requests dropped: $dropped\n";
$out .= "Accounts successfully created: $created\n";
$out .= "Accounts not created (Too similar): $toosimilar\n";
$out .= "Accounts not created (Taken): $taken\n";
$out .= "Accounts not created (Username vio): $usernamevio\n";
$out .= "Accounts not created (Technically impossible): $technical\n";
$out .= "Requests deferred to admins: $dadmins\n";
$out .= "Requests deferred back to users or reopened: $dusers\n";
$out .= $top5aout;
$out .= $top5out;
echo $out;
$to      = 'accounts-enwiki-l@lists.wikimedia.org';
$subject = "TS ACC statistics, $now";
$message = $out;
$headers = 'From: sxwiki@gmail.com' . "\n";

mail($to, $subject, $message, $headers);
?>
