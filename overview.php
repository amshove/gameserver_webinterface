<?php
############################################################
# Gameserver Webinterface                                  #
# Copyright (C) 2010 Torsten Amshove <torsten@amshove.net> #
############################################################

if($_SESSION["ad_level"] >= 1){

// Daten einlesen fuer Server-Statistik
$i=0;
$server = array();
$query = mysql_query("SELECT * FROM server ORDER BY name");
while($row = mysql_fetch_assoc($query)){
  $server[$i] = $row;
  $server[$i]["online"] = host_online($row["ip"]); // Server online?
  $query2 = mysql_query("SELECT * FROM running WHERE serverid = '".$row["id"]."'");
  while($row2 = mysql_fetch_assoc($query2)){ // Was laeuft auf dem Server?
    $server[$i]["running"][$row2["gameid"]] += 1;
    $server[$i]["score_used"] += $row2["score"];
  }
  $i++;
}

// Games einlesen
$games = array();
$query = mysql_query("SELECT * FROM games");
while($row = mysql_fetch_assoc($query)){
  $games[$row["id"]] = $row;
}


echo "<table>";
echo "  <tr>";
echo "    <th width='150'>Server status</th>";
echo "    <th width='150'>Game status</th>";
echo "  </tr>";
echo "<tr><td valign='top' align='center'>";

// Server status
foreach($server as $s){
  if($s["online"]) $server_color = "#00FF00"; // Server online? Dann Farbe anpassen
  else $server_color = "#FF0000";
  
  echo "<br><table>";
  echo "  <tr>";
  echo "    <th colspan='2' style='background-color: ".$server_color."' title='".$s["ip"]."' width='100'>".$s["name"]."</th>";
  echo "  </tr>";
  
  $min_score = 100000000000000;
  foreach(explode(",",$s["games"]) as $g){ // Games anzeigen
    if($games[$g]["score"] < $min_score) $min_score = $games[$g]["score"]; // Mindest-Score fuer Games dieses Servers ermitteln - wird fuer Score-Anzeige-Farbe benoetigt
    echo "<tr>";
    $num = $s["running"][$g];
    if(empty($num)) $num = 0;
    echo "  <td width='20'>".$num."x</td>";
    echo "  <td><img src='icons/".$games[$g]["icon"]."' title='".$games[$g]['name']."' height='$image_height'></td>";
    echo "</tr>";
  }
  if($s["online"]){ // Score-Anzeige nur wenn online
    echo "  <tr>";
    $score_free = $s["score"] - $s["score_used"];
    if($score_free >= $min_score) $score_color = "#00FF00"; // Wenn mindestens Minimum_Score verfuegbar ...
    else $score_color = "#FF0000";
    echo "    <td colspan='2' style='background-color: ".$score_color.";'><b>Score:</b> ".$score_free."/".$s["score"]." frei</td>";
    echo "  </tr>";
  }
  echo "</table>";
}

echo "<br></td><td valign='top' align='center'>";

// Game status
foreach($games as $g){
  echo "<br><table>";
  echo "  <tr>";
  echo "    <th colspan='2' width='100'><img src='icons/".$g["icon"]."' height='$image_height'> ".$g["name"]."</th>";
  echo "  </tr>";
  $query = mysql_query("SELECT COUNT(r.id) AS count, s.name AS name FROM running AS r, server AS s WHERE r.serverid = s.id AND r.gameid = '".$g["id"]."' GROUP BY s.name ORDER BY s.name");
  while($row = mysql_fetch_assoc($query)){ // Server auflisten
    echo "<tr>";
    echo "  <td width='20'>".$row["count"]."x</td>";
    echo "  <td>".$row["name"]."</td>";
    echo "</tr>";
  }
  echo "</table>";
}

echo "<br></td></tr>";
echo "</table>";
}
?>
