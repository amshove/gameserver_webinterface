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

if($_SESSION["ad_level"] >= 4){

$dotlan = mysql_connect($dotlan_mysql_host,$dotlan_mysql_user,$dotlan_mysql_pw) or die (mysql_error());
mysql_select_db($dotlan_mysql_db,$dotlan) or die (mysql_error());

// Wird fuer das Formular verwendet um zwischen add und edit zu unterscheiden
$submit_name = "add";
$submit_value = "Hinzuf&uuml;gen";
$display = "none";

if($_GET["cmd"] == "edit" && is_numeric($_GET["id"]) && !empty($_GET["id"])){
  // Es wurde auf edit geklickt - hier werden die Daten fuer das Formular eingelesen
  $submit_name = "edit";
  $submit_value = "&Auml;ndern";
  $display = "block";
  $value = mysql_fetch_assoc(mysql_query("SELECT * FROM turniere WHERE id = '".mysql_real_escape_string($_GET["id"])."' LIMIT 1",$db));
}elseif($_GET["cmd"] == "del" && is_numeric($_GET["id"]) && !empty($_GET["id"])){
  // Turnier loeschen
  $id = mysql_real_escape_string($_GET["id"]);
  mysql_query("DELETE FROM turniere WHERE id = '".$id."' LIMIT 1",$db); // Und loeschen
}

// Add/Edit Formular wurde abgeschickt
if($_POST["add"] || $_POST["edit"]){
  $id = mysql_real_escape_string($_POST["id"]);
  $game = mysql_real_escape_string($_POST["game"]);
  $turnier = mysql_real_escape_string($_POST["turnier"]);
  if($_POST["add"]){
    // Turnier-Zuordnung anlegen
    mysql_query("INSERT INTO turniere SET game = '".$game."', turnier = '".$turnier."'",$db);
    $id = mysql_insert_id();
  }elseif($_POST["edit"]){
    // Turnier-Zuordnung aendern
    mysql_query("UPDATE turniere SET game = '".$game."', turnier = '".$turnier."' WHERE id = '".$id."' LIMIT 1",$db);
  }
}

// Formular
echo "<a href='#' onClick='document.getElementById(\"formular\").style.display = \"block\";'>Turnier-Zuordnung hinzuf&uuml;gen</a><br>";

echo "<form action='index.php?page=turniere' method='POST' id='formular' style='display: $display;'>
<input type='hidden' name='id' value='".$value["id"]."'>
<table>
  <tr>
    <th colspan='2'>&nbsp;</th>
  </tr>
  <tr>
    <td width='120'>Game:</td>
    <td><select name='game'>";
$query = mysql_query("SELECT * FROM games ORDER BY name",$db);
while($row = mysql_fetch_assoc($query)){
  if($value["game"] == $row["id"]) $select = "selected='selected'";
  else $select = "";
  echo "<option value='".$row["id"]."' $select>".$row["name"]."</option>";
}
echo "</select></td>
  </tr>
  <tr>
    <td>Dotlan Turnier:</td>
    <td><select name='turnier'>";
$query = mysql_query("SELECT id,name FROM events WHERE active = 1 ORDER BY id DESC",$dotlan);
while($row = mysql_fetch_assoc($query)){
  echo "<optgroup label='".$row["name"]."'>";
  $query2 = mysql_query("SELECT tid,tname FROM t_turnier WHERE teventid = '".$row["id"]."' ORDER BY tname",$dotlan);
  while($row2 = mysql_fetch_assoc($query2)){
    if($value["turnier"] == $row2["tid"]) $select = "selected='selected'";
    else $select = "";
    echo "<option value='".$row2["tid"]."' $select>".$row2["tname"]."</option>";
  }
  echo "</optgroup>";
}
echo "</select></td>
  </tr>
  <tr>
    <td colspan='2' align='center'><input type='submit' name='".$submit_name."' value='".$submit_value."'></td>
  </tr>
</table>
</form>";

echo "<br><br>";

// Tabelle
echo "<table>
  <tr>
    <th width='200'>Game</th>
    <th width='200'>Dotlan-Turnier</th>
    <th width='100'>&nbsp;</th>
  </tr>";

$query = mysql_query("SELECT * FROM turniere",$db);
while($row = mysql_fetch_assoc($query)){
  $game = mysql_fetch_assoc(mysql_query("SELECT * FROM games WHERE id = '".$row["game"]."' LIMIT 1",$db));
  $turnier = mysql_fetch_assoc(mysql_query("SELECT * FROM t_turnier WHERE tid = '".$row["turnier"]."' LIMIT 1",$dotlan));
  echo "<tr>
    <td valign='top'><img src='images/".$game["icon"]."' height='$image_height'> ".$game["name"]."</td>
    <td valign='top'>".$turnier["tname"]."</td>
    <td valign='top' align='center'><a href='index.php?page=turniere&cmd=edit&id=".$row["id"]."'>edit</a> | <a href='index.php?page=turniere&cmd=del&id=".$row["id"]."' onClick='return confirm(\"Turnier-Zuordnung wirklich l&ouml;schen?\");'>del</a></td>
  </tr>";
}
echo "</table>";

echo "<br><br>";

$tids = array();
$query = mysql_query("SELECT turnier FROM turniere",$db);
while($row = mysql_fetch_assoc($query)) $tids[] = $row["turnier"];

echo "<b>Turnier-Begegnungen:</b>";
echo "<table>";
echo "  <tr>";
echo "    <th width='200'>Turnier</th>";
echo "    <th width='200'>Begegnung</th>";
echo "    <th width='200'>Gameserver</th>";
echo "  </tr>";
$query = mysql_query("SELECT * FROM t_contest WHERE tid IN (".implode(",",$tids).") AND won = 0 AND ready_a != '0000-00-00 00:00:00' AND ready_b != '0000-00-00 00:00:00'",$dotlan);
while($row = mysql_fetch_assoc($query)){
  $turnier_name = mysql_result(mysql_query("SELECT tname FROM t_turnier WHERE tid = '".$row["tid"]."' LIMIT 1",$dotlan),0,"tname");
  $team_a = mysql_result(mysql_query("SELECT tnname FROM t_teilnehmer WHERE tnid = '".$row["team_a"]."' LIMIT 1",$dotlan),0,"tnname");
  if(empty($team_a)) $team_a = mysql_result(mysql_query("SELECT u.nick AS nick FROM user AS u, t_teilnehmer_part AS t WHERE t.user_id = id AND t.tnid = '".$row["team_a"]."' LIMIT 1",$dotlan),0,"nick");
  $team_b = mysql_result(mysql_query("SELECT tnname FROM t_teilnehmer WHERE tnid = '".$row["team_b"]."' LIMIT 1",$dotlan),0,"tnname");
  if(empty($team_b)) $team_b = mysql_result(mysql_query("SELECT u.nick AS nick FROM user AS u, t_teilnehmer_part AS t WHERE t.user_id = id AND t.tnid = '".$row["team_b"]."' LIMIT 1",$dotlan),0,"nick");

  $running = mysql_fetch_assoc(mysql_query("SELECT * FROM running WHERE t_contest_id = '".$row["tcid"]."'",$db));
  if($running["screen"]){
    $gameserver = $running["screen"]." on ".mysql_result(mysql_query("SELECT name FROM server WHERE id = '".$running["serverid"]."'",$db),0,"name");
  }else{
    $gameserver = "(noch nicht gestartet)";
  }

  echo "<tr>";
  echo "  <td>$turnier_name</td>";
  echo "  <td>$team_a - $team_b</td>";
  echo "  <td>$gameserver</td>";
  echo "</tr>";
}
echo "</table>";
}
?>
