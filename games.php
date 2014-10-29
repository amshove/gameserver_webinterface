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

// Wird fuer das Formular verwendet um zwischen add und edit zu unterscheiden
$submit_name = "add";
$submit_value = "Hinzuf&uuml;gen";
$display = "none";

if($_GET["cmd"] == "edit" && is_numeric($_GET["id"]) && !empty($_GET["id"])){
  // Es wurde auf edit geklickt - hier werden die Daten fuer das Formular eingelesen
  $submit_name = "edit";
  $submit_value = "&Auml;ndern";
  $display = "block";
  $value = mysql_fetch_assoc(mysql_query("SELECT * FROM games WHERE id = '".mysql_real_escape_string($_GET["id"])."' LIMIT 1"));
  $query = get_server_with_game($value["id"]);
  $value["server"] = array();
  while($row = mysql_fetch_assoc($query)) $value["server"][] = $row["id"];
}elseif($_GET["cmd"] == "del" && is_numeric($_GET["id"]) && !empty($_GET["id"])){
  // Game loeschen
  $id = mysql_real_escape_string($_GET["id"]);
  $query = get_server_with_game($id);
  while($row = mysql_fetch_assoc($query)){ // Game bei den Servern austragen
    $old = explode(",",$row["games"]);
    $new = array();
    foreach($old as $o) if($o != $id) $new[] = $o;
    mysql_query("UPDATE server SET games = '".implode(",",$new)."' WHERE id = '".$row["id"]."' LIMIT 1");
  }
  mysql_query("DELETE FROM games WHERE id = '".$id."' LIMIT 1"); // Und loeschen
}elseif($_GET["cmd"] == "sync" && is_numeric($_GET["id"]) && !empty($_GET["id"])){
  // Game Syncen - Formular
  $server = array();
  $query = get_server_with_game(mysql_real_escape_string($_GET["id"]));
  while($row = mysql_fetch_assoc($query)) $server[] = $row;

  echo "<form action='index.php?page=games' method='POST'>";
  echo "<input type='hidden' name='gameid' value='".$_GET["id"]."'>";
  echo "<table width='200'>";
  echo "  <tr>";
  echo "    <th colspan='2'>Game-Files auf Server syncen</th>";
  echo "  </tr>";
  echo "  <tr>";
  echo "    <td>Quell-Server:</td>";
  echo "    <td><select name='src'>";
  foreach($server as $s) echo "<option value='".$s["id"]."'>".$s["name"]."</option>";
  echo "    </select></td>";
  echo "  </tr>";
  echo "  <tr>";
  echo "    <td valign='top'>Ziel-Server:</td>";
  echo "    <td><select name='dst[]' size='5' multiple>";
  foreach($server as $s) echo "<option value='".$s["id"]."'>".$s["name"]."</option>";
  echo "    </select></td>";
  echo "  </tr>";
  echo "  <tr>";
  echo "    <td colspan='2'><input type='submit' name='sync' value='Sync starten'></td>";
  echo "  </tr>";
  echo "</table>";
  echo "</form><br><br>";
}elseif($_POST["sync"]){
  // Game Syncen - Prozesse starten
  $game = mysql_fetch_assoc(mysql_query("SELECT * FROM games WHERE id = '".mysql_real_escape_string($_POST["gameid"])."' LIMIT 1"));
  $server = array();
  $query = get_server_with_game(mysql_real_escape_string($_POST["gameid"]));
  while($row = mysql_fetch_assoc($query)) $server[$row["id"]] = $row;

  if(!$server[$_POST["src"]]) echo "<div class='meldung_error'>Quell-Server wurde nicht gefunden.</div><br>";
  else{
    $src = $server[$_POST["src"]];
    $dst = array();
    if(is_array($_POST["dst"])){
      foreach($_POST["dst"] as $d){
        if($d != $_POST["src"] && $server[$d]) $dst[] = $server[$d];
      }
    }elseif($server[$_POST["dst"]]) $dst[] = $server[$_POST["dst"]];
    else{
      echo "<div class='meldung_error'>Keinen Ziel-Server gefunden.</div><br>";
      $error = true;
    }

    if(!$error) foreach($dst as $d) sync_game($src,$d,$game);
  }
}

