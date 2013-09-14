<?php
require("config.inc.php");
require("functions.inc.php");

$dotlan = mysql_connect($dotlan_mysql_host,$dotlan_mysql_user,$dotlan_mysql_pw) or die (mysql_error());
mysql_select_db($dotlan_mysql_db,$dotlan) or die (mysql_error());

$tids = array();
$turniere = array();
$query = mysql_query("SELECT * FROM turniere",$db);
while($row = mysql_fetch_assoc($query)){
  $tids[] = $row["turnier"];
  $turniere[$row["turnier"]] = $row;
}

$games = array();
$query = mysql_query("SELECT * FROM games",$db);
while($row = mysql_fetch_assoc($query)) $games[$row["id"]] = $row;

$running_ids = array();
$query = mysql_query("SELECT t_contest_id FROM running",$db);
while($row = mysql_fetch_assoc($query)) $running_ids[] = $row["t_contest_id"];

echo "## INFO: Starte Server ...\n";
// Alle Paarungen, wo beide Teams bereit stehen, und fuer die noch kein Server laueft
$query = mysql_query("SELECT * FROM t_contest WHERE tid IN (".implode(",",$tids).") AND ".(count($running_ids) > 0 ? "tcid NOT IN (".implode(",",$running_ids).") AND" : "")." won = 0 AND ready_a != '0000-00-00 00:00:00' AND ready_b != '0000-00-00 00:00:00'",$dotlan);
while($row = mysql_fetch_assoc($query)){
  $team_a = mysql_result(mysql_query("SELECT tnname FROM t_teilnehmer WHERE tnid = '".$row["team_a"]."' LIMIT 1",$dotlan),0,"tnname");
  if(empty($team_a)) $team_a = mysql_result(mysql_query("SELECT u.nick AS nick FROM user AS u, t_teilnehmer_part AS t WHERE t.user_id = id AND t.tnid = '".$row["team_a"]."' LIMIT 1",$dotlan),0,"nick");
  $team_b = mysql_result(mysql_query("SELECT tnname FROM t_teilnehmer WHERE tnid = '".$row["team_b"]."' LIMIT 1",$dotlan),0,"tnname");
  if(empty($team_b)) $team_b = mysql_result(mysql_query("SELECT u.nick AS nick FROM user AS u, t_teilnehmer_part AS t WHERE t.user_id = id AND t.tnid = '".$row["team_b"]."' LIMIT 1",$dotlan),0,"nick"); 

  $team_a = preg_replace("/[^a-zA-Z0-9]*/","",$team_a);
  $team_b = preg_replace("/[^a-zA-Z0-9]*/","",$team_b);

  // Game starten
  $game = $games[$turniere[$row["tid"]]["game"]];
  $server = false;
  $query2 = get_server_with_game($game["id"]);
  while($row2 = mysql_fetch_assoc($query2)){
    $score_used = @mysql_result(mysql_query("SELECT SUM(score) AS sum FROM running WHERE serverid = '".$row2["id"]."' LIMIT 1"),0,"sum");
    if(!$score_used) $score_used = 0;
    if(($row2["score"]-$score_used) > $game["score"]){
      $server = $row2;
      break;  // Einen Server zum starten gefunden
    }
  }
  if(!$server){
    echo "## ERROR: Kein Server frei .. ($team_a vs. $team_b, TCID: ".$row["tcid"].")\n";
    break; // Keinen freien Server gefunden ...
  }

  // Varablen zubereiten
  $replace_vars = array();
  $lines = explode("\n",$turniere[$row["tid"]]["vars"]);
  foreach($lines as $line){
    $tmp = explode("=>",$line);
    $replace_vars["##".$tmp[0]."##"] = $tmp[1];
  }

  $port = get_port($server,$game["start_port"]); // Port ermitteln - erster freier Port ab start_port
  if(!$port){
    echo "## ERROR: Kein Port gefunden .. ($team_a vs. $team_b, TCID: ".$row["tcid"].")\n";
    break;
  }else{
    $vars = parse_cmd($game["cmd"]); // Variablen aus cmd auslesen
    $cmd = str_replace("##port##",$port,$game["cmd"]);
    $values = "port => $port<br>";
    foreach($vars as $v){
      // Variablen durch Werte ersetzen
      $cmd = str_replace($v,$replace_vars[$v],$cmd);
      $values .= substr($v,2,-2)." => ".$replace_vars[$v]."<br>";
    }
    $cmd = str_replace("##team1##",$team_a,$cmd);
    $values .= "team1 => $team_a<br>";
    $cmd = str_replace("##team2##",$team_b,$cmd);
    $values .= "team2 => $team_b<br>";

    $screen = $game["name"]."_".$port;
    if(!starte_cmd($server,$cmd,$screen,$game["folder"])){ // Server starten ...
      echo "## ERROR: Server starten fehlgeschlagen: $cmd\n";
      break;
    }else{
      // Und in die "running"-Tabelle einfuegen
      mysql_query("INSERT INTO running SET screen = '".$screen."', serverid = '".$server["id"]."', gameid = '".$game["id"]."', port = '".$port."', cmd = '".$cmd."', score = '".$game["score"]."', vars = '".$values."', t_contest_id = '".$row["tcid"]."'",$db);
      echo "## INFO: Server erfolgreich gestartet ($team_a vs. $team_b, TCID: ".$row["tcid"].")\n";
    }
  }
}

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
