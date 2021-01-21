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
 *	    \file       htdocs/fichinter/stats/index.php
 *      \ingroup    fichinter
 *		\brief      Page with interventions statistics
 */

require '../../main.inc.php';
require_once DOL_DOCUMENT_ROOT.'/core/class/dolgraph.class.php';

dol_include_once('/monanalysevendeur/class/monanalysevendeurstats.class.php');

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

$title = $langs->trans("StatMonAnayseVendeurStatistics");
$dir = $conf->monanalysevendeur->dir_temp;

llxHeader('', $title);

print load_fiche_titre($title, '', 'intervention');

dol_mkdir($dir);

$stats = new MonAnayseVendeurStats($db);

// Build graphic number of object
$result = $stats->getData($users, $user_tags, $period_type, $from_date, $to_date);
if ($result<0) {
	setEventMessage($stats->error,'errors');
}

$data =  $stats->data;

$filenamenb = $dir.'/monanalysevendeur'.$period_type.'-'.hash('md5',implode(',',$users)).hash('md5',implode(',',$user_tags)).'-'.$from_date.'-'.$to_date.'.png';
$fileurlnb = DOL_URL_ROOT.'/viewimage.php?modulepart=monanalysevendeurstats&file=monanalysevendeur'.$period_type.'-'.hash('md5',implode(',',$users)).hash('md5',implode(',',$user_tags)).'-'.$from_date.'-'.$to_date.'.png';


$px1 = new DolGraph();
$mesg = $px1->isGraphKo();
if (!$mesg)
{
    $px1->SetData($data);
    $i = $from_date;
    $legend = array();
    /*while ($i <= $endyear)
    {
        $legend[] = $i;
        $i++;
    }*/
    $px1->SetLegend($legend);
    $px1->SetMaxValue($px1->GetCeilMaxValue());
    $px1->SetMinValue(min(0, $px1->GetFloorMinValue()));
    $px1->SetWidth($WIDTH);
    $px1->SetHeight($HEIGHT);
    $px1->SetYLabel($langs->trans("Nombre Traitement"));
    $px1->SetShading(3);
    $px1->SetHorizTickIncrement(1);
    $px1->mode = 'depth';
    $px1->SetTitle($langs->trans("Nombre Traitement"));

    $px1->draw($filenamenb, $fileurlnb);
}

/*
// Build graphic amount of object
$data = $stats->getAmountByMonthWithPrevYear($endyear, $startyear);
// $data = array(array('Lib',val1,val2,val3),...)

if (!$user->rights->societe->client->voir || $user->socid)
{
    $filenameamount = $dir.'/interventionsamountinyear-'.$user->id.'-'.$year.'.png';
    $fileurlamount = DOL_URL_ROOT.'/viewimage.php?modulepart=interventionstats&file=interventionsamountinyear-'.$user->id.'-'.$year.'.png';
}
else
{
    $filenameamount = $dir.'/interventionsamountinyear-'.$year.'.png';
    $fileurlamount = DOL_URL_ROOT.'/viewimage.php?modulepart=interventionstats&file=interventionsamountinyear-'.$year.'.png';
}

$px2 = new DolGraph();
$mesg = $px2->isGraphKo();
if (!$mesg)
{
    $px2->SetData($data);
    $i = $startyear; $legend = array();
    while ($i <= $endyear)
    {
        $legend[] = $i;
        $i++;
    }
    $px2->SetLegend($legend);
    $px2->SetMaxValue($px2->GetCeilMaxValue());
    $px2->SetMinValue(min(0, $px2->GetFloorMinValue()));
    $px2->SetWidth($WIDTH);
    $px2->SetHeight($HEIGHT);
    $px2->SetYLabel($langs->trans("AmountOfinterventions"));
    $px2->SetShading(3);
    $px2->SetHorizTickIncrement(1);
    $px2->mode = 'depth';
    $px2->SetTitle($langs->trans("AmountOfinterventionsByMonthHT"));

    $px2->draw($filenameamount, $fileurlamount);
}


$data = $stats->getAverageByMonthWithPrevYear($endyear, $startyear);

if (!$user->rights->societe->client->voir || $user->socid)
{
    $filename_avg = $dir.'/interventionsaverage-'.$user->id.'-'.$year.'.png';
    $fileurl_avg = DOL_URL_ROOT.'/viewimage.php?modulepart=interventionstats&file=interventionsaverage-'.$user->id.'-'.$year.'.png';
}
else
{
    $filename_avg = $dir.'/interventionsaverage-'.$year.'.png';
    $fileurl_avg = DOL_URL_ROOT.'/viewimage.php?modulepart=interventionstats&file=interventionsaverage-'.$year.'.png';
}

$px3 = new DolGraph();
$mesg = $px3->isGraphKo();
if (!$mesg)
{
    $px3->SetData($data);
    $i = $startyear; $legend = array();
    while ($i <= $endyear)
    {
        $legend[] = $i;
        $i++;
    }
    $px3->SetLegend($legend);
    $px3->SetYLabel($langs->trans("AmountAverage"));
    $px3->SetMaxValue($px3->GetCeilMaxValue());
    $px3->SetMinValue($px3->GetFloorMinValue());
    $px3->SetWidth($WIDTH);
    $px3->SetHeight($HEIGHT);
    $px3->SetShading(3);
    $px3->SetHorizTickIncrement(1);
    $px3->mode = 'depth';
    $px3->SetTitle($langs->trans("AmountAverage"));

    $px3->draw($filename_avg, $fileurl_avg);
}

*/
/*
// Show array
$data = $stats->getAllByYear();
$arrayyears = array();
foreach ($data as $val) {
	if (!empty($val['year'])) {
		$arrayyears[$val['year']] = $val['year'];
	}
}
if (!count($arrayyears)) $arrayyears[$nowyear] = $nowyear;
*/
$h = 0;
$head = array();
$head[$h][0] = dol_buildpath("/monanalysevendeur/index_stat.php", 1);
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
	print '<form name="stats" method="POST" action="'.$_SERVER["PHP_SELF"].'">';
	print '<input type="hidden" name="token" value="'.newToken().'">';
	print '<input type="hidden" name="mode" value="'.$mode.'">';

	print '<table class="noborder centpercent">';
	print '<tr class="liste_titre"><td class="liste_titre" colspan="2">'.$langs->trans("Filter").'</td></tr>';
	// Users
	print '<tr><td class="left">'.$langs->trans("Users").'</td><td class="left">';
	$userlist = $form->select_dolusers('', '', 0, null, 0, '', '', 0, 0, 0, 'AND u.statut = 1', 0, '', '', 0, 1);
	// Note: If user has no right to "see all thirdparties", we force selection of sale representative to him, so after creation he can see the record.
	print $form->multiselectarray('usersid', $userlist, $users, null, null, null, null, "90%");
	print '</td></tr>';
	// Categorie users
	print '<tr><td class="left">'.$langs->trans("Categories").'</td><td class="left">';
	$cate_arbo = $form->select_all_categories('user', null, 'parent', null, null, 1);
	print $form->multiselectarray('users_tags', $cate_arbo, $user_tags, null, null, null, null, '90%');
	// Periode Type
	print '<tr><td class="left">'.$langs->trans("Period").'</td><td class="left">';
	$type_period_array = array('day'=>$langs->trans('Day'),
		//'week'=>$langs->trans('Week'),
		'month'=>$langs->trans('Month'));
	print $form->selectarray('period_type', $type_period_array, $period_type, 1, 0, 0, '', 1);
	print '</td></tr>';
	// From Dt To Dt
	print '<tr><td class="left">'.$langs->trans("From").'</td><td class="left">';
	print $form->selectDate($from_date, 'frmdt', 0, 0, 1, 'stats', 1, 0);
	print '</td></tr>';
	print '<tr><td class="left">'.$langs->trans("To").'</td><td class="left">';
	print $form->selectDate($to_date, 'todt', 0, 0, 1, 'stats', 1, 0);
	print '</td></tr>';
	print '<tr><td class="center" colspan="2"><input type="submit" name="submit" class="button" value="'.$langs->trans("Refresh").'"></td></tr>';
	print '</table>';
	print '</form>';
	print '<br><br>';
