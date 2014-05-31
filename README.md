WTherm
======

## Web connected Thermostat
- Powered by Raspberry Pi
- Used in combination with a regular room thermostat
- Reads the temperature through a [HomeWizard](http://www.homewizard.nl/) smart home module
- Uses [Pushover](https://pushover.net/) to alert you if something goes wrong

## Operation
- The last measured temperature, target temperature, override status, heating status, humidity and the time at the last update are stored in a MySQL database
- thermostat.php runs every 5 minutes, controls the GPIO and updates the database
- The web interface uses javascriptm, which interacts with the database through data.php

## Installation
This requires a working installation of Apache, PHP5 and MySQL

1. Install [WiringPi](http://wiringpi.com/) and compile the GPIO utility
2. Enable the PHP Phar extension
3. Import WTherm.sql into phpMyAdmin
4. Copy the *WTherm* folder to /usr/local/bin
5. Edit the `config.php`
6. Test your configuration by adding a user:
  `php5 adduser.php username password`
7. Copy the *www* folder contents into your website location (normally /var/www)
8. Allow your Apache user to execute PHP scripts as root:
  Add `%www-data ALL=(ALL) NOPASSWD: /usr/bin/php5` to /etc/sudoers
9. Add the startup script `php5 /usr/local/bin/WTherm/startup.php > /dev/null` to /etc/rc.local
10. Set the thermostat script to run every 5 minutes:
  Add `*/5 *   * * *   root    php5 /usr/local/bin/WTherm/thermostat.php >> /usr/local/bin/WTherm/wtherm.log` to /etc/crontab
11. That's it! Try it out :)

