<?php
############################################################
# Gameserver Webinterface                                  #
# Copyright (C) 2010 Torsten Amshove <torsten@amshove.net> #
############################################################

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
  $value = mysql_fetch_assoc(mysql_query("SELECT * FROM server WHERE id = '".mysql_real_escape_string($_GET["id"])."' LIMIT 1"));
}elseif($_GET["cmd"] == "del" && is_numeric($_GET["id"]) && !empty($_GET["id"])){
  // Server loeschen
  mysql_query("DELETE FROM server WHERE id = '".mysql_real_escape_string($_GET["id"])."' LIMIT 1");
}elseif($_GET["cmd"] == "reboot" && is_numeric($_GET["id"]) && !empty($_GET["id"])){
  // Server rebooten
  $server = mysql_fetch_assoc(mysql_query("SELECT * FROM server WHERE id = '".mysql_real_escape_string($_GET["id"])."' LIMIT 1"));
  reboot_server($server);
}elseif($_GET["cmd"] == "shutdown" && is_numeric($_GET["id"]) && !empty($_GET["id"])){
  // Server herunterfahren
  $server = mysql_fetch_assoc(mysql_query("SELECT * FROM server WHERE id = '".mysql_real_escape_string($_GET["id"])."' LIMIT 1"));
  shutdown_server($server);
}elseif($_GET["cmd"] == "shutdown_all"){
  // Alle Server herunterfahren
  $query = mysql_query("SELECT * FROM server");
  while($row = mysql_fetch_assoc($query)) shutdown_server($row);
}

// Formular wurde abgeschickt
if($_POST["add"] || $_POST["edit"]){
  if(empty($_POST["ip"]) || empty($_POST["name"]) || empty($_POST["user"])){
    echo "<div class='meldung_error'>IP, User und Name m&uuml;ssen angegeben werden!</div><br>";
    $display = "block";
    $value = $_POST;
    if($_POST["edit"]){
      $submit_name = "edit";
      $submit_value = "&Auml;ndern";
      $display = "block";
    }
  }else{
    $id = mysql_real_escape_string($_POST["id"]);
    $name = mysql_real_escape_string($_POST["name"]);
    $ip = mysql_real_escape_string($_POST["ip"]);
    $user = mysql_real_escape_string($_POST["user"]);
    $score = mysql_real_escape_string($_POST["score"]);
    if(is_array($_POST["games"])) $games = mysql_real_escape_string(implode(",",$_POST["games"]));
    else $games = "";
    if($_POST["add"]){
      // Server hinzufuegen
      mysql_query("INSERT INTO server SET name = '".$name."', ip = '".$ip."', user = '$user', score = '".$score."', games = '".$games."'");
      echo "<div class='meldung_ok'>Server eingetragen</div><br><div class='meldung_notify'><b>NICHT VERGESSEN:</b> der SSH-Key muss eingespielt werden - siehe ganz unten</div><br>";
    }elseif($_POST["edit"]){
      // Server aendern
      mysql_query("UPDATE server SET name = '".$name."', ip = '".$ip."', user = '$user', score = '".$score."', games = '".$games."' WHERE id = '".$id."' LIMIT 1");
    }
  }
}
if(!is_array($value["games"])) $value["games"] = explode(",",$value["games"]); // Wenn aus DB ausgelesen, ist das noch kein Array

// Formular
echo "<a href='#' onClick='document.getElementById(\"formular\").style.display = \"block\";'>Server hinzuf&uuml;gen</a><br>";

echo "<form action='index.php?page=server' method='POST' id='formular' style='display: $display;'>
<input type='hidden' name='id' value='".$value["id"]."'>
<table>
  <tr>
    <th colspan='2'>&nbsp;</th>
  </tr>
  <tr>
    <td>Name:</td>
    <td><input type='text' name='name' value='".$value["name"]."'></td>
  </tr>
  <tr>
    <td width='50'>IP:</td>
    <td><input type='text' name='ip' value='".$value["ip"]."'></td>
  </tr>
  <tr>
    <td width='50'>User:</td>
    <td><input type='text' name='user' value='".$value["user"]."'></td>
  </tr>
  <tr>
    <td>Games:</td>
    <td><select name='games[]' size=5 multiple>";
$games = array();
$query = mysql_query("SELECT id, icon, name FROM games ORDER BY name");
while($row = mysql_fetch_assoc($query)){
  $games[$row["id"]]["name"] = $row["name"];
  $games[$row["id"]]["icon"] = $row["icon"];
  echo "<option value='".$row["id"]."' ";
  if(in_array($row["id"],$value["games"])) echo "selected='selected'";
  echo ">".$row["name"]."</option>";
}
echo "</select></td>
  </tr>
  <tr>
    <td>Score:</td>
    <td><input type='text' name='score' value='".$value["score"]."'></td>
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
    <th width='40'>Ping</th>
    <th width='40'>Login</th>
    <th width='200'>Name</th>
    <th width='200'>Games</th>
    <th width='50'>Score</th>
    <th width='200'>&nbsp;</th>
  </tr>";

$query = mysql_query("SELECT * FROM server ORDER BY name");
while($row = mysql_fetch_assoc($query)){
  if(host_check_ping($row)) $ping_color = "#00FF00";
  else $ping_color = "#FF0000";
  if(host_check_login($row)) $login_color = "#00FF00";
  else $login_color = "#FF0000";

  echo "<tr>
    <td style='background-color: $ping_color;'>&nbsp;</td>
    <td style='background-color: $login_color;'>".$row["user"]."</td>
    <td valign='top' title='".$row["ip"]."'>".$row["name"]."</td>
    <td valign='top'>";
  foreach(explode(",",$row["games"]) as $g){
    echo "<img src='images/".$games[$g]["icon"]."' title='".$games[$g]["name"]."' height='$image_height'> ";
  }
  echo "</td>
    <td valign='top'>".$row["score"]."</td>
    <td valign='top' align='center'><a href='index.php?page=server&cmd=edit&id=".$row["id"]."'>edit</a> | <a href='index.php?page=server&cmd=del&id=".$row["id"]."' onClick='return confirm(\"Server wirklich l&ouml;schen?\");'>del</a>&nbsp;&nbsp;--&nbsp;&nbsp;
    <a href='index.php?page=server&cmd=reboot&id=".$row["id"]."' onClick='return confirm(\"Server wirklich rebooten?\");'>reboot</a> | <a href='index.php?page=server&cmd=shutdown&id=".$row["id"]."' onClick='return confirm(\"Server wirklich herunterfahren?\");'>shutdown</a></td>
  </tr>";
}

echo "</table>";
echo "<a href='index.php?page=server&cmd=shutdown_all' onClick='return confirm(\"Wirklich alle Server herunterfahren?\");'>shutdown all</a>";

echo "<br><br>";

// SSH-Key anzeigen
echo "<h3>SSH-Key</h3>";
echo "Folgender ssh-Key muss auf allen Server in der Datei ~/.ssh/authorized_keys eingetragen werden! (ist nur eine Zeile!)<br>";
echo "<input type='text' size='150' value='$ssh_pub_key' onClick='this.select();'>";
}
?>
