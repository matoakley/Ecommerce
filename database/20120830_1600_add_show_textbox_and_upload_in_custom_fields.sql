-- CREATE SHOW_TEXTBOX AND SHOW_UPLOAD IN CUSTOM FIELDS 
ALTER TABLE `custom_fields` ADD COLUMN `show_textbox` tinyint(1), ADD COLUMN `show_upload` tinyint(1);