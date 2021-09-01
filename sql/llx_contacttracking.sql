-- Copyright (C) ---Put here your own copyright and developer email---
--
-- This program is free software: you can redistribute it and/or modify
-- it under the terms of the GNU General Public License as published by
-- the Free Software Foundation, either version 3 of the License, or
-- (at your option) any later version.
--
-- This program is distributed in the hope that it will be useful,
-- but WITHOUT ANY WARRANTY; without even the implied warranty of
-- MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
-- GNU General Public License for more details.
--
-- You should have received a copy of the GNU General Public License
-- along with this program.  If not, see https://www.gnu.org/licenses/.

CREATE TABLE llx_contacttracking(
    -- BEGIN MODULEBUILDER FIELDS
    rowid integer AUTO_INCREMENT PRIMARY KEY NOT NULL,
    entity integer DEFAULT 1 NOT NULL,
    fk_soc integer,
    date_creation datetime NOT NULL,
    tms timestamp NOT NULL,
    fk_user_creat integer NOT NULL,
    fk_user_modif integer,
    import_key varchar(14),
    type_contact integer NOT NULL,
    mode_contact varchar(255),
    fk_contact integer,
    element_type varchar(255),
    fk_element_id integer,
    comment text,
    object varchar(255),
    fk_product varchar(255) NULL,
    type_event varchar(255) NULL,
    fk_event integer(11) NULL,
    relance_done smallint NULL,
    sales_done smallint NULL
    -- END MODULEBUILDER FIELDS
) ENGINE=innodb;
