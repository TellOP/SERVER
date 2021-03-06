-- Copyright © 2016 University of Murcia
--
-- Licensed under the Apache License, Version 2.0 (the "License");
-- you may not use this file except in compliance with the License.
-- You may obtain a copy of the License at
--     http://www.apache.org/licenses/LICENSE-2.0
--
-- Unless required by applicable law or agreed to in writing, software
-- distributed under the License is distributed on an "AS IS" BASIS,
-- WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
-- See the License for the specific language governing permissions and
-- limitations under the License.
--
-- SQL schema for the Tell-OP Web application
DROP TABLE IF EXISTS `oauth_scopes`;
DROP TABLE IF EXISTS `oauth_refresh_tokens`;
DROP TABLE IF EXISTS `oauth_jwt`;
DROP TABLE IF EXISTS `oauth_public_keys`;
DROP TABLE IF EXISTS `oauth_jti`;
DROP TABLE IF EXISTS `oauth_authorization_codes`;
DROP TABLE IF EXISTS `oauth_access_tokens`;
DROP TABLE IF EXISTS `oauth_clients`;
DROP TABLE IF EXISTS `useractivity_dictionarysearch`;
DROP TABLE IF EXISTS `useractivity_essay`;
DROP TABLE IF EXISTS `useractivities`;
DROP TABLE IF EXISTS `activity_essay`;
DROP TABLE IF EXISTS `activity`;
DROP TABLE IF EXISTS `activity_type`;
DROP TABLE IF EXISTS `tips`;
DROP TABLE IF EXISTS `languages`;
DROP TABLE IF EXISTS `ipblock`;
DROP TABLE IF EXISTS `users`;

CREATE TABLE `users` (
    `email` VARCHAR(254) NOT NULL COMMENT 'The e-mail address (user ID) of this user.',
    `password` VARCHAR(255) NOT NULL COMMENT 'The salted and hashed password.',
    `locale` VARCHAR(20) NOT NULL DEFAULT 'en-US' COMMENT 'The locale name of the language that should be used for localization.',
    `title` VARCHAR(50) COMMENT 'The title of the user.',
    `displayname` VARCHAR(250) COMMENT 'The name and surname of the user.',
    `secrettoken` VARCHAR(100) COMMENT 'The secret token used to approve some sensitive operations.',
    `secrettokenexpire` DATETIME COMMENT 'The expiration time of the secret token.',
    `newemail` VARCHAR(254) COMMENT 'If the user is changing his/her e-mail address, this field contains the new e-mail address that was approved.',
    `accountstatus` INT(3) NOT NULL DEFAULT 0 COMMENT 'Account status: 0=newly registered and waiting for confirmation, 1=normal account, 2=locked account, 3=changing e-mail address, waiting for confirmation, 4=has requested a password reset.',
    `remembermetoken` VARCHAR(100) COMMENT 'A secret Remember Me token.',
    `scope` VARCHAR(4000) COMMENT 'OAuth 2.0 scopes.',
    `languagelevel` ENUM('A1', 'A2', 'B1', 'B2', 'C1', 'C2') NOT NULL DEFAULT 'B1' COMMENT 'Language level of the user.',
    PRIMARY KEY (`email`)
) DEFAULT CHARSET=utf8;
INSERT INTO `users` (email, password, title, displayname, accountstatus) VALUES ('admin@tellop.eu', '$2y$10$u1uJNMlVGecpwVcefE.BVuoX/jdt.sb9SgF5GgK8DPLywX.MA3Q2K', 'Dr', 'TellOP Administrator', 1);

CREATE TABLE `ipblock` (
    `ip` VARCHAR(45) NOT NULL COMMENT 'The IP address of the offender.',
    `tries` INT(1) NOT NULL DEFAULT 1 COMMENT 'The number of login tries.',
    `expire` DATETIME COMMENT 'IP block expiration.'
) DEFAULT CHARSET=utf8;

CREATE TABLE `languages` (
    `LCID` VARCHAR(5) NOT NULL COMMENT 'The ISO 639-1 code of the language.',
    `locale` VARCHAR(100) NOT NULL COMMENT 'The human readable name of the language.',
    PRIMARY KEY (`LCID`)
) DEFAULT CHARSET=utf8;
INSERT INTO `languages` (`LCID`, `locale`) VALUES ('de-DE', 'German - Germany'), ('en-GB', 'English - Great Britain'), ('en-US', 'English - United States'), ('es-ES', 'Spanish - Spain'), ('fr-FR', 'French - France'), ('it-IT', 'Italian - Italy');

