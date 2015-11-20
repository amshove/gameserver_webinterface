<?php
###########################################################################
# Gameserver Webinterface                                                 #
# Copyright (C) 2010 Torsten Amshove <torsten@amshove.net>                #
#                                                                         #
# This program is free software; you can redistribute it and/or modify    #
# it under the terms of the GNU General Public License as published by    #
# the Free Software Foundation; either version 2 of the License, or       #
# (at your option) any later version.                                     #
#                                                                         #
# This program is distributed in the hope that it will be useful,         #
# but WITHOUT ANY WARRANTY; without even the implied warranty of          #
# MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the           #
# GNU General Public License for more details.                            #
#                                                                         #
# You should have received a copy of the GNU General Public License along #
# with this program; if not, write to the Free Software Foundation, Inc., #
# 51 Franklin Street, Fifth Floor, Boston, MA 02110-1301 USA.             #
###########################################################################

if($_SESSION["ad_level"] >= 1){

$server = array();
$query = mysql_query("SELECT * FROM server WHERE active = 1 ORDER BY name");
while($row = mysql_fetch_assoc($query)) $server[] = $row;

$games = array();
$query = mysql_query("SELECT * FROM games ORDER BY name");
while($row = mysql_fetch_assoc($query)) $games[$row["id"]] = $row;

if($_SESSION["ad_level"] >= 4 && $_GET["cmd"] == "cleanup"){
  // Cleanup - gestorbene Screens aus der "running"-Tabelle loeschen
  foreach($server as $s){
    $tmp = list_screens($s);
    $query = mysql_query("SELECT id, screen FROM running WHERE serverid = '".$s["id"]."'");
    while($row = mysql_fetch_assoc($query)){
      if(!in_array($row["screen"],$tmp)) mysql_query("DELETE FROM running WHERE id = '".$row["id"]."' LIMIT 1");
    }
  }
}elseif($_GET["cmd"] == "kill" && !empty($_GET["id"]) && is_numeric($_GET["id"])){
  // Gameserver killen
  kill_server(mysql_real_escape_string($_GET["id"]));
}elseif($_GET["cmd"] == "restart" && !empty($_GET["id"]) && is_numeric($_GET["id"])){
  // Gameserver restarten
  restart_server(mysql_real_escape_string($_GET["id"]));
}elseif($_POST["kill_multi"]){
  // Mehrere Gameserver killen
  if(is_array($_POST["kill"])){
    foreach($_POST["kill"] as $id) kill_server($id);
  }
}

// Cleanup-Link
if($_SESSION["ad_level"] >= 4) echo "<a href='index.php?cmd=cleanup' title='Gestorbene Server l&ouml;schen'>Cleanup</a>";

// JS fuer die Checkboxen
echo "<script>
function checkAll(i){
  var status = document.getElementById('check_'+i).checked;
  var fields = document.getElementById('form_'+i).elements;

  if(typeof(fields.checked) != 'undefined'){
    fields.checked = status;
  }else{
    for(x=0; x<fields.length; x++){
      if(typeof(fields[x].checked) != 'undefined'){
        fields[x].checked = status;
      }
    }
  }
}
</script>";

// Tabellen
$i=0;
foreach($server as $s){
  if(host_check_login($s)) $server_color = "#00FF00"; // Server online? farbe anpassen
  else $server_color = "#FF0000";
  echo "<h3 style='background-color: $server_color; width: 150px'>&nbsp;".$s["name"].".lan (".$s["ip"].")</h3>";
  echo "<table class='hover_row'>
    <tr>
      <th><input type='checkbox' id='check_$i' onClick='checkAll(\"".$i."\");'></th>
      <th width='50'>Game</th>
      <th width='100'>Screen</th>
      <th width='500'>Variablen</th>
      <th width='100'>&nbsp;</th>
    </tr>";
  echo "<form id='form_$i' method='POST' action='index.php'>";
  $screens = list_screens($s); // Laufende Screen einlesen
  $scores = 0;
  $query = mysql_query("SELECT * FROM running WHERE serverid = '".$s["id"]."' ORDER BY gameid");
  while($row = mysql_fetch_assoc($query)){ // Games auf dem Server auflisten
    $scores += $row["score"]; // Score addieren fuer die Anzeige
    
    if(!in_array($row["screen"],$screens)) $dead = true; // Wenn Screen in running-Tabelle aber nicht auf Server: gestorben
    else $dead = false;

    echo "<tr ";
    if($dead) echo "style='background-color: #CC9999;'"; // Wenn gestorben, farbe anpassen ...
    echo ">";
    echo "<td valign='top'><input type='checkbox' name='kill[]' value='".$row["id"]."'></td>
      <td align='center' valign='top'><a href='hlsw://".$s["ip"].":".$row["port"]."'><img border=0 src='images/".$games[$row["gameid"]]["icon"]."' height='$image_height'><br>".$games[$row["gameid"]]["name"];
    if($dead) echo "<br><b>gestorben</b>"; // ... und Hinweis
    echo "</a></td>
      <td valign='top'>".$row["screen"]."</td>
      <td>".$row["vars"]."</td>
      <td valign='top' align='center'>";
    echo "<a href='index.php?cmd=restart&id=".$row["id"]."' onClick='return confirm(\"Server wirklich restarten?\");'>restart</a> | ";
    echo "<a href='index.php?cmd=kill&id=".$row["id"]."' onClick='return confirm(\"Server wirklich killen?\");'>kill</a>";
    $connect_cmd = build_connect_cmd($row);
    if($connect_cmd) echo " | <a href='$connect_cmd'>connect</a>";
    echo "</td></tr>";
  }
  echo "</table>";
  echo "Score: $scores von ".$s["score"]." belegt - ".($s["score"] - $scores)." noch frei<br>"; // Scores
  echo "<input type='submit' name='kill_multi' value='kill' onClick='return confirm(\"Wirklich alle ausgew&auml;höten Server killen?\");'>";
  echo "</form>";
  echo "<br><br>";
  $i++;
}
}
?>
