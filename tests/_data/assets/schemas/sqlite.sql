DROP TABLE IF EXISTS album;

CREATE TABLE album
(
    id       INTEGER PRIMARY KEY AUTOINCREMENT,
    name     TEXT NOT NULL COLLATE NOCASE,
    album_id INTEGER,
    photo_id INTEGER,
    FOREIGN KEY (album_id) REFERENCES album (id) ON UPDATE CASCADE ON DELETE CASCADE,
    FOREIGN KEY (photo_id) REFERENCES photo (id) ON UPDATE CASCADE ON DELETE SET NULL
);

CREATE INDEX index_foreignkey_album_album ON album (album_id);
CREATE INDEX album_ibfk_2 ON album (photo_id);

DROP TABLE IF EXISTS album_photo;

CREATE TABLE album_photo
(
    id       INTEGER PRIMARY KEY AUTOINCREMENT,
    photo_id INTEGER,
    album_id INTEGER,
    position INTEGER NOT NULL DEFAULT 999999999,
    UNIQUE (album_id, photo_id),
    FOREIGN KEY (album_id) REFERENCES album (id) ON UPDATE CASCADE ON DELETE CASCADE,
    FOREIGN KEY (photo_id) REFERENCES photo (id) ON UPDATE CASCADE ON DELETE CASCADE
);

CREATE INDEX index_foreignkey_album_photo_photo ON album_photo (photo_id);
CREATE INDEX index_foreignkey_album_photo_album ON album_photo (album_id);

DROP TABLE IF EXISTS complex_default;

CREATE TABLE complex_default
(
    id           INTEGER PRIMARY KEY AUTOINCREMENT,
    created      TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated      TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_null TIMESTAMP DEFAULT NULL
);

DROP TABLE IF EXISTS co_customers_defaults;

CREATE TABLE co_customers_defaults
(
    cst_id          INTEGER PRIMARY KEY AUTOINCREMENT,
    cst_status_flag INTEGER NOT NULL DEFAULT 1,
    cst_name_last   TEXT    NOT NULL DEFAULT 'cst_default_lastName',
    cst_name_first  TEXT    NOT NULL DEFAULT 'cst_default_firstName'
);

CREATE INDEX co_customers_defaults_cst_status_flag_index ON co_customers_defaults (cst_status_flag);
CREATE INDEX co_customers_defaults_cst_name_last_index ON co_customers_defaults (cst_name_last);
CREATE INDEX co_customers_defaults_cst_name_first_index ON co_customers_defaults (cst_name_first);

DROP TABLE IF EXISTS co_customers;

CREATE TABLE co_customers
(
    cst_id          INTEGER PRIMARY KEY AUTOINCREMENT,
    cst_status_flag INTEGER,
    cst_name_last   TEXT,
    cst_name_first  TEXT
);

CREATE INDEX co_customers_cst_status_flag_index ON co_customers (cst_status_flag);
CREATE INDEX co_customers_cst_name_last_index ON co_customers (cst_name_last);
CREATE INDEX co_customers_cst_name_first_index ON co_customers (cst_name_first);

DROP TABLE IF EXISTS co_dialect;

CREATE TABLE co_dialect
(
    field_primary           INTEGER PRIMARY KEY AUTOINCREMENT,
    field_blob              BLOB,
    field_binary            BLOB,
    field_bit               INTEGER(10),
    field_bit_default       INTEGER(10)    DEFAULT 1,
    field_bigint            INTEGER,
    field_bigint_default    INTEGER        DEFAULT 1,
    field_boolean           INTEGER(1),
    field_boolean_default   INTEGER(1)     DEFAULT 1,
    field_char              CHAR(10),
    field_char_default      CHAR(10)       DEFAULT 'ABC',
    field_decimal           NUMERIC(10, 4),
    field_decimal_default   NUMERIC(10, 4) DEFAULT 14.5678,
    field_enum              TEXT,
    field_integer           INTEGER,
    field_integer_default   INTEGER        DEFAULT 1,
    field_json              TEXT,
    field_float             REAL,
    field_float_default     REAL           DEFAULT 14.5678,
    field_date              DATETIME,
    field_date_default      DATETIME       DEFAULT '2018-10-01',
    field_datetime          DATETIME,
    field_datetime_default  DATETIME       DEFAULT '2018-10-01 12:34:56',
    field_time              DATETIME,
    field_time_default      DATETIME       DEFAULT '12:34:56',
    field_timestamp         DATETIME,
    field_timestamp_default DATETIME       DEFAULT '2018-10-01 12:34:56',
    field_timestamp_current DATETIME       DEFAULT CURRENT_TIMESTAMP,
    field_mediumint         INTEGER,
    field_mediumint_default INTEGER        DEFAULT 1,
    field_smallint          INTEGER,
    field_smallint_default  INTEGER        DEFAULT 1,
    field_tinyint           INTEGER,
    field_tinyint_default   INTEGER        DEFAULT 1,
    field_longtext          TEXT,
    field_mediumtext        TEXT,
    field_tinytext          TEXT,
    field_text              TEXT,
    field_varbinary         BLOB,
    field_varchar           CHAR(10),
    field_varchar_default   CHAR(10)       DEFAULT 'D',
    UNIQUE (field_integer)
);

