# ![MASTERPLAN](frontend/img/logo.png)
*[[English Version](README.md)] web based open source workforce management*  
*[[Deutsche Version](README.de.md)] webbasierte Open Source Dienstplansoftware*

## Highlights
- Die intuitive, browserbasierte Benutzeroberfläche erklärt sich von selbst und ist vollkommen plattformunabhängig.
- Der Dienstplanalgorithmus erstellt einen Wochenplan vollautomatisch auf Basis der Wochen- und Monatsarbeitszeit sowie der festgelegten Beschränkungen eines Mitarbeiters. Sie können feingranular einstellen, welche Dienste ein Mitarbeiter an bestimmten Wochentagen nicht ausführen kann.
- Mit dem optional aktivierbarem Self-Care-Portal können Mitarbeiter eigenständig ihre Dienste einsehen, tauschen, vakante Dienste besetzen und Abwesenheiten eintragen.
- Exportfunktion - Ausgabe im PDF- und HTML-Format zum Ausdrucken oder Einbetten in Kollaborations-Systeme oder Webseiten.
- LDAP-Anbindung für Benutzerimport möglich - binden Sie MASTERPLAN z.B. an Ihre Active-Directory-Domäne an und Mitarbeiter können sich mit dem selben Kennwort anmelden.
- Versenden von Termineinladungen für geplante Dienste sind via E-Mail möglich (zum Eintragen in Thunderbird- oder Outlook-Kalender).
- Rechtemanagement - Sie können für jeden Dienstplan separate Admins bestimmen, die die Pläne verwalten und Dienste besetzen dürfen.
- Integration in Ihr Corporate Design (CD) möglich.
- Programm-Anpassungen an Ihre Bedürfnisse auf Angebotsbasis möglich - wenn Sie eine Funktion vermissen nehmen Sie einfach Kontakt auf.

## Systemanforderungen
### Client
Folgende Webbrowser werden unterstützt, sofern JavaScript aktiviert ist.
- Google Chrome / Chromium, ab Version 70 (oder neuer)
- Opera, ab Version 60 (oder neuer)
- Vivaldi, ab Version 2.5 (oder neuer)
- Microsoft Edge, ab Version 44 (oder neuer)

Folgende Webbrowser werden im Moment explizit **nicht** unterstützt.
- Microsoft Internet Explorer (alle Versionen)
- Mozilla Firefox (alle Versionen)
- Apple Safari (alle Versionen)

### Server
#### Speicherbedarf
- 10 MiB Festplattenspeicher für die MASTERPLAN-Anwendung
- plus Speicherplatz für die (anwachsende) Datenbank
  - schätzungsweise 0,2 MiB pro Mitarbeiter und Monat
- mind. 128 MiB PHP memory_limit

#### Software
- Betriebssystem: beliebige Linux-Distribution, Empfehlung: Debian
- Webserver: Apache 2 + PHP 7 (oder neuer) mit Modulen: LDAP, GD
- Datenbanksystem: MySQL oder MariaDB

## Schnellinstallationsanleitung
1. Installieren Sie einen Linux-Server gemäß den Systemanforderungen (inkl. Apache, PHP, MySQL).
   ```
   apt install apache2 mysql-server php php-ldap php-gd
   ```
2. Legen Sie das empfohlene PHP-Memory-Limit fest:
   ```
   nano /etc/php/7.3/apache2/php.ini
   service apache2 restart
   ```
3. Legen Sie eine Datenbank auf Ihrem Datenbank-Server an und importieren Sie das Datenbankschema aus `lib/sql/masterplan.sql` (z.B. über PHPmyadmin oder das Kommandozeilenwerkzeug `mysql`).
4. Kopieren Sie alle Dateien in Ihr Webserver-Wurzelverzeichnis (z.B. `/var/www/masterplan`).
5. Editieren Sie die MASTERPLAN-Konfigurationsdatei `conf.php`.
   1. Tragen Sie Ihre Zugangsdaten zum Datenbankserver ein (befüllen Sie die Konstanten: `DB_HOST`, `DB_NAME`, `DB_USER`, `DB_PASS`).
   2. *(optional)* Wenn Sie den LDAP-Sync verwenden möchten, befüllen Sie bitte die folgenden Konstanten entsprechend: `LDAP_SERVER`, `LDAP_USER`, `LDAP_DOMAIN`, `LDAP_PASS`, `LDAP_QUERY_ROOT`, `LDAP_SYNC_GROUP`.
