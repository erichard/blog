---
extends: _layouts.post
section: content
title: "Une config Docker performante sous OSX"
description: Docker sous mac c'est bien mais avec des performances décentes c'est mieux.
categories: [docker, mac, osx, performance]
date: 2017-03-23
ghcommentid: "1"
---

Docker sous mac c'est bien mais avec des performances décentes c'est mieux.

<!-- more -->

## Docker Sync

En attendant que le problème de performance des accès disques soit réglé nativement la meilleure solution est d'utiliser [Docker Sync](http://docker-sync.io). Pour l'installer docker-sync avec Homebrew.

    $ gem install docker-sync
    $ brew install unison

## La configuration docker-compose

Je me base sur la configuration Docker que j'ai présenté dans l'article &laquo;&nbsp;[Docker pour les développeurs PHP]({{< relref "docker-pour-les-developpeurs-php.md">}})&nbsp;&raquo;

Donc vous avez un fichier `docker-compose.yml` qui ressemble à ça :

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

Nous n'allons pas modifier cette configuration puisqu'elle fonctionne très bien sur les machines Linux.

Vous aller créer un fichier `docker-compose.mac.yml`.

```
version: '2'

services:
    php:
        volumes:
            - code-sync-<nom_projet>:/var/www/symfony

volumes:
    code-sync-<nom_projet>:
        external: true
```

L'idée principal ici c'est de modifier le volume qui contient le code du projet. Plutôt que de faire un montage direct nous utilisons un volume nommé qui sera utilisé par docker-sync.

Attention a bien remplacé `<nom_projet>` par un nom **unique** sur votre machine. Sinon vous allez avoir des suprises et du mélange de code !

## La configuration docker-sync

Créez un fichier `docker-sync.yml` comme il suit

```
version: '2'

options:
 compose-dev-file-path: 'docker-compose.mac.yml'

syncs:
 code-sync-<nom_projet>:
   src: '.'
   dest: '/var/www/symfony'
   sync_host_ip: '127.0.0.1'
   sync_host_port: 10871
   sync_excludes: ['Gemfile.lock', 'Gemfile', 'config.rb', '.sass-cache/', 'sass/', 'sass-cache/', 'composer.json' , 'bower.json', 'package.json', 'Gruntfile*', 'bower_components/', 'node_modules/', '.gitignore', '.git/', '*.coffee', '*.scss', '*.sass']
   sync_excludes_type: 'Name'
   sync_userid: '1000'
```

Ici aussi il faut modifier `<nom_projet>` avec le même nom que dans le docker compose.

## Lancement du projet

Maintenant vous êtes près pour démarrer votre projet avec la commande suivante :

    $ docker-sync-stack start

Et voilà ! Docker-sync annonce une vitesse de chargement 60x plus rapide donc vous devriez voir la différence dès le premier chargement.

## Et après

Je vous invite à faire un tour sur le [wiki](https://github.com/EugenMayer/docker-sync/wiki) du projet pour voir toutes ses possibilités. Il y a un mode *daemon* que je n'ai pas encore essayé mais qui peut être pratique.

Aussi si vous voulez suivre de près les performances native de Docker for mac je vous conseil de vous abonner sur l'[issue Github](https://github.com/docker/for-mac/issues/77) qui tente de régler le problème.