// Add/Edit Formular wurde abgeschickt
if($_POST["add"] || $_POST["edit"]){
  $error = false;

  if(empty($_POST["name"]) || empty($_POST["cmd"])){ // Name und cmd duerfen nicht leer sein
    echo "<div class='meldung_error'>Name und CMD m&uuml;ssen angegeben werden!</div><br>";
    $error = true;
  }elseif(!preg_match("/^[a-zA-Z0-9_-]*$/",$_POST["name"])){ // Name ueberpruefen
    echo "<div class='meldung_error'>Der Name darf nur aus Buchstaben, Zahlen, Binde- und Unterstrichen bestehen!</div><br>";
    $error = true;
  }elseif(!preg_match("/^[0-9,]*$/",$_POST["port_blacklist"])){ // Port-Blacklist muss Kommasepariert sein
    echo "<div class='meldung_error'>Die Port Blacklist darf nur aus Zahlen und Kommas bestehen!</div><br>";
  }

  if($error){
    $display = "block";
    $value = $_POST;
    if($_POST["edit"]){
      $submit_name = "edit";
      $submit_value = "&Auml;ndern";
      $display = "block";
    }
  }else{
    $id = mysql_real_escape_string($_POST["id"]);
    $icon = mysql_real_escape_string($_POST["icon"]);
    $name = mysql_real_escape_string($_POST["name"]);
    $folder = mysql_real_escape_string($_POST["folder"]);
    $cmd = mysql_real_escape_string($_POST["cmd"]);
    $defaults = mysql_real_escape_string($_POST["defaults"]);
    $start_port = mysql_real_escape_string($_POST["start_port"]);
    $port_blacklist = mysql_real_escape_string($_POST["port_blacklist"]);
    $score = mysql_real_escape_string($_POST["score"]);
    if(is_array($_POST["server"])) $server = $_POST["server"];
    else $server = "";
    if($_POST["add"]){
      // Game anlegen
      mysql_query("INSERT INTO games SET icon = '".$icon."', name = '".$name."', folder = '".$folder."', cmd = '".$cmd."', defaults = '".$defaults."', start_port = '".$start_port."', port_blacklist = '".$port_blacklist."', score = '".$score."'");
      $id = mysql_insert_id();
    }elseif($_POST["edit"]){
      // Game aendern
      mysql_query("UPDATE games SET icon = '".$icon."', name = '".$name."', folder = '".$folder."', cmd = '".$cmd."', defaults = '".$defaults."', start_port = '".$start_port."', port_blacklist = '".$port_blacklist."', score = '".$score."' WHERE id = '".$id."' LIMIT 1");
      $query = get_server_with_game($id);
      while($row = mysql_fetch_assoc($query)){ // Alle Verweise zu dem Game loeschen - werden gleich wiederhergestellt
        $old = explode(",",$row["games"]);
        $new = array();
        foreach($old as $o) if($o != $id) $new[] = $o;
        mysql_query("UPDATE server SET games = '".implode(",",$new)."' WHERE id = '".$row["id"]."' LIMIT 1");
      }
    }
    // Anlegen der angegebenen Verweise zu den Servern
    $query = mysql_query("SELECT id, games FROM server WHERE id IN (".implode(",",$server).")");
    while($row = mysql_fetch_assoc($query)){
      $old = explode(",",$row["games"]);
      $old[] = $id;
      mysql_query("UPDATE server SET games = '".implode(",",$old)."' WHERE id = '".$row["id"]."' LIMIT 1");
    }
  }
}

if(!is_array($value["server"])) $value["server"] = array(); // Workaround um Fehler zu vermeiden

// Formular
echo "<a href='#' onClick='document.getElementById(\"formular\").style.display = \"block\";'>Game hinzuf&uuml;gen</a><br>";

