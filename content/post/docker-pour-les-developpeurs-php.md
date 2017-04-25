---
title: "Docker pour les développeurs PHP"
tags: [docker, php]
date: 2017-02-24
ghcommentid: "2"
---

Un retour d'expérience et un guide de mise en place de Docker pour les développeurs PHP mais pas que&hellip;

<!--more-->

> *TL;DR*: Pour les plus pressés les configurations complètes sont disponibles dans le dépôt github [le-phare/docker-php-dev-env](https://github.com/le-phare/docker-php-dev-env).

## Retour en 2015

Un peu de context pour ceux qui le souhaitent sinon vous pouvez directement passez à la [partie technique](#tech).

Je suis développeur PHP/Symfony à l'agence [Le Phare](http://www.lephare.com/) depuis 2010.

En 2015, notre équipe se compose de huit développeurs et deux intégrateurs. Nous travaillons sur plusieurs nouveaux projets client simultanément et assurons la maintenance évolutive et corrective des projets terminés.

A cette période, nous avions une vingtaine de projets actifs en maintenance et une petite dizaine de projets en cours de réalisation. Environ une trentaine de projets actifs. Chaque développeur garde la charge des projets dont il est le lead developer. Dans les semaines les plus chargés, on peut donc intervenir sur 5 ou 6 projets dans la semaine de travail.

Les projets sont développés avec Symfony et notre couche Faros avec des prérequis techniques évoluant selon les versions stables de PHP, MySQL et autres services. Parmi la trentaine de projets réalisés avec Symfony, nous avons plusieurs architectures techniques différentes.

 * Debian 7, PHP 5.4, Apache 2.2, Mysql 5.5
 * Debian 8, PHP 5.6, Apache 2.4, Mysql 5.5
 * Debian 8, PHP 5.6, Apache 2.4, Postgresql 9.4
 * Debian 8, PHP 5.6, Apache 2.4, Postgresql 9.5

Jusqu'à présent, l'installation, la configuration et la maintenance des postes de dev étaient laissées à la charge des développeurs. Nous avions alors toutes sortes d'installations et d'architectures. Des MAMP, apache/mod-php, apache/php-fpm, des serveurs PHP et même des geeks compilant leur propre PHP !

Evidemment avec une telle organisation nous avons connu quelques plantages en production simplement pour avoir utilisé des fonctionnalités apparues avec PHP 5.5 alors que le serveur de production était en 5.4&hellip; Pour éviter ces problèmes nous avons chercher un moyen pour développer dans un environnement le plus proche des serveurs de production pour chaque projet. Mais comment faire pour installer plusieurs versions de PHP, plusieurs serveurs web et plusieurs bases de données sur la même machine ?

Après avoir essayé Vagrant puis PuPHPet nous avons finalement choisi d'utiliser Docker. Voici un petit guide de survie de Docker en environnement de dev.

## Découvrir Docker

Je ne vais pas me lancer dans une longue présentation de Docker. D'autres ont déjà fait ça très bien. Vous trouverez plusieurs articles très intéressant (et bien d'autres choses) sur le dépôt [veggiemonk/awesome-docker](https://github.com/veggiemonk/awesome-docker#where-to-start-).

## <a name="tech"></a> Installer les outils

Premièrement, il faut installer Docker. Je vous renvoie pour cela sur la [documentation du projet](https://docs.docker.com/engine/installation/), qui explique très bien cette partie. Ensuite vous aurez besoin de `docker-compose`. Là encore, la [documentation](https://docs.docker.com/compose/) fait l'affaire.

## Une stack générique et des containers projets

Nous avons mis au point une architecture qui s'articule autour d'une stack générique qui fournit les containers de base de données, un proxy HTTP nginx, un serveur memcached et des outils pratiques comme adminer et maildev. Cette stack est nécessaire pour travailler sur n'importe quel projet.

Ensuite chaque projet fournit ses propres containers, Apache et PHP au minimum, mais éventuellement d'autres services comme MongoDB ou ElasticSearch. Ces containers projets vont communiquer avec les containers génériques et fournir un environnement parfaitement fonctionnel.

## La stack générique

Nous utilisons la version 2 des fichiers YAML de docker-compose (disponible à partir de docker 1.8 et docker-compose 1.5). Voici un extrait de la stack générique.

```
version: '2'

services:
    proxy:
        image: jwilder/nginx-proxy
        volumes:
            - /var/run/docker.sock:/tmp/docker.sock:ro
            - ./conf/ssl/certs:/etc/nginx/certs:ro
            - ./conf/proxy/default.conf:/etc/nginx/proxy.conf
        networks:
            - web
        ports:
            - '80:80'
            - '443:443'

    adminer:
        image: dehy/adminer
        environment:
            VIRTUAL_HOST: adminer.docker
        networks:
            - web
            - database

    pgsql_95:
        image: postgres:9.5
        networks:
            - database
        volumes:
            - pgsql_95_data:/var/lib/postgres/data
        environment:
            PGDATA: /var/lib/postgres/data

    maildev:
        image: djfarrelly/maildev
        networks:
            - web
            - mail
        environment:
            VIRTUAL_HOST: maildev.docker

networks:
    web:
        driver: bridge
    mail:
        driver: bridge
    database:
        driver: bridge

volumes:
    pgsql_95_data:
        driver: local
```

Je n'ai mis qu'un seul container de base de données pour simplifier l'exemple.

### Les containers

#### jwilder/nginx-proxy

Ce container est le seul qui écoute sur les ports 80 et 443 de la machine hôte. Il reçoit toutes les requêtes et les transmets au container projet (apache) en fonction de la variable d'environnement `VIRTUAL_HOST` (voir [stack projet](#stack-projet)).

Dans notre configuration nous avons également un certificat SSL auto-signé qui nous permet de configurer n'importe quel projet en HTTPS (voir [stack projet](#stack-projet)).

#### dehy/adminer

Adminer est une interface web pour la gestion des bases de données. Une sorte de phpMyAdmin compatible avec plusieurs SGBD (Postgresql, Mysql, ElasticSearch, MongoDB).

L'image `dehy/adminer` est la plus légère que nous ayons trouvée sur docker. Elle est basée sur [Alpine Linux](https://alpinelinux.org/).

#### postgres

Nos containers de base de données Postgres basés sur les images officielles.

#### djfarrelly/maildev

Maildev est un serveur SMTP qui affiche les mails envoyés dans une interface web à la gmail. Il peut être configuré pour autoriser le relai vers un autre SMTP.

### Les réseaux

La stack générique définit quatre réseaux docker. Tous les containers du même réseau sont capables de communiquer entre eux.

Le réseau **web** expose tous les containers qui exposent une interface web.

Le réseau **database** expose tous les containers de base de données.

Le réseau **mail** expose le serveur SMTP.

## <a name="stack-projet"></a> La stack projet

Voici un exemple de stack projet. Le plus simple est de stocker le fichier `docker-compose.yml` à la racine de vos projets.

```
version:  '2'

services:
    php:
        image: lephare/php:7.0
        networks:
            - default
            - database
            - mail
        volumes:
            - .:/var/www/symfony

    web:
        image: lephare/apache:2.4
        networks:
            - default
            - web
        volumes_from:
            - php
        volumes:
            - ./var/logs/apache:/var/log/apache2
        environment:
            VIRTUAL_HOST: example.docker
            CERT_NAME: generic
            HTTPS_METHOD: noredirect

networks:
    web:
        external:
            name: dev_web
    database:
        external:
            name: dev_database
    mail:
        external:
            name: dev_mail

```

### Les images utilisées

#### lephare/php

Une image php basée sur les images php-fpm officielles et pré-paramétrée pour notre configuration. Le container monte le répertoire du `docker-compose.yml` du projet en tant que volume dans `/var/www/symfony`.

#### lephare/apache

Une image docker basée sur les images Apache officielles, pré-paramétrée pour notre configuration. Ce container utilise les volumes définis par le container php puis ajoute un volume pour le stockage des logs Apache.

Les variables d'environnements :

 * `VIRTUAL_HOST` permet de donner la liste des domaines qui pointent sur ce container.
 * `CERT_NAME` permet d'indiquer le nom du certificat SSL à utiliser. Le certificat `generic` est stocké dans la stack générique.
 * `HTTPS_METHOD` permet de définir le comportement pour les redirections HTTPS. Par défaut le proxy va rediriger automatiquement le HTTP vers HTTPS. `noredirect` permet de désactiver ce comportement pour s'assurer d'avoir prévu la redirection dans le `.htaccess`.

### Les réseaux

Les containers doivent rejoindre les réseaux définis dans la stack générique. Pour cela nous utilisons des réseaux de type *external*. Docker-compose préfixe les noms de réseaux et de container avec le nom du répertoire du `docker-compose.yml`. Dans notre cas la stack générique est contenue dans un dossier `dev` car nous avons également d'autres stacks pour d'autres usages.

Le réseau **default** sert à faire communiquer les containers php et Apache, il ne contient que ce projet.

## Conclusion

Docker permet une isolation complète des composants d'une application web. Les concepts peuvent paraîtres complexes au premier coup d'oeil (et ils le sont) mais cela vaut le coup de persévérer et de maîtriser l'outil. Deux ans après nos premiers pas, il n'a jamais été aussi facile de monter un environnement de développement au Phare qui soit aussi proche des conditions de production.

## Et après ?

L'utilisation de docker résout un problème majeur mais comme toute solution informatique qui se respècte elle vient accompagnée de son lot de problèmes : comment utiliser les outils en ligne de commande d'un projet ? Composer, Symfony et Gulp par exemple. Comment profiler avec Blackfire ?

Docker apporte aussi son lot de nouveaux défis. Pourquoi ne pas utiliser cette architecture en production ? Comment configurer une platforme de CI ?

La version 1.12 apporte un nouveau système de stack, peut être que nous pourrons directement mettre nos stacks dans un registre public et simplifier encore notre système.
