# install phpmyadmin
docker exec -it c00cjz00-seeddms bash

cd /var/www/seeddms/www
apt-get update -y
apt-get install wget unzip joe -y

wget https://files.phpmyadmin.net/phpMyAdmin/5.2.0/phpMyAdmin-5.2.0-all-languages.zip
unzip phpMyAdmin-5.2.0-all-languages.zip
mv phpMyAdmin-5.2.0-all-languages mphpMyAdmin
rm phpMyAdmin-5.2.0-all-languages.zip

cp /config.sample.inc.php phpMyAdmin/config.inc.php

# edit phpMyAdmin/config.inc.php
## host='seeddms-db';
