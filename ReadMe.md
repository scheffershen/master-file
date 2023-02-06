ReadMe
===

Projet
Pour version 2, npm install and npm build ne marche plus!,

## Développement Requirement

- ubuntu 18.04
- symfony 5.4
        "damienharper/doctrine-audit-bundle": "3.4.2",
        "doctrine/dbal": "2.13.4",
- php 7.4 -> 8.0
- mysql 5.7 -> 8.0
- symfony/encore 0.31
- bootstrap 4, jquery 3
        
## Installation

Installer le vendor 

    $ composer install

Créer votre *.env* par *.env.dist* avec votre propre configuration mysql et smtp serveur

    DATABASE_URL=mysql://user:password@127.0.0.1:3306/psmf
    MAILER_URL=smtp://127.0.0.1:25

## npm 

    $ npm config set python C:\Python27

## Dossier de donnèes

Créer deux dossiers à la racine data/ et PSMF/ avec le droit RW à l'utilisateur apache www-data:www-data, ausi pour la dossier var/

## Mysql

La base de donnèes initiale se trouve à SLQ/psmf_init.sql

## Plateform

login: admin
pwd: admin2020!

## MailDev

http://test-psmf.localhost:1080/

    # snap install docker
    # docker run -d -p 1080:80 -p 25:25 maildev/maildev

## crontab

    php bin/console psmf:alerte-mail

Sur la machine dans /var/www/projet/
> crontab -e
____________________________________________________________________________________________

# psmf modification alerte mails
0 0 * * 0 php /var/www/psmf.localhost/bin/console psmf:alerte-mail >> /var/www/psmf.localhost/var/log/alerte-mail.log

___________________________________________________________________________________________________

## Fix: Warning: array_map(): Expected parameter 2 to be an array, bool given

source: vendor/tecnickcom/tcpdf/include/tcpdf_fonts.php (line 2002)

from: 

    $carr = array_map(array('TCPDF_FONTS', 'uniord'), $chars);

to: 

    $carr = array_map(array('TCPDF_FONTS', 'uniord'), (array)$chars);

## Fix: The class 'DateTime' was not found in the chain configured namespaces App\Entity

problème est la version doctrine/orm:2.8.0

    # composer remove doctrine/orm:2.8.0

    # composer require doctrine/orm:2.8.1


## attention

Option(balise) for the client must always apear first in template !!!

## pdf2docx

https://opensourcelibs.com/lib/pdf2docx

## Export en word

- Export en word, solution native work, but Libs PhpWord so not work, 
    + "Word a rencontré une erreur lors de l'ouverture du fichier"

## Accès

Admin:
    - PSMF: 
        + Monter la version
        + exporter une version archives(Super consultant)
    - template CRUD
    - Variables CRUD
    - User, Client CRUD
Utilsateur or Consultant
    - PSM
        + Voir
        + Exporter
            * draft
            * last version (Documents)

## PHP-CS-Fixer

  $ mkdir --parents tools/php-cs-fixer
  $ composer require --working-dir=tools/php-cs-fixer friendsofphp/php-cs-fixer
  $ tools/php-cs-fixer/vendor/bin/php-cs-fixer fix src/DataFixtures          

## site en maintenance

mise en maintenance cli

    php bin/console app:maintenance:lock on

mise en production cli 

    php bin/console app:maintenance:lock off

et maj public/.htccess comme 

```
<IfModule mod_rewrite.c>
  RewriteEngine on
  RewriteCond %{REQUEST_FILENAME} !-f
  RewriteCond %{REQUEST_FILENAME} !-d
  RewriteRule (.+) index.php?p=$1 [QSA,L]

  RewriteCond %{DOCUMENT_ROOT}/maintenance.html -f
  RewriteCond %{SCRIPT_FILENAME} !maintenance.html
  RewriteRule ^.*$ /maintenance.html [R=503,L]

  RewriteCond %{DOCUMENT_ROOT}/maintenance.html -f
  RewriteRule ^(.*)$ - [env=MAINTENANCE:1]

  Redirect "/docs/" "http://localhost:3000"

  <IfModule mod_headers.c>
    Header set cache-control "max-age=0,must-revalidate,post-check=0,pre-check=0" env=MAINTENANCE
    Header set Expires -1 env=MAINTENANCE
  </IfModule>
</IfModule>

ErrorDocument 503 /maintenance.html
Options -Index
```

## phpunit

    $ php bin/console --env=test doctrine:schema:update --force --no-interaction --no-debug

    $ php vendor/bin/phpunit --group controller

## php-8-0

https://websiteforstudents.com/how-to-migrate-to-php-8-0-on-ubuntu/

    $ dpkg --get-selections | grep -i php
    $ sudo apt-get install software-properties-common
    $ sudo add-apt-repository ppa:ondrej/php
    $ sudo apt update
    $ sudo apt install php8.0-bcmath php8.0-bz2 php8.0-cli php8.0-fpm php8.0-common php8.0-curl php8.0-dev php8.0-gd php8.0-imagick php8.0-imap php8.0-intl php8.0-mbstring php8.0-mysql php8.0-opcache php8.0-readline php8.0-soap php8.0-xml php8.0-xmlrpc php8.0-zip
    $ sudo a2enmod proxy_fcgi setenvif
    $ sudo a2enconf php8.0-fpm
    $ sudo systemctl restart php8.0-fpm.service
    $ sudo apt update
    $ sudo apt install php8.0 libapache2-mod-php8.0
    $ sudo a2dismod php7.4
    $ sudo a2enmod php8.0
    $ sudo systemctl restart apache2.service

## Delete the dangling image

    $ docker rmi $(docker images -qf "dangling=true")