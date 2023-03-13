# varo mutual

This is the API which sets on the master PowerDNS replication server. This works together with [Dashboard](https://github.com/varodomains/dashboard).

The TODO list for setting up Mutual is:
* Setup your web server and PHP (we run PHP 8.2)
* Setup sudo to access pdnsutil under the web user
* Import the modified database for PowerDNS/Mutual
* `cd etc && cp config.sample.php config.php && vim config.php`
* Create a cronjob to pull the TLD list as it is required to differentiate between Handshake and ICANN TLDs: `0 0 * * * /usr/bin/php /var/www/html/mutual/etc/tlds.php >/dev/null 2>&1` (modify the path to tlds.php as needed)

We highly recommend using the scripts created by Nathan Woodburn ([here](https://github.com/Nathanwoodburn/HNS-server/tree/main/varo)) rather than trying to set this up manually.

## License
[![CC BY-NC-SA](https://i.creativecommons.org/l/by-nc-nd/3.0/88x31.png)](https://creativecommons.org/licenses/by-nc-sa/4.0/)
