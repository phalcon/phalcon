DROP TABLE IF EXISTS album_photo CASCADE;
DROP TABLE IF EXISTS album CASCADE;
DROP TABLE IF EXISTS photo CASCADE;

CREATE TABLE photo
(
    id                SERIAL PRIMARY KEY,
    date_uploaded     TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP,
    original_filename TEXT         NOT NULL,
    path              TEXT         NOT NULL,
    width             SMALLINT     NOT NULL,
    height            SMALLINT     NOT NULL,
    thumb_path        TEXT         NOT NULL,
    thumb_width       SMALLINT     NOT NULL,
    thumb_height      SMALLINT     NOT NULL,
    display_path      TEXT         NOT NULL,
    display_width     SMALLINT     NOT NULL,
    display_height    SMALLINT     NOT NULL,
    mime_type         VARCHAR(255) NOT NULL,
    filesize          INTEGER NULL DEFAULT NULL,
    phash             BIGINT       NOT NULL,
    battles           INTEGER      NOT NULL DEFAULT 0,
    wins              INTEGER      NOT NULL DEFAULT 0
);

CREATE TABLE album
(
    id       SERIAL PRIMARY KEY,
    name     VARCHAR(100) NOT NULL,
    album_id INTEGER NULL DEFAULT NULL,
    photo_id INTEGER NULL DEFAULT NULL,
    CONSTRAINT album_ibfk_1
        FOREIGN KEY (album_id)
            REFERENCES album (id) ON UPDATE CASCADE ON DELETE CASCADE,
    CONSTRAINT album_ibfk_2
        FOREIGN KEY (photo_id)
            REFERENCES photo (id) ON UPDATE CASCADE ON DELETE SET NULL
);

CREATE INDEX index_foreignkey_album_album ON album (album_id);
CREATE INDEX album_ibfk_2 ON album (photo_id);

CREATE TABLE album_photo
(
    id       SERIAL PRIMARY KEY,
    photo_id INTEGER NULL DEFAULT NULL,
    album_id INTEGER NULL DEFAULT NULL,
    position INTEGER NOT NULL DEFAULT 999999999,
    CONSTRAINT c_fk_album_photo_album_id
        FOREIGN KEY (album_id)
            REFERENCES album (id) ON UPDATE CASCADE ON DELETE CASCADE,
    CONSTRAINT c_fk_album_photo_photo_id
        FOREIGN KEY (photo_id)
            REFERENCES photo (id) ON UPDATE CASCADE ON DELETE CASCADE
);

CREATE UNIQUE INDEX UQ_cadf1c545153612614511f15197cae7b6dacac97 ON album_photo (album_id, photo_id);
CREATE INDEX index_foreignkey_album_photo_photo ON album_photo (photo_id);
CREATE INDEX index_foreignkey_album_photo_album ON album_photo (album_id);

DROP TABLE IF EXISTS complex_default;

CREATE TABLE complex_default
(
    id           SERIAL PRIMARY KEY,
    created      TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated      TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_null TIMESTAMP NULL DEFAULT NULL
);

DROP TABLE IF EXISTS co_customers_defaults;

CREATE TABLE co_customers_defaults
(
    cst_id          SERIAL PRIMARY KEY,
    cst_status_flag SMALLINT     NOT NULL DEFAULT 1,
    cst_name_last   VARCHAR(100) NOT NULL DEFAULT 'cst_default_lastName',
    cst_name_first  VARCHAR(50)  NOT NULL DEFAULT 'cst_default_firstName'
);

CREATE INDEX co_customers_defaults_cst_status_flag_index ON co_customers_defaults (cst_status_flag);
CREATE INDEX co_customers_defaults_cst_name_last_index ON co_customers_defaults (cst_name_last);
CREATE INDEX co_customers_defaults_cst_name_first_index ON co_customers_defaults (cst_name_first);

DROP TABLE IF EXISTS co_customers;

CREATE TABLE co_customers
(
    cst_id          SERIAL PRIMARY KEY,
    cst_status_flag SMALLINT NULL,
    cst_name_last   VARCHAR(100) NULL,
    cst_name_first  VARCHAR(50) NULL
);

CREATE INDEX co_customers_cst_status_flag_index ON co_customers (cst_status_flag);
CREATE INDEX co_customers_cst_name_last_index ON co_customers (cst_name_last);
CREATE INDEX co_customers_cst_name_first_index ON co_customers (cst_name_first);

DROP TABLE IF EXISTS co_dialect;

