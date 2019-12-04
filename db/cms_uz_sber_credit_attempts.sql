CREATE TABLE `cms_uz_sber_credit_attempts`(
`id_order` int unsigned NOT NULL,
`try_number` int unsigned NOT NULL DEFAULT '1',
`date` datetime NOT NULL,

`id_in_sber` varchar(36) NOT NULL DEFAULT '',
`form_url` text NOT NULL,
`error_code` tinyint unsigned NOT NULL DEFAULT '0',
`error_message` text NOT NULL,

PRIMARY KEY (`id_order`, `try_number`),
KEY `i_id_order` (`id_order`),
KEY `i_id_in_sber` (`id_in_sber`)
) DEFAULT CHARSET=utf8