CREATE TABLE `tips` (
    `id` INT(11) NOT NULL AUTO_INCREMENT COMMENT 'The unique ID of the tip.',
    `LCID` VARCHAR(5) NOT NULL COMMENT 'The ISO 639-1 code of the language the tip applies to.',
    `languagelevel` ENUM('A1', 'A2', 'B1', 'B2', 'C1', 'C2') NOT NULL DEFAULT 'B1' COMMENT 'The language level the tip applies to.',
    `text` TEXT NOT NULL COMMENT 'The text of the tip.',
    PRIMARY KEY (`id`),
    FOREIGN KEY (`LCID`) REFERENCES `languages` (`LCID`) ON UPDATE CASCADE
) DEFAULT CHARSET=utf8;
INSERT INTO `tips` (`id`, `LCID`, `languagelevel`, `text`) VALUES
(1, 'de-DE', 'A1', 'Students can learn 2000 words in five years of study (Barnard, 1971). You can learn a much wider range of words if you pay attention to how words combine. (Cobb, T. From concord to lexicon: Development and test of a corpus-based lexical tutor. Montreal: Concordia University, PhD dissertation.)'),
(2, 'de-DE', 'A2', 'Students can learn 2000 words in five years of study (Barnard, 1971). You can learn a much wider range of words if you pay attention to how words combine. (Cobb, T. From concord to lexicon: Development and test of a corpus-based lexical tutor. Montreal: Concordia University, PhD dissertation.)'),
(3, 'de-DE', 'B1', 'Students can learn 2000 words in five years of study (Barnard, 1971). You can learn a much wider range of words if you pay attention to how words combine. (Cobb, T. From concord to lexicon: Development and test of a corpus-based lexical tutor. Montreal: Concordia University, PhD dissertation.)'),
(4, 'de-DE', 'B2', 'Students can learn 2000 words in five years of study (Barnard, 1971). You can learn a much wider range of words if you pay attention to how words combine. (Cobb, T. From concord to lexicon: Development and test of a corpus-based lexical tutor. Montreal: Concordia University, PhD dissertation.)'),
(5, 'de-DE', 'C1', 'Students can learn 2000 words in five years of study (Barnard, 1971). You can learn a much wider range of words if you pay attention to how words combine. (Cobb, T. From concord to lexicon: Development and test of a corpus-based lexical tutor. Montreal: Concordia University, PhD dissertation.)'),
(6, 'de-DE', 'C2', 'Students can learn 2000 words in five years of study (Barnard, 1971). You can learn a much wider range of words if you pay attention to how words combine. (Cobb, T. From concord to lexicon: Development and test of a corpus-based lexical tutor. Montreal: Concordia University, PhD dissertation.)'),
(7, 'en-GB', 'A1', 'Students can learn 2000 words in five years of study (Barnard, 1971). You can learn a much wider range of words if you pay attention to how words combine. (Cobb, T. From concord to lexicon: Development and test of a corpus-based lexical tutor. Montreal: Concordia University, PhD dissertation.)'),
(8, 'en-GB', 'A2', 'Students can learn 2000 words in five years of study (Barnard, 1971). You can learn a much wider range of words if you pay attention to how words combine. (Cobb, T. From concord to lexicon: Development and test of a corpus-based lexical tutor. Montreal: Concordia University, PhD dissertation.)'),
(9, 'en-GB', 'B1', 'Students can learn 2000 words in five years of study (Barnard, 1971). You can learn a much wider range of words if you pay attention to how words combine. (Cobb, T. From concord to lexicon: Development and test of a corpus-based lexical tutor. Montreal: Concordia University, PhD dissertation.)'),
(10, 'en-GB', 'B2', 'Students can learn 2000 words in five years of study (Barnard, 1971). You can learn a much wider range of words if you pay attention to how words combine. (Cobb, T. From concord to lexicon: Development and test of a corpus-based lexical tutor. Montreal: Concordia University, PhD dissertation.)'),
(11, 'en-GB', 'C1', 'Students can learn 2000 words in five years of study (Barnard, 1971). You can learn a much wider range of words if you pay attention to how words combine. (Cobb, T. From concord to lexicon: Development and test of a corpus-based lexical tutor. Montreal: Concordia University, PhD dissertation.)'),
(12, 'en-GB', 'C2', 'Students can learn 2000 words in five years of study (Barnard, 1971). You can learn a much wider range of words if you pay attention to how words combine. (Cobb, T. From concord to lexicon: Development and test of a corpus-based lexical tutor. Montreal: Concordia University, PhD dissertation.)'),
(13, 'en-US', 'A1', 'Students can learn 2000 words in five years of study (Barnard, 1971). You can learn a much wider range of words if you pay attention to how words combine. (Cobb, T. From concord to lexicon: Development and test of a corpus-based lexical tutor. Montreal: Concordia University, PhD dissertation.)'),
(14, 'en-US', 'A2', 'Students can learn 2000 words in five years of study (Barnard, 1971). You can learn a much wider range of words if you pay attention to how words combine. (Cobb, T. From concord to lexicon: Development and test of a corpus-based lexical tutor. Montreal: Concordia University, PhD dissertation.)'),
(15, 'en-US', 'B1', 'Students can learn 2000 words in five years of study (Barnard, 1971). You can learn a much wider range of words if you pay attention to how words combine. (Cobb, T. From concord to lexicon: Development and test of a corpus-based lexical tutor. Montreal: Concordia University, PhD dissertation.)'),
(16, 'en-US', 'B2', 'Students can learn 2000 words in five years of study (Barnard, 1971). You can learn a much wider range of words if you pay attention to how words combine. (Cobb, T. From concord to lexicon: Development and test of a corpus-based lexical tutor. Montreal: Concordia University, PhD dissertation.)'),
(17, 'en-US', 'C1', 'Students can learn 2000 words in five years of study (Barnard, 1971). You can learn a much wider range of words if you pay attention to how words combine. (Cobb, T. From concord to lexicon: Development and test of a corpus-based lexical tutor. Montreal: Concordia University, PhD dissertation.)'),
(18, 'en-US', 'C2', 'Students can learn 2000 words in five years of study (Barnard, 1971). You can learn a much wider range of words if you pay attention to how words combine. (Cobb, T. From concord to lexicon: Development and test of a corpus-based lexical tutor. Montreal: Concordia University, PhD dissertation.)'),
(19, 'es-ES', 'A1', 'Students can learn 2000 words in five years of study (Barnard, 1971). You can learn a much wider range of words if you pay attention to how words combine. (Cobb, T. From concord to lexicon: Development and test of a corpus-based lexical tutor. Montreal: Concordia University, PhD dissertation.)'),
(20, 'es-ES', 'A2', 'Students can learn 2000 words in five years of study (Barnard, 1971). You can learn a much wider range of words if you pay attention to how words combine. (Cobb, T. From concord to lexicon: Development and test of a corpus-based lexical tutor. Montreal: Concordia University, PhD dissertation.)'),
(21, 'es-ES', 'B1', 'Students can learn 2000 words in five years of study (Barnard, 1971). You can learn a much wider range of words if you pay attention to how words combine. (Cobb, T. From concord to lexicon: Development and test of a corpus-based lexical tutor. Montreal: Concordia University, PhD dissertation.)'),
(22, 'es-ES', 'B2', 'Students can learn 2000 words in five years of study (Barnard, 1971). You can learn a much wider range of words if you pay attention to how words combine. (Cobb, T. From concord to lexicon: Development and test of a corpus-based lexical tutor. Montreal: Concordia University, PhD dissertation.)'),
(23, 'es-ES', 'C1', 'Students can learn 2000 words in five years of study (Barnard, 1971). You can learn a much wider range of words if you pay attention to how words combine. (Cobb, T. From concord to lexicon: Development and test of a corpus-based lexical tutor. Montreal: Concordia University, PhD dissertation.)'),
(24, 'es-ES', 'C2', 'Students can learn 2000 words in five years of study (Barnard, 1971). You can learn a much wider range of words if you pay attention to how words combine. (Cobb, T. From concord to lexicon: Development and test of a corpus-based lexical tutor. Montreal: Concordia University, PhD dissertation.)'),
(25, 'fr-FR', 'A1', 'Students can learn 2000 words in five years of study (Barnard, 1971). You can learn a much wider range of words if you pay attention to how words combine. (Cobb, T. From concord to lexicon: Development and test of a corpus-based lexical tutor. Montreal: Concordia University, PhD dissertation.)'),
(26, 'fr-FR', 'A2', 'Students can learn 2000 words in five years of study (Barnard, 1971). You can learn a much wider range of words if you pay attention to how words combine. (Cobb, T. From concord to lexicon: Development and test of a corpus-based lexical tutor. Montreal: Concordia University, PhD dissertation.)'),
(27, 'fr-FR', 'B1', 'Students can learn 2000 words in five years of study (Barnard, 1971). You can learn a much wider range of words if you pay attention to how words combine. (Cobb, T. From concord to lexicon: Development and test of a corpus-based lexical tutor. Montreal: Concordia University, PhD dissertation.)'),
(28, 'fr-FR', 'B2', 'Students can learn 2000 words in five years of study (Barnard, 1971). You can learn a much wider range of words if you pay attention to how words combine. (Cobb, T. From concord to lexicon: Development and test of a corpus-based lexical tutor. Montreal: Concordia University, PhD dissertation.)'),
(29, 'fr-FR', 'C1', 'Students can learn 2000 words in five years of study (Barnard, 1971). You can learn a much wider range of words if you pay attention to how words combine. (Cobb, T. From concord to lexicon: Development and test of a corpus-based lexical tutor. Montreal: Concordia University, PhD dissertation.)'),
(30, 'fr-FR', 'C2', 'Students can learn 2000 words in five years of study (Barnard, 1971). You can learn a much wider range of words if you pay attention to how words combine. (Cobb, T. From concord to lexicon: Development and test of a corpus-based lexical tutor. Montreal: Concordia University, PhD dissertation.)'),
(31, 'it-IT', 'A1', 'Students can learn 2000 words in five years of study (Barnard, 1971). You can learn a much wider range of words if you pay attention to how words combine. (Cobb, T. From concord to lexicon: Development and test of a corpus-based lexical tutor. Montreal: Concordia University, PhD dissertation.)'),
(32, 'it-IT', 'A2', 'Students can learn 2000 words in five years of study (Barnard, 1971). You can learn a much wider range of words if you pay attention to how words combine. (Cobb, T. From concord to lexicon: Development and test of a corpus-based lexical tutor. Montreal: Concordia University, PhD dissertation.)'),
(33, 'it-IT', 'B1', 'Students can learn 2000 words in five years of study (Barnard, 1971). You can learn a much wider range of words if you pay attention to how words combine. (Cobb, T. From concord to lexicon: Development and test of a corpus-based lexical tutor. Montreal: Concordia University, PhD dissertation.)'),
(34, 'it-IT', 'B2', 'Students can learn 2000 words in five years of study (Barnard, 1971). You can learn a much wider range of words if you pay attention to how words combine. (Cobb, T. From concord to lexicon: Development and test of a corpus-based lexical tutor. Montreal: Concordia University, PhD dissertation.)'),
(35, 'it-IT', 'C1', 'Students can learn 2000 words in five years of study (Barnard, 1971). You can learn a much wider range of words if you pay attention to how words combine. (Cobb, T. From concord to lexicon: Development and test of a corpus-based lexical tutor. Montreal: Concordia University, PhD dissertation.)'),
(36, 'it-IT', 'C2', 'Students can learn 2000 words in five years of study (Barnard, 1971). You can learn a much wider range of words if you pay attention to how words combine. (Cobb, T. From concord to lexicon: Development and test of a corpus-based lexical tutor. Montreal: Concordia University, PhD dissertation.)'),
(37, 'de-DE', 'A1', 'Learning words by definitions has little effect on comprehending the words in novel texts. You need to learn new word in the context in which they are used. (Cobb, T. From concord to lexicon: Development and test of a corpus-based lexical tutor. Montreal: Concordia University, PhD dissertation.)'),
(38, 'de-DE', 'A2', 'Learning words by definitions has little effect on comprehending the words in novel texts. You need to learn new word in the context in which they are used. (Cobb, T. From concord to lexicon: Development and test of a corpus-based lexical tutor. Montreal: Concordia University, PhD dissertation.)'),
(39, 'de-DE', 'B1', 'Learning words by definitions has little effect on comprehending the words in novel texts. You need to learn new word in the context in which they are used. (Cobb, T. From concord to lexicon: Development and test of a corpus-based lexical tutor. Montreal: Concordia University, PhD dissertation.)'),
(40, 'de-DE', 'B2', 'Learning words by definitions has little effect on comprehending the words in novel texts. You need to learn new word in the context in which they are used. (Cobb, T. From concord to lexicon: Development and test of a corpus-based lexical tutor. Montreal: Concordia University, PhD dissertation.)'),
(41, 'de-DE', 'C1', 'Learning words by definitions has little effect on comprehending the words in novel texts. You need to learn new word in the context in which they are used. (Cobb, T. From concord to lexicon: Development and test of a corpus-based lexical tutor. Montreal: Concordia University, PhD dissertation.)'),
(42, 'de-DE', 'C2', 'Learning words by definitions has little effect on comprehending the words in novel texts. You need to learn new word in the context in which they are used. (Cobb, T. From concord to lexicon: Development and test of a corpus-based lexical tutor. Montreal: Concordia University, PhD dissertation.)'),
(43, 'en-GB', 'A1', 'Learning words by definitions has little effect on comprehending the words in novel texts. You need to learn new word in the context in which they are used. (Cobb, T. From concord to lexicon: Development and test of a corpus-based lexical tutor. Montreal: Concordia University, PhD dissertation.)'),
(44, 'en-GB', 'A2', 'Learning words by definitions has little effect on comprehending the words in novel texts. You need to learn new word in the context in which they are used. (Cobb, T. From concord to lexicon: Development and test of a corpus-based lexical tutor. Montreal: Concordia University, PhD dissertation.)'),
(45, 'en-GB', 'B1', 'Learning words by definitions has little effect on comprehending the words in novel texts. You need to learn new word in the context in which they are used. (Cobb, T. From concord to lexicon: Development and test of a corpus-based lexical tutor. Montreal: Concordia University, PhD dissertation.)'),
(46, 'en-GB', 'B2', 'Learning words by definitions has little effect on comprehending the words in novel texts. You need to learn new word in the context in which they are used. (Cobb, T. From concord to lexicon: Development and test of a corpus-based lexical tutor. Montreal: Concordia University, PhD dissertation.)'),
(47, 'en-GB', 'C1', 'Learning words by definitions has little effect on comprehending the words in novel texts. You need to learn new word in the context in which they are used. (Cobb, T. From concord to lexicon: Development and test of a corpus-based lexical tutor. Montreal: Concordia University, PhD dissertation.)'),
(48, 'en-GB', 'C2', 'Learning words by definitions has little effect on comprehending the words in novel texts. You need to learn new word in the context in which they are used. (Cobb, T. From concord to lexicon: Development and test of a corpus-based lexical tutor. Montreal: Concordia University, PhD dissertation.)'),
(49, 'en-US', 'A1', 'Learning words by definitions has little effect on comprehending the words in novel texts. You need to learn new word in the context in which they are used. (Cobb, T. From concord to lexicon: Development and test of a corpus-based lexical tutor. Montreal: Concordia University, PhD dissertation.)'),
(50, 'en-US', 'A2', 'Learning words by definitions has little effect on comprehending the words in novel texts. You need to learn new word in the context in which they are used. (Cobb, T. From concord to lexicon: Development and test of a corpus-based lexical tutor. Montreal: Concordia University, PhD dissertation.)'),
(51, 'en-US', 'B1', 'Learning words by definitions has little effect on comprehending the words in novel texts. You need to learn new word in the context in which they are used. (Cobb, T. From concord to lexicon: Development and test of a corpus-based lexical tutor. Montreal: Concordia University, PhD dissertation.)'),
(52, 'en-US', 'B2', 'Learning words by definitions has little effect on comprehending the words in novel texts. You need to learn new word in the context in which they are used. (Cobb, T. From concord to lexicon: Development and test of a corpus-based lexical tutor. Montreal: Concordia University, PhD dissertation.)'),
(53, 'en-US', 'C1', 'Learning words by definitions has little effect on comprehending the words in novel texts. You need to learn new word in the context in which they are used. (Cobb, T. From concord to lexicon: Development and test of a corpus-based lexical tutor. Montreal: Concordia University, PhD dissertation.)'),
(54, 'en-US', 'C2', 'Learning words by definitions has little effect on comprehending the words in novel texts. You need to learn new word in the context in which they are used. (Cobb, T. From concord to lexicon: Development and test of a corpus-based lexical tutor. Montreal: Concordia University, PhD dissertation.)'),
(55, 'es-ES', 'A1', 'Learning words by definitions has little effect on comprehending the words in novel texts. You need to learn new word in the context in which they are used. (Cobb, T. From concord to lexicon: Development and test of a corpus-based lexical tutor. Montreal: Concordia University, PhD dissertation.)'),
(56, 'es-ES', 'A2', 'Learning words by definitions has little effect on comprehending the words in novel texts. You need to learn new word in the context in which they are used. (Cobb, T. From concord to lexicon: Development and test of a corpus-based lexical tutor. Montreal: Concordia University, PhD dissertation.)'),
(57, 'es-ES', 'B1', 'Learning words by definitions has little effect on comprehending the words in novel texts. You need to learn new word in the context in which they are used. (Cobb, T. From concord to lexicon: Development and test of a corpus-based lexical tutor. Montreal: Concordia University, PhD dissertation.)'),
(58, 'es-ES', 'B2', 'Learning words by definitions has little effect on comprehending the words in novel texts. You need to learn new word in the context in which they are used. (Cobb, T. From concord to lexicon: Development and test of a corpus-based lexical tutor. Montreal: Concordia University, PhD dissertation.)'),
(59, 'es-ES', 'C1', 'Learning words by definitions has little effect on comprehending the words in novel texts. You need to learn new word in the context in which they are used. (Cobb, T. From concord to lexicon: Development and test of a corpus-based lexical tutor. Montreal: Concordia University, PhD dissertation.)'),
(60, 'es-ES', 'C2', 'Learning words by definitions has little effect on comprehending the words in novel texts. You need to learn new word in the context in which they are used. (Cobb, T. From concord to lexicon: Development and test of a corpus-based lexical tutor. Montreal: Concordia University, PhD dissertation.)'),
(61, 'fr-FR', 'A1', 'Learning words by definitions has little effect on comprehending the words in novel texts. You need to learn new word in the context in which they are used. (Cobb, T. From concord to lexicon: Development and test of a corpus-based lexical tutor. Montreal: Concordia University, PhD dissertation.)'),
(62, 'fr-FR', 'A2', 'Learning words by definitions has little effect on comprehending the words in novel texts. You need to learn new word in the context in which they are used. (Cobb, T. From concord to lexicon: Development and test of a corpus-based lexical tutor. Montreal: Concordia University, PhD dissertation.)'),
(63, 'fr-FR', 'B1', 'Learning words by definitions has little effect on comprehending the words in novel texts. You need to learn new word in the context in which they are used. (Cobb, T. From concord to lexicon: Development and test of a corpus-based lexical tutor. Montreal: Concordia University, PhD dissertation.)'),
(64, 'fr-FR', 'B2', 'Learning words by definitions has little effect on comprehending the words in novel texts. You need to learn new word in the context in which they are used. (Cobb, T. From concord to lexicon: Development and test of a corpus-based lexical tutor. Montreal: Concordia University, PhD dissertation.)'),
(65, 'fr-FR', 'C1', 'Learning words by definitions has little effect on comprehending the words in novel texts. You need to learn new word in the context in which they are used. (Cobb, T. From concord to lexicon: Development and test of a corpus-based lexical tutor. Montreal: Concordia University, PhD dissertation.)'),
(66, 'fr-FR', 'C2', 'Learning words by definitions has little effect on comprehending the words in novel texts. You need to learn new word in the context in which they are used. (Cobb, T. From concord to lexicon: Development and test of a corpus-based lexical tutor. Montreal: Concordia University, PhD dissertation.)'),
(67, 'it-IT', 'A1', 'Learning words by definitions has little effect on comprehending the words in novel texts. You need to learn new word in the context in which they are used. (Cobb, T. From concord to lexicon: Development and test of a corpus-based lexical tutor. Montreal: Concordia University, PhD dissertation.)'),
(68, 'it-IT', 'A2', 'Learning words by definitions has little effect on comprehending the words in novel texts. You need to learn new word in the context in which they are used. (Cobb, T. From concord to lexicon: Development and test of a corpus-based lexical tutor. Montreal: Concordia University, PhD dissertation.)'),
(69, 'it-IT', 'B1', 'Learning words by definitions has little effect on comprehending the words in novel texts. You need to learn new word in the context in which they are used. (Cobb, T. From concord to lexicon: Development and test of a corpus-based lexical tutor. Montreal: Concordia University, PhD dissertation.)'),
(70, 'it-IT', 'B2', 'Learning words by definitions has little effect on comprehending the words in novel texts. You need to learn new word in the context in which they are used. (Cobb, T. From concord to lexicon: Development and test of a corpus-based lexical tutor. Montreal: Concordia University, PhD dissertation.)'),
(71, 'it-IT', 'C1', 'Learning words by definitions has little effect on comprehending the words in novel texts. You need to learn new word in the context in which they are used. (Cobb, T. From concord to lexicon: Development and test of a corpus-based lexical tutor. Montreal: Concordia University, PhD dissertation.)'),
(72, 'it-IT', 'C2', 'Learning words by definitions has little effect on comprehending the words in novel texts. You need to learn new word in the context in which they are used. (Cobb, T. From concord to lexicon: Development and test of a corpus-based lexical tutor. Montreal: Concordia University, PhD dissertation.)'),
(73, 'de-DE', 'A1', 'Meeting a word in several contexts enables a language learner to comprehend the word in novel contexts. Knowing a word means understanding how a word is used by different users to express different meanings. (Cobb, T. From concord to lexicon: Development and test of a corpus-based lexical tutor. Montreal: Concordia University, PhD dissertation.)'),
(74, 'de-DE', 'A2', 'Meeting a word in several contexts enables a language learner to comprehend the word in novel contexts. Knowing a word means understanding how a word is used by different users to express different meanings. (Cobb, T. From concord to lexicon: Development and test of a corpus-based lexical tutor. Montreal: Concordia University, PhD dissertation.)'),
(75, 'de-DE', 'B1', 'Meeting a word in several contexts enables a language learner to comprehend the word in novel contexts. Knowing a word means understanding how a word is used by different users to express different meanings. (Cobb, T. From concord to lexicon: Development and test of a corpus-based lexical tutor. Montreal: Concordia University, PhD dissertation.)'),
(76, 'de-DE', 'B2', 'Meeting a word in several contexts enables a language learner to comprehend the word in novel contexts. Knowing a word means understanding how a word is used by different users to express different meanings. (Cobb, T. From concord to lexicon: Development and test of a corpus-based lexical tutor. Montreal: Concordia University, PhD dissertation.)'),
(77, 'de-DE', 'C1', 'Meeting a word in several contexts enables a language learner to comprehend the word in novel contexts. Knowing a word means understanding how a word is used by different users to express different meanings. (Cobb, T. From concord to lexicon: Development and test of a corpus-based lexical tutor. Montreal: Concordia University, PhD dissertation.)'),
(78, 'de-DE', 'C2', 'Meeting a word in several contexts enables a language learner to comprehend the word in novel contexts. Knowing a word means understanding how a word is used by different users to express different meanings. (Cobb, T. From concord to lexicon: Development and test of a corpus-based lexical tutor. Montreal: Concordia University, PhD dissertation.)'),
(79, 'en-GB', 'A1', 'Meeting a word in several contexts enables a language learner to comprehend the word in novel contexts. Knowing a word means understanding how a word is used by different users to express different meanings. (Cobb, T. From concord to lexicon: Development and test of a corpus-based lexical tutor. Montreal: Concordia University, PhD dissertation.)'),
(80, 'en-GB', 'A2', 'Meeting a word in several contexts enables a language learner to comprehend the word in novel contexts. Knowing a word means understanding how a word is used by different users to express different meanings. (Cobb, T. From concord to lexicon: Development and test of a corpus-based lexical tutor. Montreal: Concordia University, PhD dissertation.)'),
(81, 'en-GB', 'B1', 'Meeting a word in several contexts enables a language learner to comprehend the word in novel contexts. Knowing a word means understanding how a word is used by different users to express different meanings. (Cobb, T. From concord to lexicon: Development and test of a corpus-based lexical tutor. Montreal: Concordia University, PhD dissertation.)'),
(82, 'en-GB', 'B2', 'Meeting a word in several contexts enables a language learner to comprehend the word in novel contexts. Knowing a word means understanding how a word is used by different users to express different meanings. (Cobb, T. From concord to lexicon: Development and test of a corpus-based lexical tutor. Montreal: Concordia University, PhD dissertation.)'),
(83, 'en-GB', 'C1', 'Meeting a word in several contexts enables a language learner to comprehend the word in novel contexts. Knowing a word means understanding how a word is used by different users to express different meanings. (Cobb, T. From concord to lexicon: Development and test of a corpus-based lexical tutor. Montreal: Concordia University, PhD dissertation.)'),
(84, 'en-GB', 'C2', 'Meeting a word in several contexts enables a language learner to comprehend the word in novel contexts. Knowing a word means understanding how a word is used by different users to express different meanings. (Cobb, T. From concord to lexicon: Development and test of a corpus-based lexical tutor. Montreal: Concordia University, PhD dissertation.)'),
(85, 'en-US', 'A1', 'Meeting a word in several contexts enables a language learner to comprehend the word in novel contexts. Knowing a word means understanding how a word is used by different users to express different meanings. (Cobb, T. From concord to lexicon: Development and test of a corpus-based lexical tutor. Montreal: Concordia University, PhD dissertation.)'),
(86, 'en-US', 'A2', 'Meeting a word in several contexts enables a language learner to comprehend the word in novel contexts. Knowing a word means understanding how a word is used by different users to express different meanings. (Cobb, T. From concord to lexicon: Development and test of a corpus-based lexical tutor. Montreal: Concordia University, PhD dissertation.)'),
(87, 'en-US', 'B1', 'Meeting a word in several contexts enables a language learner to comprehend the word in novel contexts. Knowing a word means understanding how a word is used by different users to express different meanings. (Cobb, T. From concord to lexicon: Development and test of a corpus-based lexical tutor. Montreal: Concordia University, PhD dissertation.)'),
(88, 'en-US', 'B2', 'Meeting a word in several contexts enables a language learner to comprehend the word in novel contexts. Knowing a word means understanding how a word is used by different users to express different meanings. (Cobb, T. From concord to lexicon: Development and test of a corpus-based lexical tutor. Montreal: Concordia University, PhD dissertation.)'),
(89, 'en-US', 'C1', 'Meeting a word in several contexts enables a language learner to comprehend the word in novel contexts. Knowing a word means understanding how a word is used by different users to express different meanings. (Cobb, T. From concord to lexicon: Development and test of a corpus-based lexical tutor. Montreal: Concordia University, PhD dissertation.)'),
(90, 'en-US', 'C2', 'Meeting a word in several contexts enables a language learner to comprehend the word in novel contexts. Knowing a word means understanding how a word is used by different users to express different meanings. (Cobb, T. From concord to lexicon: Development and test of a corpus-based lexical tutor. Montreal: Concordia University, PhD dissertation.)'),
(91, 'es-ES', 'A1', 'Meeting a word in several contexts enables a language learner to comprehend the word in novel contexts. Knowing a word means understanding how a word is used by different users to express different meanings. (Cobb, T. From concord to lexicon: Development and test of a corpus-based lexical tutor. Montreal: Concordia University, PhD dissertation.)'),
(92, 'es-ES', 'A2', 'Meeting a word in several contexts enables a language learner to comprehend the word in novel contexts. Knowing a word means understanding how a word is used by different users to express different meanings. (Cobb, T. From concord to lexicon: Development and test of a corpus-based lexical tutor. Montreal: Concordia University, PhD dissertation.)'),
(93, 'es-ES', 'B1', 'Meeting a word in several contexts enables a language learner to comprehend the word in novel contexts. Knowing a word means understanding how a word is used by different users to express different meanings. (Cobb, T. From concord to lexicon: Development and test of a corpus-based lexical tutor. Montreal: Concordia University, PhD dissertation.)'),
(94, 'es-ES', 'B2', 'Meeting a word in several contexts enables a language learner to comprehend the word in novel contexts. Knowing a word means understanding how a word is used by different users to express different meanings. (Cobb, T. From concord to lexicon: Development and test of a corpus-based lexical tutor. Montreal: Concordia University, PhD dissertation.)'),
(95, 'es-ES', 'C1', 'Meeting a word in several contexts enables a language learner to comprehend the word in novel contexts. Knowing a word means understanding how a word is used by different users to express different meanings. (Cobb, T. From concord to lexicon: Development and test of a corpus-based lexical tutor. Montreal: Concordia University, PhD dissertation.)'),
(96, 'es-ES', 'C2', 'Meeting a word in several contexts enables a language learner to comprehend the word in novel contexts. Knowing a word means understanding how a word is used by different users to express different meanings. (Cobb, T. From concord to lexicon: Development and test of a corpus-based lexical tutor. Montreal: Concordia University, PhD dissertation.)'),
(97, 'fr-FR', 'A1', 'Meeting a word in several contexts enables a language learner to comprehend the word in novel contexts. Knowing a word means understanding how a word is used by different users to express different meanings. (Cobb, T. From concord to lexicon: Development and test of a corpus-based lexical tutor. Montreal: Concordia University, PhD dissertation.)'),
(98, 'fr-FR', 'A2', 'Meeting a word in several contexts enables a language learner to comprehend the word in novel contexts. Knowing a word means understanding how a word is used by different users to express different meanings. (Cobb, T. From concord to lexicon: Development and test of a corpus-based lexical tutor. Montreal: Concordia University, PhD dissertation.)'),
(99, 'fr-FR', 'B1', 'Meeting a word in several contexts enables a language learner to comprehend the word in novel contexts. Knowing a word means understanding how a word is used by different users to express different meanings. (Cobb, T. From concord to lexicon: Development and test of a corpus-based lexical tutor. Montreal: Concordia University, PhD dissertation.)'),
(100, 'fr-FR', 'B2', 'Meeting a word in several contexts enables a language learner to comprehend the word in novel contexts. Knowing a word means understanding how a word is used by different users to express different meanings. (Cobb, T. From concord to lexicon: Development and test of a corpus-based lexical tutor. Montreal: Concordia University, PhD dissertation.)'),
(101, 'fr-FR', 'C1', 'Meeting a word in several contexts enables a language learner to comprehend the word in novel contexts. Knowing a word means understanding how a word is used by different users to express different meanings. (Cobb, T. From concord to lexicon: Development and test of a corpus-based lexical tutor. Montreal: Concordia University, PhD dissertation.)'),
(102, 'fr-FR', 'C2', 'Meeting a word in several contexts enables a language learner to comprehend the word in novel contexts. Knowing a word means understanding how a word is used by different users to express different meanings. (Cobb, T. From concord to lexicon: Development and test of a corpus-based lexical tutor. Montreal: Concordia University, PhD dissertation.)'),
(103, 'it-IT', 'A1', 'Meeting a word in several contexts enables a language learner to comprehend the word in novel contexts. Knowing a word means understanding how a word is used by different users to express different meanings. (Cobb, T. From concord to lexicon: Development and test of a corpus-based lexical tutor. Montreal: Concordia University, PhD dissertation.)'),
(104, 'it-IT', 'A2', 'Meeting a word in several contexts enables a language learner to comprehend the word in novel contexts. Knowing a word means understanding how a word is used by different users to express different meanings. (Cobb, T. From concord to lexicon: Development and test of a corpus-based lexical tutor. Montreal: Concordia University, PhD dissertation.)'),
(105, 'it-IT', 'B1', 'Meeting a word in several contexts enables a language learner to comprehend the word in novel contexts. Knowing a word means understanding how a word is used by different users to express different meanings. (Cobb, T. From concord to lexicon: Development and test of a corpus-based lexical tutor. Montreal: Concordia University, PhD dissertation.)'),
(106, 'it-IT', 'B2', 'Meeting a word in several contexts enables a language learner to comprehend the word in novel contexts. Knowing a word means understanding how a word is used by different users to express different meanings. (Cobb, T. From concord to lexicon: Development and test of a corpus-based lexical tutor. Montreal: Concordia University, PhD dissertation.)'),
(107, 'it-IT', 'C1', 'Meeting a word in several contexts enables a language learner to comprehend the word in novel contexts. Knowing a word means understanding how a word is used by different users to express different meanings. (Cobb, T. From concord to lexicon: Development and test of a corpus-based lexical tutor. Montreal: Concordia University, PhD dissertation.)'),
(108, 'it-IT', 'C2', 'Meeting a word in several contexts enables a language learner to comprehend the word in novel contexts. Knowing a word means understanding how a word is used by different users to express different meanings. (Cobb, T. From concord to lexicon: Development and test of a corpus-based lexical tutor. Montreal: Concordia University, PhD dissertation.)'),
(109, 'de-DE', 'A1', 'A university graduate will have a vocabulary of around 20,000 word families (Goulden, Nation and Read, 1990). This represents the lexicon on a learned person. The most frequent 5000 word families cover 89% of the lexicon of English (as modelled by the Brown Corpus of English). (Paul Nation and Robert Waring. 1997. Vocabulary size, text coverage and word lists. In Schmitt, N. and M. McCarthy (eds.): Vocabulary: description, acquisition and pedagogy (pp. 6-19). Cambridge: Cambridge University Press.)'),
(110, 'de-DE', 'A2', 'A university graduate will have a vocabulary of around 20,000 word families (Goulden, Nation and Read, 1990). This represents the lexicon on a learned person. The most frequent 5000 word families cover 89% of the lexicon of English (as modelled by the Brown Corpus of English). (Paul Nation and Robert Waring. 1997. Vocabulary size, text coverage and word lists. In Schmitt, N. and M. McCarthy (eds.): Vocabulary: description, acquisition and pedagogy (pp. 6-19). Cambridge: Cambridge University Press.)'),
(111, 'de-DE', 'B1', 'A university graduate will have a vocabulary of around 20,000 word families (Goulden, Nation and Read, 1990). This represents the lexicon on a learned person. The most frequent 5000 word families cover 89% of the lexicon of English (as modelled by the Brown Corpus of English). (Paul Nation and Robert Waring. 1997. Vocabulary size, text coverage and word lists. In Schmitt, N. and M. McCarthy (eds.): Vocabulary: description, acquisition and pedagogy (pp. 6-19). Cambridge: Cambridge University Press.)'),
(112, 'de-DE', 'B2', 'A university graduate will have a vocabulary of around 20,000 word families (Goulden, Nation and Read, 1990). This represents the lexicon on a learned person. The most frequent 5000 word families cover 89% of the lexicon of English (as modelled by the Brown Corpus of English). (Paul Nation and Robert Waring. 1997. Vocabulary size, text coverage and word lists. In Schmitt, N. and M. McCarthy (eds.): Vocabulary: description, acquisition and pedagogy (pp. 6-19). Cambridge: Cambridge University Press.)'),
(113, 'de-DE', 'C1', 'A university graduate will have a vocabulary of around 20,000 word families (Goulden, Nation and Read, 1990). This represents the lexicon on a learned person. The most frequent 5000 word families cover 89% of the lexicon of English (as modelled by the Brown Corpus of English). (Paul Nation and Robert Waring. 1997. Vocabulary size, text coverage and word lists. In Schmitt, N. and M. McCarthy (eds.): Vocabulary: description, acquisition and pedagogy (pp. 6-19). Cambridge: Cambridge University Press.)'),
(114, 'de-DE', 'C2', 'A university graduate will have a vocabulary of around 20,000 word families (Goulden, Nation and Read, 1990). This represents the lexicon on a learned person. The most frequent 5000 word families cover 89% of the lexicon of English (as modelled by the Brown Corpus of English). (Paul Nation and Robert Waring. 1997. Vocabulary size, text coverage and word lists. In Schmitt, N. and M. McCarthy (eds.): Vocabulary: description, acquisition and pedagogy (pp. 6-19). Cambridge: Cambridge University Press.)'),
(115, 'en-GB', 'A1', 'A university graduate will have a vocabulary of around 20,000 word families (Goulden, Nation and Read, 1990). This represents the lexicon on a learned person. The most frequent 5000 word families cover 89% of the lexicon of English (as modelled by the Brown Corpus of English). (Paul Nation and Robert Waring. 1997. Vocabulary size, text coverage and word lists. In Schmitt, N. and M. McCarthy (eds.): Vocabulary: description, acquisition and pedagogy (pp. 6-19). Cambridge: Cambridge University Press.)'),
(116, 'en-GB', 'A2', 'A university graduate will have a vocabulary of around 20,000 word families (Goulden, Nation and Read, 1990). This represents the lexicon on a learned person. The most frequent 5000 word families cover 89% of the lexicon of English (as modelled by the Brown Corpus of English). (Paul Nation and Robert Waring. 1997. Vocabulary size, text coverage and word lists. In Schmitt, N. and M. McCarthy (eds.): Vocabulary: description, acquisition and pedagogy (pp. 6-19). Cambridge: Cambridge University Press.)'),
(117, 'en-GB', 'B1', 'A university graduate will have a vocabulary of around 20,000 word families (Goulden, Nation and Read, 1990). This represents the lexicon on a learned person. The most frequent 5000 word families cover 89% of the lexicon of English (as modelled by the Brown Corpus of English). (Paul Nation and Robert Waring. 1997. Vocabulary size, text coverage and word lists. In Schmitt, N. and M. McCarthy (eds.): Vocabulary: description, acquisition and pedagogy (pp. 6-19). Cambridge: Cambridge University Press.)'),
(118, 'en-GB', 'B2', 'A university graduate will have a vocabulary of around 20,000 word families (Goulden, Nation and Read, 1990). This represents the lexicon on a learned person. The most frequent 5000 word families cover 89% of the lexicon of English (as modelled by the Brown Corpus of English). (Paul Nation and Robert Waring. 1997. Vocabulary size, text coverage and word lists. In Schmitt, N. and M. McCarthy (eds.): Vocabulary: description, acquisition and pedagogy (pp. 6-19). Cambridge: Cambridge University Press.)'),
(119, 'en-GB', 'C1', 'A university graduate will have a vocabulary of around 20,000 word families (Goulden, Nation and Read, 1990). This represents the lexicon on a learned person. The most frequent 5000 word families cover 89% of the lexicon of English (as modelled by the Brown Corpus of English). (Paul Nation and Robert Waring. 1997. Vocabulary size, text coverage and word lists. In Schmitt, N. and M. McCarthy (eds.): Vocabulary: description, acquisition and pedagogy (pp. 6-19). Cambridge: Cambridge University Press.)'),
(120, 'en-GB', 'C2', 'A university graduate will have a vocabulary of around 20,000 word families (Goulden, Nation and Read, 1990). This represents the lexicon on a learned person. The most frequent 5000 word families cover 89% of the lexicon of English (as modelled by the Brown Corpus of English). (Paul Nation and Robert Waring. 1997. Vocabulary size, text coverage and word lists. In Schmitt, N. and M. McCarthy (eds.): Vocabulary: description, acquisition and pedagogy (pp. 6-19). Cambridge: Cambridge University Press.)'),
(121, 'en-US', 'A1', 'A university graduate will have a vocabulary of around 20,000 word families (Goulden, Nation and Read, 1990). This represents the lexicon on a learned person. The most frequent 5000 word families cover 89% of the lexicon of English (as modelled by the Brown Corpus of English). (Paul Nation and Robert Waring. 1997. Vocabulary size, text coverage and word lists. In Schmitt, N. and M. McCarthy (eds.): Vocabulary: description, acquisition and pedagogy (pp. 6-19). Cambridge: Cambridge University Press.)'),
(122, 'en-US', 'A2', 'A university graduate will have a vocabulary of around 20,000 word families (Goulden, Nation and Read, 1990). This represents the lexicon on a learned person. The most frequent 5000 word families cover 89% of the lexicon of English (as modelled by the Brown Corpus of English). (Paul Nation and Robert Waring. 1997. Vocabulary size, text coverage and word lists. In Schmitt, N. and M. McCarthy (eds.): Vocabulary: description, acquisition and pedagogy (pp. 6-19). Cambridge: Cambridge University Press.)'),
(123, 'en-US', 'B1', 'A university graduate will have a vocabulary of around 20,000 word families (Goulden, Nation and Read, 1990). This represents the lexicon on a learned person. The most frequent 5000 word families cover 89% of the lexicon of English (as modelled by the Brown Corpus of English). (Paul Nation and Robert Waring. 1997. Vocabulary size, text coverage and word lists. In Schmitt, N. and M. McCarthy (eds.): Vocabulary: description, acquisition and pedagogy (pp. 6-19). Cambridge: Cambridge University Press.)'),
(124, 'en-US', 'B2', 'A university graduate will have a vocabulary of around 20,000 word families (Goulden, Nation and Read, 1990). This represents the lexicon on a learned person. The most frequent 5000 word families cover 89% of the lexicon of English (as modelled by the Brown Corpus of English). (Paul Nation and Robert Waring. 1997. Vocabulary size, text coverage and word lists. In Schmitt, N. and M. McCarthy (eds.): Vocabulary: description, acquisition and pedagogy (pp. 6-19). Cambridge: Cambridge University Press.)'),
(125, 'en-US', 'C1', 'A university graduate will have a vocabulary of around 20,000 word families (Goulden, Nation and Read, 1990). This represents the lexicon on a learned person. The most frequent 5000 word families cover 89% of the lexicon of English (as modelled by the Brown Corpus of English). (Paul Nation and Robert Waring. 1997. Vocabulary size, text coverage and word lists. In Schmitt, N. and M. McCarthy (eds.): Vocabulary: description, acquisition and pedagogy (pp. 6-19). Cambridge: Cambridge University Press.)'),
(126, 'en-US', 'C2', 'A university graduate will have a vocabulary of around 20,000 word families (Goulden, Nation and Read, 1990). This represents the lexicon on a learned person. The most frequent 5000 word families cover 89% of the lexicon of English (as modelled by the Brown Corpus of English). (Paul Nation and Robert Waring. 1997. Vocabulary size, text coverage and word lists. In Schmitt, N. and M. McCarthy (eds.): Vocabulary: description, acquisition and pedagogy (pp. 6-19). Cambridge: Cambridge University Press.)'),
(127, 'es-ES', 'A1', 'A university graduate will have a vocabulary of around 20,000 word families (Goulden, Nation and Read, 1990). This represents the lexicon on a learned person. The most frequent 5000 word families cover 89% of the lexicon of English (as modelled by the Brown Corpus of English). (Paul Nation and Robert Waring. 1997. Vocabulary size, text coverage and word lists. In Schmitt, N. and M. McCarthy (eds.): Vocabulary: description, acquisition and pedagogy (pp. 6-19). Cambridge: Cambridge University Press.)'),
(128, 'es-ES', 'A2', 'A university graduate will have a vocabulary of around 20,000 word families (Goulden, Nation and Read, 1990). This represents the lexicon on a learned person. The most frequent 5000 word families cover 89% of the lexicon of English (as modelled by the Brown Corpus of English). (Paul Nation and Robert Waring. 1997. Vocabulary size, text coverage and word lists. In Schmitt, N. and M. McCarthy (eds.): Vocabulary: description, acquisition and pedagogy (pp. 6-19). Cambridge: Cambridge University Press.)'),
(129, 'es-ES', 'B1', 'A university graduate will have a vocabulary of around 20,000 word families (Goulden, Nation and Read, 1990). This represents the lexicon on a learned person. The most frequent 5000 word families cover 89% of the lexicon of English (as modelled by the Brown Corpus of English). (Paul Nation and Robert Waring. 1997. Vocabulary size, text coverage and word lists. In Schmitt, N. and M. McCarthy (eds.): Vocabulary: description, acquisition and pedagogy (pp. 6-19). Cambridge: Cambridge University Press.)'),
(130, 'es-ES', 'B2', 'A university graduate will have a vocabulary of around 20,000 word families (Goulden, Nation and Read, 1990). This represents the lexicon on a learned person. The most frequent 5000 word families cover 89% of the lexicon of English (as modelled by the Brown Corpus of English). (Paul Nation and Robert Waring. 1997. Vocabulary size, text coverage and word lists. In Schmitt, N. and M. McCarthy (eds.): Vocabulary: description, acquisition and pedagogy (pp. 6-19). Cambridge: Cambridge University Press.)'),
(131, 'es-ES', 'C1', 'A university graduate will have a vocabulary of around 20,000 word families (Goulden, Nation and Read, 1990). This represents the lexicon on a learned person. The most frequent 5000 word families cover 89% of the lexicon of English (as modelled by the Brown Corpus of English). (Paul Nation and Robert Waring. 1997. Vocabulary size, text coverage and word lists. In Schmitt, N. and M. McCarthy (eds.): Vocabulary: description, acquisition and pedagogy (pp. 6-19). Cambridge: Cambridge University Press.)'),
(132, 'es-ES', 'C2', 'A university graduate will have a vocabulary of around 20,000 word families (Goulden, Nation and Read, 1990). This represents the lexicon on a learned person. The most frequent 5000 word families cover 89% of the lexicon of English (as modelled by the Brown Corpus of English). (Paul Nation and Robert Waring. 1997. Vocabulary size, text coverage and word lists. In Schmitt, N. and M. McCarthy (eds.): Vocabulary: description, acquisition and pedagogy (pp. 6-19). Cambridge: Cambridge University Press.)'),
(133, 'fr-FR', 'A1', 'A university graduate will have a vocabulary of around 20,000 word families (Goulden, Nation and Read, 1990). This represents the lexicon on a learned person. The most frequent 5000 word families cover 89% of the lexicon of English (as modelled by the Brown Corpus of English). (Paul Nation and Robert Waring. 1997. Vocabulary size, text coverage and word lists. In Schmitt, N. and M. McCarthy (eds.): Vocabulary: description, acquisition and pedagogy (pp. 6-19). Cambridge: Cambridge University Press.)'),
(134, 'fr-FR', 'A2', 'A university graduate will have a vocabulary of around 20,000 word families (Goulden, Nation and Read, 1990). This represents the lexicon on a learned person. The most frequent 5000 word families cover 89% of the lexicon of English (as modelled by the Brown Corpus of English). (Paul Nation and Robert Waring. 1997. Vocabulary size, text coverage and word lists. In Schmitt, N. and M. McCarthy (eds.): Vocabulary: description, acquisition and pedagogy (pp. 6-19). Cambridge: Cambridge University Press.)'),
(135, 'fr-FR', 'B1', 'A university graduate will have a vocabulary of around 20,000 word families (Goulden, Nation and Read, 1990). This represents the lexicon on a learned person. The most frequent 5000 word families cover 89% of the lexicon of English (as modelled by the Brown Corpus of English). (Paul Nation and Robert Waring. 1997. Vocabulary size, text coverage and word lists. In Schmitt, N. and M. McCarthy (eds.): Vocabulary: description, acquisition and pedagogy (pp. 6-19). Cambridge: Cambridge University Press.)'),
(136, 'fr-FR', 'B2', 'A university graduate will have a vocabulary of around 20,000 word families (Goulden, Nation and Read, 1990). This represents the lexicon on a learned person. The most frequent 5000 word families cover 89% of the lexicon of English (as modelled by the Brown Corpus of English). (Paul Nation and Robert Waring. 1997. Vocabulary size, text coverage and word lists. In Schmitt, N. and M. McCarthy (eds.): Vocabulary: description, acquisition and pedagogy (pp. 6-19). Cambridge: Cambridge University Press.)'),
(137, 'fr-FR', 'C1', 'A university graduate will have a vocabulary of around 20,000 word families (Goulden, Nation and Read, 1990). This represents the lexicon on a learned person. The most frequent 5000 word families cover 89% of the lexicon of English (as modelled by the Brown Corpus of English). (Paul Nation and Robert Waring. 1997. Vocabulary size, text coverage and word lists. In Schmitt, N. and M. McCarthy (eds.): Vocabulary: description, acquisition and pedagogy (pp. 6-19). Cambridge: Cambridge University Press.)'),
(138, 'fr-FR', 'C2', 'A university graduate will have a vocabulary of around 20,000 word families (Goulden, Nation and Read, 1990). This represents the lexicon on a learned person. The most frequent 5000 word families cover 89% of the lexicon of English (as modelled by the Brown Corpus of English). (Paul Nation and Robert Waring. 1997. Vocabulary size, text coverage and word lists. In Schmitt, N. and M. McCarthy (eds.): Vocabulary: description, acquisition and pedagogy (pp. 6-19). Cambridge: Cambridge University Press.)'),
(139, 'it-IT', 'A1', 'A university graduate will have a vocabulary of around 20,000 word families (Goulden, Nation and Read, 1990). This represents the lexicon on a learned person. The most frequent 5000 word families cover 89% of the lexicon of English (as modelled by the Brown Corpus of English). (Paul Nation and Robert Waring. 1997. Vocabulary size, text coverage and word lists. In Schmitt, N. and M. McCarthy (eds.): Vocabulary: description, acquisition and pedagogy (pp. 6-19). Cambridge: Cambridge University Press.)'),
(140, 'it-IT', 'A2', 'A university graduate will have a vocabulary of around 20,000 word families (Goulden, Nation and Read, 1990). This represents the lexicon on a learned person. The most frequent 5000 word families cover 89% of the lexicon of English (as modelled by the Brown Corpus of English). (Paul Nation and Robert Waring. 1997. Vocabulary size, text coverage and word lists. In Schmitt, N. and M. McCarthy (eds.): Vocabulary: description, acquisition and pedagogy (pp. 6-19). Cambridge: Cambridge University Press.)'),
(141, 'it-IT', 'B1', 'A university graduate will have a vocabulary of around 20,000 word families (Goulden, Nation and Read, 1990). This represents the lexicon on a learned person. The most frequent 5000 word families cover 89% of the lexicon of English (as modelled by the Brown Corpus of English). (Paul Nation and Robert Waring. 1997. Vocabulary size, text coverage and word lists. In Schmitt, N. and M. McCarthy (eds.): Vocabulary: description, acquisition and pedagogy (pp. 6-19). Cambridge: Cambridge University Press.)'),
(142, 'it-IT', 'B2', 'A university graduate will have a vocabulary of around 20,000 word families (Goulden, Nation and Read, 1990). This represents the lexicon on a learned person. The most frequent 5000 word families cover 89% of the lexicon of English (as modelled by the Brown Corpus of English). (Paul Nation and Robert Waring. 1997. Vocabulary size, text coverage and word lists. In Schmitt, N. and M. McCarthy (eds.): Vocabulary: description, acquisition and pedagogy (pp. 6-19). Cambridge: Cambridge University Press.)'),
(143, 'it-IT', 'C1', 'A university graduate will have a vocabulary of around 20,000 word families (Goulden, Nation and Read, 1990). This represents the lexicon on a learned person. The most frequent 5000 word families cover 89% of the lexicon of English (as modelled by the Brown Corpus of English). (Paul Nation and Robert Waring. 1997. Vocabulary size, text coverage and word lists. In Schmitt, N. and M. McCarthy (eds.): Vocabulary: description, acquisition and pedagogy (pp. 6-19). Cambridge: Cambridge University Press.)'),
(144, 'it-IT', 'C2', 'A university graduate will have a vocabulary of around 20,000 word families (Goulden, Nation and Read, 1990). This represents the lexicon on a learned person. The most frequent 5000 word families cover 89% of the lexicon of English (as modelled by the Brown Corpus of English). (Paul Nation and Robert Waring. 1997. Vocabulary size, text coverage and word lists. In Schmitt, N. and M. McCarthy (eds.): Vocabulary: description, acquisition and pedagogy (pp. 6-19). Cambridge: Cambridge University Press.)'),
(145, 'de-DE', 'A1', 'For adult learners of English as a foreign language, the gap between their vocabulary size and that of native speakers is usually very large, with many adult foreign learners of English having a vocabulary size of much less than 5000 word families in spite of having studied English for several years. (Paul Nation and Robert Waring. 1997. Vocabulary size, text coverage and word lists. In Schmitt, N. and M. McCarthy (eds.): Vocabulary: description, acquisition and pedagogy (pp. 6-19). Cambridge: Cambridge University Press.)'),
(146, 'de-DE', 'A2', 'For adult learners of English as a foreign language, the gap between their vocabulary size and that of native speakers is usually very large, with many adult foreign learners of English having a vocabulary size of much less than 5000 word families in spite of having studied English for several years. (Paul Nation and Robert Waring. 1997. Vocabulary size, text coverage and word lists. In Schmitt, N. and M. McCarthy (eds.): Vocabulary: description, acquisition and pedagogy (pp. 6-19). Cambridge: Cambridge University Press.)'),
(147, 'de-DE', 'B1', 'For adult learners of English as a foreign language, the gap between their vocabulary size and that of native speakers is usually very large, with many adult foreign learners of English having a vocabulary size of much less than 5000 word families in spite of having studied English for several years. (Paul Nation and Robert Waring. 1997. Vocabulary size, text coverage and word lists. In Schmitt, N. and M. McCarthy (eds.): Vocabulary: description, acquisition and pedagogy (pp. 6-19). Cambridge: Cambridge University Press.)'),
(148, 'de-DE', 'B2', 'For adult learners of English as a foreign language, the gap between their vocabulary size and that of native speakers is usually very large, with many adult foreign learners of English having a vocabulary size of much less than 5000 word families in spite of having studied English for several years. (Paul Nation and Robert Waring. 1997. Vocabulary size, text coverage and word lists. In Schmitt, N. and M. McCarthy (eds.): Vocabulary: description, acquisition and pedagogy (pp. 6-19). Cambridge: Cambridge University Press.)'),
(149, 'de-DE', 'C1', 'For adult learners of English as a foreign language, the gap between their vocabulary size and that of native speakers is usually very large, with many adult foreign learners of English having a vocabulary size of much less than 5000 word families in spite of having studied English for several years. (Paul Nation and Robert Waring. 1997. Vocabulary size, text coverage and word lists. In Schmitt, N. and M. McCarthy (eds.): Vocabulary: description, acquisition and pedagogy (pp. 6-19). Cambridge: Cambridge University Press.)'),
(150, 'de-DE', 'C2', 'For adult learners of English as a foreign language, the gap between their vocabulary size and that of native speakers is usually very large, with many adult foreign learners of English having a vocabulary size of much less than 5000 word families in spite of having studied English for several years. (Paul Nation and Robert Waring. 1997. Vocabulary size, text coverage and word lists. In Schmitt, N. and M. McCarthy (eds.): Vocabulary: description, acquisition and pedagogy (pp. 6-19). Cambridge: Cambridge University Press.)'),
(151, 'en-GB', 'A1', 'For adult learners of English as a foreign language, the gap between their vocabulary size and that of native speakers is usually very large, with many adult foreign learners of English having a vocabulary size of much less than 5000 word families in spite of having studied English for several years. (Paul Nation and Robert Waring. 1997. Vocabulary size, text coverage and word lists. In Schmitt, N. and M. McCarthy (eds.): Vocabulary: description, acquisition and pedagogy (pp. 6-19). Cambridge: Cambridge University Press.)'),
(152, 'en-GB', 'A2', 'For adult learners of English as a foreign language, the gap between their vocabulary size and that of native speakers is usually very large, with many adult foreign learners of English having a vocabulary size of much less than 5000 word families in spite of having studied English for several years. (Paul Nation and Robert Waring. 1997. Vocabulary size, text coverage and word lists. In Schmitt, N. and M. McCarthy (eds.): Vocabulary: description, acquisition and pedagogy (pp. 6-19). Cambridge: Cambridge University Press.)'),
(153, 'en-GB', 'B1', 'For adult learners of English as a foreign language, the gap between their vocabulary size and that of native speakers is usually very large, with many adult foreign learners of English having a vocabulary size of much less than 5000 word families in spite of having studied English for several years. (Paul Nation and Robert Waring. 1997. Vocabulary size, text coverage and word lists. In Schmitt, N. and M. McCarthy (eds.): Vocabulary: description, acquisition and pedagogy (pp. 6-19). Cambridge: Cambridge University Press.)'),
(154, 'en-GB', 'B2', 'For adult learners of English as a foreign language, the gap between their vocabulary size and that of native speakers is usually very large, with many adult foreign learners of English having a vocabulary size of much less than 5000 word families in spite of having studied English for several years. (Paul Nation and Robert Waring. 1997. Vocabulary size, text coverage and word lists. In Schmitt, N. and M. McCarthy (eds.): Vocabulary: description, acquisition and pedagogy (pp. 6-19). Cambridge: Cambridge University Press.)'),
(155, 'en-GB', 'C1', 'For adult learners of English as a foreign language, the gap between their vocabulary size and that of native speakers is usually very large, with many adult foreign learners of English having a vocabulary size of much less than 5000 word families in spite of having studied English for several years. (Paul Nation and Robert Waring. 1997. Vocabulary size, text coverage and word lists. In Schmitt, N. and M. McCarthy (eds.): Vocabulary: description, acquisition and pedagogy (pp. 6-19). Cambridge: Cambridge University Press.)'),
(156, 'en-GB', 'C2', 'For adult learners of English as a foreign language, the gap between their vocabulary size and that of native speakers is usually very large, with many adult foreign learners of English having a vocabulary size of much less than 5000 word families in spite of having studied English for several years. (Paul Nation and Robert Waring. 1997. Vocabulary size, text coverage and word lists. In Schmitt, N. and M. McCarthy (eds.): Vocabulary: description, acquisition and pedagogy (pp. 6-19). Cambridge: Cambridge University Press.)'),
(157, 'en-US', 'A1', 'For adult learners of English as a foreign language, the gap between their vocabulary size and that of native speakers is usually very large, with many adult foreign learners of English having a vocabulary size of much less than 5000 word families in spite of having studied English for several years. (Paul Nation and Robert Waring. 1997. Vocabulary size, text coverage and word lists. In Schmitt, N. and M. McCarthy (eds.): Vocabulary: description, acquisition and pedagogy (pp. 6-19). Cambridge: Cambridge University Press.)'),
(158, 'en-US', 'A2', 'For adult learners of English as a foreign language, the gap between their vocabulary size and that of native speakers is usually very large, with many adult foreign learners of English having a vocabulary size of much less than 5000 word families in spite of having studied English for several years. (Paul Nation and Robert Waring. 1997. Vocabulary size, text coverage and word lists. In Schmitt, N. and M. McCarthy (eds.): Vocabulary: description, acquisition and pedagogy (pp. 6-19). Cambridge: Cambridge University Press.)'),
(159, 'en-US', 'B1', 'For adult learners of English as a foreign language, the gap between their vocabulary size and that of native speakers is usually very large, with many adult foreign learners of English having a vocabulary size of much less than 5000 word families in spite of having studied English for several years. (Paul Nation and Robert Waring. 1997. Vocabulary size, text coverage and word lists. In Schmitt, N. and M. McCarthy (eds.): Vocabulary: description, acquisition and pedagogy (pp. 6-19). Cambridge: Cambridge University Press.)'),
(160, 'en-US', 'B2', 'For adult learners of English as a foreign language, the gap between their vocabulary size and that of native speakers is usually very large, with many adult foreign learners of English having a vocabulary size of much less than 5000 word families in spite of having studied English for several years. (Paul Nation and Robert Waring. 1997. Vocabulary size, text coverage and word lists. In Schmitt, N. and M. McCarthy (eds.): Vocabulary: description, acquisition and pedagogy (pp. 6-19). Cambridge: Cambridge University Press.)'),
(161, 'en-US', 'C1', 'For adult learners of English as a foreign language, the gap between their vocabulary size and that of native speakers is usually very large, with many adult foreign learners of English having a vocabulary size of much less than 5000 word families in spite of having studied English for several years. (Paul Nation and Robert Waring. 1997. Vocabulary size, text coverage and word lists. In Schmitt, N. and M. McCarthy (eds.): Vocabulary: description, acquisition and pedagogy (pp. 6-19). Cambridge: Cambridge University Press.)'),
(162, 'en-US', 'C2', 'For adult learners of English as a foreign language, the gap between their vocabulary size and that of native speakers is usually very large, with many adult foreign learners of English having a vocabulary size of much less than 5000 word families in spite of having studied English for several years. (Paul Nation and Robert Waring. 1997. Vocabulary size, text coverage and word lists. In Schmitt, N. and M. McCarthy (eds.): Vocabulary: description, acquisition and pedagogy (pp. 6-19). Cambridge: Cambridge University Press.)'),
(163, 'es-ES', 'A1', 'For adult learners of English as a foreign language, the gap between their vocabulary size and that of native speakers is usually very large, with many adult foreign learners of English having a vocabulary size of much less than 5000 word families in spite of having studied English for several years. (Paul Nation and Robert Waring. 1997. Vocabulary size, text coverage and word lists. In Schmitt, N. and M. McCarthy (eds.): Vocabulary: description, acquisition and pedagogy (pp. 6-19). Cambridge: Cambridge University Press.)'),
(164, 'es-ES', 'A2', 'For adult learners of English as a foreign language, the gap between their vocabulary size and that of native speakers is usually very large, with many adult foreign learners of English having a vocabulary size of much less than 5000 word families in spite of having studied English for several years. (Paul Nation and Robert Waring. 1997. Vocabulary size, text coverage and word lists. In Schmitt, N. and M. McCarthy (eds.): Vocabulary: description, acquisition and pedagogy (pp. 6-19). Cambridge: Cambridge University Press.)'),
(165, 'es-ES', 'B1', 'For adult learners of English as a foreign language, the gap between their vocabulary size and that of native speakers is usually very large, with many adult foreign learners of English having a vocabulary size of much less than 5000 word families in spite of having studied English for several years. (Paul Nation and Robert Waring. 1997. Vocabulary size, text coverage and word lists. In Schmitt, N. and M. McCarthy (eds.): Vocabulary: description, acquisition and pedagogy (pp. 6-19). Cambridge: Cambridge University Press.)'),
(166, 'es-ES', 'B2', 'For adult learners of English as a foreign language, the gap between their vocabulary size and that of native speakers is usually very large, with many adult foreign learners of English having a vocabulary size of much less than 5000 word families in spite of having studied English for several years. (Paul Nation and Robert Waring. 1997. Vocabulary size, text coverage and word lists. In Schmitt, N. and M. McCarthy (eds.): Vocabulary: description, acquisition and pedagogy (pp. 6-19). Cambridge: Cambridge University Press.)'),
(167, 'es-ES', 'C1', 'For adult learners of English as a foreign language, the gap between their vocabulary size and that of native speakers is usually very large, with many adult foreign learners of English having a vocabulary size of much less than 5000 word families in spite of having studied English for several years. (Paul Nation and Robert Waring. 1997. Vocabulary size, text coverage and word lists. In Schmitt, N. and M. McCarthy (eds.): Vocabulary: description, acquisition and pedagogy (pp. 6-19). Cambridge: Cambridge University Press.)'),
(168, 'es-ES', 'C2', 'For adult learners of English as a foreign language, the gap between their vocabulary size and that of native speakers is usually very large, with many adult foreign learners of English having a vocabulary size of much less than 5000 word families in spite of having studied English for several years. (Paul Nation and Robert Waring. 1997. Vocabulary size, text coverage and word lists. In Schmitt, N. and M. McCarthy (eds.): Vocabulary: description, acquisition and pedagogy (pp. 6-19). Cambridge: Cambridge University Press.)'),
(169, 'fr-FR', 'A1', 'For adult learners of English as a foreign language, the gap between their vocabulary size and that of native speakers is usually very large, with many adult foreign learners of English having a vocabulary size of much less than 5000 word families in spite of having studied English for several years. (Paul Nation and Robert Waring. 1997. Vocabulary size, text coverage and word lists. In Schmitt, N. and M. McCarthy (eds.): Vocabulary: description, acquisition and pedagogy (pp. 6-19). Cambridge: Cambridge University Press.)'),
(170, 'fr-FR', 'A2', 'For adult learners of English as a foreign language, the gap between their vocabulary size and that of native speakers is usually very large, with many adult foreign learners of English having a vocabulary size of much less than 5000 word families in spite of having studied English for several years. (Paul Nation and Robert Waring. 1997. Vocabulary size, text coverage and word lists. In Schmitt, N. and M. McCarthy (eds.): Vocabulary: description, acquisition and pedagogy (pp. 6-19). Cambridge: Cambridge University Press.)'),
(171, 'fr-FR', 'B1', 'For adult learners of English as a foreign language, the gap between their vocabulary size and that of native speakers is usually very large, with many adult foreign learners of English having a vocabulary size of much less than 5000 word families in spite of having studied English for several years. (Paul Nation and Robert Waring. 1997. Vocabulary size, text coverage and word lists. In Schmitt, N. and M. McCarthy (eds.): Vocabulary: description, acquisition and pedagogy (pp. 6-19). Cambridge: Cambridge University Press.)'),
(172, 'fr-FR', 'B2', 'For adult learners of English as a foreign language, the gap between their vocabulary size and that of native speakers is usually very large, with many adult foreign learners of English having a vocabulary size of much less than 5000 word families in spite of having studied English for several years. (Paul Nation and Robert Waring. 1997. Vocabulary size, text coverage and word lists. In Schmitt, N. and M. McCarthy (eds.): Vocabulary: description, acquisition and pedagogy (pp. 6-19). Cambridge: Cambridge University Press.)'),
(173, 'fr-FR', 'C1', 'For adult learners of English as a foreign language, the gap between their vocabulary size and that of native speakers is usually very large, with many adult foreign learners of English having a vocabulary size of much less than 5000 word families in spite of having studied English for several years. (Paul Nation and Robert Waring. 1997. Vocabulary size, text coverage and word lists. In Schmitt, N. and M. McCarthy (eds.): Vocabulary: description, acquisition and pedagogy (pp. 6-19). Cambridge: Cambridge University Press.)'),
(174, 'fr-FR', 'C2', 'For adult learners of English as a foreign language, the gap between their vocabulary size and that of native speakers is usually very large, with many adult foreign learners of English having a vocabulary size of much less than 5000 word families in spite of having studied English for several years. (Paul Nation and Robert Waring. 1997. Vocabulary size, text coverage and word lists. In Schmitt, N. and M. McCarthy (eds.): Vocabulary: description, acquisition and pedagogy (pp. 6-19). Cambridge: Cambridge University Press.)'),
(175, 'it-IT', 'A1', 'For adult learners of English as a foreign language, the gap between their vocabulary size and that of native speakers is usually very large, with many adult foreign learners of English having a vocabulary size of much less than 5000 word families in spite of having studied English for several years. (Paul Nation and Robert Waring. 1997. Vocabulary size, text coverage and word lists. In Schmitt, N. and M. McCarthy (eds.): Vocabulary: description, acquisition and pedagogy (pp. 6-19). Cambridge: Cambridge University Press.)'),
(176, 'it-IT', 'A2', 'For adult learners of English as a foreign language, the gap between their vocabulary size and that of native speakers is usually very large, with many adult foreign learners of English having a vocabulary size of much less than 5000 word families in spite of having studied English for several years. (Paul Nation and Robert Waring. 1997. Vocabulary size, text coverage and word lists. In Schmitt, N. and M. McCarthy (eds.): Vocabulary: description, acquisition and pedagogy (pp. 6-19). Cambridge: Cambridge University Press.)'),
(177, 'it-IT', 'B1', 'For adult learners of English as a foreign language, the gap between their vocabulary size and that of native speakers is usually very large, with many adult foreign learners of English having a vocabulary size of much less than 5000 word families in spite of having studied English for several years. (Paul Nation and Robert Waring. 1997. Vocabulary size, text coverage and word lists. In Schmitt, N. and M. McCarthy (eds.): Vocabulary: description, acquisition and pedagogy (pp. 6-19). Cambridge: Cambridge University Press.)'),
(178, 'it-IT', 'B2', 'For adult learners of English as a foreign language, the gap between their vocabulary size and that of native speakers is usually very large, with many adult foreign learners of English having a vocabulary size of much less than 5000 word families in spite of having studied English for several years. (Paul Nation and Robert Waring. 1997. Vocabulary size, text coverage and word lists. In Schmitt, N. and M. McCarthy (eds.): Vocabulary: description, acquisition and pedagogy (pp. 6-19). Cambridge: Cambridge University Press.)'),
(179, 'it-IT', 'C1', 'For adult learners of English as a foreign language, the gap between their vocabulary size and that of native speakers is usually very large, with many adult foreign learners of English having a vocabulary size of much less than 5000 word families in spite of having studied English for several years. (Paul Nation and Robert Waring. 1997. Vocabulary size, text coverage and word lists. In Schmitt, N. and M. McCarthy (eds.): Vocabulary: description, acquisition and pedagogy (pp. 6-19). Cambridge: Cambridge University Press.)'),
(180, 'it-IT', 'C2', 'For adult learners of English as a foreign language, the gap between their vocabulary size and that of native speakers is usually very large, with many adult foreign learners of English having a vocabulary size of much less than 5000 word families in spite of having studied English for several years. (Paul Nation and Robert Waring. 1997. Vocabulary size, text coverage and word lists. In Schmitt, N. and M. McCarthy (eds.): Vocabulary: description, acquisition and pedagogy (pp. 6-19). Cambridge: Cambridge University Press.)'),
(181, 'de-DE', 'A1', 'The most frequent 1000 word families cover 72% of the lexicon of English (as modelled by the Brown Corpus of English).'),
(182, 'de-DE', 'A2', 'The most frequent 1000 word families cover 72% of the lexicon of English (as modelled by the Brown Corpus of English).'),
(183, 'de-DE', 'B1', 'The most frequent 1000 word families cover 72% of the lexicon of English (as modelled by the Brown Corpus of English).'),
(184, 'de-DE', 'B2', 'The most frequent 1000 word families cover 72% of the lexicon of English (as modelled by the Brown Corpus of English).'),
(185, 'de-DE', 'C1', 'The most frequent 1000 word families cover 72% of the lexicon of English (as modelled by the Brown Corpus of English).'),
(186, 'de-DE', 'C2', 'The most frequent 1000 word families cover 72% of the lexicon of English (as modelled by the Brown Corpus of English).'),
(187, 'en-GB', 'A1', 'The most frequent 1000 word families cover 72% of the lexicon of English (as modelled by the Brown Corpus of English).'),
(188, 'en-GB', 'A2', 'The most frequent 1000 word families cover 72% of the lexicon of English (as modelled by the Brown Corpus of English).'),
(189, 'en-GB', 'B1', 'The most frequent 1000 word families cover 72% of the lexicon of English (as modelled by the Brown Corpus of English).'),
(190, 'en-GB', 'B2', 'The most frequent 1000 word families cover 72% of the lexicon of English (as modelled by the Brown Corpus of English).'),
(191, 'en-GB', 'C1', 'The most frequent 1000 word families cover 72% of the lexicon of English (as modelled by the Brown Corpus of English).'),
(192, 'en-GB', 'C2', 'The most frequent 1000 word families cover 72% of the lexicon of English (as modelled by the Brown Corpus of English).'),
(193, 'en-US', 'A1', 'The most frequent 1000 word families cover 72% of the lexicon of English (as modelled by the Brown Corpus of English).'),
(194, 'en-US', 'A2', 'The most frequent 1000 word families cover 72% of the lexicon of English (as modelled by the Brown Corpus of English).'),
(195, 'en-US', 'B1', 'The most frequent 1000 word families cover 72% of the lexicon of English (as modelled by the Brown Corpus of English).'),
(196, 'en-US', 'B2', 'The most frequent 1000 word families cover 72% of the lexicon of English (as modelled by the Brown Corpus of English).'),
(197, 'en-US', 'C1', 'The most frequent 1000 word families cover 72% of the lexicon of English (as modelled by the Brown Corpus of English).'),
(198, 'en-US', 'C2', 'The most frequent 1000 word families cover 72% of the lexicon of English (as modelled by the Brown Corpus of English).'),
(199, 'es-ES', 'A1', 'The most frequent 1000 word families cover 72% of the lexicon of English (as modelled by the Brown Corpus of English).'),
(200, 'es-ES', 'A2', 'The most frequent 1000 word families cover 72% of the lexicon of English (as modelled by the Brown Corpus of English).'),
(201, 'es-ES', 'B1', 'The most frequent 1000 word families cover 72% of the lexicon of English (as modelled by the Brown Corpus of English).'),
(202, 'es-ES', 'B2', 'The most frequent 1000 word families cover 72% of the lexicon of English (as modelled by the Brown Corpus of English).'),
(203, 'es-ES', 'C1', 'The most frequent 1000 word families cover 72% of the lexicon of English (as modelled by the Brown Corpus of English).'),
(204, 'es-ES', 'C2', 'The most frequent 1000 word families cover 72% of the lexicon of English (as modelled by the Brown Corpus of English).'),
(205, 'fr-FR', 'A1', 'The most frequent 1000 word families cover 72% of the lexicon of English (as modelled by the Brown Corpus of English).'),
(206, 'fr-FR', 'A2', 'The most frequent 1000 word families cover 72% of the lexicon of English (as modelled by the Brown Corpus of English).'),
(207, 'fr-FR', 'B1', 'The most frequent 1000 word families cover 72% of the lexicon of English (as modelled by the Brown Corpus of English).'),
(208, 'fr-FR', 'B2', 'The most frequent 1000 word families cover 72% of the lexicon of English (as modelled by the Brown Corpus of English).'),
(209, 'fr-FR', 'C1', 'The most frequent 1000 word families cover 72% of the lexicon of English (as modelled by the Brown Corpus of English).'),
(210, 'fr-FR', 'C2', 'The most frequent 1000 word families cover 72% of the lexicon of English (as modelled by the Brown Corpus of English).'),
(211, 'it-IT', 'A1', 'The most frequent 1000 word families cover 72% of the lexicon of English (as modelled by the Brown Corpus of English).'),
(212, 'it-IT', 'A2', 'The most frequent 1000 word families cover 72% of the lexicon of English (as modelled by the Brown Corpus of English).'),
(213, 'it-IT', 'B1', 'The most frequent 1000 word families cover 72% of the lexicon of English (as modelled by the Brown Corpus of English).'),
(214, 'it-IT', 'B2', 'The most frequent 1000 word families cover 72% of the lexicon of English (as modelled by the Brown Corpus of English).'),
(215, 'it-IT', 'C1', 'The most frequent 1000 word families cover 72% of the lexicon of English (as modelled by the Brown Corpus of English).'),
(216, 'it-IT', 'C2', 'The most frequent 1000 word families cover 72% of the lexicon of English (as modelled by the Brown Corpus of English).'),
(217, 'de-DE', 'A1', 'The most frequent 2000 word families cover 80% of the lexicon of English (as modelled by the Brown Corpus of English).'),
(218, 'de-DE', 'A2', 'The most frequent 2000 word families cover 80% of the lexicon of English (as modelled by the Brown Corpus of English).'),
(219, 'de-DE', 'B1', 'The most frequent 2000 word families cover 80% of the lexicon of English (as modelled by the Brown Corpus of English).'),
(220, 'de-DE', 'B2', 'The most frequent 2000 word families cover 80% of the lexicon of English (as modelled by the Brown Corpus of English).'),
(221, 'de-DE', 'C1', 'The most frequent 2000 word families cover 80% of the lexicon of English (as modelled by the Brown Corpus of English).'),
(222, 'de-DE', 'C2', 'The most frequent 2000 word families cover 80% of the lexicon of English (as modelled by the Brown Corpus of English).'),
(223, 'en-GB', 'A1', 'The most frequent 2000 word families cover 80% of the lexicon of English (as modelled by the Brown Corpus of English).'),
(224, 'en-GB', 'A2', 'The most frequent 2000 word families cover 80% of the lexicon of English (as modelled by the Brown Corpus of English).'),
(225, 'en-GB', 'B1', 'The most frequent 2000 word families cover 80% of the lexicon of English (as modelled by the Brown Corpus of English).'),
(226, 'en-GB', 'B2', 'The most frequent 2000 word families cover 80% of the lexicon of English (as modelled by the Brown Corpus of English).'),
(227, 'en-GB', 'C1', 'The most frequent 2000 word families cover 80% of the lexicon of English (as modelled by the Brown Corpus of English).'),
(228, 'en-GB', 'C2', 'The most frequent 2000 word families cover 80% of the lexicon of English (as modelled by the Brown Corpus of English).'),
(229, 'en-US', 'A1', 'The most frequent 2000 word families cover 80% of the lexicon of English (as modelled by the Brown Corpus of English).'),
(230, 'en-US', 'A2', 'The most frequent 2000 word families cover 80% of the lexicon of English (as modelled by the Brown Corpus of English).'),
(231, 'en-US', 'B1', 'The most frequent 2000 word families cover 80% of the lexicon of English (as modelled by the Brown Corpus of English).'),
(232, 'en-US', 'B2', 'The most frequent 2000 word families cover 80% of the lexicon of English (as modelled by the Brown Corpus of English).'),
(233, 'en-US', 'C1', 'The most frequent 2000 word families cover 80% of the lexicon of English (as modelled by the Brown Corpus of English).'),
(234, 'en-US', 'C2', 'The most frequent 2000 word families cover 80% of the lexicon of English (as modelled by the Brown Corpus of English).'),
(235, 'es-ES', 'A1', 'The most frequent 2000 word families cover 80% of the lexicon of English (as modelled by the Brown Corpus of English).'),
(236, 'es-ES', 'A2', 'The most frequent 2000 word families cover 80% of the lexicon of English (as modelled by the Brown Corpus of English).'),
(237, 'es-ES', 'B1', 'The most frequent 2000 word families cover 80% of the lexicon of English (as modelled by the Brown Corpus of English).'),
(238, 'es-ES', 'B2', 'The most frequent 2000 word families cover 80% of the lexicon of English (as modelled by the Brown Corpus of English).'),
(239, 'es-ES', 'C1', 'The most frequent 2000 word families cover 80% of the lexicon of English (as modelled by the Brown Corpus of English).'),
(240, 'es-ES', 'C2', 'The most frequent 2000 word families cover 80% of the lexicon of English (as modelled by the Brown Corpus of English).'),
(241, 'fr-FR', 'A1', 'The most frequent 2000 word families cover 80% of the lexicon of English (as modelled by the Brown Corpus of English).'),
(242, 'fr-FR', 'A2', 'The most frequent 2000 word families cover 80% of the lexicon of English (as modelled by the Brown Corpus of English).'),
(243, 'fr-FR', 'B1', 'The most frequent 2000 word families cover 80% of the lexicon of English (as modelled by the Brown Corpus of English).'),
(244, 'fr-FR', 'B2', 'The most frequent 2000 word families cover 80% of the lexicon of English (as modelled by the Brown Corpus of English).'),
(245, 'fr-FR', 'C1', 'The most frequent 2000 word families cover 80% of the lexicon of English (as modelled by the Brown Corpus of English).'),
(246, 'fr-FR', 'C2', 'The most frequent 2000 word families cover 80% of the lexicon of English (as modelled by the Brown Corpus of English).'),
(247, 'it-IT', 'A1', 'The most frequent 2000 word families cover 80% of the lexicon of English (as modelled by the Brown Corpus of English).'),
(248, 'it-IT', 'A2', 'The most frequent 2000 word families cover 80% of the lexicon of English (as modelled by the Brown Corpus of English).'),
(249, 'it-IT', 'B1', 'The most frequent 2000 word families cover 80% of the lexicon of English (as modelled by the Brown Corpus of English).'),
(250, 'it-IT', 'B2', 'The most frequent 2000 word families cover 80% of the lexicon of English (as modelled by the Brown Corpus of English).'),
(251, 'it-IT', 'C1', 'The most frequent 2000 word families cover 80% of the lexicon of English (as modelled by the Brown Corpus of English).'),
(252, 'it-IT', 'C2', 'The most frequent 2000 word families cover 80% of the lexicon of English (as modelled by the Brown Corpus of English).'),
(253, 'de-DE', 'A1', 'The most frequent 3000 word families cover 84% of the lexicon of English (as modelled by the Brown Corpus of English).'),
(254, 'de-DE', 'A2', 'The most frequent 3000 word families cover 84% of the lexicon of English (as modelled by the Brown Corpus of English).'),
(255, 'de-DE', 'B1', 'The most frequent 3000 word families cover 84% of the lexicon of English (as modelled by the Brown Corpus of English).'),
(256, 'de-DE', 'B2', 'The most frequent 3000 word families cover 84% of the lexicon of English (as modelled by the Brown Corpus of English).'),
(257, 'de-DE', 'C1', 'The most frequent 3000 word families cover 84% of the lexicon of English (as modelled by the Brown Corpus of English).'),
(258, 'de-DE', 'C2', 'The most frequent 3000 word families cover 84% of the lexicon of English (as modelled by the Brown Corpus of English).'),
(259, 'en-GB', 'A1', 'The most frequent 3000 word families cover 84% of the lexicon of English (as modelled by the Brown Corpus of English).'),
(260, 'en-GB', 'A2', 'The most frequent 3000 word families cover 84% of the lexicon of English (as modelled by the Brown Corpus of English).'),
(261, 'en-GB', 'B1', 'The most frequent 3000 word families cover 84% of the lexicon of English (as modelled by the Brown Corpus of English).'),
(262, 'en-GB', 'B2', 'The most frequent 3000 word families cover 84% of the lexicon of English (as modelled by the Brown Corpus of English).'),
(263, 'en-GB', 'C1', 'The most frequent 3000 word families cover 84% of the lexicon of English (as modelled by the Brown Corpus of English).'),
(264, 'en-GB', 'C2', 'The most frequent 3000 word families cover 84% of the lexicon of English (as modelled by the Brown Corpus of English).'),
(265, 'en-US', 'A1', 'The most frequent 3000 word families cover 84% of the lexicon of English (as modelled by the Brown Corpus of English).'),
(266, 'en-US', 'A2', 'The most frequent 3000 word families cover 84% of the lexicon of English (as modelled by the Brown Corpus of English).'),
(267, 'en-US', 'B1', 'The most frequent 3000 word families cover 84% of the lexicon of English (as modelled by the Brown Corpus of English).'),
(268, 'en-US', 'B2', 'The most frequent 3000 word families cover 84% of the lexicon of English (as modelled by the Brown Corpus of English).'),
(269, 'en-US', 'C1', 'The most frequent 3000 word families cover 84% of the lexicon of English (as modelled by the Brown Corpus of English).'),
(270, 'en-US', 'C2', 'The most frequent 3000 word families cover 84% of the lexicon of English (as modelled by the Brown Corpus of English).'),
(271, 'es-ES', 'A1', 'The most frequent 3000 word families cover 84% of the lexicon of English (as modelled by the Brown Corpus of English).'),
(272, 'es-ES', 'A2', 'The most frequent 3000 word families cover 84% of the lexicon of English (as modelled by the Brown Corpus of English).'),
(273, 'es-ES', 'B1', 'The most frequent 3000 word families cover 84% of the lexicon of English (as modelled by the Brown Corpus of English).'),
(274, 'es-ES', 'B2', 'The most frequent 3000 word families cover 84% of the lexicon of English (as modelled by the Brown Corpus of English).'),
(275, 'es-ES', 'C1', 'The most frequent 3000 word families cover 84% of the lexicon of English (as modelled by the Brown Corpus of English).'),
(276, 'es-ES', 'C2', 'The most frequent 3000 word families cover 84% of the lexicon of English (as modelled by the Brown Corpus of English).'),
(277, 'fr-FR', 'A1', 'The most frequent 3000 word families cover 84% of the lexicon of English (as modelled by the Brown Corpus of English).'),
(278, 'fr-FR', 'A2', 'The most frequent 3000 word families cover 84% of the lexicon of English (as modelled by the Brown Corpus of English).'),
(279, 'fr-FR', 'B1', 'The most frequent 3000 word families cover 84% of the lexicon of English (as modelled by the Brown Corpus of English).'),
(280, 'fr-FR', 'B2', 'The most frequent 3000 word families cover 84% of the lexicon of English (as modelled by the Brown Corpus of English).'),
(281, 'fr-FR', 'C1', 'The most frequent 3000 word families cover 84% of the lexicon of English (as modelled by the Brown Corpus of English).'),
(282, 'fr-FR', 'C2', 'The most frequent 3000 word families cover 84% of the lexicon of English (as modelled by the Brown Corpus of English).'),
(283, 'it-IT', 'A1', 'The most frequent 3000 word families cover 84% of the lexicon of English (as modelled by the Brown Corpus of English).'),
(284, 'it-IT', 'A2', 'The most frequent 3000 word families cover 84% of the lexicon of English (as modelled by the Brown Corpus of English).'),
(285, 'it-IT', 'B1', 'The most frequent 3000 word families cover 84% of the lexicon of English (as modelled by the Brown Corpus of English).'),
(286, 'it-IT', 'B2', 'The most frequent 3000 word families cover 84% of the lexicon of English (as modelled by the Brown Corpus of English).'),
(287, 'it-IT', 'C1', 'The most frequent 3000 word families cover 84% of the lexicon of English (as modelled by the Brown Corpus of English).'),
(288, 'it-IT', 'C2', 'The most frequent 3000 word families cover 84% of the lexicon of English (as modelled by the Brown Corpus of English).'),
(289, 'de-DE', 'A1', 'The most frequent 4000 word families cover 87% of the lexicon of English (as modelled by the Brown Corpus of English).'),
(290, 'de-DE', 'A2', 'The most frequent 4000 word families cover 87% of the lexicon of English (as modelled by the Brown Corpus of English).'),
(291, 'de-DE', 'B1', 'The most frequent 4000 word families cover 87% of the lexicon of English (as modelled by the Brown Corpus of English).'),
(292, 'de-DE', 'B2', 'The most frequent 4000 word families cover 87% of the lexicon of English (as modelled by the Brown Corpus of English).'),
(293, 'de-DE', 'C1', 'The most frequent 4000 word families cover 87% of the lexicon of English (as modelled by the Brown Corpus of English).'),
(294, 'de-DE', 'C2', 'The most frequent 4000 word families cover 87% of the lexicon of English (as modelled by the Brown Corpus of English).'),
(295, 'en-GB', 'A1', 'The most frequent 4000 word families cover 87% of the lexicon of English (as modelled by the Brown Corpus of English).'),
(296, 'en-GB', 'A2', 'The most frequent 4000 word families cover 87% of the lexicon of English (as modelled by the Brown Corpus of English).'),
(297, 'en-GB', 'B1', 'The most frequent 4000 word families cover 87% of the lexicon of English (as modelled by the Brown Corpus of English).'),
(298, 'en-GB', 'B2', 'The most frequent 4000 word families cover 87% of the lexicon of English (as modelled by the Brown Corpus of English).'),
(299, 'en-GB', 'C1', 'The most frequent 4000 word families cover 87% of the lexicon of English (as modelled by the Brown Corpus of English).'),
(300, 'en-GB', 'C2', 'The most frequent 4000 word families cover 87% of the lexicon of English (as modelled by the Brown Corpus of English).'),
(301, 'en-US', 'A1', 'The most frequent 4000 word families cover 87% of the lexicon of English (as modelled by the Brown Corpus of English).'),
(302, 'en-US', 'A2', 'The most frequent 4000 word families cover 87% of the lexicon of English (as modelled by the Brown Corpus of English).'),
(303, 'en-US', 'B1', 'The most frequent 4000 word families cover 87% of the lexicon of English (as modelled by the Brown Corpus of English).'),
(304, 'en-US', 'B2', 'The most frequent 4000 word families cover 87% of the lexicon of English (as modelled by the Brown Corpus of English).'),
(305, 'en-US', 'C1', 'The most frequent 4000 word families cover 87% of the lexicon of English (as modelled by the Brown Corpus of English).'),
(306, 'en-US', 'C2', 'The most frequent 4000 word families cover 87% of the lexicon of English (as modelled by the Brown Corpus of English).'),
(307, 'es-ES', 'A1', 'The most frequent 4000 word families cover 87% of the lexicon of English (as modelled by the Brown Corpus of English).'),
(308, 'es-ES', 'A2', 'The most frequent 4000 word families cover 87% of the lexicon of English (as modelled by the Brown Corpus of English).'),
(309, 'es-ES', 'B1', 'The most frequent 4000 word families cover 87% of the lexicon of English (as modelled by the Brown Corpus of English).'),
(310, 'es-ES', 'B2', 'The most frequent 4000 word families cover 87% of the lexicon of English (as modelled by the Brown Corpus of English).'),
(311, 'es-ES', 'C1', 'The most frequent 4000 word families cover 87% of the lexicon of English (as modelled by the Brown Corpus of English).'),
(312, 'es-ES', 'C2', 'The most frequent 4000 word families cover 87% of the lexicon of English (as modelled by the Brown Corpus of English).'),
(313, 'fr-FR', 'A1', 'The most frequent 4000 word families cover 87% of the lexicon of English (as modelled by the Brown Corpus of English).'),
(314, 'fr-FR', 'A2', 'The most frequent 4000 word families cover 87% of the lexicon of English (as modelled by the Brown Corpus of English).'),
(315, 'fr-FR', 'B1', 'The most frequent 4000 word families cover 87% of the lexicon of English (as modelled by the Brown Corpus of English).'),
(316, 'fr-FR', 'B2', 'The most frequent 4000 word families cover 87% of the lexicon of English (as modelled by the Brown Corpus of English).'),
(317, 'fr-FR', 'C1', 'The most frequent 4000 word families cover 87% of the lexicon of English (as modelled by the Brown Corpus of English).'),
(318, 'fr-FR', 'C2', 'The most frequent 4000 word families cover 87% of the lexicon of English (as modelled by the Brown Corpus of English).'),
(319, 'it-IT', 'A1', 'The most frequent 4000 word families cover 87% of the lexicon of English (as modelled by the Brown Corpus of English).'),
(320, 'it-IT', 'A2', 'The most frequent 4000 word families cover 87% of the lexicon of English (as modelled by the Brown Corpus of English).'),
(321, 'it-IT', 'B1', 'The most frequent 4000 word families cover 87% of the lexicon of English (as modelled by the Brown Corpus of English).'),
(322, 'it-IT', 'B2', 'The most frequent 4000 word families cover 87% of the lexicon of English (as modelled by the Brown Corpus of English).'),
(323, 'it-IT', 'C1', 'The most frequent 4000 word families cover 87% of the lexicon of English (as modelled by the Brown Corpus of English).'),
(324, 'it-IT', 'C2', 'The most frequent 4000 word families cover 87% of the lexicon of English (as modelled by the Brown Corpus of English).'),
(325, 'de-DE', 'A1', 'The most frequent 5000 word families cover 89% of the lexicon of English (as modelled by the Brown Corpus of English).'),
(326, 'de-DE', 'A2', 'The most frequent 5000 word families cover 89% of the lexicon of English (as modelled by the Brown Corpus of English).'),
(327, 'de-DE', 'B1', 'The most frequent 5000 word families cover 89% of the lexicon of English (as modelled by the Brown Corpus of English).'),
(328, 'de-DE', 'B2', 'The most frequent 5000 word families cover 89% of the lexicon of English (as modelled by the Brown Corpus of English).'),
(329, 'de-DE', 'C1', 'The most frequent 5000 word families cover 89% of the lexicon of English (as modelled by the Brown Corpus of English).'),
(330, 'de-DE', 'C2', 'The most frequent 5000 word families cover 89% of the lexicon of English (as modelled by the Brown Corpus of English).'),
(331, 'en-GB', 'A1', 'The most frequent 5000 word families cover 89% of the lexicon of English (as modelled by the Brown Corpus of English).'),
(332, 'en-GB', 'A2', 'The most frequent 5000 word families cover 89% of the lexicon of English (as modelled by the Brown Corpus of English).'),
(333, 'en-GB', 'B1', 'The most frequent 5000 word families cover 89% of the lexicon of English (as modelled by the Brown Corpus of English).'),
(334, 'en-GB', 'B2', 'The most frequent 5000 word families cover 89% of the lexicon of English (as modelled by the Brown Corpus of English).'),
(335, 'en-GB', 'C1', 'The most frequent 5000 word families cover 89% of the lexicon of English (as modelled by the Brown Corpus of English).'),
(336, 'en-GB', 'C2', 'The most frequent 5000 word families cover 89% of the lexicon of English (as modelled by the Brown Corpus of English).'),
(337, 'en-US', 'A1', 'The most frequent 5000 word families cover 89% of the lexicon of English (as modelled by the Brown Corpus of English).'),
(338, 'en-US', 'A2', 'The most frequent 5000 word families cover 89% of the lexicon of English (as modelled by the Brown Corpus of English).'),
(339, 'en-US', 'B1', 'The most frequent 5000 word families cover 89% of the lexicon of English (as modelled by the Brown Corpus of English).'),
(340, 'en-US', 'B2', 'The most frequent 5000 word families cover 89% of the lexicon of English (as modelled by the Brown Corpus of English).'),
(341, 'en-US', 'C1', 'The most frequent 5000 word families cover 89% of the lexicon of English (as modelled by the Brown Corpus of English).'),
(342, 'en-US', 'C2', 'The most frequent 5000 word families cover 89% of the lexicon of English (as modelled by the Brown Corpus of English).'),
(343, 'es-ES', 'A1', 'The most frequent 5000 word families cover 89% of the lexicon of English (as modelled by the Brown Corpus of English).'),
(344, 'es-ES', 'A2', 'The most frequent 5000 word families cover 89% of the lexicon of English (as modelled by the Brown Corpus of English).'),
(345, 'es-ES', 'B1', 'The most frequent 5000 word families cover 89% of the lexicon of English (as modelled by the Brown Corpus of English).'),
(346, 'es-ES', 'B2', 'The most frequent 5000 word families cover 89% of the lexicon of English (as modelled by the Brown Corpus of English).'),
(347, 'es-ES', 'C1', 'The most frequent 5000 word families cover 89% of the lexicon of English (as modelled by the Brown Corpus of English).'),
(348, 'es-ES', 'C2', 'The most frequent 5000 word families cover 89% of the lexicon of English (as modelled by the Brown Corpus of English).'),
(349, 'fr-FR', 'A1', 'The most frequent 5000 word families cover 89% of the lexicon of English (as modelled by the Brown Corpus of English).'),
(350, 'fr-FR', 'A2', 'The most frequent 5000 word families cover 89% of the lexicon of English (as modelled by the Brown Corpus of English).'),
(351, 'fr-FR', 'B1', 'The most frequent 5000 word families cover 89% of the lexicon of English (as modelled by the Brown Corpus of English).'),
(352, 'fr-FR', 'B2', 'The most frequent 5000 word families cover 89% of the lexicon of English (as modelled by the Brown Corpus of English).'),
(353, 'fr-FR', 'C1', 'The most frequent 5000 word families cover 89% of the lexicon of English (as modelled by the Brown Corpus of English).'),
(354, 'fr-FR', 'C2', 'The most frequent 5000 word families cover 89% of the lexicon of English (as modelled by the Brown Corpus of English).'),
(355, 'it-IT', 'A1', 'The most frequent 5000 word families cover 89% of the lexicon of English (as modelled by the Brown Corpus of English).'),
(356, 'it-IT', 'A2', 'The most frequent 5000 word families cover 89% of the lexicon of English (as modelled by the Brown Corpus of English).'),
(357, 'it-IT', 'B1', 'The most frequent 5000 word families cover 89% of the lexicon of English (as modelled by the Brown Corpus of English).'),
(358, 'it-IT', 'B2', 'The most frequent 5000 word families cover 89% of the lexicon of English (as modelled by the Brown Corpus of English).'),
(359, 'it-IT', 'C1', 'The most frequent 5000 word families cover 89% of the lexicon of English (as modelled by the Brown Corpus of English).'),
(360, 'it-IT', 'C2', 'The most frequent 5000 word families cover 89% of the lexicon of English (as modelled by the Brown Corpus of English).'),
(361, 'de-DE', 'A1', 'The most frequent 6000 word families cover 90% of the lexicon of English (as modelled by the Brown Corpus of English).'),
(362, 'de-DE', 'A2', 'The most frequent 6000 word families cover 90% of the lexicon of English (as modelled by the Brown Corpus of English).'),
(363, 'de-DE', 'B1', 'The most frequent 6000 word families cover 90% of the lexicon of English (as modelled by the Brown Corpus of English).'),
(364, 'de-DE', 'B2', 'The most frequent 6000 word families cover 90% of the lexicon of English (as modelled by the Brown Corpus of English).'),
(365, 'de-DE', 'C1', 'The most frequent 6000 word families cover 90% of the lexicon of English (as modelled by the Brown Corpus of English).'),
(366, 'de-DE', 'C2', 'The most frequent 6000 word families cover 90% of the lexicon of English (as modelled by the Brown Corpus of English).'),
(367, 'en-GB', 'A1', 'The most frequent 6000 word families cover 90% of the lexicon of English (as modelled by the Brown Corpus of English).'),
(368, 'en-GB', 'A2', 'The most frequent 6000 word families cover 90% of the lexicon of English (as modelled by the Brown Corpus of English).'),
(369, 'en-GB', 'B1', 'The most frequent 6000 word families cover 90% of the lexicon of English (as modelled by the Brown Corpus of English).'),
(370, 'en-GB', 'B2', 'The most frequent 6000 word families cover 90% of the lexicon of English (as modelled by the Brown Corpus of English).'),
(371, 'en-GB', 'C1', 'The most frequent 6000 word families cover 90% of the lexicon of English (as modelled by the Brown Corpus of English).'),
(372, 'en-GB', 'C2', 'The most frequent 6000 word families cover 90% of the lexicon of English (as modelled by the Brown Corpus of English).'),
(373, 'en-US', 'A1', 'The most frequent 6000 word families cover 90% of the lexicon of English (as modelled by the Brown Corpus of English).'),
(374, 'en-US', 'A2', 'The most frequent 6000 word families cover 90% of the lexicon of English (as modelled by the Brown Corpus of English).'),
(375, 'en-US', 'B1', 'The most frequent 6000 word families cover 90% of the lexicon of English (as modelled by the Brown Corpus of English).'),
(376, 'en-US', 'B2', 'The most frequent 6000 word families cover 90% of the lexicon of English (as modelled by the Brown Corpus of English).'),
(377, 'en-US', 'C1', 'The most frequent 6000 word families cover 90% of the lexicon of English (as modelled by the Brown Corpus of English).'),
(378, 'en-US', 'C2', 'The most frequent 6000 word families cover 90% of the lexicon of English (as modelled by the Brown Corpus of English).'),
(379, 'es-ES', 'A1', 'The most frequent 6000 word families cover 90% of the lexicon of English (as modelled by the Brown Corpus of English).'),
(380, 'es-ES', 'A2', 'The most frequent 6000 word families cover 90% of the lexicon of English (as modelled by the Brown Corpus of English).'),
(381, 'es-ES', 'B1', 'The most frequent 6000 word families cover 90% of the lexicon of English (as modelled by the Brown Corpus of English).'),
(382, 'es-ES', 'B2', 'The most frequent 6000 word families cover 90% of the lexicon of English (as modelled by the Brown Corpus of English).'),
(383, 'es-ES', 'C1', 'The most frequent 6000 word families cover 90% of the lexicon of English (as modelled by the Brown Corpus of English).'),
(384, 'es-ES', 'C2', 'The most frequent 6000 word families cover 90% of the lexicon of English (as modelled by the Brown Corpus of English).'),
(385, 'fr-FR', 'A1', 'The most frequent 6000 word families cover 90% of the lexicon of English (as modelled by the Brown Corpus of English).'),
(386, 'fr-FR', 'A2', 'The most frequent 6000 word families cover 90% of the lexicon of English (as modelled by the Brown Corpus of English).'),
(387, 'fr-FR', 'B1', 'The most frequent 6000 word families cover 90% of the lexicon of English (as modelled by the Brown Corpus of English).'),
(388, 'fr-FR', 'B2', 'The most frequent 6000 word families cover 90% of the lexicon of English (as modelled by the Brown Corpus of English).'),
(389, 'fr-FR', 'C1', 'The most frequent 6000 word families cover 90% of the lexicon of English (as modelled by the Brown Corpus of English).'),
(390, 'fr-FR', 'C2', 'The most frequent 6000 word families cover 90% of the lexicon of English (as modelled by the Brown Corpus of English).'),
(391, 'it-IT', 'A1', 'The most frequent 6000 word families cover 90% of the lexicon of English (as modelled by the Brown Corpus of English).'),
(392, 'it-IT', 'A2', 'The most frequent 6000 word families cover 90% of the lexicon of English (as modelled by the Brown Corpus of English).'),
(393, 'it-IT', 'B1', 'The most frequent 6000 word families cover 90% of the lexicon of English (as modelled by the Brown Corpus of English).'),
(394, 'it-IT', 'B2', 'The most frequent 6000 word families cover 90% of the lexicon of English (as modelled by the Brown Corpus of English).'),
(395, 'it-IT', 'C1', 'The most frequent 6000 word families cover 90% of the lexicon of English (as modelled by the Brown Corpus of English).'),
(396, 'it-IT', 'C2', 'The most frequent 6000 word families cover 90% of the lexicon of English (as modelled by the Brown Corpus of English).'),
(397, 'de-DE', 'A1', 'The most frequent 16000 word families cover 97% of the lexicon of English (as modelled by the Brown Corpus of English).'),
(398, 'de-DE', 'A2', 'The most frequent 16000 word families cover 97% of the lexicon of English (as modelled by the Brown Corpus of English).'),
(399, 'de-DE', 'B1', 'The most frequent 16000 word families cover 97% of the lexicon of English (as modelled by the Brown Corpus of English).'),
(400, 'de-DE', 'B2', 'The most frequent 16000 word families cover 97% of the lexicon of English (as modelled by the Brown Corpus of English).'),
(401, 'de-DE', 'C1', 'The most frequent 16000 word families cover 97% of the lexicon of English (as modelled by the Brown Corpus of English).'),
(402, 'de-DE', 'C2', 'The most frequent 16000 word families cover 97% of the lexicon of English (as modelled by the Brown Corpus of English).'),
(403, 'en-GB', 'A1', 'The most frequent 16000 word families cover 97% of the lexicon of English (as modelled by the Brown Corpus of English).'),
(404, 'en-GB', 'A2', 'The most frequent 16000 word families cover 97% of the lexicon of English (as modelled by the Brown Corpus of English).'),
(405, 'en-GB', 'B1', 'The most frequent 16000 word families cover 97% of the lexicon of English (as modelled by the Brown Corpus of English).'),
(406, 'en-GB', 'B2', 'The most frequent 16000 word families cover 97% of the lexicon of English (as modelled by the Brown Corpus of English).'),
(407, 'en-GB', 'C1', 'The most frequent 16000 word families cover 97% of the lexicon of English (as modelled by the Brown Corpus of English).'),
(408, 'en-GB', 'C2', 'The most frequent 16000 word families cover 97% of the lexicon of English (as modelled by the Brown Corpus of English).'),
(409, 'en-US', 'A1', 'The most frequent 16000 word families cover 97% of the lexicon of English (as modelled by the Brown Corpus of English).'),
(410, 'en-US', 'A2', 'The most frequent 16000 word families cover 97% of the lexicon of English (as modelled by the Brown Corpus of English).'),
(411, 'en-US', 'B1', 'The most frequent 16000 word families cover 97% of the lexicon of English (as modelled by the Brown Corpus of English).'),
(412, 'en-US', 'B2', 'The most frequent 16000 word families cover 97% of the lexicon of English (as modelled by the Brown Corpus of English).'),
(413, 'en-US', 'C1', 'The most frequent 16000 word families cover 97% of the lexicon of English (as modelled by the Brown Corpus of English).'),
(414, 'en-US', 'C2', 'The most frequent 16000 word families cover 97% of the lexicon of English (as modelled by the Brown Corpus of English).'),
(415, 'es-ES', 'A1', 'The most frequent 16000 word families cover 97% of the lexicon of English (as modelled by the Brown Corpus of English).'),
(416, 'es-ES', 'A2', 'The most frequent 16000 word families cover 97% of the lexicon of English (as modelled by the Brown Corpus of English).'),
(417, 'es-ES', 'B1', 'The most frequent 16000 word families cover 97% of the lexicon of English (as modelled by the Brown Corpus of English).'),
(418, 'es-ES', 'B2', 'The most frequent 16000 word families cover 97% of the lexicon of English (as modelled by the Brown Corpus of English).'),
(419, 'es-ES', 'C1', 'The most frequent 16000 word families cover 97% of the lexicon of English (as modelled by the Brown Corpus of English).'),
(420, 'es-ES', 'C2', 'The most frequent 16000 word families cover 97% of the lexicon of English (as modelled by the Brown Corpus of English).'),
(421, 'fr-FR', 'A1', 'The most frequent 16000 word families cover 97% of the lexicon of English (as modelled by the Brown Corpus of English).'),
(422, 'fr-FR', 'A2', 'The most frequent 16000 word families cover 97% of the lexicon of English (as modelled by the Brown Corpus of English).'),
(423, 'fr-FR', 'B1', 'The most frequent 16000 word families cover 97% of the lexicon of English (as modelled by the Brown Corpus of English).'),
(424, 'fr-FR', 'B2', 'The most frequent 16000 word families cover 97% of the lexicon of English (as modelled by the Brown Corpus of English).'),
(425, 'fr-FR', 'C1', 'The most frequent 16000 word families cover 97% of the lexicon of English (as modelled by the Brown Corpus of English).'),
(426, 'fr-FR', 'C2', 'The most frequent 16000 word families cover 97% of the lexicon of English (as modelled by the Brown Corpus of English).'),
(427, 'it-IT', 'A1', 'The most frequent 16000 word families cover 97% of the lexicon of English (as modelled by the Brown Corpus of English).'),
(428, 'it-IT', 'A2', 'The most frequent 16000 word families cover 97% of the lexicon of English (as modelled by the Brown Corpus of English).'),
(429, 'it-IT', 'B1', 'The most frequent 16000 word families cover 97% of the lexicon of English (as modelled by the Brown Corpus of English).'),
(430, 'it-IT', 'B2', 'The most frequent 16000 word families cover 97% of the lexicon of English (as modelled by the Brown Corpus of English).'),
(431, 'it-IT', 'C1', 'The most frequent 16000 word families cover 97% of the lexicon of English (as modelled by the Brown Corpus of English).'),
(432, 'it-IT', 'C2', 'The most frequent 16000 word families cover 97% of the lexicon of English (as modelled by the Brown Corpus of English).');

