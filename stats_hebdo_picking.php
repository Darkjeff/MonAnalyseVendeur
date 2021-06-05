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
llxHeader ( '', 'Compta - Ventilation' );

$textprevyear = "<a href=\"stats.php?year=" . ($year_current - 1) . "\">" . img_previous () . "</a>";
$textnextyear = " <a href=\"stats.php?year=" . ($year_current + 1) . "\">" . img_next () . "</a>";

print_fiche_titre ( $langs->trans("Rapport Picking Hebdomadaire")." ".$textprevyear." ".$langs->trans("Year")." ".$year_start." ".$textnextyear);

print '<table border="0" width="100%" class="notopnoleftnoright">';
print '<tr><td valign="top" width="30%" class="notopnoleft">';

$y = $year_current;

$var = true;

print '<table class="noborder" width="100%">';
print "</table>\n";
print '</td><td valign="top" width="70%" class="notopnoleftnoright"></td>';
print '</tr><tr><td colspan=2>';
print '<table class="noborder" width="100%">';
print '<tr class="liste_titre"><td width=150>'.$langs->trans("NB Picking").'</td>';
print '<td align="center">'.$conf->global->MONANALYSEVENDEUR_MAG_1.'</td>';
print '<td align="center">'.$conf->global->MONANALYSEVENDEUR_MAG_2.'</td>';
print '<td align="center">'.$conf->global->MONANALYSEVENDEUR_MAG_3.'</td>';
print '<td align="center">'.$conf->global->MONANALYSEVENDEUR_MAG_4.'</td>';
print '<td align="center">'.$conf->global->MONANALYSEVENDEUR_MAG_5.'</td>';
print '<td align="center">'.$conf->global->MONANALYSEVENDEUR_MAG_6.'</td>';
print '<td align="center">'.$conf->global->MONANALYSEVENDEUR_MAG_7.'</td>';
print '<td align="center">'.$conf->global->MONANALYSEVENDEUR_MAG_8.'</td>';
print '<td align="center">'.$conf->global->MONANALYSEVENDEUR_MAG_9.'</td>';
print '<td align="center"><b>'.$langs->trans("Total").'</b></td></tr>';


	
$sql = "SELECT WEEK(fi.datec) AS Week,";
$sql .= "  ROUND(SUM(IF(cat.label='".$conf->global->MONANALYSEVENDEUR_MAG_1."',fi.entity,0)),0) AS '".$conf->global->MONANALYSEVENDEUR_MAG_1."',";
$sql .= "  ROUND(SUM(IF(cat.label='".$conf->global->MONANALYSEVENDEUR_MAG_2."',fi.entity,0)),0) AS '".$conf->global->MONANALYSEVENDEUR_MAG_2."',";
$sql .= "  ROUND(SUM(IF(cat.label='".$conf->global->MONANALYSEVENDEUR_MAG_3."',fi.entity,0)),0) AS '".$conf->global->MONANALYSEVENDEUR_MAG_3."',";
$sql .= "  ROUND(SUM(IF(cat.label='".$conf->global->MONANALYSEVENDEUR_MAG_4."',fi.entity,0)),0) AS '".$conf->global->MONANALYSEVENDEUR_MAG_4."',";
$sql .= "  ROUND(SUM(IF(cat.label='".$conf->global->MONANALYSEVENDEUR_MAG_5."',fi.entity,0)),0) AS '".$conf->global->MONANALYSEVENDEUR_MAG_5."',";
$sql .= "  ROUND(SUM(IF(cat.label='".$conf->global->MONANALYSEVENDEUR_MAG_6."',fi.entity,0)),0) AS '".$conf->global->MONANALYSEVENDEUR_MAG_6."',";
$sql .= "  ROUND(SUM(IF(cat.label='".$conf->global->MONANALYSEVENDEUR_MAG_7."',fi.entity,0)),0) AS '".$conf->global->MONANALYSEVENDEUR_MAG_7."',";
$sql .= "  ROUND(SUM(IF(cat.label='".$conf->global->MONANALYSEVENDEUR_MAG_8."',fi.entity,0)),0) AS '".$conf->global->MONANALYSEVENDEUR_MAG_8."',";
$sql .= "  ROUND(SUM(IF(cat.label='".$conf->global->MONANALYSEVENDEUR_MAG_9."',fi.entity,0)),0) AS '".$conf->global->MONANALYSEVENDEUR_MAG_9."',";
$sql .= "  ROUND(SUM(fi.entity),0) as 'Total'";
$sql .= " FROM " . MAIN_DB_PREFIX . "fichinter as fi";
$sql .= " , " . MAIN_DB_PREFIX . "fichinter_extrafields as fix";
$sql .= " , " . MAIN_DB_PREFIX . "categorie_user as cu";
$sql .= " , " . MAIN_DB_PREFIX . "categorie as cat";
$sql .= " WHERE fi.datec >= '" . $db->idate ( dol_get_first_day ( $y, 1, false ) ) . "'";
$sql .= "  AND fi.datec <= '" . $db->idate ( dol_get_last_day ( $y, 12, false ) ) . "'";
$sql .= "  AND fi.rowid = fix.fk_object ";
$sql .= "  AND fix.vendeur = cu.fk_user ";
$sql .= "  AND cat.rowid = cu.fk_categorie ";
$sql .= " GROUP BY WEEK(fi.datec)";

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
