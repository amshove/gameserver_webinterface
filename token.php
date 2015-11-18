<?php
###########################################################################
# Gameserver Webinterface                                                 #
# Copyright (C) 2015 Torsten Amshove <torsten@amshove.net>                #
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
  $value = mysql_fetch_assoc(mysql_query("SELECT * FROM token WHERE id = '".mysql_real_escape_string($_GET["id"])."' LIMIT 1"));
}elseif($_GET["cmd"] == "del" && is_numeric($_GET["id"]) && !empty($_GET["id"])){
  // Token-Pool loeschen
  $id = mysql_real_escape_string($_GET["id"]);
  mysql_query("UPDATE games SET token_pool = '0' WHERE token_pool = '".$id."'"); // Verweise zuruecksetzen
  mysql_query("DELETE FROM token WHERE id = '".$id."' LIMIT 1");
}

// Add/Edit Formular wurde abgeschickt
if($_POST["add"] || $_POST["edit"]){
  $error = false;

  if(empty($_POST["name"])){ // Name darf nicht leer sein
    echo "<div class='meldung_error'>Name muss angegeben werden!</div><br>";
    $error = true;
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
    $name = mysql_real_escape_string($_POST["name"]);
    $token = mysql_real_escape_string($_POST["token"]);
    if($_POST["add"]){
      // Token-Pool anlegen
      mysql_query("INSERT INTO token SET name = '".$name."', token = '".$token."'");
      $id = mysql_insert_id();
    }elseif($_POST["edit"]){
      // Token-Pool aendern
      mysql_query("UPDATE token SET name = '".$name."', token = '".$token."' WHERE id = '".$id."' LIMIT 1");
    }
  }
}

// Formular
echo "<a href='#' onClick='document.getElementById(\"formular\").style.display = \"block\";'>Token-Pool hinzuf&uuml;gen</a><br>";

echo "<form action='index.php?page=token' method='POST' id='formular' style='display: $display;'>
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
    <td>Token:</td>
    <td><textarea name='token'>".$value["token"]."</textarea><br>
        Ein Token pro Zeile</td>
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
    <th width='100'>Name</th>
    <th width='350'>Token</th>
    <th width='100'>&nbsp;</th>
  </tr>";

$query = mysql_query("SELECT * FROM token ORDER BY name");
while($row = mysql_fetch_assoc($query)){
  echo "<tr>
    <td valign='top'>".$row["name"]."</td>
    <td>".nl2br($row["token"])."</td>
    <td align='center' valign='top'><a href='index.php?page=token&cmd=edit&id=".$row["id"]."'>edit</a> | <a href='index.php?page=token&cmd=del&id=".$row["id"]."' onClick='return confirm(\"Token-Pool wirklich l&ouml;schen?\");'>del</a></td>
  </tr>";
}

echo "</table>";
}
?>
