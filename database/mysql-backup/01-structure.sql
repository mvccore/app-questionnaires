SET NAMES utf8;
SET foreign_key_checks = 0;
SET sql_mode = 'NO_AUTO_VALUE_ON_ZERO';

CREATE DATABASE `Questionnaire` COLLATE 'utf8_general_ci';

USE `Questionnaire`;

DROP TABLE IF EXISTS `Answers`;
CREATE TABLE `Answers` (
  `IdQuestionnaire` int(3) NOT NULL,
  `IdQuestion` int(4) NOT NULL,
  `IdPerson` int(11) NOT NULL,
  `Option` int(11) DEFAULT NULL,
  `Boolean` tinyint(1) DEFAULT NULL,
  `Integer` int(11) DEFAULT NULL,
  `Float` float DEFAULT NULL,
  `Varchar` varchar(256) DEFAULT NULL,
  `Text` text,
  KEY `id_questionnaire` (`IdQuestionnaire`),
  KEY `id_question` (`IdQuestion`),
  KEY `id_person` (`IdPerson`),
  KEY `option` (`Option`),
  KEY `boolean` (`Boolean`),
  KEY `integer` (`Integer`),
  KEY `float` (`Float`),
  KEY `varchar` (`Varchar`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS `Executed`;
CREATE TABLE `Executed` (
  `IdPerson` int(11) NOT NULL,
  `IdQuestionnaire` int(11) NOT NULL,
  KEY `id_person` (`IdPerson`),
  KEY `id_questionnaire` (`IdQuestionnaire`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS `Persons`;
CREATE TABLE `Persons` (
  `Id` int(11) NOT NULL AUTO_INCREMENT,
  `Created` datetime NOT NULL,
  `Sex` char(1) NOT NULL,
  `Age` int(3) NOT NULL,
  `Education` varchar(50) NOT NULL,
  `Job` varchar(50) NOT NULL,
  PRIMARY KEY (`Id`),
  KEY `sex` (`Sex`),
  KEY `age` (`Age`),
  KEY `created` (`Created`),
  KEY `job` (`Job`),
  KEY `highest_education` (`Education`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

