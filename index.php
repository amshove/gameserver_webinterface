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

require("config.inc.php");
require("functions.inc.php");

// Logout
if($_GET["logout"]){
  session_destroy();
  session_start();
  $_SESSION["ad_level"] = 0;
  $logged_in = false;
}

?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN"
 "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="de" lang="de" dir="ltr">
<head>
  <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
  <title>maxlan Gameserver Webinterface</title>
  <link rel="SHORTCUT ICON" href="favicon.ico" type="image/x-icon">
  <link rel="stylesheet" type="text/css" href="style.css" />
</head>
<body>

<?php
if($_POST["submit_login"]){
  // Login
  if(empty($_POST["submit_login"]) || empty($_POST["pw"])) echo "<div class='meldung_error'>Nicht alle Felder angegeben.</div><br>";
  else{
    $query = mysql_query("SELECT id, name, ad_level FROM user WHERE LOWER(login) = LOWER('".mysql_escape_string($_POST["login"])."') AND pw = '".sha1($_POST["pw"])."' LIMIT 1");
    if(mysql_num_rows($query) == 1){
      $_SESSION["user_id"] = mysql_result($query,0,"id");
      $_SESSION["user_name"] = mysql_result($query,0,"name");
      $_SESSION["ad_level"] = mysql_result($query,0,"ad_level");
      $logged_in = true;
      if($_POST["pw"] == $default_pw) $set_pw = true; // Wenn das das default-pw war, dann aendern
    }
  }
}elseif($_POST["submit_pw"]){
  // default-PW aendern
  if(empty($_POST["pw1"]) || empty($_POST["pw2"])){
    echo "<div class='meldung_error'>Nicht alles ausgef&uuml;llt.</div><br>";
    $set_pw = true;
  }elseif($_POST["pw1"] != $_POST["pw2"]){
    echo "<div class='meldung_error'>PWs stimmen nicht &uuml;berein.</div><br>";
    $set_pw = true;
  }else{
    mysql_query("UPDATE user SET pw = '".sha1($_POST["pw1"])."' WHERE id = '".$_SESSION["user_id"]."' LIMIT 1");
  }
}

if(!$logged_in){
  // Login-Formular
  echo "<form action='index.php' method='POST'>
  <table>
    <tr>
      <th colspan='2'>Login</th>
    </tr>
    <tr>
      <td width='60'>Login:</td>
      <td><input type='text' name='login'></td>
    </tr>
    <tr>
      <td>Pw:</td>
      <td><input type='password' name='pw'></td>
    </tr>
    <tr>
      <td colspan='2' align='center'><input type='submit' name='submit_login' value='login'></td>
    </tr>
  </table>
  </form>";
}elseif($set_pw){
  // Default-PW aendern
  echo "<form action='index.php' method='POST'>
  <table>
    <tr>
      <td width='100'>neues PW:</td>
      <td><input type='password' name='pw1'></td>
    </tr>
    <tr>
      <td>Nochmal:</td>
      <td><input type='password' name='pw2'></td>
    </tr>
    <tr>
      <td colspan='2' align='center'><input type='submit' name='submit_pw' value='PW setzen'></td>
    </tr>
  </table>
  </form>";
}else{
  // Eigentliche Seite
  echo "<div class='navi'><a class='navi' href='index.php'>Home</a>";
  echo " | <a class='navi' href='index.php?page=anlegen'>Server starten</a>";
  echo " | <a class='navi' href='index.php?page=overview'>&Uuml;bersicht</a>";
  if($_SESSION["ad_level"] >= 4) echo " | <a class='navi' href='index.php?page=server'>Server administrieren</a>";
  if($_SESSION["ad_level"] >= 4) echo " | <a class='navi' href='index.php?page=games'>Games administrieren</a>";
  if($_SESSION["ad_level"] >= 4) echo " | <a class='navi' href='index.php?page=turniere'>Turniere administrieren</a>";
  if($_SESSION["ad_level"] >= 5) echo " | <a class='navi' href='index.php?page=user'>User administrieren</a>";
  echo " | <a class='navi' href='index.php?logout=true'>Logout</a>";
  echo "&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; <font style='font-size: 10px'>Gameserver Webinterface by <a style='color:#FFFFFF; font-size: 10px;' href='http://www.amshove.net/'>Torsten Amshove</a></font>";
  echo "</div>";
  switch($_GET["page"]){
    case "anlegen": include("anlegen.php"); break;
    case "overview": include("overview.php"); break;
    case "server": include("server.php"); break;
    case "games": include("games.php"); break;
    case "turniere": include("turniere.php"); break;
    case "user": include("user.php"); break;
    default: include("home.php"); break;
  }
}
?>

</body>
</html>
