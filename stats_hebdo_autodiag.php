<?php
/* Copyright (C) 2001-2006 Rodolphe Quiedeville <rodolphe@quiedeville.org>
 * Copyright (C) 2004-2005 Laurent Destailleur  <eldy@users.sourceforge.net>
 * Copyright (C) 2013 Olivier Geffroy  <jeff@jeffinfo.com>
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 59 Temple Place - Suite 330, Boston, MA 02111-1307, USA.
 *
 * $Id: index.php 10 2011-01-24 16:58:03Z hregis $
 * $Source: /cvsroot/dolibarr/dolibarr/htdocs/compta/ventilation/index.php,v $
 */

/**
 * \file htdocs/compta/ventilation/index.php
 * \ingroup compta
 * \brief Page accueil ventilation
 */

// Dolibarr environment
$res=@include("../main.inc.php");
if (! $res && file_exists("../main.inc.php")) $res=@include("../main.inc.php");
if (! $res && file_exists("../../main.inc.php")) $res=@include("../../main.inc.php");
if (! $res && file_exists("../../../main.inc.php")) $res=@include("../../../main.inc.php");
if (! $res) die("Include of main fails");

// Class
require_once (DOL_DOCUMENT_ROOT . "/core/lib/date.lib.php");

// Langs
$langs->load ( "immobilier@immobilier" );
$langs->load ( "bills" );
$langs->load ( "other" );

// Filter
$year = $_GET ["year"];
if ($year == 0) {
	$year_current = strftime ( "%Y", time () );
	$year_start = $year_current;
} else {
	$year_current = $year;
	$year_start = $year;
}

/*
 * View
 */
llxHeader ( '', 'Stats Hebdo Autodiag' );

$textprevyear = "<a href=\"stats_hebdo_autodiag.php?year=" . ($year_current - 1) . "\">" . img_previous () . "</a>";
$textnextyear = " <a href=\"stats_hebdo_autodiag.php?year=" . ($year_current + 1) . "\">" . img_next () . "</a>";

print_fiche_titre ( $langs->trans("Rapport Autodiag Hebdomadaire")." ".$textprevyear." ".$langs->trans("Year")." ".$year_start." ".$textnextyear);

print '<table border="0" width="100%" class="notopnoleftnoright">';
print '<tr><td valign="top" width="30%" class="notopnoleft">';

$y = $year_current;

$var = true;

print '<table class="noborder" width="100%">';
print "</table>\n";
print '</td><td valign="top" width="70%" class="notopnoleftnoright"></td>';
print '</tr><tr><td colspan=2>';
print '<table class="noborder" width="100%">';
print '<tr class="liste_titre"><td width=150>'.$langs->trans("Semaine").'</td>';
print '<td align="center">'.$langs->trans("Armentières").'</td>';
print '<td align="center">'.$langs->trans("Abbeville CV").'</td>';
print '<td align="center">'.$langs->trans("Boulogne").'</td>';
print '<td align="center">'.$langs->trans("Dury").'</td>';
print '<td align="center">'.$langs->trans("Auchy les Mines").'</td>';
print '<td align="center">'.$langs->trans("Aire sur la Lys").'</td>';
print '<td align="center">'.$langs->trans("Longuenesse").'</td>';
print '<td align="center">'.$langs->trans("Abbeville CC").'</td>';
print '<td align="center">'.$langs->trans("Hazebrouck").'</td>';
print '<td align="center"><b>'.$langs->trans("Total").'</b></td></tr>';


	
$sql = "SELECT WEEK(ct.date_creation) AS Week,";
$sql .= "  ROUND(SUM(IF(cat.label='Armentières',ct.entity,0)),0) AS 'Armentières',";
$sql .= "  ROUND(SUM(IF(cat.label='Abbeville CV',ct.entity,0)),0) AS 'Abbeville CV',";
$sql .= "  ROUND(SUM(IF(cat.label='Boulogne',ct.entity,0)),0) AS 'Boulogne',";
$sql .= "  ROUND(SUM(IF(cat.label='Dury',ct.entity,0)),0) AS 'Dury',";
$sql .= "  ROUND(SUM(IF(cat.label='Auchy les Mines',ct.entity,0)),0) AS 'Auchy les Mines',";
$sql .= "  ROUND(SUM(IF(cat.label='Aire sur la Lys',ct.entity,0)),0) AS 'Aire sur la Lys',";
$sql .= "  ROUND(SUM(IF(cat.label='Longuenesse',ct.entity,0)),0) AS 'Longuenesse',";
$sql .= "  ROUND(SUM(IF(cat.label='Abbeville CC',ct.entity,0)),0) AS 'Abbeville CC',";
$sql .= "  ROUND(SUM(IF(cat.label='Hazebrouck',ct.entity,0)),0) AS 'Hazebrouck',";
$sql .= "  ROUND(SUM(ct.entity),0) as 'Total'";
$sql .= " FROM " . MAIN_DB_PREFIX . "contacttracking as ct";
$sql .= " , " . MAIN_DB_PREFIX . "categorie_user as cu";
$sql .= " , " . MAIN_DB_PREFIX . "categorie as cat";
$sql .= " WHERE ct.date_creation >= '" . $db->idate ( dol_get_first_day ( $y, 1, false ) ) . "'";
$sql .= "  AND ct.date_creation <= '" . $db->idate ( dol_get_last_day ( $y, 12, false ) ) . "'";
$sql .= "  AND ct.fk_user_creat = cu.fk_user ";
$sql .= "  AND cat.rowid = cu.fk_categorie ";
$sql .= "  AND ct.fk_event is null ";
$sql .= " GROUP BY WEEK(ct.date_creation)";

$resql = $db->query ( $sql );
if ($resql) {
	$i = 0;
	$num = $db->num_rows ( $resql );
	
	while ( $i < $num ) {
		
		$row = $db->fetch_row ( $resql );
		
		print '<tr><td>' . $row [0] . '</td>';
		print '<td align="right">' . $row [1] . '</td>';
		print '<td align="right">' . $row [2] . '</td>';
		print '<td align="right">' . $row [3] . '</td>';
		print '<td align="right">' . $row [4] . '</td>';
		print '<td align="right">' . $row [5] . '</td>';
		print '<td align="right">' . $row [6] . '</td>';
		print '<td align="right">' . $row [7] . '</td>';
		print '<td align="right">' . $row [8] . '</td>';
		print '<td align="right">' . $row [9] . '</td>';
		print '<td align="right">' . $row [10] . '</td>';
		print '<td align="right">' . $row [11] . '</td>';
		print '<td align="right">' . $row [12] . '</td>';
		print '<td align="right"><b>' . $row [13] . '</b></td>';
		print '</tr>';
		$i ++;
	}
	$db->free ( $resql );
} else {
	print $db->lasterror (); // affiche la derniere erreur sql
}




print "</table>\n";

print '</td></tr></table>';

$db->close ();

llxFooter ( '$Date: 2006/12/23 15:24:24 $ - $Revision: 1.11 $' );

?>
