<?php
/* Copyright (C) 2015-2018  Alexandre Spangaro	<aspangaro@open-dsi.fr>
 * Copyright (C) 2016       Charlie Benke		<charlie@patas-monkey.com>
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program. If not, see <https://www.gnu.org/licenses/>.
 */

// Protection to avoid direct call of template
if (empty($conf) || !is_object($conf))
{
	print "Error, template page can't be called as URL";
	exit;
}


$prefix = 'csv';
$filename='export_ecoute';

$date_export = "_".dol_print_date(dol_now(), '%Y%m%d%H%M%S');

header('Content-Type: text/csv');

$completefilename = $filename.".".$prefix;


header('Content-Disposition: attachment;filename='.$completefilename);
