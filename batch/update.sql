-- Tue Jun 12 21:39:35 JST 2018
ALTER TABLE `iwebshop_user` ADD COLUMN `seller_id` INT(11) NULL COMMENT '所属卖家ID' AFTER `sfz_image2`;
-- 2018-6-20 15:15:00
ALTER TABLE `iwebshop_user` 
ADD COLUMN `create_time` DATETIME DEFAULT CURRENT_TIMESTAMP COMMENT '创建时间' AFTER `seller_id` ,
ADD COLUMN `update_time` DATETIME ON UPDATE CURRENT_TIMESTAMP COMMENT '更新时间' AFTER `create_time` ;