CREATE TABLE `activity_type` (
    `id` VARCHAR(50) NOT NULL COMMENT 'The activity type ID.',
    `description` VARCHAR(254) DEFAULT NULL COMMENT 'A concise description of the activity.',
    PRIMARY KEY (`id`)
) DEFAULT CHARSET=utf8;
INSERT INTO `activity_type` (`id`, `description`) VALUES ('DICT_SEARCH', 'The user performs a dictionary search.'), ('ESSAY', 'The user writes an essay.');

CREATE TABLE `activity` (
    `id` INT(11) NOT NULL AUTO_INCREMENT COMMENT 'The unique identifier of the activity.',
    `type` VARCHAR(50) NOT NULL COMMENT 'The activity type.',
    `level` ENUM('A1', 'A2', 'B1', 'B2', 'C1', 'C2') NOT NULL COMMENT 'The CEFR language level associated to the activity.',
    `language` VARCHAR(5) NOT NULL COMMENT 'The language of the activity.',
    `featured` BOOL NOT NULL COMMENT 'Whether this exercise is a featured one.',
    PRIMARY KEY (`id`),
    FOREIGN KEY (`language`) REFERENCES `languages` (`LCID`) ON UPDATE CASCADE,
    FOREIGN KEY (`type`) REFERENCES `activity_type` (`id`) ON UPDATE CASCADE
) AUTO_INCREMENT=22 DEFAULT CHARSET=utf8;
-- Remark: the dictionary search IDs must be hardcoded.
INSERT INTO `activity` (`id`, `type`, `level`, `language`, `featured`) VALUES (1, 'ESSAY', 'A2', 'en-GB', TRUE), (2, 'ESSAY', 'A2', 'en-GB', FALSE), (3, 'ESSAY', 'A2', 'en-GB', FALSE), (4, 'ESSAY', 'A2', 'en-GB', TRUE), (5, 'ESSAY', 'A2', 'en-GB', FALSE), (6, 'ESSAY', 'A2', 'en-GB', TRUE), (7, 'ESSAY', 'A2', 'en-GB', TRUE), (8, 'ESSAY', 'A2', 'en-GB', FALSE), (9, 'ESSAY', 'A2', 'en-GB', FALSE), (10, 'ESSAY', 'A2', 'en-GB', FALSE), (11, 'ESSAY', 'B2', 'en-GB', FALSE), (12, 'ESSAY', 'B2', 'en-GB', FALSE), (13, 'ESSAY', 'B2', 'en-GB', FALSE), (14, 'ESSAY', 'B2', 'en-GB', FALSE), (15, 'ESSAY', 'B2', 'en-GB', FALSE), (16, 'ESSAY', 'B2', 'en-GB', FALSE), (17, 'ESSAY', 'B2', 'en-GB', FALSE), (18, 'ESSAY', 'B2', 'en-GB', FALSE), (19, 'ESSAY', 'B2', 'en-GB', FALSE), (20, 'ESSAY', 'B2', 'en-GB', FALSE), (21, 'DICT_SEARCH', 'A1', 'en-GB', FALSE);

