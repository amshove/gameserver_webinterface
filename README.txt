############################################################
# Gameserver Webinterface                                  #
# Copyright (C) 2010 Torsten Amshove <torsten@amshove.net> #
############################################################

################
# Features     #
################
Das Gameserver Webinterface ist dafür gedacht, auf einer LAN-Party bestimmte Gameserver (CS, COD, ..) auf Linux-Servern zu starten.
Vorteil daran ist, man kann das Webinterface für etliche Server nutzen und muss nicht für jeden Server einzeln auf die Konsole gehen.
Außerdem kann die Webseite z.B. an den Turnier-Support übergeben werden, damit diese ohne Linux- und Server-Kenntnisse mit einem Knopfdruck Gameserver bereitstellen können.
Deswegen auch die unterschiedlichen Rechte für die Benutzer:
 - User       - darf Server anlegen, killen und sich die Übersicht angucken
 - Admin      - darf zusätzlich Server und Games einrichten - und darf unter "home" die Liste von gestorbenen Servern aufräumen - und ihm werden weitere Informationen angezeigt (z.B. beim Starten der Befehl, der ausgeführt wird) - und er darf die Server rebooten und herunterfahren
 - Superadmin - darf zusätzlich User anlegen und editieren
Dabei richtet sich der User an den Typischen Turnier-Support, der Gameserver bereitstellt, aber nichts mit den eigentlichen Servern zu tun hat. Dank des Score-Systems braucht der User kein Hintergrundwissen - Wenn er ein Gameserver starten will, werden ihm nur Server zur Auswahl gegeben, auf denen das Game auch läuft, auf denen noch genug Ressourcen frei sind und die erreichbar sind.
Die Admins sind dann diejenigen, die die Server einrichten und administrieren. Der Cleanup z.B. darf nur von diesen ausgeführt werden, damit sie die Möglichkeit haben, nach Ursachen zu forschen.
Übrigens: Beim Anlegen eines Users kann kein Passwort gesetzt werden - es wird ein default-Passwort gesetzt, was beim Anmelden vom User geändert werden muss.

Featureliste:
 - Einfaches Starten und Stoppen von Gameservern per Webinterface
 - Stoppen mehrerer Gameserver gleichzeitig
 - Unterschiedliche Berechtigungen
 - Benutzerdefenierte Variablen in den Start-Befehlen für die Gameserver
 - Default-Werte für die benutzerdefenierten Variablen
 - Zuweisung der Games zu den Servern -> Games können nur auf den passenden Servern gestartet werden
 - Score-System, damit nicht zu viele Gameserver auf einem Server gestartet werden können
 - Übersicht mit laufenden Servern, Games und Scores, sowie farbliche Anzeige freier und ausgefallener Server
 - Abgeschmierte Gameserver werden farblich markiert
 - Nicht erreichbare Server werden farblich markiert und stehen nicht zum Starten von Games zur Verfügung
 - Alle Gameserver werden mit dem Programm "screen" gestartet
 - Passwortlose SSH-Verbindung vom Webinterface per SSH-Key auf die Server
 - Herunterfahren und rebooten einzelner oder aller Server (Linux shutdown)


################
# Installation #
################
1) Daten in das htdocs Verzeichnis entpacken
2) SSH-Keys erzeugen:
 > ssh-keygen
3) Privaten SSH-Key (id_rsa / id_dsa) verschieben in einen Ordner, an den der Webserver-User dran kommt
4) Den öffentlichen Schlüssel (id_rsa.pub / id_dsa.pub) öffnen und die Zeile in die Zwischenablage kopieren (ist nur EINE Zeile! wichtig!)
5) config.inc.php anpassen
 - MySQL-Daten anpassen
 - Priv-SSH-Key-Pfad anpassen
 - Publ-SSH-Key eintragen (den kopierten)
6) DB-Struktur einspielen: DB.sql
7) Seite aufrufen
 - User: superadmin
 - PW: default
8) Game-Icons besorgen und nach images/ hochladen


################
# Einrichtung  #
################
Server einrichten:
 - Hier gibt man Name, IP und Score an
 - Wenn schon Games vorhanden sind, kann man dem Server games zuweisen - nur diese Games können dann auch auf dem Server gestartet werden
 - WICHTIG:
   - Der SSH-Key, der auf der Seite angezeigt wird muss auf dem Gameserver für den User eingespielt werden:
   - Der Key muss in die Datei ~/.ssh/authorized_keys - die muss angelegt werden, wenn noch nicht vorhanden
   - Wichtig: Das ist nur eine lange Zeile - das darf auch nur eine sein!
 - TROUBLESHOOTING:
   - Testen kann man die Verbindung, indem man auf dem Server mit dem Webinterface als root folgendes eingibt:
    > sudo -u <Webserver-User> ssh -i </pfad/zum/ssh_key> <User>@<IP_zu_Zielserver>
   - Wichtig ist, dass der SSH-Key auf dem Server mit dem Webinterface dem User gehört, unter dem der Webserver läuft
   - Und die Rechte müssen auf 600 bleiben, sonst verweigert SSH die Benutzung
   
Games einrichten:
 - Einzugeben ist:
   - Icon (Auflistung aller Dateien unter "images/")
   - Name (Kurzname, ohne sonderzeichen etc - wird für den Screen-Namen benutzt) - z.B. "cs16" oder "css" oder "cod4" oder ...
   - CMD (Befehl zum Starten des Gameservers - hier können Variablen im Format ##varname## benutzt werden)
     - ##port## gibt den Server-Port an - der wird automatisch vergeben beim Starten
     - Ansonsten kann man eigene Variablen vergeben, z.B. ##Servername## für den Namen und ##MaxPlayers## für die Anzahl der Spieler
     - Die eigenen Variablen werden beim Starten alle abgefragt
   - Defaults (Default-Werte für die eigenen(!) Variablen getrennt durch Semikolon ";")
     - Wenn man z.B. ##Servername## und ##MaxPlayers## defeniert hat, kann man als Defaults folgendes angeben: Servername xyz cs Server;11
     - Damit würden die Felder beim Starten automatisch mit "Servername xyz cs Server" und "11" gefüllt werden
     - Wichtig: Die Defaults müssen in der Reihenfolge angegeben werden, in der die variablen im CMD auftauchen
     - Für ##port## kann man keine Defaults angeben
   - Startport (Port, ab dem der nächste freie Port für den Server verwendet wird)
   - Score (Score-Wert, die dieses Game kostet)
   - Server (Liste der Server, auf denen dieses Game laufen kann)
 - Ein Game kann nur auf den Servern gestartet werden, auf denen das Game zugewiesen ist

Die Sache mit den Scores:
 - Die Server bekommen bestimmte Scores (z.B. 100)
 - Die Games bekommen bestimmte Scores (z.B. cs16 = 10, css = 20)
 Dann hat der Server 100 Score-Punkte zu verfügung - die Games kosten 20 bzw 10 Score-Punkte
 In diesem Beispiel können also maximal fünf css Gameserver auf dem Server gestartet werden, da 5 x 20 = 100
 Wenn nur vier css Gameserver gestartet werden (4 x 20 = 80) können zusätzlich noch zwei cs16 auf diesem Server gestartet werden (2 x 10 = 20) - Zusammen macht das dann wieder 100
 Wenn auf einem Server nicht mehr genug Score-Punkte für ein Gameserver verfügbar sind, steht dieser nicht zur Verfügung.
 Damit kann man die Last der Server automatisch beschränken und kontrollieren.
