-- **********************************************************
-- *                                                        *
-- * IMPORTANT NOTE                                         *
-- *                                                        *
-- * Do not import this file manually but use the TYPOlight *
-- * install tool to create and maintain database tables!   *
-- *                                                        *
-- **********************************************************

-- 
-- Table `tl_content`
-- 

CREATE TABLE `tl_content` (
  `github_user` varchar(255) NOT NULL default '',
  `github_project` varchar(255) NOT NULL default '',
  `github_branch` varchar(255) NOT NULL default '',
  `github_path` varchar(255) NOT NULL default '',
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