CREATE TABLE `activity_essay` (
    `id` INT(11) NOT NULL COMMENT 'The unique identifier of the essay activity.',
    `title` VARCHAR(50) NOT NULL COMMENT 'The essay title.',
    `description` VARCHAR(500) NOT NULL COMMENT 'A description of the task.',
    `tags` VARCHAR(254) NOT NULL COMMENT 'A comma-separated list of tags associated to the activity.',
    `minimumwords` INT(11) NOT NULL DEFAULT '80' COMMENT 'The minimum number of words required for the essay.',
    `maximumwords` INT(11) NOT NULL DEFAULT '250' COMMENT 'The maximum number of words allowed for the essay.',
    `text` TEXT COMMENT 'The text the user should read before performing the activity.',
    PRIMARY KEY (`id`),
    FOREIGN KEY (`id`) REFERENCES `activity` (`id`) ON UPDATE CASCADE
) DEFAULT CHARSET=utf8;
INSERT INTO `activity_essay` (`id`, `title`, `description`, `tags`, `minimumwords`, `maximumwords`, `text`) VALUES
(1, 'A New Friend', 'Write a short e-mail to your family telling them about your new friend. Describe him/her.', '', 80, 150, NULL),
(2, 'The Movies', 'Write a short text (around 150 words) describing your favorite movie. Why did you like it so much?', 'Scene description,Present simple,Present continuous,Past simple,Past continuous', 140, 170, NULL),
(3, 'A Place To Eat', 'Write a short text (around 120 words) in which you tell your friend about a new restaurant you discovered last week. Why did you like it?', 'Past simple,Food description,Place description,Comparative/superlative', 110, 130, NULL),
(4, 'Holidays', 'Write a short social media-like update talking about your last vacation. Where did you go? What was the place like?', 'Place description,Past simple,Past continuous,Comparative/superlative', 50, 200, NULL),
(5, 'Tourism', 'After reading the text, answer the e-mail by suggesting places to go in Rome.', 'Place description,Modal verbs (could/should),Comparative/superlative', 100, 150, 'Dear Colleagues,
Big news! I will be on holiday in Italy for two weeks next month. I will be back in the office a week after, although you can reach me by email at any time when I get home. In Rome, however, I will be completely offline, so I can enjoy my holiday! But don''t worry, I will send you all the photos when I get back. Have a good summer, everyone!
Best wishes,
Claire'),
(6, 'Your Job', 'Claire is going on holiday, and you cannot do all the work by yourself. Write one of your colleagues and ask him/her for help. Describe your work to him/her. How can he/she help you?', 'Job description,Present simple,Present continuous,Formal interaction', 100, 150, 'Dear Colleagues,
Big news! I will be on holiday in Italy for two weeks next month. I will be back in the office a week after, although you can reach me by email at any time when I get home. In Rome, however, I will be completely offline, so I can enjoy my holiday! But don''t worry, I will send you all the photos when I get back. Have a good summer, everyone!
Best wishes,
Claire'),
(7, 'Far Away Places', 'Choose a country. Send a letter to a friend and tell him/her about and why you want/don''t want to go there on holidays.', 'Exchange of information,Comparative/superlative', 100, 150, NULL),
(8, 'Shopping', 'When you go shopping, where do you usually go? Why? What do you usually buy?', 'Shopping vocabulary,Present simple,Comparative/superlative', 100, 150, NULL),
(9, 'Informal Communication I', 'Write a 150-word chat conversation (through Facebook, WhatsApp, Twitter, etc…) with a person you recently met. What''s his/her name? What is he/she like? What does he/she like to do in his/her spare time?', 'Natural communication,Simple description of people,Events or places,Present simple,Present continuous,Hobbies vocabulary', 140, 160, NULL),
(10, 'Informal Communication II', 'Imagine you have ten minutes to have a conversation between you and a famous actor/actress. What would you ask him/her? In 150 words, write down the conversation.', 'Natural communication,Usage of frequent expressions,Present simple,Past simple', 140, 160, NULL),
(11, 'Cultural Differences', 'Write a 200-word-long text on the cultural differences you may encounter when travelling to another country.', 'Ethnic vocabulary,Society description,Present simple,Present continuous,Past simple,Past continuous', 180, 220, NULL),
(12, 'Promotion', 'Write a 200-word letter to your boss asking for a promotion. Justify why you deserve this.', 'Job vocabulary,Detailed exposition of arguments,Formal communication', 180, 220, NULL),
(13, 'Protecting the environment', 'Are you environmentally-concerned? How do you contribute to a greener, more sustainable planet?', 'Vocabulary,Exposition of arguments,Description of personal experiences', 180, 220, NULL),
(14, 'Debating Culture', 'Your best friend thinks that once you have watched a movie based on a book, there is no point in reading it. Explain whether you agree or not and why in 200 words.', 'Development of arguments to support a point of view,Literature and movie vocabulary', 180, 220, NULL),
(15, 'Informal Communication II', 'Your best friend was left by his girlfriend a couple of months ago, but now he reaches out to you because she is trying to go back with him. You are speaking through WhatsApp. Write down the conversation in which he points out the reasons why he should go back with her and you try to convince him not to.', 'Natural language,Detailed exposition of different points of view,Advanced reasoning,Using details to support one''s argument', 180, 220, NULL),
(16, 'Fitness', 'Read the text and write a 200 word text on the benefits of exercise for our health.', 'Health vocabulary,Highlighting significant points and relevant supporting detail', 180, 220, 'The regional governments have finally decided to take on the responsibility of ensuring people''s health and fitness. This is why in the following weeks, citizens will be asked to enter the H&F program, which will allow them to use local leisure services completely free of charge.
Participants will get a membership card after registering into the service, so they''ll be able to use the facilities who have joined the program across the city at certain times of the day.
As of today, fifty leisure centers have signed up to the scheme. Each one of them must offer one hour minimum of swimming time and one hour of gym time to H&F members. Specific exercise classes such as Zumba or Body Combat will also be made available besides the standard facilities. H&F classes will also be offered in schools and community centers. There will be guided cycling, and other activities intended for public spaces such as gymkhanas and half-marathons in parks.
Since it was announced last week, up to 200,000 people have signed up for the program. 60% of the registered come from minority groups. The majority of them were not members of any sports club before registering, half of them suffered from obesity, and a large portion acknowledged being in poor health. This indicates that the program is effectively reaching people who need it most.'),
(17, 'Life Events', 'Describe the most important moment in your life in 200 words. Why was it so important? What happened?', 'Past perfect,Past perfect continuous,Passive,Narrative tenses,Detailed event description', 180, 220, NULL),
(18, 'Clean Energy or Clean Landscape?', 'Write a 200 word long text explaining your position in favor or against the construction of the wind farm.', 'Environment vocabulary,Detailed exposition of points of view,Modal verbs.', 180, 220, 'The Local Neighbors Association has voted to oppose to the state''s plan to build a wind farm on a 20-acre site next to the city''s suburbs, which is currently being used for agriculture purposes. The plan states that the area is to be surrounded by a 10ft-high steel fence, while the windmills will be around 25 feet high.
The association has already submitted an appeal to the local authority against the construction of the wind farm, and will hold a meeting to discuss this issue next week. Our objections will be presented before the board. A representative from the wind energy company will put forward the case for the development.
We encourage our fellow residents to voice their objections in the meeting. Some of the most common objections are listed below, although you may expose your own ideas:
1. The gorgeous views from the village and recreation ground will be blocked by the windmill towers and high fencing. Besides, the site may be considered brownfield once it has been built upon, thus making it an acceptable site for housing or industrial development. Thus, it does not comply with the local policy which states that developments must not adversely effect on the appearance or character of the landscape.
2. The recreation ground has recently undergone major reforms including a perimeter running track, new playground equipment and seating. It is being heavily used by families, sports teams and dog walkers, and is regularly used for village events. The massive building project will surely cause several disturbances and annoyances to residents intending to use them. Thus, the construction goes against the National Planning Policy Framework which requires developments to promote high quality public space and encourage the active and continual use of public areas.
3. There has been no assessment yet of the extent to which noise from the windmills will affect the residents, but we may rightfully assume that such noise will cause deep disturbances in the neighborhood.'),
(19, 'Proposing Alternatives', 'Choose one of the most common objections and propose a possible solution.', 'Evaluation of different problems to propose solutions,Detailed exposition of points of view,Conditional tenses', 180, 220, 'The Local Neighbors Association has voted to oppose to the state''s plan to build a wind farm on a 20-acre site next to the city''s suburbs, which is currently being used for agriculture purposes. The plan states that the area is to be surrounded by a 10ft-high steel fence, while the windmills will be around 25 feet high.
The association has already submitted an appeal to the local authority against the construction of the wind farm, and will hold a meeting to discuss this issue next week. Our objections will be presented before the board. A representative from the wind energy company will put forward the case for the development.
We encourage our fellow residents to voice their objections in the meeting. Some of the most common objections are listed below, although you may expose your own ideas:
1. The gorgeous views from the village and recreation ground will be blocked by the windmill towers and high fencing. Besides, the site may be considered brownfield once it has been built upon, thus making it an acceptable site for housing or industrial development. Thus, it does not comply with the local policy which states that developments must not adversely effect on the appearance or character of the landscape.
2. The recreation ground has recently undergone major reforms including a perimeter running track, new playground equipment and seating. It is being heavily used by families, sports teams and dog walkers, and is regularly used for village events. The massive building project will surely cause several disturbances and annoyances to residents intending to use them. Thus, the construction goes against the National Planning Policy Framework which requires developments to promote high quality public space and encourage the active and continual use of public areas.
3. There has been no assessment yet of the extent to which noise from the windmills will affect the residents, but we may rightfully assume that such noise will cause deep disturbances in the neighborhood.'),
(20, 'Informal Communication I', 'During a dinner with your family, you have a debate with your brother/sister about whether modern films are better or worse than classic movies. Write down the conversation explaining both points.', 'Natural language,Detailed exposition of different points of view,Advanced reasoning,Using details to support one''s argument', 180, 220, NULL);

CREATE TABLE `useractivities` (
    `user` VARCHAR(254) NOT NULL COMMENT 'The user who did the activity.',
    `activity` INT(11) NOT NULL COMMENT 'The activity done by the user.',
    PRIMARY KEY (`user`,`activity`),
    FOREIGN KEY (`activity`) REFERENCES `activity` (`id`) ON UPDATE CASCADE,
    FOREIGN KEY (`user`) REFERENCES `users` (`email`) ON DELETE CASCADE ON UPDATE CASCADE
) DEFAULT CHARSET=utf8;

CREATE TABLE `useractivity_essay` (
    `user` VARCHAR(254) NOT NULL COMMENT 'The user who did the activity.',
    `activity` INT(11) NOT NULL COMMENT 'The activity done by the user.',
    `text` TEXT NOT NULL COMMENT 'The development of the activity.',
    `timestamp` DATETIME NOT NULL COMMENT 'The time and date the activity was submitted.',
    `passed` BOOL COMMENT 'Whether the text was deemed satisfactory or not. A NULL value denotes that the exercise was not graded',
    PRIMARY KEY (`user`,`activity`),
    FOREIGN KEY (`activity`) REFERENCES `useractivities` (`activity`) ON DELETE CASCADE ON UPDATE CASCADE,
    FOREIGN KEY (`user`) REFERENCES `useractivities` (`user`) ON DELETE CASCADE ON UPDATE CASCADE
) DEFAULT CHARSET=utf8;

CREATE TABLE `useractivity_dictionarysearch` (
    `user` VARCHAR(254) NOT NULL COMMENT 'The user who did the activity.',
    `activity` INT(11) NOT NULL COMMENT 'The activity done by the user.',
    `word` VARCHAR(200) NOT NULL COMMENT 'The word that was searched for.',
    `timestamp` DATETIME NOT NULL COMMENT 'The time and date the search was performed at.',
    PRIMARY KEY (`user`,`activity`,`word`,`timestamp`),
    FOREIGN KEY (`activity`) REFERENCES `useractivities` (`activity`) ON DELETE CASCADE ON UPDATE CASCADE,
    FOREIGN KEY (`user`) REFERENCES `useractivities` (`user`) ON DELETE CASCADE ON UPDATE CASCADE
) DEFAULT CHARSET=utf8;

CREATE TABLE `oauth_clients` (
    `client_id` VARCHAR(80) NOT NULL COMMENT 'A unique client identifier',
    `app_name` VARCHAR(250) NOT NULL COMMENT 'The application name.',
    `client_secret` VARCHAR(80) COMMENT 'Used to secure Client Credentials Grant',
    `redirect_uri` VARCHAR(2000) NOT NULL COMMENT 'Redirect URI used for Authorization Grant',
    `grant_types` VARCHAR(80) COMMENT 'Space-delimited list of permitted grant types',
    `scope` VARCHAR(100) COMMENT 'Space-delimited list of permitted scopes',
    `user_id` VARCHAR(254) COMMENT 'OAUTH_USERS.USER_ID',
    PRIMARY KEY (`client_id`),
    FOREIGN KEY (`user_id`) REFERENCES `users`(`email`)
    ON UPDATE CASCADE
    ON DELETE CASCADE
) DEFAULT CHARSET=utf8;
-- FIXME: fill in the OAuth 2.0 client secret below
INSERT INTO `oauth_clients` (client_id, app_name, client_secret, redirect_uri, grant_types, scope, user_id) VALUES ('1011a510829210912e6b9c63f4108e5b28fdc110e7dde792dfb1ee45524cc5c1f4a78', 'TellOP mobile app', 'FIXME', 'https://tellop.inf.um.es/oauth/2/success', 'authorization_code implicit refresh_token', 'basic dashboard exercises profile onlineresources', 'admin@tellop.eu');

CREATE TABLE `oauth_access_tokens` (
    `access_token` VARCHAR(40) NOT NULL COMMENT 'System generated access token',
    `client_id` VARCHAR(80) NOT NULL COMMENT 'OAUTH_CLIENTS.CLIENT_ID',
    `user_id` VARCHAR(254) NOT NULL COMMENT 'OAUTH_USERS.USER_ID',
    `expires` TIMESTAMP NOT NULL COMMENT 'When the token becomes invalid',
    `scope` VARCHAR(2000) COMMENT 'Space-delimited list of scopes token can access',
    PRIMARY KEY (`access_token`),
    FOREIGN KEY (`client_id`) REFERENCES `oauth_clients`(`client_id`),
    FOREIGN KEY (`user_id`) REFERENCES `users`(`email`)
    ON UPDATE CASCADE
    ON DELETE CASCADE
) DEFAULT CHARSET=utf8;

CREATE TABLE `oauth_authorization_codes` (
    `authorization_code` VARCHAR(40) NOT NULL COMMENT 'System generated authorization code',
    `client_id` VARCHAR(80) NOT NULL COMMENT 'OAUTH_CLIENTS.CLIENT_ID',
    `user_id` VARCHAR(254) NOT NULL COMMENT 'OAUTH_USERS.USER_ID',
    `redirect_uri` VARCHAR(2000) COMMENT 'URI to redirect user after authorization',
    `expires` TIMESTAMP NOT NULL COMMENT 'When the code becomes invalid',
    `scope` VARCHAR(2000) COMMENT 'Space-delimited list scopes that the code can request',
    `id_token` VARCHAR(1000) COMMENT 'Token for OpenID Connect',
    PRIMARY KEY (`authorization_code`),
    FOREIGN KEY (`client_id`) REFERENCES `oauth_clients`(`client_id`),
    FOREIGN KEY (`user_id`) REFERENCES `users`(`email`)
    ON UPDATE CASCADE
    ON DELETE CASCADE
) DEFAULT CHARSET=utf8;

-- TODO: add more relational integrity constraints
CREATE TABLE `oauth_jti` (
    `issuer` VARCHAR(80) NOT NULL,
    `subject` VARCHAR(80),
    `audience` VARCHAR(80),
    `expires` TIMESTAMP NOT NULL,
    `jti` VARCHAR(2000) NOT NULL
);

CREATE TABLE `oauth_public_keys` (
    `client_id` VARCHAR(80) NOT NULL,
    `public_key` VARCHAR(2000),
    `private_key` VARCHAR(2000),
    `encryption_algorithm` VARCHAR(100) DEFAULT 'RS256'
);

CREATE TABLE `oauth_jwt` (
    `client_id` VARCHAR(80) NOT NULL,
    `subject` VARCHAR(80),
    `public_key` VARCHAR(2000),
    PRIMARY KEY (`client_id`),
    FOREIGN KEY (`client_id`) REFERENCES `oauth_clients`(`client_id`)
    ON UPDATE CASCADE
    ON DELETE CASCADE
) DEFAULT CHARSET=utf8;

CREATE TABLE `oauth_refresh_tokens` (
    `refresh_token` VARCHAR(40) NOT NULL COMMENT 'System generated refresh token',
    `client_id` VARCHAR(80) NOT NULL COMMENT 'OAUTH_CLIENTS.CLIENT_ID',
    `user_id` VARCHAR(254) NOT NULL COMMENT 'OAUTH_USERS.USER_ID',
    `expires` TIMESTAMP NOT NULL COMMENT 'When the token becomes invalid',
    `scope` VARCHAR(2000) COMMENT 'Space-delimited list scopes token can access',
    PRIMARY KEY (`refresh_token`),
    FOREIGN KEY (`client_id`) REFERENCES `oauth_clients`(`client_id`),
    FOREIGN KEY (`user_id`) REFERENCES `users`(`email`)
    ON UPDATE CASCADE
    ON DELETE CASCADE
) DEFAULT CHARSET=utf8;

CREATE TABLE `oauth_scopes` (
    `scope` TEXT COMMENT 'Name of scope, without spaces',
    `is_default` BOOLEAN
) DEFAULT CHARSET=utf8;
