CREATE TABLE `comments` (
 `id` int(11) NOT NULL AUTO_INCREMENT,
 `domain_id` int(11) NOT NULL,
 `name` varchar(255) NOT NULL,
 `type` varchar(10) NOT NULL,
 `modified_at` int(11) NOT NULL,
 `account` varchar(40) CHARACTER SET utf8 DEFAULT NULL,
 `comment` text CHARACTER SET utf8 NOT NULL,
 PRIMARY KEY (`id`),
 KEY `comments_name_type_idx` (`name`,`type`),
 KEY `comments_order_idx` (`domain_id`,`modified_at`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

CREATE TABLE `cryptokeys` (
 `id` int(11) NOT NULL AUTO_INCREMENT,
 `domain_id` int(11) NOT NULL,
 `flags` int(11) NOT NULL,
 `active` tinyint(1) DEFAULT NULL,
 `published` tinyint(1) DEFAULT 1,
 `content` text DEFAULT NULL,
 PRIMARY KEY (`id`),
 KEY `domainidindex` (`domain_id`)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=latin1;

CREATE TABLE `domainmetadata` (
 `id` int(11) NOT NULL AUTO_INCREMENT,
 `domain_id` int(11) NOT NULL,
 `kind` varchar(32) DEFAULT NULL,
 `content` text DEFAULT NULL,
 PRIMARY KEY (`id`),
 KEY `domainmetadata_idx` (`domain_id`,`kind`)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=latin1;

CREATE TABLE `domains` (
 `id` int(11) NOT NULL AUTO_INCREMENT,
 `name` varchar(255) NOT NULL,
 `master` varchar(128) DEFAULT NULL,
 `last_check` int(11) DEFAULT NULL,
 `type` varchar(6) NOT NULL,
 `notified_serial` int(10) unsigned DEFAULT NULL,
 `account` varchar(40) CHARACTER SET utf8 DEFAULT NULL,
 `uuid` varchar(64) DEFAULT NULL,
 `handshake` tinyint(1) DEFAULT NULL,
 `expiration` int(11) DEFAULT NULL,
 `renew` int(11) DEFAULT NULL,
 `registrar` varchar(16) DEFAULT NULL,
 PRIMARY KEY (`id`),
 UNIQUE KEY `name_index` (`name`),
 UNIQUE KEY `uuid` (`uuid`),
 KEY `account` (`account`)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=latin1;

CREATE TABLE `records` (
 `id` bigint(20) NOT NULL AUTO_INCREMENT,
 `domain_id` int(11) DEFAULT NULL,
 `name` varchar(255) DEFAULT NULL,
 `type` varchar(10) DEFAULT NULL,
 `content` varchar(64000) DEFAULT NULL,
 `ttl` int(11) DEFAULT NULL,
 `prio` int(11) DEFAULT NULL,
 `disabled` tinyint(1) DEFAULT 0,
 `ordername` varchar(255) CHARACTER SET latin1 COLLATE latin1_bin DEFAULT NULL,
 `auth` tinyint(1) DEFAULT 1,
 `uuid` varchar(64) DEFAULT NULL,
 `system` tinyint(1) DEFAULT 0,
 PRIMARY KEY (`id`),
 UNIQUE KEY `uuid` (`uuid`),
 KEY `nametype_index` (`name`,`type`),
 KEY `domain_id` (`domain_id`),
 KEY `ordername` (`ordername`)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=latin1;

CREATE TABLE `supermasters` (
 `ip` varchar(64) NOT NULL,
 `nameserver` varchar(255) NOT NULL,
 `account` varchar(40) CHARACTER SET utf8 NOT NULL,
 PRIMARY KEY (`ip`,`nameserver`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

CREATE TABLE `tsigkeys` (
 `id` int(11) NOT NULL AUTO_INCREMENT,
 `name` varchar(255) DEFAULT NULL,
 `algorithm` varchar(50) DEFAULT NULL,
 `secret` varchar(255) DEFAULT NULL,
 PRIMARY KEY (`id`),
 UNIQUE KEY `namealgoindex` (`name`,`algorithm`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