CREATE INDEX dialect_table_index ON co_dialect (field_bigint);
CREATE INDEX dialect_table_two_fields ON co_dialect (field_char, field_char_default);

DROP TABLE IF EXISTS fractal_dates;

CREATE TABLE fractal_dates
(
    id         INTEGER PRIMARY KEY AUTOINCREMENT,
    ftime      TEXT,
    fdatetime  TEXT,
    ftimestamp TEXT
);

DROP TABLE IF EXISTS co_invoices;

CREATE TABLE co_invoices
(
    inv_id          INTEGER PRIMARY KEY AUTOINCREMENT,
    inv_cst_id      INTEGER,
    inv_status_flag INTEGER,
    inv_title       TEXT,
    inv_total       REAL,
    inv_created_at  TEXT
);

CREATE INDEX co_invoices_inv_cst_id_index ON co_invoices (inv_cst_id);
CREATE INDEX co_invoices_inv_status_flag_index ON co_invoices (inv_status_flag);
CREATE INDEX co_invoices_inv_created_at_index ON co_invoices (inv_created_at);

DROP TABLE IF EXISTS objects;

CREATE TABLE objects
(
    obj_id   INTEGER PRIMARY KEY AUTOINCREMENT,
    obj_name TEXT    NOT NULL,
    obj_type INTEGER NOT NULL
);

DROP TABLE IF EXISTS co_orders;

CREATE TABLE co_orders
(
    ord_id   INTEGER PRIMARY KEY AUTOINCREMENT,
    ord_name TEXT
);

DROP TABLE IF EXISTS co_orders_x_products;

CREATE TABLE co_orders_x_products
(
    oxp_ord_id   INTEGER NOT NULL,
    oxp_prd_id   INTEGER NOT NULL,
    oxp_quantity INTEGER NOT NULL,
    PRIMARY KEY (oxp_ord_id, oxp_prd_id)
);

DROP TABLE IF EXISTS no_primary_key;

CREATE TABLE no_primary_key
(
    nokey_id   INTEGER,
    nokey_name TEXT NOT NULL
);

DROP TABLE IF EXISTS photo;

CREATE TABLE photo
(
    id                INTEGER PRIMARY KEY AUTOINCREMENT,
    date_uploaded     TEXT    NOT NULL DEFAULT CURRENT_TIMESTAMP,
    original_filename TEXT    NOT NULL,
    path              TEXT    NOT NULL,
    width             INTEGER NOT NULL,
    height            INTEGER NOT NULL,
    thumb_path        TEXT    NOT NULL,
    thumb_width       INTEGER NOT NULL,
    thumb_height      INTEGER NOT NULL,
    display_path      TEXT    NOT NULL,
    display_width     INTEGER NOT NULL,
    display_height    INTEGER NOT NULL,
    mime_type         TEXT    NOT NULL,
    filesize          INTEGER,
    phash             INTEGER NOT NULL,
    battles           INTEGER NOT NULL DEFAULT 0,
    wins              INTEGER NOT NULL DEFAULT 0
);

DROP TABLE IF EXISTS co_products;

CREATE TABLE co_products
(
    prd_id   INTEGER PRIMARY KEY AUTOINCREMENT,
    prd_name TEXT
);

DROP TABLE IF EXISTS co_rb_test_model;

CREATE TABLE co_rb_test_model
(
    id   INTEGER,
    name TEXT NOT NULL
);

DROP TABLE IF EXISTS co_setters;

CREATE TABLE co_setters
(
    id      INTEGER PRIMARY KEY AUTOINCREMENT,
    column1 TEXT,
    column2 TEXT,
    column3 TEXT
);

DROP TABLE IF EXISTS co_sources;

CREATE TABLE co_sources
(
    id       INTEGER PRIMARY KEY AUTOINCREMENT,
    username TEXT,
    source   TEXT
);

CREATE INDEX co_sources_username_index ON co_sources (username);

DROP TABLE IF EXISTS table_with_uuid_primary;

CREATE TABLE table_with_uuid_primary
(
    uuid      TEXT NOT NULL PRIMARY KEY,
    int_field INTEGER
);

DROP TABLE IF EXISTS stuff;

CREATE TABLE stuff
(
    stf_id   INTEGER PRIMARY KEY AUTOINCREMENT,
    stf_name TEXT    NOT NULL,
    stf_type INTEGER NOT NULL
);
