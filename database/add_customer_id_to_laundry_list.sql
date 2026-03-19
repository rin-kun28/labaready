-- Add customer_id column to laundry_list table
-- Run this SQL in phpMyAdmin to add the customer_id foreign key

ALTER TABLE `laundry_list` 
ADD COLUMN `customer_id` INT(11) NULL AFTER `id`,
ADD INDEX `fk_customer_id` (`customer_id`);

-- Optional: Add foreign key constraint (uncomment if you want strict referential integrity)
-- ALTER TABLE `laundry_list` 
-- ADD CONSTRAINT `fk_laundry_customer` 
-- FOREIGN KEY (`customer_id`) REFERENCES `customers`(`id`) 
-- ON DELETE SET NULL ON UPDATE CASCADE;