6. Erteilen Sie dem Webserver-Benutzer (`www-data`) rekursiv Schreibrechte für die Verzeichnisse `tmp` und `template` innerhalb ihrer MASTERPLAN-Installation.
   ```
   cd /var/www/masterplan
   chown -R www-data tmp && chmod 0755 tmp
   chown -R www-data template && chmod 0755 template
   ```
7. Sofern Sie Funktionen von MASTERPLAN verwenden möchten, die einen E-Mail-Versand voraussetzen (z.B. der Versand von Termineinladungen für Dienste), stellen Sie bitte sicher, dass Ihr Mailsystem korrekt eingerichtet ist. MASTERPLAN verwendet die Standard-PHP-Funktion `mail()` für den Versand.
8. Rufen Sie MASTERPLAN im Webbrowser auf. Sie werden vom Einrichtungsassistenten begrüßt. Bitte wählen Sie einen Administrator-Benutzernamen und ein Kennwort für diesen. Mit einem Klick auf „Einrichten“ wird der Benutzer angelegt und Sie können sich an der Anmeldemaske anmelden.
9. Wechseln Sie in das Menü 'Stammdaten' > 'Globale Einstellungen'. Importieren Sie Ihre Lizenzdatei und tragen Sie Ihren Domänennamen ein.
10. Richten Sie HTTPS auf Ihrem Webserver ein, damit die Clients über einen verschlüsselten Kanal auf MASTERPLAN zugreifen können. Hinweise zum Apache SSL-Modul finden Sie z.B. unter: https://wiki.ubuntuusers.de/Apache/mod_ssl/
11. Richten Sie ein Backup für die Datenbank ein. Hinweise für eine Datenbanksicherung finden Sie z.B. hier: https://wiki.ubuntuusers.de/MySQL/Backup/

## Konfiguration & Verwendung
Bitte lesen Sie hierzu das PDF-Handbuch im Ordner `frontend/manual`.

## Upgrade auf eine neuere Version
Bitte lesen Sie die [Upgrade Notes](UPGRADE.md).

## Lizenzierung
Sie können MASTERPLAN mit bis zu 5 Benutzern kostenfrei verwenden (Spenden zur Weiterentwicklung sind gerne gesehen). Für mehr als 5 Benutzer ist eine Lizenz notwendig, welche Sie [hier](https://georg-sieber.de/?page=masterplan) erwerben können. Bei Fragen nehmen Sie bitte [Kontakt auf](https://georg-sieber.de/?page=impressum).

## Übersetzungen & Verbesserungen Willkommen!
Bitte eröffnen Sie einen Pull-Request wenn Sie die Software verbessern möchten!

## Support & Cloud-Hosting
Sie haben keinen eigenen Webserver, benötigen Unterstützung bei Installation oder Betrieb oder wünschen eine spezielle Anpassung für Ihr Unternehmen? Kein Problem. Sie können einen bereitgestellten Zugang für eine monatliche Gebühr pro Mitarbeiter nutzen, den E-Mail-Support in Anspruch nehmen oder Angebote zur Weiterentwicklung der Software erhalten. Bitte [kontaktieren Sie mich](https://georg-sieber.de/?page=impressum).

## Screenshots
![Plan](.github/screenshots/2plan.png)  
![Plan ausgefüllt](.github/screenshots/2planfilled.png)  
![Mitarbeiterübersicht](.github/screenshots/2useroverview.png)  
![Dienste](.github/screenshots/1services.png)  
![Geburtstage](.github/screenshots/1birthdays.png)  
![Beschränkungen](.github/screenshots/1userconstraints.png)  
![Abwesenheiten](.github/screenshots/3absence.png)  
![Meine Dienste](.github/screenshots/3myservices.png)  
![Diensttausch](.github/screenshots/3swap.png)  