CREATE TABLE co_dialect
(
    field_primary           SERIAL PRIMARY KEY,
    field_blob              BYTEA,
    field_binary            BYTEA,
    field_bit               BIT(10),
    field_bit_default       BIT(10)        DEFAULT B'1',
    field_bigint            BIGINT,
    field_bigint_default    BIGINT         DEFAULT 1,
    field_boolean           BOOLEAN,
    field_boolean_default   BOOLEAN        DEFAULT TRUE,
    field_char              CHAR(10),
    field_char_default      CHAR(10)       DEFAULT 'ABC',
    field_decimal           DECIMAL(10, 4),
    field_decimal_default   DECIMAL(10, 4) DEFAULT 14.5678,
    field_enum              TEXT CHECK (field_enum IN ('xs', 's', 'm', 'l', 'xl', 'internal')),
    field_integer           INTEGER,
    field_integer_default   INTEGER        DEFAULT 1,
    field_json              JSON,
    field_float             FLOAT(10),
    field_float_default     FLOAT(10)      DEFAULT 14.5678,
    field_date              DATE,
    field_date_default      DATE           DEFAULT '2018-10-01',
    field_datetime          TIMESTAMP,
    field_datetime_default  TIMESTAMP      DEFAULT '2018-10-01 12:34:56',
    field_time              TIME,
    field_time_default      TIME           DEFAULT '12:34:56',
    field_timestamp         TIMESTAMP,
    field_timestamp_default TIMESTAMP      DEFAULT '2018-10-01 12:34:56',
    field_timestamp_current TIMESTAMP      DEFAULT CURRENT_TIMESTAMP,
    field_mediumint         INTEGER,
    field_mediumint_default INTEGER        DEFAULT 1,
    field_smallint          SMALLINT,
    field_smallint_default  SMALLINT       DEFAULT 1,
    field_tinyint           SMALLINT,
    field_tinyint_default   SMALLINT       DEFAULT 1,
    field_longtext          TEXT,
    field_mediumtext        TEXT,
    field_tinytext          TEXT,
    field_text              TEXT,
    field_varbinary         BYTEA,
    field_varchar           VARCHAR(10),
    field_varchar_default   VARCHAR(10)    DEFAULT 'D',
    UNIQUE (field_integer)
);

CREATE INDEX dialect_table_index ON co_dialect (field_bigint);
CREATE INDEX dialect_table_two_fields ON co_dialect (field_char, field_char_default);

COMMENT ON COLUMN co_dialect.field_primary           IS 'field_primary field';
COMMENT ON COLUMN co_dialect.field_blob              IS 'field_blob field';
COMMENT ON COLUMN co_dialect.field_binary            IS 'field_binary field';
COMMENT ON COLUMN co_dialect.field_bit               IS 'field_bit field';
COMMENT ON COLUMN co_dialect.field_bit_default       IS 'field_bit_default field';
COMMENT ON COLUMN co_dialect.field_bigint            IS 'field_bigint field';
COMMENT ON COLUMN co_dialect.field_bigint_default    IS 'field_bigint_default field';
COMMENT ON COLUMN co_dialect.field_boolean           IS 'field_boolean field';
COMMENT ON COLUMN co_dialect.field_boolean_default   IS 'field_boolean_default field';
COMMENT ON COLUMN co_dialect.field_char              IS 'field_char field';
COMMENT ON COLUMN co_dialect.field_char_default      IS 'field_char_default field';
COMMENT ON COLUMN co_dialect.field_decimal           IS 'field_decimal field';
COMMENT ON COLUMN co_dialect.field_decimal_default   IS 'field_decimal_default field';
COMMENT ON COLUMN co_dialect.field_enum              IS 'field_enum field';
COMMENT ON COLUMN co_dialect.field_integer           IS 'field_integer field';
COMMENT ON COLUMN co_dialect.field_integer_default   IS 'field_integer_default field';
COMMENT ON COLUMN co_dialect.field_json              IS 'field_json field';
COMMENT ON COLUMN co_dialect.field_float             IS 'field_float field';
COMMENT ON COLUMN co_dialect.field_float_default     IS 'field_float_default field';
COMMENT ON COLUMN co_dialect.field_date              IS 'field_date field';
COMMENT ON COLUMN co_dialect.field_date_default      IS 'field_date_default field';
COMMENT ON COLUMN co_dialect.field_datetime          IS 'field_datetime field';
COMMENT ON COLUMN co_dialect.field_datetime_default  IS 'field_datetime_default field';
COMMENT ON COLUMN co_dialect.field_time              IS 'field_time field';
COMMENT ON COLUMN co_dialect.field_time_default      IS 'field_time_default field';
COMMENT ON COLUMN co_dialect.field_timestamp         IS 'field_timestamp field';
COMMENT ON COLUMN co_dialect.field_timestamp_default IS 'field_timestamp_default field';
COMMENT ON COLUMN co_dialect.field_timestamp_current IS 'field_timestamp_current field';
COMMENT ON COLUMN co_dialect.field_mediumint         IS 'field_mediumint field';
COMMENT ON COLUMN co_dialect.field_mediumint_default IS 'field_mediumint_default field';
COMMENT ON COLUMN co_dialect.field_smallint          IS 'field_smallint field';
COMMENT ON COLUMN co_dialect.field_smallint_default  IS 'field_smallint_default field';
COMMENT ON COLUMN co_dialect.field_tinyint           IS 'field_tinyint field';
COMMENT ON COLUMN co_dialect.field_tinyint_default   IS 'field_tinyint_default field';
COMMENT ON COLUMN co_dialect.field_longtext          IS 'field_longtext field';
COMMENT ON COLUMN co_dialect.field_mediumtext        IS 'field_mediumtext field';
COMMENT ON COLUMN co_dialect.field_tinytext          IS 'field_tinytext field';
COMMENT ON COLUMN co_dialect.field_text              IS 'field_text field';
COMMENT ON COLUMN co_dialect.field_varbinary         IS 'field_varbinary field';
COMMENT ON COLUMN co_dialect.field_varchar           IS 'field_varchar field';
COMMENT ON COLUMN co_dialect.field_varchar_default   IS 'field_varchar_default field';


