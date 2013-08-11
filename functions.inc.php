<?php
############################################################
# Gameserver Webinterface                                  #
# Copyright (C) 2010 Torsten Amshove <torsten@amshove.net> #
############################################################

// Bezeichnungen der admin-level
$ad_level = array(
  3 => "User",
  4 => "Admin",
  5 => "Superadmin"
);

// Mit MySQL verbinden
mysql_connect($mysql_host,$mysql_user,$mysql_pw) or die(mysql_error());
mysql_select_db($mysql_db) or die(mysql_error());

// Session starten
session_start();
if(!empty($_SESSION["user_id"])) $logged_in = true;
else $logged_in = false;

// Funktion zum auslesen der Variablen aus dem Befehl
function parse_cmd($cmd){
  $vars = array();
  while(true){
    preg_match("/(##[a-zA-Z0-9]*##)/",$cmd,$matches);
    if(!empty($matches[1])){
      if($matches[1] != "##port##"){
        $vars[] = $matches[1];
      }
      $cmd = str_replace($matches[1],"",$cmd);
    }else break;
  }
  return $vars;
}

// Funktion zum bestimmen des naechsten freien Ports
function get_port($ip,$port){
  global $ssh_string;
  if(trim(shell_exec("$ssh_string root@$ip \"echo 1\"")) == 1){
    for($i=0; $i<=20; $i++){
      exec("$ssh_string root@$ip \"netstat -tuln | grep $port\"",$retarr,$rc);
      if($rc == 1) return $port;
      else $port++;
    }
    return false;
  }else return false;
}

// Funktion zum Starten des Gameservers
function starte_cmd($ip,$cmd,$screen,$folder=""){
  global $ssh_string;
  exec("$ssh_string root@$ip \"ls -1 /var/run/screen/S-root/ | cut -d . -f 2\"",$retarr,$rc);
  foreach($retarr as $line){
    if($line == $screen){ // Wenn bereits ein Screen mit dem Namen vorhanden ist ...
      if($_SESSION["ad_level"] >= 4) echo "<div class='meldung_error'>Screen-Name <b>$screen</b> bereits vergeben - toter Screen? Port scheint noch nicht belegt, aber Screen vorhanden</div><br>"; // Zusaetzliche Infos fuer Admin
      return false;
    }
  }
  if(!empty($folder)) $folder = "cd $folder && ";
  unset($retarr,$rc);
  exec("$ssh_string root@$ip \"".$folder."screen -dmS $screen $cmd\"",$retarr,$rc);
#echo "$ssh_string root@$ip \"".$folder."screen -dmS $screen $cmd\"";
  if($rc != 0){
    if($_SESSION["ad_level"] >= 4) echo "<div class='meldung_error'><b>ERROR:</b> <pre>"; print_r($retarr); echo "</pre></div><br>"; // Zusaetzliche Infos fuer Admin
    return false;
  }
  unset($retarr,$rc);
  exec("$ssh_string root@$ip \"ls -1 /var/run/screen/S-root/ | cut -d . -f 2\"",$retarr,$rc); // Laeuft der Screen wirklich?
  foreach($retarr as $line){
    if($line == $screen) return true;
  }
  if($_SESSION["ad_level"] >= 4) echo "<div class='meldung_error'>Screen anscheinend gestorben.</div><br>"; // Zusaetzliche Infos fuer Admin
  return false;
}

// Funktion zum auflisten aller laufenden Screens eines Servers
function list_screens($ip){
  global $ssh_string;
  exec("$ssh_string root@$ip \"screen -wipe\"");
  exec("$ssh_string root@$ip \"ls -1 /var/run/screen/S-root/ | cut -d . -f 2\"",$retarr,$rc);
  return $retarr;
}

// Funktion zum Beenden eines Screens
function kill_screen($ip,$screen){
  global $ssh_string;
  // PID bestimmen
  exec("$ssh_string root@$ip \"ps ax --format=#%p#%a | grep '[S]CREEN -dmS $screen' | cut -d '#' -f 2\"",$retarr,$rc);
  $pid = trim($retarr[0]);
  if(!is_numeric($pid) || empty($pid)){
    if($_SESSION["ad_level"] >= 5) echo "<div class='meldung_error'>PID nicht erkannt: $pid</div><br>"; // Zusaetzliche Infos fuer Admin
    return false;
  }
  unset($retarr,$rc);
  exec("$ssh_string root@$ip \"kill $pid\"",$retarr,$rc); // PID killen
  if($rc != 0){
    if($_SESSION["ad_level"] >= 5) echo "<div class='meldung_error'>kill nicht erfolgreich - PID: $pid</div><br>"; // Zusaetzliche Infos fuer Admin
    return false;
  }
  unset($retarr,$rc);
  // Screen wirklich beendet worden?
  exec("$ssh_string root@$ip \"ls -1 /var/run/screen/S-root/ | cut -d . -f 2\"",$retarr,$rc);
  foreach($retarr as $line){
    if($line == $screen){
      if($_SESSION["ad_level"] >= 4) echo "<div class='meldung_error'>Screen-Name <b>$screen</b> l&auml;uft noch ...</div><br>"; // Zusaetzliche Infos fuer Admin
      return false;
    }
  }  
  return true;
}

