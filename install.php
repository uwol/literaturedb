<?php
/*
This file is part of literaturedb.

literaturedb is free software: you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation, either version 3 of the License, or
(at your option) any later version.

literaturedb is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with literaturedb. If not, see <http://www.gnu.org/licenses/>.
*/

include "lib/masterinclude.php";

echo '<p>Connecting to the database server ...</p>';
LibDb::connect();

echo '<p>Now the database tables are created. Therefore the MySQL parameters have to be set correctly in custom/systemconfig.php</p>';

echo 'Creating table: literaturedb_asso_document_author<br />';
$cmd = "CREATE TABLE literaturedb_asso_document_author (
  document_id bigint(20) unsigned NOT NULL,
  person_id bigint(20) unsigned NOT NULL,
  position tinyint(4) default NULL,
  PRIMARY KEY (document_id,person_id),
  KEY person_id (person_id)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;";
LibDb::query($cmd);

echo 'Creating table: literaturedb_asso_document_editor<br />';
$cmd = "CREATE TABLE literaturedb_asso_document_editor (
  document_id bigint(20) unsigned NOT NULL,
  person_id bigint(20) unsigned NOT NULL,
  position tinyint(4) default NULL,
  PRIMARY KEY (document_id,person_id),
  KEY person_id (person_id)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;";
LibDb::query($cmd);

echo 'Creating table: literaturedb_asso_document_tag<br />';
$cmd = "CREATE TABLE literaturedb_asso_document_tag (
  document_id bigint(20) unsigned NOT NULL,
  tag_id bigint(20) unsigned NOT NULL,
  PRIMARY KEY (document_id,tag_id),
  KEY tag_id (tag_id)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;";
LibDb::query($cmd);

echo 'Creating table: literaturedb_document<br />';
$cmd = "CREATE TABLE literaturedb_document (
  id bigint(20) unsigned NOT NULL auto_increment,
  datetime_created datetime,
  entrytype_id int(11),
  title varchar(255),
  date date default '0000-00-00',
  abstract text,
  address varchar(255),
  booktitle varchar(255),
  chapter varchar(255),
  doi varchar(255),
  ean varchar(255),
  edition tinyint(4),
  institution varchar(255),
  journal_id bigint(20) unsigned,
  number varchar(255),
  organization varchar(255),
  pages varchar(255),
  publisher_id bigint(20) unsigned,
  school varchar(255),
  series varchar(255),
  url varchar(255),
  volume varchar(255),
  note text,
  rating tinyint(4),
  filename varchar(255),
  extension varchar(255),
  filesize varchar(255),
  hash varchar(255),
  user_id bigint(20) unsigned NOT NULL,
  PRIMARY KEY (id),
  KEY user_id (user_id),
  KEY datetime_created (datetime_created)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8;";
LibDb::query($cmd);

echo 'Creating table: literaturedb_journal<br />';
$cmd = "CREATE TABLE literaturedb_journal (
  id bigint(20) unsigned NOT NULL auto_increment,
  name varchar(255) NOT NULL,
  user_id bigint(20) unsigned NOT NULL,
  PRIMARY KEY (id),
  UNIQUE KEY name (name,user_id),
  KEY user_id (user_id)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8;";
LibDb::query($cmd);

echo 'Creating table: literaturedb_person<br />';
$cmd = "CREATE TABLE literaturedb_person (
  id bigint(20) unsigned NOT NULL auto_increment,
  firstname varchar(255),
  prefix varchar(255),
  lastname varchar(255),
  suffix varchar(255),
  user_id bigint(20) unsigned NOT NULL,
  PRIMARY KEY (id),
  UNIQUE KEY firstname (firstname(100),lastname(100),user_id),
  KEY user_id (user_id)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8;";
LibDb::query($cmd);

echo 'Creating table: literaturedb_publisher<br />';
$cmd = "CREATE TABLE literaturedb_publisher (
  id bigint(20) unsigned NOT NULL auto_increment,
  name varchar(255) NOT NULL,
  user_id bigint(20) unsigned NOT NULL,
  PRIMARY KEY (id),
  UNIQUE KEY name (name,user_id),
  KEY user_id (user_id)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8;";
LibDb::query($cmd);

echo 'Creating table: literaturedb_sys_event<br />';
$cmd = "CREATE TABLE literaturedb_sys_event (
  id bigint(20) unsigned NOT NULL auto_increment,
  user_id bigint(20) unsigned NOT NULL,
  date datetime NOT NULL,
  type int(11) NOT NULL,
  ipaddress varchar(255),
  PRIMARY KEY (id),
  KEY user_id (user_id)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8;";
LibDb::query($cmd);

echo 'Creating table: literaturedb_sys_share<br />';
$cmd = "CREATE TABLE literaturedb_sys_share (
  id bigint(20) unsigned NOT NULL auto_increment,
  local_user_id bigint(20) unsigned NOT NULL,
  remote_user_address varchar(255) NOT NULL,
  following tinyint(4) NOT NULL default 1,
  sharing tinyint(4) NOT NULL default 1,
  PRIMARY KEY (id),
  UNIQUE KEY local_user_id (local_user_id,remote_user_address)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8;";
LibDb::query($cmd);

echo 'Creating table: literaturedb_sys_user<br />';
$cmd = "CREATE TABLE literaturedb_sys_user (
  id bigint(20) unsigned NOT NULL auto_increment,
  firstname varchar(255),
  lastname varchar(255),
  username varchar(255),
  emailaddress varchar(255),
  password_hash varchar(255),
  password_salt varchar(255),
  activated tinyint(4) NOT NULL default 0,
  PRIMARY KEY (id),
  UNIQUE KEY username (username),
  UNIQUE KEY email (emailaddress)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8;";
LibDb::query($cmd);

echo 'Creating table: literaturedb_tag<br />';
$cmd = "CREATE TABLE literaturedb_tag (
  id bigint(20) unsigned NOT NULL auto_increment,
  name varchar(255) NOT NULL,
  user_id bigint(20) unsigned NOT NULL,
  PRIMARY KEY (id),
  UNIQUE KEY name (name,user_id),
  KEY user_id (user_id)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8;";
LibDb::query($cmd);
?>