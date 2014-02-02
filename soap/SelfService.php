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

require("../config.inc.php");
require("../functions.inc.php");

if($_SERVER["REMOTE_ADDR"] != "127.0.0.1" && (!isset($_SERVER['PHP_AUTH_USER']) || $_SERVER['PHP_AUTH_USER'] != $soap_user || $_SERVER['PHP_AUTH_PW'] != $soap_pw || $soap_pw == "changeme")){
  header('WWW-Authenticate: Basic realm="Gameserver Webinterface SOAP"');
  header('HTTP/1.0 401 Unauthorized');
  echo "Don't Panic!";
  exit;
}else{
  if(isset($_GET["wsdl"])){
    echo "<?xml version ='1.0' encoding ='UTF-8' ?>
    <definitions name='SelfService'
      targetNamespace='http://".$_SERVER['HTTP_HOST']."/SelfService'
      xmlns:tns='http://".$_SERVER['HTTP_HOST']."/SelfService'
      xmlns:soap='http://schemas.xmlsoap.org/wsdl/soap/'
      xmlns:xsd='http://www.w3.org/2001/XMLSchema'
      xmlns:soapenc='http://schemas.xmlsoap.org/soap/encoding/'
      xmlns:wsdl='http://schemas.xmlsoap.org/wsdl/'
      xmlns='http://schemas.xmlsoap.org/wsdl/'> 
    
    <message name='getServerRequest'>
      <part name='tcid' type='xsd:int'/>
    </message> 
    <message name='getServerResponse'>
      <part name='Result' type='xsd:array'/>
    </message> 
    
    <portType name='getServerPortType'>
      <operation name='getServer'>
        <input message='tns:getServerRequest'/>
        <output message='tns:getServerResponse'/>
      </operation>
    </portType> 
    
    <binding name='getServerBinding' type='tns:getServerPortType'>
      <soap:binding style='rpc'
        transport='http://schemas.xmlsoap.org/soap/http'/>
      <operation name='getServer'>
        <soap:operation soapAction='urn:SelfService#getServer'/>
        <input>
          <soap:body use='encoded' namespace='urn:SelfService'
            encodingStyle='http://schemas.xmlsoap.org/soap/encoding/'/>
        </input>
        <output>
          <soap:body use='encoded' namespace='urn:SelfService'
            encodingStyle='http://schemas.xmlsoap.org/soap/encoding/'/>
        </output>
      </operation>
    </binding> 
    
    <service name='getServerService'>
      <port name='getServerPort' binding='getServerBinding'>
        <soap:address location='http://".$_SERVER['HTTP_HOST']."/soap/SelfService.php'/>
      </port>
    </service>


    <message name='startServerRequest'>
      <part name='tcid' type='xsd:int'/>
      <part name='turnierid' type='xsd:int'/>
      <part name='variables' type='xsd:array'/>
    </message> 
    <message name='startServerResponse'>
      <part name='Result' type='xsd:array'/>
    </message> 
    
    <portType name='startServerPortType'>
      <operation name='startServer'>
        <input message='tns:startServerRequest'/>
        <output message='tns:startServerResponse'/>
      </operation>
    </portType> 
    
    <binding name='startServerBinding' type='tns:startServerPortType'>
      <soap:binding style='rpc'
        transport='http://schemas.xmlsoap.org/soap/http'/>
      <operation name='startServer'>
        <soap:operation soapAction='urn:SelfService#startServer'/>
        <input>
          <soap:body use='encoded' namespace='urn:SelfService'
            encodingStyle='http://schemas.xmlsoap.org/soap/encoding/'/>
        </input>
        <output>
          <soap:body use='encoded' namespace='urn:SelfService'
            encodingStyle='http://schemas.xmlsoap.org/soap/encoding/'/>
        </output>
      </operation>
    </binding> 

    <service name='startServerService'>
      <port name='startServerPort' binding='startServerBinding'>
        <soap:address location='http://".$_SERVER['HTTP_HOST']."/soap/SelfService.php'/>
      </port>
    </service>



    <message name='restartServerRequest'>
      <part name='id' type='xsd:int'/>
    </message> 
    <message name='restartServerResponse'>
      <part name='Result' type='xsd:boolean'/>
    </message> 
    
    <portType name='restartServerPortType'>
      <operation name='restartServer'>
        <input message='tns:restartServerRequest'/>
        <output message='tns:restartServerResponse'/>
      </operation>
    </portType> 
    
    <binding name='restartServerBinding' type='tns:restartServerPortType'>
      <soap:binding style='rpc'
        transport='http://schemas.xmlsoap.org/soap/http'/>
      <operation name='restartServer'>
        <soap:operation soapAction='urn:SelfService#restartServer'/>
        <input>
          <soap:body use='encoded' namespace='urn:SelfService'
            encodingStyle='http://schemas.xmlsoap.org/soap/encoding/'/>
        </input>
        <output>
          <soap:body use='encoded' namespace='urn:SelfService'
            encodingStyle='http://schemas.xmlsoap.org/soap/encoding/'/>
        </output>
      </operation>
    </binding>
    
    <service name='restartServerService'>
      <port name='restartServerPort' binding='restartServerBinding'>
        <soap:address location='http://".$_SERVER['HTTP_HOST']."/soap/SelfService.php'/>
      </port>
    </service>
    </definitions>";
  }else{
    // Gibt den Server fuer das Match zurueck
    function getServer($tcid){
      $tcid = mysql_real_escape_string($tcid);
      if(!is_numeric($tcid)) return false;
      $server = array();

      $query = mysql_query("SELECT * FROM running WHERE t_contest_id = '$tcid' LIMIT 1");
      if(mysql_num_rows($query) > 0){
        $val = mysql_fetch_assoc($query);
        $server["id"] = $val["id"];
        $server["status"] = "running";
        $server["screen"] = $val["screen"];
        $server["port"] = $val["port"];
        $server["variables"] = array();
        $vars = explode("<br>",$val["vars"]);
        foreach($vars as $var){
          $var = explode(" => ",$var);
          $server["variables"][trim($var[0])] = trim($var[1]);
        }

        $query2 = mysql_query("SELECT * FROM server WHERE id = '".$val["serverid"]."' LIMIT 1");
        $val2 = mysql_fetch_assoc($query2);
        $server["name"] = $val2["name"];
        $server["ip"] = $val2["ip"];
      }else{
        $server["status"] = "not running";
      }

      return $server;
      // $server["id"]          - interne ID des Servers (wenn status == running)
      // $server["status"]      - running / not running
      // $server["screen"]      - Name des Screens (eher fuer debugging als fuer den User)
      // $server["port"]        - Port des Servers
      // $server["variables"][] - Array mit allen Variablen, die zum Starten des Games gefuellt wurden (key => value)
      // $server["name"]        - Hostname des Servers
      // $server["ip"]          - IP des Servers
    }

    // Startet einen Server fur das Match
    function startServer($tcid,$turnierid,$variables){
      global $tmp_dir;

      $lockfile = $tmp_dir."/gswi_soap_starting";
      while(file_exists($lockfile)){
        usleep(rand(1000,100000));
      }
      touch($lockfile);

      if(mysql_num_rows(mysql_query("SELECT * FROM running WHERE t_contest_id = '".mysql_real_escape_string($tcid)."'")) > 0){
        $return[0] = false;
        $return[1] = "Zu dieser Begegnung ist schon ein Server gestartet.";
        unlink($lockfile);
        return $return;
      }

      $query = mysql_query("SELECT * FROM turniere WHERE turnier = '".mysql_real_escape_string($turnierid)."' LIMIT 1");
      if(mysql_num_rows($query) < 1){
        $return[0] = false;
        $return[1] = "Turnier wurde keinem Gameserver zugeordnet.";
        unlink($lockfile);
        return $return;
      }else{
        $query2 = mysql_query("SELECT * FROM games WHERE id = '".mysql_result($query,0,"game")."' LIMIT 1");
        $game = mysql_fetch_assoc($query2);
      }

      // Freien Server suchen
      $server = false;
      $query = get_server_with_game($game["id"]);
      while($row = mysql_fetch_assoc($query)){
        $score_used = @mysql_result(mysql_query("SELECT SUM(score) AS sum FROM running WHERE serverid = '".$row["id"]."' LIMIT 1"),0,"sum");
        if(!$score_used) $score_used = 0;
        if(($row["score"]-$score_used) >= $game["score"]){
          $server = $row;
          break;  // Einen Server zum starten gefunden
        }
      }
      if(!$server){
        $return[0] = false;
        $return[1] = "Kein Server frei ..";
        unlink($lockfile);
        return $return; // Keinen freien Server gefunden ...
      }
    
      // Varablen zubereiten
      $replace_vars = array();
      foreach($variables as $k => $v){
        $replace_vars["##".$k."##"] = $v;
      }
    
      // CMD zusammenbauen
      $port = get_port($server,$game["start_port"]); // Port ermitteln - erster freier Port ab start_port
      if(!$port){
        $return[0] = false;
        $return[1] = "Kein Port gefunden ..";
        unlink($lockfile);
        return $return;
      }else{
        $vars = parse_cmd($game["cmd"]); // Variablen aus cmd auslesen
        $cmd = str_replace("##port##",$port,$game["cmd"]);
        $values = "port => $port<br>";
        foreach($vars as $v){
          // Variablen durch Werte ersetzen
          $cmd = str_replace($v,$replace_vars[$v],$cmd);
          $values .= substr($v,2,-2)." => ".$replace_vars[$v]."<br>";
        }
    
        $screen = $game["name"]."_".$port;
        if(!starte_cmd($server,$cmd,$screen,$game["folder"])){ // Server starten ...
          unlink($tmp_dir."/".$server["ip"]."_".$port); // Lockfile loeschen
          $return[0] = false;
          $return[1] = "Server starten fehlgeschlagen";
          unlink($lockfile);
          return $return;
        }else{
          // Und in die "running"-Tabelle einfuegen
          mysql_query("INSERT INTO running SET screen = '".$screen."', serverid = '".$server["id"]."', gameid = '".$game["id"]."', port = '".$port."', cmd = '".str_replace("'","\'",$cmd)."', score = '".$game["score"]."', vars = '".str_replace("'","\'",$values)."', t_contest_id = '".$tcid."'");
          unlink($tmp_dir."/".$server["ip"]."_".$port); // Lockfile loeschen
          $return[0] = true;
          $return[1] = "Server erfolgreich gestartet";
          unlink($lockfile);
          return $return;
        }
      }
      // $return[0] - true/false - Server (nicht) erfolgreich gestartet
      // $return[1] - (Fehler-)meldung
      unlink($lockfile);
    }

    // Startet einen Server neu
    function restartServer($id){
      if(!restart_server(mysql_real_escape_string($id))) return false;
      else return true;
    }

    #$server = new SoapServer("http://".$_SERVER['HTTP_HOST']."/soap/SelfService.php?wsdl");
    $server = new SoapServer("http://127.0.0.1/soap/SelfService.php?wsdl");
    $server->addFunction("getServer");
    $server->addFunction("startServer");
    $server->addFunction("restartServer");
    $server->handle();
  }
}
?>