// Funktion zum beenden eines Gameservers
function kill_server($running_id){
  $query = mysql_query("SELECT r.id AS id, ip, screen FROM running AS r, server AS s WHERE r.serverid = s.id AND r.id = '".$running_id."' LIMIT 1");
  $row = mysql_fetch_assoc($query);
  if(!kill_screen($row["ip"],$row["screen"])){
    echo "<div class='meldung_error'>Server konnte nicht gestoppt werden.</div><br>";
  }else{
    mysql_query("DELETE FROM running WHERE id = '".$row["id"]."' LIMIT 1");
    echo "<div class='meldung_ok'>Server gekillt.</div><br>";
  }
}

// Funktion zum restarten eines Gameservers
function restart_server($running_id){
  $query = mysql_query("SELECT r.id AS id, ip, screen FROM running AS r, server AS s WHERE r.serverid = s.id AND r.id = '".$running_id."' LIMIT 1");
  $row = mysql_fetch_assoc($query);
  $gameid = $row["gameid"];
echo mysql_error();
  $serverid = $row["serverid"];
  $port = $row["port"];
  $vars = $row["vars"];
echo print_r($row);
  if(!kill_screen($row["ip"],$row["screen"])){
    echo "<div class='meldung_error'>Server konnte nicht gestoppt werden.</div><br>";
    return false;
  }else{
    mysql_query("DELETE FROM running WHERE id = '".$row["id"]."' LIMIT 1");
    echo "<div class='meldung_ok'>Server gekillt.</div><br>";
  }

  // Game starten
  $query = mysql_query("SELECT * FROM games WHERE id = '".$gameid."' LIMIT 1");
  $game = mysql_fetch_assoc($query);
  $query = mysql_query("SELECT * FROM server WHERE id = '".$serverid."' LIMIT 1");
  $server = mysql_fetch_assoc($query);
  $port = $port;

  $old_vars = array();
  $tmp = explode("<br>",$vars);
  foreach($tmp as $tmp2){
    $tmp3 = explode(" => ",$tmp2);
    $old_vars[$tmp3[0]] = $tmp3[1];
  }

  $vars = parse_cmd($game["cmd"]); // Variablen aus cmd auslesen
  $cmd = str_replace("##port##",$port,$game["cmd"]);
echo $cmd;
  $values = "port => $port<br>";
  foreach($vars as $v){
    // Variablen durch Werte ersetzen
    $cmd = str_replace($v,$old_vars[$v],$cmd);
    $values .= substr($v,2,-2)." => ".$old_vars[$v]."<br>";
  }
echo $cmd;
echo $values;
  if($_SESSION["ad_level"] >= 4) echo "<div class='meldung_notify'><b>CMD:</b> $cmd</div><br>"; // Fuer Admins wird der Befehl mit angezeigt
  $screen = $game["name"]."_".$port;
  if(!starte_cmd($server["ip"],$cmd,$screen,$game["folder"])){ // Server starten ...
    echo "<div class='meldung_error'>Server konnte nicht gestartet werden.</div><br>";
  }else{
    // Und in die "running"-Tabelle einfuegen
    mysql_query("INSERT INTO running SET screen = '".$screen."', serverid = '".$server["id"]."', gameid = '".$game["id"]."', port = '".$port."', score = '".$game["score"]."', vars = '".$values."'");
    echo "<div class='meldung_ok'>Server erfolgreich gestartet.</div><br>";
  }
}

// Funktion zum Auflisten aller Server, denen ein bestimmtes Game zugewiesen ist
function get_server_with_game($gameid){
  $query = mysql_query("SELECT * FROM server WHERE games LIKE '".$gameid."' OR games LIKE '".$gameid.",%' OR games LIKE '%,".$gameid.",%' OR games LIKE '%,".$gameid."' ORDER BY name");
  return $query;
}

// Funktion zum pruefen, ob der Server erreichbar ist
function host_online($ip){
  global $ssh_string;
  exec("$ssh_string root@$ip \"exit 0\"",$retarr,$rc);
  if($rc == 0) return true;
  else return false;
}

// Funktion zum neustarten des Servers
function reboot_server($id){
  global $ssh_string;
  $ip = mysql_result(mysql_query("SELECT ip FROM server WHERE id = '".$id."' LIMIT 1"),0,"ip");
  exec("$ssh_string root@$ip \"reboot\"");
  echo "<div class='meldung_ok'>Reboot an den Server gesendet.</div><br>";
}

// Funktion zum herunterfahren des Servers
function shutdown_server($id){
  global $ssh_string;
  $query = mysql_query("SELECT ip, name FROM server WHERE id = '".$id."' LIMIT 1");
  $ip = mysql_result($query,0,"ip");
  $name = mysql_result($query,0,"name");
  exec("$ssh_string root@$ip \"shutdown -h now\"");
  echo "<div class='meldung_ok'>Shutdown an den Server <b>$name</b> gesendet.</div><br>";
}
?>
