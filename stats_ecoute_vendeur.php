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
dol_include_once('/monanalysevendeur/class/monanalysevendeurstatsecoute.class.php');

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
	dol_include_once('/monanalysevendeur/tpl/export_ecoute.tpl.php');
	$result = $stats->getDataStatVendeur($from_date, $to_date);
	foreach($result as $userId=>$data) {
		print '"'.$data['name'].'"'.$sep;
		print '"'.$data['nb'].'"'.$sep;
		print '"'.$data['foyerequip'].'"'.$sep;
		print '"'.$data['foyercompo'].'"'.$sep;
		print '"'.$data['foyerfai'].'"'.$sep;
		print '"'.$data['foyereli'].'"'.$sep;
		print '"'.$data['propcoh'].'"'.$sep;
		print '"'.$data['proprotv'].'"'.$sep;
		print '"'.$data['proprooption'].'"'.$sep;
		print '"'.$data['propropro5g'].'"'.$sep;
		print '"'.$data['ventespartner'].'"'.$sep;
		print '"'.$data['venteschubb'].'"'.$sep;
		print '"'.$data['ventesaccess'].'"'.$sep;
		print '"'.$data['expsfr'].'"'.$sep;
		print '"'.$data['expenqu'].'"'.$sep;
		print '"'.$data['expremise'].'"'.$sep;
		print '"'.$data['exprdv'].'"'.$sep;
		print '"'.$data['expremise'].'"'.$sep;
		print '"'.$data['rebondbox'].'"'.$sep;
		print '"'.$data['rebondabo'].'"'.$sep;
		print '"'.$data['rebondrmd'].'"'.$sep;
		print '"'.$data['rebondoptions'].'"'.$sep;
		print '"'.$data['moyensdevis'].'"'.$sep;
		print '"'.$data['moyensdouble'].'"'.$sep;
		print '"'.$data['moyensreprise'].'"'.$sep;
		print '"'.$data['moyensfloa'].'"'.$sep;
		print '"'.$data['moyensfamily'].'"'.$sep;
		print '"'.$data['moyenspropo'].'"'.$sep;
		print "\n";
	}

	exit;
}


/*
 * View
 */
llxHeader ( '', 'Stats vendeur' );


dol_fiche_head($head, 'stats', $langs->trans("Statistics"), -1);

$stats = new MonAnayseVendeurStats($db);

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
print '<tr><td class="left">' . $langs->trans("Agence") . '</td><td class="left">';
print $form->select_all_categories('user', $categid, 'categuser', null, null, 0);
print '</td></tr>';
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
print '<td>Vendeur</td>';
print '<td>Nb Ecoute</td>';
print '<td>Equipements foyer</td>';
print '<td>Composition foyer</td>';
print '<td>FAI Actuel</td>';
print '<td>Test eligibilite</td>';
print '<td>Propo cohérente avec découverte besoins</td>';
print '<td>Propo offre avec TV</td>';
print '<td>Propo Options SFR</td>';
print '<td>Propo argumentée de la 5G</td>';
print '<td>Proposition Offres partenaires</td>';
print '<td>Proposition Offre Chubb</td>';
print '<td>Proposition Accessoires</td>';
print '<td>Commentaire Vente additionneles</td>';
print '<td>Démo d au moins d un service SFR</td>';
print '<td>Invitation à répondre à l enquête</td>';
print '<td>Remise de la carte de visite avec explication</td>';
print '<td>Proposition d une prise de RDV</td>';
print '<td>Propo box ou Mig Fibre</td>';
print '<td>Propo ABO</td>';
print '<td>Propo RMD</td>';
print '<td>Propo Options ou Mig intra</td>';
print '<td>En cas de réflexion client remise d un devis</td>';
print '<td>Devis gardé en double avec coordonnées clients + Motif de réflexion</td>';
print '<td>Propo reprise mobile</td>';
print '<td>Propo FLOA</td>';
print '<td>Argu SFR FAMILY</td>';
print '<td>Propo ODR ou VF en cours</td>';
print '</tr>';
// Build graphic number of object
if (!empty($from_date) && !empty($to_date)) {
	$result = $stats->getDataStatVendeur($from_date, $to_date, $categid);
	if (!is_array($result) && $result < 0) {
		setEventMessage($stats->error, 'errors');
	} else {

		foreach($result as $userId=>$data) {
			print '<tr class="oddeven">';

			//User
			print '<td>';
			print $data['name'];
			print '</td>';
			
			//User
			print '<td>';
			print $data['nb'];
			print '</td>';


			//Nb Traitment
			print '<td>';
			print $data['foyerequip'];
			print '</td>';

			//Tx Transfo Box
			print '<td>';
			print $data['foyercompo'];
			print '</td>';

			//Tx Transfo AboHV
			print '<td>';
			print $data['foyerfai'];
			print '</td>';

			//Tx Transfo Service
			print '<td>';
			print $data['foyereli'];
			print '</td>';

			//relance
			print '<td>';
			print $data['propcoh'];
			print '</td>';

			//Picking
			print '<td>';
			print $data['proprotv'];
			print '</td>';

			//PotBox
			print '<td>';
			print $data['proprooption'];
			print '</td>';
			
			//PotBox
			print '<td>';
			print $data['propropro5g'];
			print '</td>';
			
			//PotBox
			print '<td>';
			print $data['ventespartner'];
			print '</td>';
			
			//PotBox
			print '<td>';
			print $data['venteschubb'];
			print '</td>';
			
			//PotBox
			print '<td>';
			print $data['ventesaccess'];
			print '</td>';
			
			//PotBox
			print '<td>';
			print $data['ventesaccess'];
			print '</td>';

			//Box
			print '<td>';
			print $data['expsfr'];
			print '</td>';
			
			//Box
			print '<td>';
			print $data['expenqu'];
			print '</td>';
			
			//Box
			print '<td>';
			print $data['expremise'];
			print '</td>';
			
			//Box
			print '<td>';
			print $data['exprdv'];
			print '</td>';
			
			//Box
			print '<td>';
			print $data['rebondbox'];
			print '</td>';
			
			//Box
			print '<td>';
			print $data['rebondabo'];
			print '</td>';


			//TxBB
			print '<td>';
			print $data['rebondrmd'];
			print '</td>';

			//ecoute
			print '<td>';
			print $data['rebondoptions'];
			print '</td>';
			
			//ecoute
			print '<td>';
			print $data['moyensdevis'];
			print '</td>';
			//ecoute
			print '<td>';
			print $data['moyensdouble'];
			print '</td>';
			//ecoute
			print '<td>';
			print $data['moyensreprise'];
			print '</td>';
			//ecoute
			print '<td>';
			print $data['moyensfloa'];
			print '</td>';
			//ecoute
			print '<td>';
			print $data['moyensfamily'];
			print '</td>';
			//ecoute
			print '<td>';
			print $data['moyenspropo'];
			print '</td>';
			
			
			
			
			
			

			print '</tr>';
		}


	}
}

print '</table>'."\n";

$db->close ();

llxFooter ();
