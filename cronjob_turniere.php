<?php
require("config.inc.php");
require("functions.inc.php");

$dotlan = mysql_connect($dotlan_mysql_host,$dotlan_mysql_user,$dotlan_mysql_pw) or die (mysql_error());
mysql_select_db($dotlan_mysql_db,$dotlan) or die (mysql_error());

echo "## INFO: Stoppe Server ...\n";
// Laufende Gameserver stoppen, wenn Ergebnis eingetragen
$query = mysql_query("SELECT * FROM running WHERE t_contest_id > 0",$db);
while($row = mysql_fetch_assoc($query)){
  $won = mysql_result(mysql_query("SELECT won FROM t_contest WHERE tcid = '".$row["t_contest_id"]."' LIMIT 1",$dotlan),0,"won");
  if($won > 0){
    echo "## INFO: Stoppe Server (".$row["screen"].")\n";
    kill_server($row["id"]);
  }
}
?>
