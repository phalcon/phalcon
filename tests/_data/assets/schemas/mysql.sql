set foreign_key_checks = 0;

drop table if exists `album_photo`;
drop table if exists `album`;
drop table if exists `photo`;
drop table if exists `complex_default`;
drop table if exists `co_customers_defaults`;
drop table if exists `co_customers`;
drop table if exists `co_dialect`;
drop table if exists `fractal_dates`;
drop table if exists `co_invoices`;
drop table if exists `objects`;
drop table if exists `co_orders`;
drop table if exists `co_orders_x_products`;
drop table if exists `co_products`;
drop table if exists `co_rb_test_model`;
drop table if exists `co_setters`;
drop table if exists `co_sources`;
drop table if exists `table_with_uuid_primary`;
drop table if exists `stuff`;

create table `album` (
	`id`       int(11) UNSIGNED not null AUTO_INCREMENT,
	`name`     varchar(100)     not null collate 'utf8mb4_unicode_520_ci',
	`album_id` int(11) unsigned null default null,
	`photo_id` int(11) unsigned null default null COMMENT 'The ID of the featured photo',
	primary key (`id`) using BTREE,
	index `index_foreignkey_album_album` (`album_id`) using BTREE,
	index `album_ibfk_2` (`photo_id`) using BTREE,
	constraint `album_ibfk_1` foreign key (`album_id`) references `album` (`id`) on update cascade on delete cascade,
	constraint `album_ibfk_2` foreign key (`photo_id`) references `photo` (`id`) on update cascade on delete set null
);

create table `album_photo` (
	`id`       int(11) unsigned not null AUTO_INCREMENT,
	`photo_id` int(11) unsigned null default null,
	`album_id` int(11) unsigned null default null,
	`position` int(10) unsigned not null default '999999999',
	primary key (`id`) using BTREE,
	unique index `UQ_cadf1c545153612614511f15197cae7b6dacac97` (`album_id`, `photo_id`) using BTREE,
	index `index_foreignkey_album_photo_photo` (`photo_id`) using BTREE,
	index `index_foreignkey_album_photo_album` (`album_id`) using BTREE,
	constraint `c_fk_album_photo_album_id` foreign key (`album_id`) references `album` (`id`) on update cascade on delete cascade,
	constraint `c_fk_album_photo_photo_id` foreign key (`photo_id`) references `photo` (`id`) on update cascade on delete cascade
);

create table complex_default
(
    `id`           int(10) auto_increment primary key,
    `created`      TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated`      TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    `updated_null` TIMESTAMP NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP
);

create table co_customers_defaults
(
    `cst_id`          int(10) auto_increment primary key,
    `cst_status_flag` tinyint(1)   not null DEFAULT 1,
    `cst_name_last`   varchar(100) not null DEFAULT 'cst_default_lastName',
    `cst_name_first`  varchar(50)  not null DEFAULT 'cst_default_firstName'
);

create index co_customers_defaults_cst_status_flag_index
    on `co_customers_defaults` (`cst_status_flag`);

create index co_customers_defaults_cst_name_last_index
    on `co_customers_defaults` (`cst_name_last`);

create index co_customers_defaults_cst_name_first_index
    on `co_customers_defaults` (`cst_name_first`);

create table co_customers
(
    `cst_id`          int(10) auto_increment primary key,
    `cst_status_flag` tinyint(1)   null,
    `cst_name_last`   varchar(100) null,
    `cst_name_first`  varchar(50)  null
);

create index co_customers_cst_status_flag_index
    on `co_customers` (`cst_status_flag`);

create index co_customers_cst_name_last_index
    on `co_customers` (`cst_name_last`);

create index co_customers_cst_name_first_index
    on `co_customers` (`cst_name_first`);

create table co_dialect
(
  field_primary            int auto_increment primary key,
  field_blob               blob                                        null,
  field_binary             binary(10)                                  null,
  field_bit                bit(10)                                     null,
  field_bit_default        bit(10)       default b'1'                  null,
  field_bigint             bigint        unsigned                      null,
  field_bigint_default     bigint        default 1                     null,
  field_boolean            tinyint(1)    unsigned                      null,
  field_boolean_default    tinyint(1)    default 1                     null,
  field_char               char(10)                                    null,
  field_char_default       char(10)      default 'ABC'                 null,
  field_decimal            decimal(10,4)                               null,
  field_decimal_default    decimal(10,4) default 14.5678               null,
  field_enum               enum('xs', 's', 'm', 'l', 'xl', 'internal') null,
  field_integer            int(10)                                     null,
  field_integer_default    int(10)       default 1                     null,
  field_json               json                                        null,
  field_float              float(10,4)                                 null,
  field_float_default      float(10,4)   default 14.5678               null,
  field_date               date                                        null,
  field_date_default       date          default '2018-10-01'          null,
  field_datetime           datetime                                    null,
  field_datetime_default   datetime      default '2018-10-01 12:34:56' null,
  field_time               time                                        null,
  field_time_default       time          default '12:34:56'            null,
  field_timestamp          timestamp                                   null,
  field_timestamp_default  timestamp     default '2018-10-01 12:34:56' null,
  field_mediumint          mediumint(10) unsigned                      null,
  field_mediumint_default  mediumint(10) default 1                     null,
  field_smallint           smallint(10)  unsigned                      null,
  field_smallint_default   smallint(10)  default 1                     null,
  field_tinyint            tinyint(10)   unsigned                      null,
  field_tinyint_default    tinyint(10)   default 1                     null,
  field_longtext           longtext                                    null,
  field_mediumtext         mediumtext                                  null,
  field_tinytext           tinytext                                    null,
  field_text               text                                        null,
  field_varbinary          varbinary(10)                               null,
  field_varchar            varchar(10)                                 null,
  field_varchar_default    varchar(10)   default 'D'                   null,
  unique key dialect_table_unique (field_integer),
  key dialect_table_index (field_bigint),
  key dialect_table_two_fields (field_char, field_char_default)
);

create table fractal_dates
(
    `id`           int(10)      auto_increment primary key,
    `ftime`        time(2)      null,
    `fdatetime`    datetime(2)  null,
    `ftimestamp`   timestamp(2) null
);

create table co_invoices
(
    `inv_id`          int(10) auto_increment primary key,
    `inv_cst_id`      int(10)      null,
    `inv_status_flag` tinyint(1)   null,
    `inv_title`       varchar(100) null,
    `inv_total`       float(10, 2) null,
    `inv_created_at`  datetime     null
);

create index co_invoices_inv_cst_id_index
    on `co_invoices` (`inv_cst_id`);

create index co_invoices_inv_status_flag_index
    on `co_invoices` (`inv_status_flag`);

create index co_invoices_inv_created_at_index
    on `co_invoices` (`inv_created_at`);

create table `objects`
(
    `obj_id`   int(10) auto_increment primary key,
    `obj_name` varchar(100) not null,
    `obj_type` tinyint(3) unsigned not null
);

create table `co_orders` (
    `ord_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
    `ord_name` VARCHAR(70) NULL,
    PRIMARY KEY (`ord_id`)
);

