/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
/**
 * Author:  Ruben
 * Created: 4 sept. 2021
 */


CREATE DATABASE IF NOT EXISTS blog;
USE blog;

CREATE TABLE users(
id                      int(255) auto_increment not null,
username                varchar(50) NOT NULL,
password                varchar(255) NOT NULL,
description             text,
image                   varchar(255),
created_at              datetime DEFAULT NULL,
updated_at              datetime DEFAULT NULL,
remember_token          varchar(255),
CONSTRAINT pk_users PRIMARY KEY(id)
)ENGINE=InnoDb;

CREATE TABLE posts(
id                      int(255) auto_increment not null,
user_id                 int(255) NOT NULL,
title                   varchar(50) NOT NULL,
content                 text,
image                   varchar(255),
created_at              datetime DEFAULT NULL,
updated_at              datetime DEFAULT NULL,
CONSTRAINT pk_posts PRIMARY KEY(id),
CONSTRAINT fk_post_user FOREIGN KEY(user_id) REFERENCES users(id)
)ENGINE=InnoDb;

