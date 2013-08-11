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

// MySQL-Settings
$mysql_host = "localhost";
$mysql_user = "gameserver";
$mysql_pw = "";
$mysql_db = "gameserver";

// SSH-Settings
# Der private-Key muss irgendwo liegen, wo man nicht per URL dran kommt!!!!!
# Und der Owner muss der Benutzer sein, mit dem der Webserver ausgefuehrt wird!
# Un die Rechte muessen 600 sein!
$ssh_priv_key = "/etc/apache2/ssh_key_gswi";
$ssh_pub_key = "/etc/apache2/ssh_key_gswi.pub";

// Default-PW, was gesetzt wird
$default_pw = "default";

// Groesse der Bilder
$image_height = "15";
?>
