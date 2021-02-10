<?php
/* Copyright (C) 2016 Laurent Destailleur  <eldy@users.sourceforge.net>
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

/**
 *        \file       htdocs/fichinter/stats/index.php
 *      \ingroup    fichinter
 *        \brief      Page with interventions statistics
 */

require '../../main.inc.php';
require_once DOL_DOCUMENT_ROOT . '/core/class/dolgraph.class.php';

dol_include_once('/monanalysevendeur/class/pickingstats.class.php');

$WIDTH = DolGraph::getDefaultGraphSizeForStats('width');
$HEIGHT = DolGraph::getDefaultGraphSizeForStats('height');

$mode = 'customer';
if (!$user->rights->monanalysevendeur->read) accessforbidden();

$users = GETPOST('usersid', 'array');
$user_tags = GETPOST('users_tags', 'array');
$period_type = GETPOST('period_type', 'alpha');
$from_date = dol_mktime(0, 0, 0, GETPOST('frmdtmonth', 'int'), GETPOST('frmdtday', 'int'), GETPOST('frmdtyear', 'int'));
$to_date = dol_mktime(23, 59, 59, GETPOST('todtmonth', 'int'), GETPOST('todtday', 'int'), GETPOST('todtyear', 'int'));

$nowyear = strftime("%Y", dol_now());
$year = GETPOST('year') > 0 ? GETPOST('year', 'int') : $nowyear;

// Load translation files required by the page
$langs->loadLangs(array('monanalysevendeur@monanalysevendeur', 'other'));


/*
 * View
 */

$form = new Form($db);

$title = $langs->trans("StatPickingStatistics");
$dir = $conf->monanalysevendeur->dir_temp;

llxHeader('', $title);

print load_fiche_titre($title, '', 'intervention');

dol_mkdir($dir);

$stats = new PickingStats($db);

// Build graphic number of object
$result = $stats->getData($users, $user_tags, $period_type, $from_date, $to_date);
if ($result < 0) {
	setEventMessage($stats->error, 'errors');
}

$data_pickcount = $stats->data_pickcount;
//$data_transfo = $stats->data_transfo;
$legend = array();
foreach ($stats->data_legend as $i => $u) {
	$user_static = new User($db);
	$user_static->fetch($u);
	$legend[] = $user_static->getFullName($langs);
}

$filenamenb = $dir . '/monanalysevendeur_pick_count' . $period_type . '-' . hash('md5', implode(',', $users)) . hash('md5', implode(',', $user_tags)) . '-' . $from_date . '-' . $to_date . '.png';
$fileurlnb = DOL_URL_ROOT . '/viewimage.php?modulepart=monanalysevendeurstats&file=monanalysevendeur_pick_count' . $period_type . '-' . hash('md5', implode(',', $users)) . hash('md5', implode(',', $user_tags)) . '-' . $from_date . '-' . $to_date . '.png';

$px1 = new DolGraph();
$mesg = $px1->isGraphKo();
if (!$mesg && !empty($data_traitement)) {
	$px1->SetData($data_traitement);
	$px1->SetLegend($legend);
	$px1->SetMaxValue($px1->GetCeilMaxValue());
	$px1->SetMinValue(min(0, $px1->GetFloorMinValue()));
	$px1->SetWidth($WIDTH);
	$px1->SetHeight($HEIGHT);
	$px1->SetYLabel($langs->trans("Nb Picking"));
	$px1->SetShading(3);
	$px1->SetHorizTickIncrement(1);
	$px1->mode = 'depth';
	$px1->SetTitle($langs->trans("Nb Picking"));

	$px1->draw($filenamenb, $fileurlnb);
}

/*$filenamenbtx = $dir . '/monanalysevendeurtx' . $period_type . '-' . hash('md5', implode(',', $users)) . hash('md5', implode(',', $user_tags)) . '-' . $from_date . '-' . $to_date . '.png';
$fileurlnbtx = DOL_URL_ROOT . '/viewimage.php?modulepart=monanalysevendeurstats&file=monanalysevendeurtx' . $period_type . '-' . hash('md5', implode(',', $users)) . hash('md5', implode(',', $user_tags)) . '-' . $from_date . '-' . $to_date . '.png';

$px2 = new DolGraph();
$mesg = $px2->isGraphKo();
if (!$mesg && !empty($data_transfo)) {
	$px2->SetData($data_transfo);
	$px2->SetLegend($legend);
	$px2->SetMaxValue($px2->GetCeilMaxValue());
	$px2->SetMinValue(min(0, $px2->GetFloorMinValue()));
	$px2->SetWidth($WIDTH);
	$px2->SetHeight($HEIGHT);
	$px2->SetYLabel($langs->trans("Box Validée"));
	$px2->SetShading(3);
	$px2->SetHorizTickIncrement(1);
	$px2->mode = 'depth';
	$px2->SetTitle($langs->trans("Box Validée"));

	$px2->draw($filenamenbtx, $fileurlnbtx);
}

$filenamenbtx = $dir . '/monanalysevendeurtx' . $period_type . '-' . hash('md5', implode(',', $users)) . hash('md5', implode(',', $user_tags)) . '-' . $from_date . '-' . $to_date . '.png';
$fileurlnbtx = DOL_URL_ROOT . '/viewimage.php?modulepart=monanalysevendeurstats&file=monanalysevendeurtx' . $period_type . '-' . hash('md5', implode(',', $users)) . hash('md5', implode(',', $user_tags)) . '-' . $from_date . '-' . $to_date . '.png';

$px3 = new DolGraph();
$mesg = $px3->isGraphKo();
if (!$mesg && !empty($data_transfo)) {
	$px3->SetData($data_transfo);
	$px3->SetLegend($legend);
	$px3->SetMaxValue($px2->GetCeilMaxValue());
	$px3->SetMinValue(min(0, $px2->GetFloorMinValue()));
	$px3->SetWidth($WIDTH);
	$px3->SetHeight($HEIGHT);
	$px3->SetYLabel($langs->trans("Nb Picking"));
	$px3->SetShading(3);
	$px3->SetHorizTickIncrement(1);
	$px3->mode = 'depth';
	$px3->SetTitle($langs->trans("Nb Picking"));

	$px3->draw($filenamenbtx, $fileurlnbtx);
}*/

