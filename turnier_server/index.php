<?php
###########################################################################
# Gameserver Webinterface - dotlan Modul                                  #
# Copyright (C) 2013 Christian Egbers <c.egbers@serious-networx.net>      #
#                    Torsten Amshove <torsten@amshove.net>                #
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
// Include wichtiger Funktionen
include_once("../global.php");

// Config
$soap_ip = "192.168.0.185"; // IP des gameserver Webinterface
$soap_user = "game_wi"; // $soap_user aus config.inc.php auf dem mgameserver Webinterface
$soap_pw = "changeme"; // $soap_pw aus config.inc.php auf dem gameserver Webinterface

////////////////////////////////////////////////
// Seitettietel
$PAGE->sitetitle = $PAGE->htmltitle = _("Contest Server starten!"); // Tietel der als THML-Überschrift der Seite angezeigt wird

// genutzte Variablen
$event_id   = $EVENT->next;      // ID des anstehenden Event's
$user_id    = $CURRENT_USER->id;
$date       = date("Y.m.d");
$time       = date("H:i:s");

if(!$user_id ){ $PAGE->error_die($HTML->gettemplate("error_logintopost"));}

// Verbindung zum Gameserver Webinterface herstellen
try{
  $client = new SoapClient("http://$soap_ip/soap/SelfService.php?wsdl",array("login"=>$soap_user,"password"=>$soap_pw));
}catch(Exception $e){
  $output .= "Connect ERROR: ".$e->getMessage();
}

if($client && !empty($_GET["tcid"]) && is_numeric($_GET["tcid"])){
  // DB Abfragen
  $sql_turnier = $DB->query("SELECT * FROM t_contest WHERE tcid = '".mysql_real_escape_string($_GET['tcid'])."'");
  $out_turnier = $DB->fetch_array($sql_turnier);
  $out_contest = $DB->fetch_array($DB->query("SELECT * FROM t_turnier WHERE tid = '".$out_turnier['tid']."' "));

  $output = "<table width='100%' cellspacing='1' cellpadding='2' border='0' class='msg2'>
    <tbody>
      <tr>";
  
  if($out_contest['tlogo'] <> ""){
    $output .= "<td colspan='2' class='msghead'>";
  }else{
    $output .= "<td class='msghead'>";
  }
  
  $output .= "<b>".$out_contest['tname']." --> ".htmlentities($_GET['round'])."</b></td>
      </tr>
      <tr class='msgrow2'>";
  
  if($out_contest['tlogo'] <> ""){
    $output .= "<td><img height='75' src='/images/turnier_logo/".$out_contest['tlogo']."'></td>";
  }
  
  $output .= "<td width='100%' valign='top'><div style='padding-top: 5px; padding-left: 5px;'>
    <b>Contest-ID: ".$out_turnier['tcid']."</b><br><br>";
  
  if($_GET['view'] == "single"){
    $out_contest_name = $DB->fetch_array($DB->query("SELECT * FROM t_teilnehmer WHERE tnid = '".$out_turnier['team_a']."'"));
    $out_contest_name_a = $DB->fetch_array($DB->query("SELECT * FROM user WHERE id = '".$out_contest_name['tnleader']."'"));
    $team_a = $out_contest_name_a['nick'];
  }else{
    $out_contest_team_name_a = $DB->fetch_array($DB->query("SELECT * FROM t_teilnehmer WHERE tnid = '".$out_turnier['team_a']."' "));
    $team_a = $out_contest_team_name_a['tnname'];
  }
  
  if($_GET['view'] == "single"){
    $out_contest_name = $DB->fetch_array($DB->query("SELECT * FROM t_teilnehmer WHERE tnid = '".$out_turnier['team_b']."'"));
    $out_contest_name_b = $DB->fetch_array($DB->query("SELECT * FROM user WHERE id = '".$out_contest_name['tnleader']."'"));
    $team_b = $out_contest_name_b['nick'];
  }else{
    $out_contest_team_name_b = $DB->fetch_array($DB->query("SELECT * FROM t_teilnehmer WHERE tnid = '".$out_turnier['team_b']."' "));
    $team_b = $out_contest_team_name_b['tnname'];
  }
  
  $output .= $team_a." vs. ".$team_b;

  $output .= " (".substr($out_turnier['starttime'], 0, 10)." ".substr($out_turnier['starttime'], 11).")</div>";

  //// Ab hier der Kram mit dem Gameserver - vorher war nur Dotlan DB Inhalte ////
  // Server starten
  if($_POST["startServer"]){

    // Alles ausser Buchstaben und Zahlen aus den Teamnamen entfernen
    $team_a = preg_replace("/[^a-zA-Z0-9]*/","",$team_a);
    $team_b = preg_replace("/[^a-zA-Z0-9]*/","",$team_b);

    // Variablen, die im CMD ersetzt werden sollen:
    $start_vars = array(
      "name" => "Contest ".$out_turnier['tcid']." - ".$team_a." vs ".$team_b,
      "rcon" => "TODO_rcon",
      "pw" => "TODO_pw"
    );

    try{
      $result = $client->startServer($out_turnier['tcid'],$out_turnier['tid'],$start_vars);
    }catch(Exception $e){
      $output .= "startServer ERROR: ".$e->getMessage();
    }

    if($result[0]){
      $output .= "<br>Server erfolgreich gestartet.<br>";
    }else{
      $output .= "<br>Beim Starten des Servers ist ein Fehler aufgetreten: ".$result[1]."<br>";
    }
  }

  // Server Infos holen
  try{
    $server = $client->getServer($_GET["tcid"]);
  }catch(Exception $e){
    $output .= "getServer ERROR: ".$e->getMessage();
  }
  // $server["status"]      - running / not running
  // $server["screen"]      - Name des Screens (eher fuer debugging als fuer den User)
  // $server["port"]        - Port des Servers
  // $server["variables"][] - Array mit allen Variablen, die zum Starten des Games gefuellt wurden (key => value)
  // $server["name"]        - Hostname des Servers
  // $server["ip"]          - IP des Servers

  $output .= "<br>";
  if($server["status"] == "not running"){
    if($out_turnier["won"] > 0){
      $output .= "Die Begegnung wurde bereits ausgetragen und das Ergebnis eingetragen.";
    }elseif($out_turnier['ready_a'] <> "0000-00-00 00:00:00" && $out_turnier['ready_b'] <> "0000-00-00 00:00:00" ){
      $output .= "<form action='".$_SERVER["REQUEST_URI"]."' method='POST'><input type='submit' name='startServer' value='Server starten'></form>";
    }else{
      $output .= "Es sind nicht alle Spieler bereit.";
    }
  }elseif($server["status"] == "running"){
    $output .= "<div style='padding-top: 5px; padding-left: 5px;'>";
    $output .= "<b>".$server["ip"].":".$server["port"]."</b><br>";
    $output .= "Name: ".$server["variables"]["name"]."<br>";
    $output .= "RCON: ".$server["variables"]["rcon"]."<br>";
    $output .= "Server-Passwort: ".$server["variables"]["pw"]."<br>";
    $output .= "<br><b>Der Server wird automatisch gestoppt, sobald das <a href='/turnier/?do=contest&id=".$_GET["tcid"]."'>Ergebnis</a> eingetragen wurde.</b>";
    $output .= "</div>";
  }else{
    $output .= "Der Server-Status konnte nicht abgefragt werden.";
  }
  
  $output .= "</td>
      </tr>
    </tbody>
  </table>";
}else $output .= "<br><br>Es ist ein Fehler aufgetreten.";

$PAGE->render( utf8_decode(utf8_encode($output) ) );
?>
