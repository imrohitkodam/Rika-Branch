ALTER TABLE `#__jticketing_types` add column `ticket_startdate` datetime DEFAULT NULL after `desc`;
SET sql_mode = '';
UPDATE `#__jticketing_types` SET `ticket_enddate` = NULL WHERE `ticket_enddate` = '0000-00-00 00:00:00';