//}

print '<div class="div-table-responsive-no-min">';
print '<table class="noborder centpercent">';
print '<tr class="liste_titre" height="24">';
print '<td class="center">'.$langs->trans($type_period_array[$period_type]).'</td>';
print '<td class="right">'.$langs->trans("Nombre Traitement").'</td>';
print '<td class="right">%</td>';
print '<td class="right">'.$langs->trans("AmountTotal").'</td>';
print '<td class="right">%</td>';
print '<td class="right">'.$langs->trans("AmountAverage").'</td>';
print '<td class="right">%</td>';
print '</tr>';

$oldyear = 0;
foreach ($data as $val)
{
	/*$year = $val['year'];
	while (!empty($year) && $oldyear > $year + 1)
	{
        // If we have empty year
		$oldyear--;

		print '<tr class="oddeven" height="24">';
		print '<td class="center"><a href="'.$_SERVER["PHP_SELF"].'?year='.$oldyear.'&amp;mode='.$mode.($socid > 0 ? '&socid='.$socid : '').($userid > 0 ? '&userid='.$userid : '').'">'.$oldyear.'</a></td>';

		print '<td class="right">0</td>';
		print '<td class="right"></td>';
		print '<td class="right">0</td>';
		print '<td class="right"></td>';
		print '<td class="right">0</td>';
		print '<td class="right"></td>';
		print '</tr>';
	}


	print '<tr class="oddeven" height="24">';
	print '<td class="center"><a href="'.$_SERVER["PHP_SELF"].'?year='.$year.'&amp;mode='.$mode.($socid > 0 ? '&socid='.$socid : '').($userid > 0 ? '&userid='.$userid : '').'">'.$year.'</a></td>';
	print '<td class="right">'.$val['nb'].'</td>';
	print '<td class="right" style="'.(($val['nb_diff'] >= 0) ? 'color: green;' : 'color: red;').'">'.round($val['nb_diff']).'</td>';
	print '<td class="right">'.price(price2num($val['total'], 'MT'), 1).'</td>';
	print '<td class="right" style="'.(($val['total_diff'] >= 0) ? 'color: green;' : 'color: red;').'">'.round($val['total_diff']).'</td>';
	print '<td class="right">'.price(price2num($val['avg'], 'MT'), 1).'</td>';
	print '<td class="right" style="'.(($val['avg_diff'] >= 0) ? 'color: green;' : 'color: red;').'">'.round($val['avg_diff']).'</td>';
	print '</tr>';
	$oldyear = $year;*/
}

print '</table>';
print '</div>';


print '</div><div class="fichetwothirdright"><div class="ficheaddleft">';


// Show graphs
print '<table class="border centpercent"><tr class="pair nohover"><td class="center">';
if ($mesg) { print $mesg; }
else {
    print $px1->show();
    /*print "<br>\n";
    print $px2->show();
    print "<br>\n";
    print $px3->show();*/
}
print '</td></tr></table>';


print '</div></div></div>';
print '<div style="clear:both"></div>';

dol_fiche_end();


llxFooter();

$db->close();

