DROP TABLE IF EXISTS ".PREFIX."category;
CREATE TABLE ".PREFIX."category (
  catid int(10) unsigned NOT NULL auto_increment,
  name varchar(255) NOT NULL,
  fullName varchar(255) NOT NULL,
  hidden enum('0','1') NOT NULL default '0',
  PRIMARY KEY  (catid)
) TYPE=MyISAM;


DROP TABLE IF EXISTS ".PREFIX."comment;
CREATE TABLE ".PREFIX."comment (
  commentid int(10) unsigned NOT NULL auto_increment,
  content text NOT NULL,
  senderName varchar(255) NOT NULL,
  senderMail varchar(255) default NULL,
  senderURL varchar(255) default NULL,
  entryid int(10) unsigned NOT NULL default '0',
  intime int(10) unsigned NOT NULL default '0',
  notify enum('0','1') NOT NULL default '0',
  sortorder int(10) unsigned NOT NULL default '0',
  level int(10) unsigned NOT NULL default '0',
  parent int(10) unsigned default NULL,
  admin ENUM('0','1') DEFAULT '0',
  deleted ENUM('0','1') DEFAULT '0',
  ip INT default NULL,
  PRIMARY KEY  (commentid),
  KEY intime (intime),
  KEY parent (parent),
  KEY entryid (entryid)
) TYPE=MyISAM;


DROP TABLE IF EXISTS ".PREFIX."entry;
CREATE TABLE ".PREFIX."entry (
  entryid int(10) unsigned NOT NULL auto_increment,
  subject varchar(255) default NULL,
  content text,
  content_p text,
  catid int(10) unsigned NOT NULL default '0',
  intime int(15) unsigned NOT NULL default '0',
  modified timestamp(14) NOT NULL,
  comments enum('0','1') NOT NULL default '0',
  commentcount int(10) unsigned NOT NULL default '0',
  format enum('0','1') NOT NULL default '1',
  image enum('0','1') NOT NULL default '0',
  ljid int(10) unsigned default NULL,
  ljurl int(10) unsigned default NULL,
  keywordcache varchar(255) default NULL,
  urlcache varchar(255) default NULL,
  PRIMARY KEY  (entryid),
  KEY catid (catid),
  KEY intime (intime),
  KEY urlcache (urlcache)
) TYPE=MyISAM;


DROP TABLE IF EXISTS ".PREFIX."keyword;
CREATE TABLE ".PREFIX."keyword (
  wordid int(10) unsigned NOT NULL auto_increment,
  word varchar(255) NOT NULL,
  unixword varchar(255) default NULL,
  PRIMARY KEY  (wordid),
  UNIQUE KEY unixword (unixword),
  UNIQUE KEY word (word)
) TYPE=MyISAM;


DROP TABLE IF EXISTS ".PREFIX."keywords;
CREATE TABLE ".PREFIX."keywords (
  entryid int(10) unsigned NOT NULL default '0',
  wordid int(10) unsigned NOT NULL default '0',
  PRIMARY KEY  (entryid,wordid)
) TYPE=MyISAM;


DROP TABLE IF EXISTS ".PREFIX."pages;
create table ".PREFIX."pages(
    page_id INT UNSIGNED NOT NULL AUTO_INCREMENT,    
    url VARCHAR(255) NOT NULL,
    title VARCHAR(255),
    content TEXT,
    content_p TEXT,    
    intime INT(15) UNSIGNED,
    modified TIMESTAMP,    
    show_in_menu ENUM('f','t') DEFAULT 'f',    
    PRIMARY KEY(page_id),
    UNIQUE(url)
) TYPE=MyISAM;
