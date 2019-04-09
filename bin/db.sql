CREATE TABLE `user` (
  `id`         INT(11)    NOT NULL AUTO_INCREMENT,
  `email`      CHAR(255)  NOT NULL,
  `login`      CHAR(80)            DEFAULT NULL,
  `password`   CHAR(40)   NOT NULL,
  `active`     TINYINT(1) NOT NULL DEFAULT '0',
  `banned`     TINYINT(1) NOT NULL DEFAULT '0',
  `registered` TIMESTAMP  NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `lastseen`   TIMESTAMP  NOT NULL DEFAULT '0000-00-00 00:00:00',
  `activation` VARCHAR(48)         DEFAULT NULL,
  `info`       BLOB,
  PRIMARY KEY (`id`),
  UNIQUE KEY `email` (`email`) USING HASH,
  UNIQUE KEY `login` (`login`) USING HASH,
  KEY `active` (`active`) USING HASH,
  KEY `banned` (`banned`) USING HASH,
  KEY `activation` (`activation`) USING HASH
)
  ENGINE = InnoDB
  DEFAULT CHARSET = `utf8`;

CREATE TABLE `user_recover` (
  `recover`        CHAR(24)   NOT NULL,
  `user`           INT(11)             DEFAULT NULL,
  `used`           TINYINT(1) NOT NULL DEFAULT '0',
  `date_requested` TIMESTAMP  NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `date_used`      TIMESTAMP  NOT NULL DEFAULT '0000-00-00 00:00:00',
  PRIMARY KEY (`recover`),
  KEY `user` (`user`) USING HASH,
  KEY `date_requested` (`date_requested`) USING BTREE,
  CONSTRAINT `fk_user_recover` FOREIGN KEY (`user`) REFERENCES `user` (`id`)
    ON DELETE CASCADE
)
  ENGINE = InnoDB
  DEFAULT CHARSET = `utf8`;

CREATE TABLE `user_field` (
  `user`  INT(11)  NOT NULL,
  `name`  CHAR(64) NOT NULL,
  `value` CHAR(250) DEFAULT NULL,
  PRIMARY KEY (`user`, `name`),
  KEY `name_value` (`name`, `value`) USING HASH,
  KEY `user` (`user`),
  CONSTRAINT `fk_user_field` FOREIGN KEY (`user`) REFERENCES `user` (`id`)
    ON DELETE CASCADE
)
  ENGINE = InnoDB
  DEFAULT CHARSET = `utf8`;

CREATE TABLE `user_session` (
  `session` CHAR(64)         NOT NULL,
  `user`    INT(11)          NOT NULL,
  `date`    TIMESTAMP        NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `ip`      INT(11) UNSIGNED NOT NULL,
  PRIMARY KEY (`session`),
  KEY `session` (`session`),
  KEY `fk_user_session` (`user`),
  CONSTRAINT `fk_user_session` FOREIGN KEY (`user`) REFERENCES `user` (`id`)
    ON DELETE CASCADE
)
  ENGINE = InnoDB
  DEFAULT CHARSET = `utf8`;