echo "<form action='index.php?page=games' method='POST' id='formular' style='display: $display;'>
<input type='hidden' name='id' value='".$value["id"]."'>
<table>
  <tr>
    <th colspan='2'>&nbsp;</th>
  </tr>
  <tr>
    <td width='50'>Icon:</td>
    <td><select name='icon'>";
$icons = scandir("images");
foreach($icons as $i){
  if($i == "." || $i == ".." || $i == ".svn") continue;
  echo "<option ";
  if($value["icon"] == $i) echo "selected = \"selected\"";
  echo ">".$i."</option>";
}
echo "</select></td>
  </tr>
  <tr>
    <td>Name:</td>
    <td><input type='text' name='name' value='".$value["name"]."'></td>
  </tr>
  <tr>
    <td>Ordner:</td>
    <td><input type='text' name='folder' value='".$value["folder"]."'></td>
  </tr>
  <tr>
    <td>CMD:</td>
    <td><input type='text' name='cmd' value='".$value["cmd"]."' size='100'><br>
        ##port## & ##port1## f&uuml;r Ports, ##var1## .. f&uuml;r weitere Variablen</td>
  </tr>
  <tr>
    <td>Defaults:</td>
    <td><input type='text' name='defaults' value='".$value["defaults"]."' size='100'><br>
        In der Reihenfolge der Variablen im CMD (ohne ##port## & ##port1##) - Getrennt durch ;</td>
  </tr>
  <tr>
    <td>Startport:</td>
    <td><input type='text' name='start_port' value='".$value["start_port"]."'></td>
  </tr>
  <tr>
    <td>Port Blacklist:</td>
    <td><input type='text' name='port_blacklist' value='".$value["port_blacklist"]."'><br>
        Ports, die ausgelassen werden sollen - Kommasepariert (z.B. f&uuml;r cs:go 27020,27021,27022,27023,27024)</td>
  </tr>
  <tr>
    <td>Score:</td>
    <td><input type='text' name='score' value='".$value["score"]."'></td>
  </tr>
  <tr>
    <td>Server:</td>
    <td><select name='server[]' size='5' multiple>";
$query = mysql_query("SELECT id, name FROM server ORDER BY name");
while($row = mysql_fetch_assoc($query)){
  echo "<option value='".$row["id"]."' ";
  if(in_array($row["id"],$value["server"])) echo "selected='selected'";
  echo ">".$row["name"]."</option>";
}
echo "</td></select>
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
    <th width='50'>Icon</th>
    <th width='100'>Name</th>
    <th width='350'>defaults</th>
    <th width='50'>Startport</th>
    <th width='50'>Score</th>
    <th width='100'>&nbsp;</th>
  </tr>";

$query = mysql_query("SELECT * FROM games ORDER BY name");
while($row = mysql_fetch_assoc($query)){
  echo "<tr>
    <td align='center'><img src='images/".$row["icon"]."' height='$image_height'></td>
    <td>".$row["name"]."</td>
    <td>".$row["defaults"]."</td>
    <td>".$row["start_port"]."</td>
    <td>".$row["score"]."</td>
    <td align='center'><a href='index.php?page=games&cmd=edit&id=".$row["id"]."'>edit</a> | <a href='index.php?page=games&cmd=del&id=".$row["id"]."' onClick='return confirm(\"Game wirklich l&ouml;schen?\");'>del</a> | <a href='index.php?page=games&cmd=sync&id=".$row["id"]."'>sync</a></td>
  </tr>";
}

echo "</table>";

echo "<br><br>";

$syncs = sync_list();
if(count($syncs) > 1){
  echo "<table>";
  echo "  <tr>";
  echo "    <th width='300'>Aktive Sync-Prozesse:</th>";
  echo "  </tr>";
  foreach($syncs as $s){
    echo "<tr>";
    echo "  <td>$s</td>";
    echo "</tr>";
  }
  echo "</table>";
}
}
?>
