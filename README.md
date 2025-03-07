commande pour créer crontab :

crontab -e

dans crontab -e rajouter cette ligne à la fin : 

00 01 * * * /usr/local/bin/php /var/www/symfony_docker/bin/console app:deduire-medicaments >> /var/log/cron.log 2>&1
