<?php
############################################################
# Gameserver Webinterface                                  #
# Copyright (C) 2010 Torsten Amshove <torsten@amshove.net> #
############################################################

if($_SESSION["ad_level"] >= 1){

if($_POST["anlegen"]){
  // Game starten
  $query = mysql_query("SELECT * FROM games WHERE id = '".mysql_real_escape_string($_POST["game"])."' LIMIT 1");
  $game = mysql_fetch_assoc($query);
  $query = mysql_query("SELECT * FROM server WHERE id = '".mysql_real_escape_string($_POST["server"])."' LIMIT 1");
  $server = mysql_fetch_assoc($query);
  $port = get_port($server["ip"],$game["start_port"]); // Port ermitteln - erster freier Port ab start_port
  if(!$port) echo "<div class='meldung_error'>Konnte keinen freien Port finden - Server erreichbar?</div><br>";
  else{
    $vars = parse_cmd($game["cmd"]); // Variablen aus cmd auslesen
    $cmd = str_replace("##port##",$port,$game["cmd"]);
    $values = "port => $port<br>";
    foreach($vars as $v){
      // Variablen durch Werte ersetzen
      $cmd = str_replace($v,$_POST[$v],$cmd);
      $values .= substr($v,2,-2)." => ".$_POST[$v]."<br>";
    }
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
}

// Auflisten der Games
$query = mysql_query("SELECT id, icon, name FROM games ORDER BY name");
while($row = mysql_fetch_assoc($query)){
  echo "<a href='index.php?page=anlegen&game=".$row["id"]."'><img src='icons/".$row["icon"]."' height='$image_height'> ".$row["name"]."</a><br>";
}

echo "<br>";

// Wenn ein Game angeklickt wurde, Formular anzeigen
if($_GET["game"]){
  $query = mysql_query("SELECT * FROM games WHERE id = '".mysql_real_escape_string($_GET["game"])."' LIMIT 1");
  $game = mysql_fetch_assoc($query);
  echo "<form action='index.php?page=anlegen' method='POST'>
  <input type='hidden' name='game' value='".$game["id"]."'>
  <table>
    <tr>
      <td>Zielserver:</td>
      <td><select name='server'>";
$query = get_server_with_game($game["id"]); // Zeigt nur die Server an, auf denen das Game laeuft ...
while($row = mysql_fetch_assoc($query)){
  if(host_online($row["ip"])){ // ... und auch nur wenn der Server online ist
    echo "<option value='".$row["id"]."' ";
    $score_used = @mysql_result(mysql_query("SELECT SUM(score) AS score FROM running GROUP BY serverid HAVING serverid = '".$row["id"]."'"),0,"score");
    if(($row["score"]-$score_used) < $game["score"]) echo "disabled"; // Wenn der Score zu hoch ist, Server ausgrauen
    echo ">".$row["name"]."</option>";
  }
}
  echo "</select></td>
    </tr>";
  $defaults = explode(";",$game["defaults"]);
  $vars = parse_cmd($game["cmd"]);
  $i=0;
  foreach($vars as $var){
    // Pro Variable wird ein Formular-Feld angezeigt und mit den Defaults gefuellt, wenn vorhanden
    echo "<tr>
      <td>".substr($var,2,-2).":</td>
      <td><input type='text' name='".$var."' value='".$defaults[$i]."' size='70'></td>
    </tr>";
    $i++;
  }
  echo "<tr>
      <td colspan='2' align='center'><input type='submit' name='anlegen' value='starten'></td>
    </tr>
  </table></form>";
}
}
?>
