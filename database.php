<?php

// The SQL to uninstall this tool
$DATABASE_UNINSTALL = array(
    "drop table if exists {$CFG->dbprefix}vulahelp_users"
);

// The SQL to create the tables if they don't exist
$DATABASE_INSTALL = array(
array( "{$CFG->dbprefix}vulahelp_users",
"create table {$CFG->dbprefix}vulahelp_users (
    id              int(11) unsigned NOT NULL AUTO_INCREMENT,
    link_id         INTEGER NOT NULL,
    list            MEDIUMTEXT NOT NULL,
    created_at      DATETIME NOT NULL,
    created_by      VARCHAR(255) NOT NULL,
    active          TINYINT(1) NOT NULL DEFAULT 0,

    PRIMARY KEY (`id`),
    UNIQUE(link_id, id)
) ENGINE = InnoDB DEFAULT CHARSET=utf8")

);
