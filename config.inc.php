<?php
############################################################
# Gameserver Webinterface                                  #
# Copyright (C) 2010 Torsten Amshove <torsten@amshove.net> #
############################################################

// MySQL-Settings
$mysql_host = "localhost";
$mysql_user = "gameserver";
$mysql_pw = "";
$mysql_db = "gameserver";

// SSH-Settings
# Der private-Key muss irgendwo liegen, wo man nicht per URL dran kommt!!!!!
# Und der Owner muss der Benutzer sein, mit dem der Webserver ausgefuehrt wird!
# Un die Rechte muessen 600 sein!
$ssh_priv_key = "/etc/apache2/ssh_key_gameserver";
$ssh_pub_key = "";
$ssh_string = "ssh -o StrictHostKeyChecking=no -o ConnectTimeout=1 -i $ssh_priv_key"; // SSH-Befehl, mit dem die Verbindung aufgebaut

// Default-PW, was gesetzt wird
$default_pw = "default";

// Groesse der Bilder
$image_height = "15";
?>