DROP TABLE IF EXISTS fractal_dates;

CREATE TABLE fractal_dates
(
    id         SERIAL PRIMARY KEY,
    ftime      TIME(2),
    fdatetime  TIMESTAMP(2),
    ftimestamp TIMESTAMP(2)
);

DROP TABLE IF EXISTS co_invoices;

CREATE TABLE co_invoices
(
    inv_id          SERIAL PRIMARY KEY,
    inv_cst_id      INTEGER,
    inv_status_flag SMALLINT,
    inv_title       VARCHAR(100),
    inv_total       NUMERIC(10, 2),
    inv_created_at  TIMESTAMP
);

CREATE INDEX co_invoices_inv_cst_id_index ON co_invoices (inv_cst_id);
CREATE INDEX co_invoices_inv_status_flag_index ON co_invoices (inv_status_flag);
CREATE INDEX co_invoices_inv_created_at_index ON co_invoices (inv_created_at);

DROP TABLE IF EXISTS no_primary_key;

CREATE TABLE no_primary_key
(
    nokey_id   INTEGER,
    nokey_name VARCHAR(100) NOT NULL
);

DROP TABLE IF EXISTS objects;

CREATE TABLE objects
(
    obj_id   SERIAL PRIMARY KEY,
    obj_name VARCHAR(100) NOT NULL,
    obj_type SMALLINT     NOT NULL
);

DROP TABLE IF EXISTS co_orders;

CREATE TABLE co_orders
(
    ord_id   SERIAL PRIMARY KEY,
    ord_name VARCHAR(70) NULL
);

DROP TABLE IF EXISTS co_orders_x_products;

CREATE TABLE co_orders_x_products
(
    oxp_ord_id   INTEGER NOT NULL,
    oxp_prd_id   INTEGER NOT NULL,
    oxp_quantity INTEGER NOT NULL,
    PRIMARY KEY (oxp_ord_id, oxp_prd_id)
);


DROP TABLE IF EXISTS co_products;

CREATE TABLE co_products
(
    prd_id   SERIAL PRIMARY KEY,
    prd_name VARCHAR(70) NULL
);

DROP TABLE IF EXISTS co_rb_test_model;

CREATE TABLE co_rb_test_model
(
    id   SMALLINT,
    name VARCHAR(10) NOT NULL
);

DROP TABLE IF EXISTS co_setters;

CREATE TABLE co_setters
(
    id      SERIAL PRIMARY KEY,
    column1 VARCHAR(100) NULL,
    column2 VARCHAR(100) NULL,
    column3 VARCHAR(100) NULL
);

DROP TABLE IF EXISTS co_sources;

CREATE TABLE co_sources
(
    id       SERIAL PRIMARY KEY,
    username VARCHAR(100) NULL,
    source   VARCHAR(100) NULL
);

CREATE INDEX co_sources_username_index ON co_sources (username);

DROP TABLE IF EXISTS table_with_uuid_primary;

CREATE TABLE table_with_uuid_primary
(
    uuid      UUID PRIMARY KEY,
    int_field INTEGER NULL
);

DROP TABLE IF EXISTS stuff;

CREATE TABLE stuff
(
    stf_id   SERIAL PRIMARY KEY,
    stf_name VARCHAR(100) NOT NULL,
    stf_type SMALLINT     NOT NULL
);
