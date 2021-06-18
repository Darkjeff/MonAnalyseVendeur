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
dol_include_once('/monanalysevendeur/class/monanalysemagstats.class.php');

// Langs
$langs->load ( "immobilier@immobilier" );
$langs->load ( "bills" );
$langs->load ( "other" );

$from_date = dol_mktime(0, 0, 0, GETPOST('frmdtmonth', 'int'), GETPOST('frmdtday', 'int'), GETPOST('frmdtyear', 'int'));
$to_date = dol_mktime(23, 59, 59, GETPOST('todtmonth', 'int'), GETPOST('todtday', 'int'), GETPOST('todtyear', 'int'));
$categid=GETPOST('categuser','int');
if ($categid==-1) {
	$categid=0;
}
$submit=GETPOST('submit', 'alpha');
if ($submit=='Export Excel') {
	$action='exportcsv';
}

/////action to export to excel
$stats = new MonAnayseVendeurStats($db);
if ( $action == "exportcsv" ) {
	$sep = ';';
	$filename = 'rapport';
	dol_include_once('/monanalysevendeur/tpl/export_mag.tpl.php');
	$result = $stats->getDataStatVendeur($from_date, $to_date);
	foreach($result as $userId=>$data) {
		print '"'.$data['mag'].'"'.$sep;
		print '"'.$data['dilax'].'"'.$sep;
		print '"'.$data['nbt'].'"'.$sep;
		print '"'.$data['nbt'].'"'.$sep;
		print '"'.$data['nba'].'"'.$sep;
		print '"'.$data['nbs'].'"'.$sep;
		print "\n";
	}

	exit;
}



/*
 * View
 */

llxHeader ( '', 'Stats Magasin' );


dol_fiche_head($head, 'stats', $langs->trans("Statistics"), -1);



//if (empty($socid))
//{
// Show filter box
print '<form name="stats" method="POST" action="' . $_SERVER["PHP_SELF"] . '">';
print '<input type="hidden" name="token" value="' . newToken() . '">';

print '<table class="noborder centpercent">';
print '<tr class="liste_titre"><td class="liste_titre" colspan="2">' . $langs->trans("Filter") . '</td></tr>';
// From Dt To Dt
print '<tr><td class="left">' . $langs->trans("From") . '</td><td class="left">';
print $form->selectDate($from_date, 'frmdt', 0, 0, 1, 'stats', 1, 0);
print '</td></tr>';
print '<tr><td class="left">' . $langs->trans("To") . '</td><td class="left">';
print $form->selectDate($to_date, 'todt', 0, 0, 1, 'stats', 1, 0);
print '</td></tr>';
//print '<tr><td class="left">' . $langs->trans("Agence") . '</td><td class="left">';
//print $form->select_all_categories('user', $categid, 'categuser', null, null, 0);
//print '</td></tr>';
//print '<input type="button" class="butAction" name="exportcsv" value="'.$langs->trans("ExportCSV").'" onclick="launch_export();" />';
print '<input type="submit" name="submit" class="butAction" value="Export Excel" style="font-weight: bold;float:right;text-shadow: none;">';
print '<tr><td class="center" colspan="2"><input type="submit" name="submit" class="button" value="' . $langs->trans("Refresh") . '"></td></tr>';

// TODO Avoid using js. We can use a direct link with $param
	print '
	<script type="text/javascript">
		function launch_export() {
			$("div.fiche form input[name=\"action\"]").val("exportcsv");
			$("div.fiche form input[type=\"submit\"]").click();
			$("div.fiche form input[name=\"action\"]").val("");
		}

	</script>';




print '</table>';
print '</form>';
print '<br><br>';


print '<div class="div-table-responsive">'; // You can use div-table-responsive-no-min if you dont need reserved height for your table
print '<table class="tagtable nobottomiftotal liste">'."\n";
print '<tr class="liste_titre">';
print '<td>Magasin</td>';
print '<td>Nb vendeur</td>';
print '<td>Dilax</td>';
print '<td>Nb traitement</td>';
print '<td>Tx traitement</td>';
print '<td>Nb Box</td>';
print '<td>Nb AboHV</td>';
print '<td>Nb Service</td>';
print '<td>Relance</td>';
print '<td>Picking</td>';
print '<td>Pot Box</td>';
print '<td>Box</td>';
print '<td>Tx Transfo</td>';
print '<td>Ecoute</td>';
print '</tr>';


// Build graphic number of object
if (!empty($from_date) && !empty($to_date)) {
	$result = $stats->getDataStatVendeur($from_date, $to_date);
	//print var_dump($result);
	if (!is_array($result) && $result < 0) {
		setEventMessage($stats->error, 'errors');
	} else {
		$total=array();
		$total['mag']='Total';
		foreach($result as $userId=>$data) {
			print '<tr class="oddeven">';

			//mag
			print '<td>';
			print $data['mag'];
			print '</td>';


			//Nb Vendeur
			print '<td>';
			print $data['vendeur'];
			$total['vendeur'] += (int)$data['vendeur'];
			print '</td>';

			//Dilax
			print '<td>';
			print $data['dilax'];
			$total['dilax'] += (int)$data['dilax'];
			print '</td>';


			//Nb Traitment
			print '<td>';
			print $data['nbt'];
			$total['nbt'] += (int)$data['nbt'];
			print '</td>';

			//Tx Traitment
			print '<td>';
			if (!empty($data['dilax'])) {
				print round($data['nbt'] / $data['dilax'], 2);
				$total['tx_traitement'] = '';
			} else {
				print 'N/A';
			}
			print '</td>';

			//Tx Transfo Box
			print '<td>';
			print $data['nbb'];
			$total['nbb'] += (int)$data['nbt'];
			print '</td>';

			//Tx Transfo AboHV
			print '<td>';
			print $data['nba'];
			$total['nba'] += (int)$data['nba'];
			print '</td>';

			//Tx Transfo Service
			print '<td>';
			print $data['nbs'];
			$total['nbs'] += (int)$data['nbs'];
			print '</td>';

			//relance
			print '<td>';
			print $data['relance'];
			$total['relance'] += (int)$data['relance'];
			print '</td>';

			//Picking
			print '<td>';
			print $data['picking'];
			$total['picking'] += (int)$data['picking'];
			print '</td>';

			//PotBox
			print '<td>';
			print $data['potbox'];
			$total['potbox'] += (int)$data['potbox'];
			print '</td>';

			//Box
			print '<td>';
			print $data['box'];
			$total['box'] += (int)$data['box'];
			print '</td>';

			//TxBB
			print '<td>';
			print $data['txbb'];
			$total['txbb'] += (int)$data['txbb'];
			print '</td>';

			//ecoute
			print '<td>';
			print $data['ecoute'];
			$total['ecoute'] += (int)$data['ecoute'];
			print '</td>';

			print '</tr>';
		}
	 if (!empty($total)) {
		 print '<tr class="liste_total">';
		 foreach($total as $key=>$val) {
		 	print '<td>'.$val.'</td>';
		 }
		 print '</tr>';
	 }

	}
}

print '</table>'."\n";

$db->close ();

llxFooter ();
