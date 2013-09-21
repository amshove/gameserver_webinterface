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

if($_SESSION["ad_level"] >= 5){
echo "Hier werden ein par Einstellungen getestet. Hier sollte alles gr&uuml;n sein. Wenn etwas rot ist, wird ein Hinweis zum Fehler angezeigt.<br><br>";

echo "<table>";
echo "  <tr>";
$bgcolor = "#FF0000";
$meldung = "Diese Variable darf weder leer sein, noch darf dort \"changeme\" stehen. Hier wird ein geheimer String ben&ouml;tigt, der hier und im dotlan-Modul eingetragen wird.";
if($soap_pw != "" && $soap_pw != "changeme"){
  $bgcolor = "#00FF00";
  $meldung = "";
}
echo "    <td bgcolor='$bgcolor'>\$soap_pw</td>";
echo "    <td>$meldung</td>";
echo "  </tr>";
echo "  <tr>";
$bgcolor = "#00FF00";
$meldung = "";
if(!file_exists($ssh_priv_key)){
  $bgcolor = "#FF0000";
  $meldung = $ssh_priv_key." wurde nicht gefunden.";
}elseif(decoct(fileperms($ssh_priv_key)) != "100600"){
  $bgcolor = "#FF0000";
  $meldung = $ssh_priv_key." hat die falschen Rechte - diese m&uuml;ssen \"0600\" bzw. \"-rw-------\" sein.";
}elseif(!is_readable($ssh_priv_key)){
  $bgcolor = "#FF0000";
  $meldung = $ssh_priv_key." kann vom Webserver nicht gelesen werden. Ist der Owner der gleiche wie der User, mit dem der Webserver l&auml;uft?";
}
echo "    <td bgcolor='$bgcolor'>\$ssh_priv_key</td>";
echo "    <td>$meldung</td>";
echo "  </tr>";
echo "  <tr>";
$bgcolor = "#00FF00";
$meldung = "";
if(!file_exists($ssh_pub_key)){
  $bgcolor = "#FF0000";
  $meldung = $ssh_pub_key." wurde nicht gefunden.";
}elseif(!is_readable($ssh_pub_key)){
  $bgcolor = "#FF0000";
  $meldung = $ssh_pub_key." kann vom Webserver nicht gelesen werden.";
}
echo "    <td bgcolor='$bgcolor'>\$ssh_pub_key</td>";
echo "    <td>$meldung</td>";
echo "  </tr>";
echo "  <tr>";
$bgcolor = "#FF0000";
$meldung = "Zugriff auf die dotlan-DB nicht m&ouml;glich. F&uuml;r die richtigen Rechte des Users siehe die Readme.";
$dotlan = mysql_connect($dotlan_mysql_host,$dotlan_mysql_user,$dotlan_mysql_pw);
if($dotlan){
  if(mysql_select_db($dotlan_mysql_db,$dotlan)){
    if(mysql_query("SELECT * FROM t_teilnehmer LIMIT 1",$dotlan) && mysql_query("SELECT * FROM t_turnier LIMIT 1",$dotlan) && mysql_query("SELECT nick, id FROM user LIMIT 1",$dotlan) && mysql_query("SELECT * FROM events LIMIT 1",$dotlan) && mysql_query("SELECT * FROM t_teilnehmer_part LIMIT 1",$dotlan) && mysql_query("SELECT * FROM t_contest LIMIT 1",$dotlan)){
      $bgcolor = "#00FF00";
      $meldung = "";
    }
  }
}
echo "    <td bgcolor='$bgcolor'>Dotlan DB</td>";
echo "    <td>$meldung</td>";
echo "  </tr>";
echo "  <tr>";
$bgcolor = "#FF0000";
$meldung = "Cronjob-Eintrag in /etc/crontab fehlt.";
if(trim(shell_exec("grep cronjob_turniere.php /etc/crontab > /dev/null 2>&1 ; echo $?")) == "0"){
  $bgcolor = "#00FF00";
  $meldung = "";
}
echo "    <td bgcolor='$bgcolor'>Cronjob</td>";
echo "    <td>$meldung</td>";
echo "  </tr>";
echo "</table>";
}
?>
