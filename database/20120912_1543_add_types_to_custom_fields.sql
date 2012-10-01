-- Add column for type
ALTER TABLE `custom_fields` ADD COLUMN `type` varchar(25) NOT NULL;

-- Set type column based on legacy fields
UPDATE custom_fields SET type = 'wysiwyg' WHERE show_editor = 1;
UPDATE custom_fields SET type = 'upload' WHERE show_upload = 1;
UPDATE custom_fields SET type = 'text' WHERE type = '' OR type IS NULL;

-- Remove legacy fields
ALTER TABLE `custom_fields` DROP COLUMN `show_editor`, DROP COLUMN `show_textbox`, DROP COLUMN `show_upload`;