$h = 0;
$head = array();
$head[$h][0] = dol_buildpath("/monanalysevendeur/picking_stat.php", 1);
$head[$h][1] = $langs->trans("Statistics");
$head[$h][2] = 'stats';
$h++;

//$type = 'fichinter_stats';

//complete_head_from_modules($conf, $langs, null, $head, $h, $type);

dol_fiche_head($head, 'stats', $langs->trans("Statistics"), -1);


print '<div class="fichecenter"><div class="fichethirdleft">';


//if (empty($socid))
//{
// Show filter box
print '<form name="stats" method="POST" action="' . $_SERVER["PHP_SELF"] . '">';
print '<input type="hidden" name="token" value="' . newToken() . '">';
print '<input type="hidden" name="mode" value="' . $mode . '">';

print '<table class="noborder centpercent">';
print '<tr class="liste_titre"><td class="liste_titre" colspan="2">' . $langs->trans("Filter") . '</td></tr>';
// Users
print '<tr><td class="left">' . $langs->trans("Users") . '</td><td class="left">';
$userlist = $form->select_dolusers('', '', 0, null, 0, '', '', 0, 0, 0, 'AND u.statut = 1', 0, '', '', 0, 1);
// Note: If user has no right to "see all thirdparties", we force selection of sale representative to him, so after creation he can see the record.
print $form->multiselectarray('usersid', $userlist, $users, null, null, null, null, "90%");
print '</td></tr>';
// Categorie users
print '<tr><td class="left">' . $langs->trans("Categories") . '</td><td class="left">';
$cate_arbo = $form->select_all_categories('user', null, 'parent', null, null, 1);
print $form->multiselectarray('users_tags', $cate_arbo, $user_tags, null, null, null, null, '90%');
// Periode Type
print '<tr><td class="left">' . $langs->trans("Period") . '</td><td class="left">';
$type_period_array = array('day' => $langs->trans('Day'),
	'week'=>$langs->trans('Week'),
	'month' => $langs->trans('Month'));
print $form->selectarray('period_type', $type_period_array, $period_type, 1, 0, 0, '', 1);
print '</td></tr>';
// From Dt To Dt
print '<tr><td class="left">' . $langs->trans("From") . '</td><td class="left">';
print $form->selectDate($from_date, 'frmdt', 0, 0, 1, 'stats', 1, 0);
print '</td></tr>';
print '<tr><td class="left">' . $langs->trans("To") . '</td><td class="left">';
print $form->selectDate($to_date, 'todt', 0, 0, 1, 'stats', 1, 0);
print '</td></tr>';
print '<tr><td class="center" colspan="2"><input type="submit" name="submit" class="button" value="' . $langs->trans("Refresh") . '"></td></tr>';
print '</table>';
print '</form>';
print '<br><br>';
//}

print '<div class="div-table-responsive-no-min">';
print '<table class="noborder centpercent">';
print '<tr class="liste_titre" height="24">';
print '<td class="center">' . $langs->trans($type_period_array[$period_type]) . '</td>';
print '<td class="center">' . $langs->trans('User') . '</td>';
print '<td class="right">' . $langs->trans("Potentiel Box") . '</td>';
print '<td class="right">' . $langs->trans("Box Validée") . '</td>';
print '</tr>';


foreach ($stats->data_row as $val) {

	print '<tr class="oddeven">';
	print '<td class="center">' . $val[0] . '</td>';
	$user_static = new User($db);
	$user_static->fetch($val[1]);
	print '<td class="center">' . $user_static->getFullName($langs). '</td>';
	print '<td class="right">' . $val[2] . '</td>';
	print '<td class="right">' . ($val[2]!=0?round(($val[3] / $val[2]) * 100):'') . '</td>';
	print '</tr>';
}

print '</table>';
print '</div>';


print '</div><div class="fichetwothirdright"><div class="ficheaddleft">';


// Show graphs
print '<table class="border centpercent"><tr class="pair nohover"><td class="center">';
if ($mesg) {
	print $mesg;
} else {
	print $px1->show();
	print "<br>\n";
	/*print $px2->show();
	print "<br>\n";
	print $px3->show();*/
}
print '</td></tr></table>';


print '</div></div></div>';
print '<div style="clear:both"></div>';

dol_fiche_end();


llxFooter();

$db->close();