create table `co_orders_x_products` (
  `oxp_ord_id` int(10) unsigned NOT NULL,
  `oxp_prd_id` int(10) unsigned NOT NULL,
  `oxp_quantity` int(10) unsigned NOT NULL,
  PRIMARY KEY (`oxp_ord_id`, `oxp_prd_id` )
);


create table `photo` (
	`id`                int(10) unsigned not null auto_increment,
	`date_uploaded`     datetime not null default current_timestamp(),
	`original_filename` text not null collate 'utf8mb4_unicode_520_ci',
	`path`              text not null collate 'utf8mb4_unicode_520_ci',
	`width`             smallint(11) unsigned not null,
	`height`            smallint(11) unsigned not null,
	`thumb_path`        text not null collate 'utf8mb4_unicode_520_ci',
	`thumb_width`       smallint(5) unsigned not null,
	`thumb_height`      smallint(5) unsigned not null,
	`display_path`      text not null collate 'utf8mb4_unicode_520_ci',
	`display_width`     smallint(5) unsigned not null,
	`display_height`    smallint(5) unsigned not null,
	`mime_type`         VARCHAR(255) not null collate 'utf8mb4_unicode_520_ci',
	`filesize`          int(11) unsigned NULL DEFAULT NULL comment 'In bytes',
	`phash`             bigint(255) unsigned not null,
	`battles`           int(10) unsigned not null DEFAULT '0',
	`wins`              int(10) unsigned not null DEFAULT '0',
	primary key (`id`) using BTREE
);

create table `co_products` (
    `prd_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
    `prd_name` VARCHAR(70) NULL,
    PRIMARY KEY (`prd_id`)
);

create table co_rb_test_model (
    `id`   smallint,
    `name` varchar(10) not null
);

create table co_setters
(
    `id`       int(10) auto_increment primary key,
    `column1`  varchar(100) null,
    `column2`  varchar(100) null,
    `column3`  varchar(100) null
);

create table co_sources
(
    `id`       int(10) auto_increment primary key,
    `username` varchar(100) null,
    `source`   varchar(100) null
);

create index co_sources_username_index
    on co_sources (username);

create table table_with_uuid_primary
(
    `uuid`          char(36) not null primary key,
    `int_field`     int null
);

create table stuff
(
    `stf_id`   int(10) auto_increment primary key,
    `stf_name` varchar(100) not null,
    `stf_type` tinyint(3) unsigned not null
);
