<?php

/* Copyright (C) 2007-2017 Laurent Destailleur  <eldy@users.sourceforge.net>
 * Copyright (C) ---Put here your own copyright and developer email---
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
 * along with this program. If not, see <http://www.gnu.org/licenses/>.
 */

/**
 *    \file       contacttracking_list.php
 *        \ingroup    contacttracking
 *        \brief      List page for contacttracking
 */
//if (! defined('NOREQUIREUSER'))          define('NOREQUIREUSER','1');
//if (! defined('NOREQUIREDB'))            define('NOREQUIREDB','1');
//if (! defined('NOREQUIRESOC'))           define('NOREQUIRESOC','1');
//if (! defined('NOREQUIRETRAN'))          define('NOREQUIRETRAN','1');
//if (! defined('NOSCANGETFORINJECTION'))  define('NOSCANGETFORINJECTION','1');			// Do not check anti CSRF attack test
//if (! defined('NOSCANPOSTFORINJECTION')) define('NOSCANPOSTFORINJECTION','1');		// Do not check anti CSRF attack test
//if (! defined('NOCSRFCHECK'))            define('NOCSRFCHECK','1');			// Do not check anti CSRF attack test done when option MAIN_SECURITY_CSRF_WITH_TOKEN is on.
//if (! defined('NOSTYLECHECK'))           define('NOSTYLECHECK','1');			// Do not check style html tag into posted data
//if (! defined('NOTOKENRENEWAL'))         define('NOTOKENRENEWAL','1');		// Do not check anti POST attack test
//if (! defined('NOREQUIREMENU'))          define('NOREQUIREMENU','1');			// If there is no need to load and show top and left menu
//if (! defined('NOREQUIREHTML'))          define('NOREQUIREHTML','1');			// If we don't need to load the html.form.class.php
//if (! defined('NOREQUIREAJAX'))          define('NOREQUIREAJAX','1');         // Do not load ajax.lib.php library
//if (! defined("NOLOGIN"))                define("NOLOGIN",'1');				// If this page is public (can be called outside logged session)
// Load Dolibarr environment
$res = 0;
// Try main.inc.php into web root known defined into CONTEXT_DOCUMENT_ROOT (not always defined)
if (!$res && !empty($_SERVER["CONTEXT_DOCUMENT_ROOT"]))
	$res = @include $_SERVER["CONTEXT_DOCUMENT_ROOT"] . "/main.inc.php";
// Try main.inc.php into web root detected using web root caluclated from SCRIPT_FILENAME
$tmp = empty($_SERVER['SCRIPT_FILENAME']) ? '' : $_SERVER['SCRIPT_FILENAME'];
$tmp2 = realpath(__FILE__);
$i = strlen($tmp) - 1;
$j = strlen($tmp2) - 1;
while ($i > 0 && $j > 0 && isset($tmp[$i]) && isset($tmp2[$j]) && $tmp[$i] == $tmp2[$j]) {
	$i--;
	$j--;
}

if (!$res && $i > 0 && file_exists(substr($tmp, 0, ($i + 1)) . "/main.inc.php"))
	$res = @include substr($tmp, 0, ($i + 1)) . "/main.inc.php";
if (!$res && $i > 0 && file_exists(dirname(substr($tmp, 0, ($i + 1))) . "/main.inc.php"))
	$res = @include dirname(substr($tmp, 0, ($i + 1))) . "/main.inc.php";
// Try main.inc.php using relative path
if (!$res && file_exists("../main.inc.php"))
	$res = @include "../main.inc.php";
if (!$res && file_exists("../../main.inc.php"))
	$res = @include "../../main.inc.php";
if (!$res && file_exists("../../../main.inc.php"))
	$res = @include "../../../main.inc.php";
if (!$res)
	die("Include of main fails");

require_once DOL_DOCUMENT_ROOT . '/core/class/html.formcompany.class.php';
require_once DOL_DOCUMENT_ROOT . '/core/lib/date.lib.php';
require_once DOL_DOCUMENT_ROOT . '/core/lib/company.lib.php';
dol_include_once('/monanalysevendeur/class/contacttracking.class.php');
dol_include_once('/comm/action/class/actioncomm.class.php');
dol_include_once('/comm/action/class/cactioncomm.class.php');

require_once DOL_DOCUMENT_ROOT . '/core/class/html.formactions.class.php';

$cactioncomm = new CActionComm($db);

dol_include_once('/comm/propal/class/propal.class.php');
dol_include_once('/commande/class/commande.class.php');
dol_include_once('/compta/facture/class/facture.class.php');
dol_include_once('/fourn/class/fournisseur.commande.class.php');
dol_include_once('/supplier_proposal/class/supplier_proposal.class.php');
dol_include_once('/fourn/class/fournisseur.facture.class.php');
dol_include_once('/user/class/user.class.php');
dol_include_once('/societe/class/societe.class.php');

$propal = new Propal($db);
$commande = new Commande($db);
$facture = new Facture($db);
$supplierProposal = new SupplierProposal($db);
$commandeFournisseur = new CommandeFournisseur($db);
$factureFournisseur = new FactureFournisseur($db);
$userdef = new User($db);
$societedef = new Societe($db);
$formactions = new FormActions($db);

global $user;

$objecttab = array(
	'propal' => $propal,
	'commande' => $commande,
	'order' => $commande,
	'facture' => $facture,
	'supplier_proposal' => $supplierProposal,
	'order_supplier' => $commandeFournisseur,
	'fournisseur.commande' => $commandeFournisseur,
	'invoice_supplier' => $factureFournisseur,
	'fournisseur.facture' => $factureFournisseur,
	'societe' => $societedef,
);

// Load traductions files requiredby by page
if (method_exists($langs, 'loadLangs')) {
	$langs->loadLangs(array("contacttracking@monanalysevendeur", "other"));
} else {
	$langs->load('other');
	$langs->load('companies');
	$langs->load('main');
	$langs->load('user');
	$langs->load('contacttracking@monanalysevendeur');
}

$action = GETPOST('action', 'alpha') ? GETPOST('action', 'alpha') : 'view';    // The action 'add', 'create', 'edit', 'update', 'view', ...
$massaction = GETPOST('massaction', 'alpha');           // The bulk action (combo box choice into lists)
$show_files = GETPOST('show_files', 'int');            // Show files area generated by bulk actions ?
$confirm = GETPOST('confirm', 'alpha');            // Result of a confirmation
$cancel = GETPOST('cancel', 'alpha');            // We click on a Cancel button
$toselect = GETPOST('toselect', 'array');            // Array of ids of elements selected into a list
$contextpage = GETPOST('contextpage', 'aZ') ? GETPOST('contextpage', 'aZ') : 'contacttrackinglist';   // To manage different context of search
$backtopage = GETPOST('backtopage', 'alpha');           // Go back to a dedicated page
$optioncss = GETPOST('optioncss', 'aZ');            // Option for the css output (always '' except when 'print')

$id = GETPOST('id', 'int');

// Load variable for pagination
$limit = GETPOST('limit', 'int') ? GETPOST('limit', 'int') : $conf->liste_limit;
$sortfield = GETPOST('sortfield', 'alpha');
$sortorder = GETPOST('sortorder', 'alpha');
$page = GETPOST('page', 'int');
if (empty($page) || $page == -1) {
	$page = 0;
}     // If $page is not defined, or '' or -1
$offset = $limit * $page;
$pageprev = $page - 1;
$pagenext = $page + 1;

// Initialize technical objects
$object = new Contacttracking($db);
$extrafields = new ExtraFields($db);
$diroutputmassaction = $conf->monanalysevendeur->dir_output . '/temp/massgeneration/' . $user->id;
$hookmanager->initHooks(array('contacttrackinglist'));     // Note that conf->hooks_modules contains array
// Fetch optionals attributes and labels
$extralabels = $extrafields->fetch_name_optionals_label('contacttracking');
$search_array_options = $extrafields->getOptionalsFromPost($extralabels, '', 'search_');

// Default sort order (if not yet defined by previous GETPOST)
if (!$sortfield)
	$sortfield = "t.date_creation";   // Set here default search field. By default 1st field in definition.
if (!$sortorder)
	$sortorder = "DESC";

// Protection if external user
$socid = 0;
if ($user->societe_id > 0) {
	//$socid = $user->societe_id;
	accessforbidden();
}

if (!$user->rights->monanalysevendeur->read) {
	accessforbidden();
}

// Initialize array of search criterias
$search_all = trim(GETPOST("search_all", 'alpha'));
$search = array();
foreach ($object->fields as $key => $val) {
	switch ($key) {
		case 'fk_product':
			$search['nom'] = GETPOST('search_' . $key, 'alpha');
			break;
		default:
			if (GETPOST('search_' . $key, 'alpha')) {
				$search[$key] = GETPOST('search_' . $key, 'alpha');
			}
	}
}

$object->fields['fk_event']['enabled'] = GETPOST('relance', 'int');

if ((GETPOST('element_type') == 'thirdparty' || GETPOST('element_type') == 'societe') && GETPOST('socid')) {
	$search['fk_soc'] = GETPOST('socid');
}

if (GETPOST('element_type') == 'order' && GETPOST('id')) {
	$search['element_type'] = 'commande';
	$search['fk_element_id'] = GETPOST('id');
}

if (GETPOST('element_type') == 'propal' && GETPOST('id')) {
	$search['element_type'] = 'propal';
	$search['fk_element_id'] = GETPOST('id');
}

if (GETPOST('element_type') == 'contact' && GETPOST('id')) {
	$search['fk_contact'] = GETPOST('id');
}

if (GETPOST('element_type') == 'user' && GETPOST('id')) {
	$search['fk_user_creat'] = GETPOST('id');
}

if (GETPOST('element_type') == 'intervention' && GETPOST('id')) {
	$search['element_type'] = 'fichinter';
	$search['fk_element_id'] = GETPOST('id');
}

if (GETPOST('element_type') == 'invoice' && GETPOST('id')) {
	$search['element_type'] = 'facture';
	$search['fk_element_id'] = GETPOST('id');
}

if (GETPOST('element_type') == 'contract' && GETPOST('id')) {
	$search['element_type'] = 'contrat';
	$search['fk_element_id'] = GETPOST('id');
}

if (GETPOST('element_type') == 'stock' && GETPOST('id')) {
	$search['element_type'] = 'stock';
	$search['fk_element_id'] = GETPOST('id');
}

if (GETPOST('element_type') == 'project' && GETPOST('id')) {
	$search['element_type'] = 'projet';
	$search['fk_element_id'] = GETPOST('id');
}

if (GETPOST('element_type') == 'supplier_proposal' && GETPOST('id')) {
	$search['element_type'] = 'supplier_proposal';
	$search['fk_element_id'] = GETPOST('id');
}

if (GETPOST('element_type') == 'invoice_supplier' && GETPOST('id')) {
	$search['element_type'] = 'invoice_supplier';
	$search['fk_element_id'] = GETPOST('id');
}

if (GETPOST('element_type') == 'supplier_order' && GETPOST('id')) {
	$search['element_type'] = 'supplier_order';
	$search['fk_element_id'] = GETPOST('id');
}

if ($search['fk_soc'] == '-1') {
	$search['fk_soc'] = '';
}

if ($user->rights->monanalysevendeur->read) {
	if ($search['fk_user_creat'] == -1) {
		$search['fk_user_creat'] = GETPOST('id');
	}
} else {
	$search['fk_user_creat'] = $user->id;
}

// List of fields to search into when doing a "search in all"
$fieldstosearchall = array();
foreach ($object->fields as $key => $val) {
	if ($val['searchall'])
		$fieldstosearchall['t.' . $key] = $val['label'];
}

// Definition of fields for list
$arrayfields = array();
foreach ($object->fields as $key => $val) {
	// If $val['visible']==0, then we never show the field
	if (!empty($val['visible']))
		$arrayfields['t.' . $key] = array('label' => $val['label'], 'checked' => (($val['visible'] < 0) ? 0 : 1), 'enabled' => $val['enabled'], 'position' => $val['position']);
}

// Extra fields
if (is_array($extrafields->attribute_label) && count($extrafields->attribute_label)) {
	foreach ($extrafields->attribute_label as $key => $val) {
		if (!empty($extrafields->attribute_list[$key]))
			$arrayfields["ef." . $key] = array('label' => $extrafields->attribute_label[$key], 'checked' => (($extrafields->attribute_list[$key] < 0) ? 0 : 1), 'position' => $extrafields->attribute_pos[$key], 'enabled' => (abs($extrafields->attribute_list[$key]) != 3 && $extrafields->attribute_perms[$key]));
	}
}

$object->fields = dol_sort_array($object->fields, 'position');
$arrayfields = dol_sort_array($arrayfields, 'position');


/*
 * Actions
 *
 * Put here all code to do according to value of "$action" parameter
 */

if ($action == 'delete' && $confirm == 'yes' && !empty($toselect)) {
	foreach ($toselect as $key => $idExchange) {
		$exchange = new Contacttracking($db);
		$exchange->fetch($idExchange);
		$exchange->delete($user);
	}
}

if (GETPOST('cancel', 'alpha')) {
	$action = 'list';
	$massaction = '';
}

if (!GETPOST('confirmmassaction', 'alpha') && $massaction != 'presend' && $massaction != 'confirm_presend') {
	$massaction = '';
}

$parameters = array();
$reshook = $hookmanager->executeHooks('doActions', $parameters, $object, $action);    // Note that $action and $object may have been modified by some hooks
if ($reshook < 0)
	setEventMessages($hookmanager->error, $hookmanager->errors, 'errors');

if (empty($reshook)) {
	// Selection of new fields
	@include DOL_DOCUMENT_ROOT . '/core/actions_changeselectedfields.inc.php';

	// Purge search criteria
	if (GETPOST('button_removefilter_x', 'alpha') || GETPOST('button_removefilter.x', 'alpha') || GETPOST('button_removefilter', 'alpha')) { // All tests are required to be compatible with all browsers
		foreach ($object->fields as $key => $val) {
			$search[$key] = '';
		}

		$toselect = '';
		$search_array_options = array();
	}

	if (GETPOST('button_removefilter_x', 'alpha') || GETPOST('button_removefilter.x', 'alpha') || GETPOST('button_removefilter', 'alpha') || GETPOST('button_search_x', 'alpha') || GETPOST('button_search.x', 'alpha') || GETPOST('button_search', 'alpha')) {
		$massaction = '';     // Protection to avoid mass action if we force a new search during a mass action confirmation
	}

	// Mass actions
	$objectclass = 'Contacttracking';
	$objectlabel = 'Contacttracking';
	$permtoread = $user->rights->monanalysevendeur->read;
	$permtodelete = $user->rights->monanalysevendeur->delete;
	$uploaddir = $conf->monanalysevendeur->dir_output;
	include DOL_DOCUMENT_ROOT . '/core/actions_massactions.inc.php';
}


/*
 * View
 *
 * Put here all code to render page
 */

$form = new Form($db);

$now = dol_now();

//$help_url="EN:Module_Contacttracking|FR:Module_Contacttracking_FR|ES:Módulo_Contacttracking";
$help_url = '';
$title = $langs->trans('ListOf', $langs->transnoentitiesnoconv("Contacttrackings"));


// Build and execute select
// --------------------------------------------------------------------

$sql = 'SELECT t.rowid, t.entity, t.mode_contact, t.fk_product, t.fk_event, t.type_event, t.fk_soc, t.fk_contact, t.element_type, t.fk_element_id, t.object, t.comment, t.date_creation, t.tms, t.fk_user_creat, t.fk_user_modif
        FROM ' . MAIN_DB_PREFIX . 'contacttracking as t ';

// Add fields from hooks
$parameters = array();
$reshook = $hookmanager->executeHooks('printFieldListSelect', $parameters, $object);    // Note that $action and $object may have been modified by hook
$sql .= $hookmanager->resPrint;
if (is_array($extrafields->attribute_label) && count($extrafields->attribute_label))
	$sql .= " LEFT JOIN " . MAIN_DB_PREFIX . "contacttracking_extrafields as ef on (t.rowid = ef.fk_object)";

////add link to table in order to recup relance date : ac.datep
if (GETPOST('relance', 'int') == 1) {
	$sql .= " LEFT JOIN " . MAIN_DB_PREFIX . "actioncomm as ac on (ac.id = t.fk_event)";
} 


if ($object->ismultientitymanaged == 1)
	$sql .= " WHERE t.entity IN (" . getEntity('contacttracking') . ")";
else
	$sql .= " WHERE 1 = 1";
foreach ($search as $key => $val) {
	if ((int)DOL_VERSION >= 7) {
		$mode_search = (($object->isInt($object->fields[$key]) || $object->isFloat($object->fields[$key])) ? 1 : 0);
	}

	if ($search[$key] != '')
		$sql .= natural_search($key, $search[$key], (($key == 'status') ? 2 : $mode_search));
}

if ($search_all)
	$sql .= natural_search(array_keys($fieldstosearchall), $search_all);

if (GETPOST('relance', 'int') == 1) {
	$sql .= " AND t.fk_event IS NOT NULL";
} else {
	$sql .= " AND t.fk_event IS NULL";
}

///  / Add where from extra fields
@include DOL_DOCUMENT_ROOT . '/core/tpl/extrafields_list_search_sql.tpl.php';
// Add where from hooks
$parameters = array();
$reshook = $hookmanager->executeHooks('printFieldListWhere', $parameters, $object);    // Note that $action and $object may have been modified by hook
$sql .= $hookmanager->resPrint;

$sql .= $db->order($sortfield, $sortorder);
// Count total nb of records
$nbtotalofrecords = '';
if (empty($conf->global->MAIN_DISABLE_FULL_SCANLIST)) {
	$result = $db->query($sql);
	$nbtotalofrecords = $db->num_rows($result);
}

$sql .= $db->plimit($limit + 1, $offset);
$resql = $db->query($sql);
if (!$resql) {
	dol_print_error($db);
	exit;
}

$num = $db->num_rows($resql);

// Output page
// --------------------------------------------------------------------

llxHeader('', $title, '');
$param = '&element_type=' . GETPOST('element_type') . '&id=' . GETPOST('id');
if (GETPOST('element_type') == 'thirdparty') {
	require_once DOL_DOCUMENT_ROOT . '/core/lib/company.lib.php';
	require_once DOL_DOCUMENT_ROOT . '/societe/class/societe.class.php';
	$soc = new Societe($db);
	$soc->fetch(GETPOST('socid'));
	$head = societe_prepare_head($soc);
	dol_fiche_head($head, 'historyexchange', $langs->trans("ThirdParty"), -1, 'company');
	$param = '&element_type=' . GETPOST('element_type') . '&socid=' . GETPOST('socid');
}

if (GETPOST('element_type') == 'order') {
	require_once DOL_DOCUMENT_ROOT . '/core/lib/order.lib.php';
	require_once DOL_DOCUMENT_ROOT . '/commande/class/commande.class.php';
	$com = new Commande($db);
	$com->fetch(GETPOST('id'));
	$head = commande_prepare_head($com);
	dol_fiche_head($head, 'historyexchange', $langs->trans("CustomerOrder"), -1, 'order');
}

if (GETPOST('element_type') == 'propal') {
	require_once DOL_DOCUMENT_ROOT . '/core/lib/propal.lib.php';
	require_once DOL_DOCUMENT_ROOT . '/comm/propal/class/propal.class.php';
	$prop = new Propal($db);
	$prop->fetch(GETPOST('id'));
	$head = propal_prepare_head($prop);
	dol_fiche_head($head, 'historyexchange', $langs->trans("Proposal"), -1, 'propal');
}

if (GETPOST('element_type') == 'contact') {
	require_once DOL_DOCUMENT_ROOT . '/core/lib/contact.lib.php';
	require_once DOL_DOCUMENT_ROOT . '/contact/class/contact.class.php';
	$cont = new Contact($db);
	$cont->fetch(GETPOST('id'));
	$head = contact_prepare_head($cont);
	dol_fiche_head($head, 'historyexchange', $langs->trans("Contact"), -1, 'contact');
}

if (GETPOST('element_type') == 'user') {
	require_once DOL_DOCUMENT_ROOT . '/core/lib/usergroups.lib.php';
	require_once DOL_DOCUMENT_ROOT . '/user/class/user.class.php';
	$us = new User($db);
	$us->fetch(GETPOST('id'));
	$head = user_prepare_head($us);
	dol_fiche_head($head, 'historyexchange', $langs->trans("User"), -1, 'user');
}

if (GETPOST('element_type') == 'intervention') {
	require_once DOL_DOCUMENT_ROOT . '/core/lib/fichinter.lib.php';
	require_once DOL_DOCUMENT_ROOT . '/fichinter/class/fichinter.class.php';
	$fichinter = new Fichinter($db);
	$fichinter->fetch(GETPOST('id'));
	$head = fichinter_prepare_head($fichinter);
	dol_fiche_head($head, 'historyexchange', $langs->trans("Fichinter"), -1, 'fichinter');
}

if (GETPOST('element_type') == 'invoice') {
	require_once DOL_DOCUMENT_ROOT . '/core/lib/invoice.lib.php';
	require_once DOL_DOCUMENT_ROOT . '/compta/facture/class/facture.class.php';
	$fact = new Facture($db);
	$fact->fetch(GETPOST('id'));
	$head = facture_prepare_head($fact);
	dol_fiche_head($head, 'historyexchange', $langs->trans("Facture"), -1, 'invoice');
}

if (GETPOST('element_type') == 'contract') {
	require_once DOL_DOCUMENT_ROOT . '/core/lib/contract.lib.php';
	require_once DOL_DOCUMENT_ROOT . '/contrat/class/contrat.class.php';
	$contr = new Contrat($db);
	$contr->fetch(GETPOST('id'));
	$head = contract_prepare_head($contr);
	dol_fiche_head($head, 'historyexchange', $langs->trans("Contract"), -1, 'contract');
}

if (GETPOST('element_type') == 'stock') {
	require_once DOL_DOCUMENT_ROOT . '/core/lib/stock.lib.php';
	require_once DOL_DOCUMENT_ROOT . '/product/stock/class/entrepot.class.php';
	$entrepot = new Entrepot($db);
	$entrepot->fetch(GETPOST('id'));
	$head = stock_prepare_head($entrepot);
	dol_fiche_head($head, 'historyexchange', $langs->trans("Warehouse"), -1, 'stock');
}

if (GETPOST('element_type') == 'project') {
	require_once DOL_DOCUMENT_ROOT . '/core/lib/project.lib.php';
	require_once DOL_DOCUMENT_ROOT . '/projet/class/project.class.php';
	$project = new Project($db);
	$project->fetch(GETPOST('id'));
	$head = project_prepare_head($project);
	dol_fiche_head($head, 'historyexchange', $langs->trans("Project"), -1, 'project');
}

if (GETPOST('element_type') == 'supplier_proposal') {
	require_once DOL_DOCUMENT_ROOT . '/core/lib/supplier_proposal.lib.php';
	require_once DOL_DOCUMENT_ROOT . '/supplier_proposal/class/supplier_proposal.class.php';
	$supplierproposal = new SupplierProposal($db);
	$supplierproposal->fetch(GETPOST('id'));
	$head = supplier_proposal_prepare_head($supplierproposal);
	dol_fiche_head($head, 'historyexchange', $langs->trans("CommRequest"), -1, 'supplier_proposal');
}

if (GETPOST('element_type') == 'invoice_supplier') {
	require_once DOL_DOCUMENT_ROOT . '/core/lib/fourn.lib.php';
	require_once DOL_DOCUMENT_ROOT . '/fourn/class/fournisseur.facture.class.php';
	$facturefournisseur = new FactureFournisseur($db);
	$facturefournisseur->fetch(GETPOST('id'));
	$head = facturefourn_prepare_head($facturefournisseur);
	dol_fiche_head($head, 'historyexchange', $langs->trans("SupplierInvoice"), -1, 'bill');
}

if (GETPOST('element_type') == 'supplier_order') {
	require_once DOL_DOCUMENT_ROOT . '/core/lib/fourn.lib.php';
	require_once DOL_DOCUMENT_ROOT . '/fourn/class/fournisseur.commande.class.php';
	$commandefournisseur = new CommandeFournisseur($db);
	$commandefournisseur->fetch(GETPOST('id'));
	$head = ordersupplier_prepare_head($commandefournisseur);
	dol_fiche_head($head, 'historyexchange', $langs->trans("SupplierOrder"), -1, 'order');
}


$arrayofselected = is_array($toselect) ? $toselect : array();


if (!empty($contextpage) && $contextpage != $_SERVER["PHP_SELF"])
	$param .= '&contextpage=' . urlencode($contextpage);
if ($limit > 0 && $limit != $conf->liste_limit)
	$param .= '&limit=' . urlencode($limit);
foreach ($search as $key => $val) {
	$param .= '&search_' . $key . '=' . urlencode($search[$key]);
}

if ($optioncss != '')
	$param .= '&optioncss=' . urlencode($optioncss);
// Add $param from extra fields
@include DOL_DOCUMENT_ROOT . '/core/tpl/extrafields_list_search_param.tpl.php';

// List of mass actions available
$arrayofmassactions = array(
	//'presend'=>$langs->trans("SendByMail"),
	//'builddoc'=>$langs->trans("PDFMerge"),
);
if ($user->rights->monanaylsevendeur->delete)
	$arrayofmassactions['predelete'] = $langs->trans("Delete");
if (in_array($massaction, array('presend', 'predelete')))
	$arrayofmassactions = array();
$massactionbutton = $form->selectMassAction('', $arrayofmassactions);

print '<form method="POST" id="searchFormList" action="' . $_SERVER["PHP_SELF"] . '">';
print '<input type="hidden" name="token" value="' . $_SESSION['newtoken'] . '">';
if ($optioncss != '')
	print '<input type="hidden" name="optioncss" value="' . $optioncss . '">';
print '<input type="hidden" name="token" value="' . $_SESSION['newtoken'] . '">';
print '<input type="hidden" name="formfilteraction" id="formfilteraction" value="list">';
print '<input type="hidden" name="action" value="list">';
print '<input type="hidden" name="sortfield" value="' . $sortfield . '">';
print '<input type="hidden" name="sortorder" value="' . $sortorder . '">';
print '<input type="hidden" name="page" value="' . $page . '">';
print '<input type="hidden" name="contextpage" value="' . $contextpage . '">';
print '<input type="hidden" name="contextpage" value="' . $contextpage . '">';
print '<input type="hidden" name="element_type" value="' . GETPOST('element_type') . '">';
print '<input type="hidden" name="socid" value="' . GETPOST('socid') . '">';
print '<input type="hidden" name="id" value="' . GETPOST('id') . '">';

print_barre_liste($title, $page, $_SERVER["PHP_SELF"], $param, $sortfield, $sortorder, $massactionbutton, $num, $nbtotalofrecords, 'title_companies', 0, '', '', $limit);

// Add code for pre mass action (confirmation or email presend form)
$topicmail = "SendContacttrackingRef";
$modelmail = "contacttracking";
$objecttmp = new Contacttracking($db);
$trackid = 'xxxx' . $object->id;
@include DOL_DOCUMENT_ROOT . '/core/tpl/massactions_pre.tpl.php';

if ($sall) {
	foreach ($fieldstosearchall as $key => $val)
		$fieldstosearchall[$key] = $langs->trans($val);
	print $langs->trans("FilterOnInto", $sall) . join(', ', $fieldstosearchall);
}

$moreforfilter = '';
/* $moreforfilter.='<div class="divsearchfield">';
  $moreforfilter.= $langs->trans('MyFilter') . ': <input type="text" name="search_myfield" value="'.dol_escape_htmltag($search_myfield).'">';
  $moreforfilter.= '</div>'; */

$parameters = array();
$reshook = $hookmanager->executeHooks('printFieldPreListTitle', $parameters, $object);    // Note that $action and $object may have been modified by hook
if (empty($reshook))
	$moreforfilter .= $hookmanager->resPrint;
else
	$moreforfilter = $hookmanager->resPrint;

if (!empty($moreforfilter)) {
	print '<div class="liste_titre liste_titre_bydiv centpercent">';
	print $moreforfilter;
	print '</div>';
}

$varpage = empty($contextpage) ? $_SERVER["PHP_SELF"] : $contextpage;
$selectedfields = $form->multiSelectArrayWithCheckbox('selectedfields', $arrayfields, $varpage); // This also change content of $arrayfields
$selectedfields .= (count($arrayofmassactions) ? $form->showCheckAddButtons('checkforselect', 1) : '');

print '<div class="div-table-responsive">';  // You can use div-table-responsive-no-min if you dont need reserved height for your table
print '<table class="tagtable liste' . ($moreforfilter ? " listwithfilterbefore" : "") . '">' . "\n";


// Fields title search
// --------------------------------------------------------------------
print '<tr class="liste_titre">';
/*
  foreach($object->fields as $key => $val)
  {
  $align='';
  if (in_array($val['type'], array('date','datetime','timestamp'))) $align.=($align?' ':'').'center';
  if (in_array($val['type'], array('timestamp'))) $align.=($align?' ':'').'nowrap';
  if ($key == 'status') $align.=($align?' ':'').'center';
  if (! empty($arrayfields['t.'.$key]['checked'])) print '<td class="liste_titre'.($align?' '.$align:'').'"><input type="text" class="flat maxwidth75" name="search_'.$key.'" value="'.dol_escape_htmltag($search[$key]).'"></td>';
  }
  // Extra fields
  @include DOL_DOCUMENT_ROOT.'/core/tpl/extrafields_list_search_input.tpl.php';

  // Fields from hook
  $parameters=array('arrayfields'=>$arrayfields);
  $reshook=$hookmanager->executeHooks('printFieldListOption', $parameters, $object);    // Note that $action and $object may have been modified by hook
  print $hookmanager->resPrint;
  // Action column
  print '<td class="liste_titre" align="right">';
  $searchpicto= method_exists($form, 'showFilterButtons') ? $form->showFilterButtons() : '';
  print $searchpicto;
  print '</td>';
  print '</tr>'."\n";
 */

// Fields title label
// --------------------------------------------------------------------

print '<tr class="liste_titre">';
foreach ($object->fields as $key => $val) {
	switch ($key) {
		case 'fk_element_id':
			print '<td></td>';
			break;
		case 'object':
			if (!empty($arrayfields['t.' . $key]['checked'])) {
				$modes = explode(PHP_EOL, $conf->global->contacttracking_CONTACTOBJECT);
				print '<td class="liste_titre' . ($align ? ' ' . $align : '') . '">';
				print '<select name="search_' . $key . '">';
				print '<option value=""></option>';
				foreach ($modes as $mode) {
					if (rtrim(trim($mode)) != '') {
						if ($search['object'] == $mode) {
							$select = "selected";
						} else {
							$select = "";
						}

						print '<option value="' . $mode . '" ' . $select . '>' . $mode . '</option>';
					}
				}

				print '</select>';
			}
			break;

		case 'fk_product':
			if (!empty($conf->global->contacttracking_CONTACTPRODUCT)) {
				print '<td></td>';
			}
			break;

		case 'fk_contact':
			print '<td></td>';
			break;

		case 'type_event':
			//if (!empty($conf->global->AGENDA_USE_EVENT_TYPE)) {
			print '<td></td>';
			print '<td></td>';
			//}

			/*
			  print '<td class="liste_titre' . ($align ? ' ' . $align : '') . '">';
			  $formactions->select_type_actions(GETPOST("actioncode") ? GETPOST("actioncode") : ($object->type_code ? $object->type_code : $default), "actioncode", "systemauto", 0, 1);
			  print '</td>';
			 */
			break;

		case 'mode_contact':
			if (!empty($arrayfields['t.' . $key]['checked'])) {
				$modes = explode(PHP_EOL, $conf->global->contacttracking_CONTACTMODE);
				print '<td class="liste_titre' . ($align ? ' ' . $align : '') . '">';
				print '<select name="search_' . $key . '">';
				print '<option value=""></option>';
				foreach ($modes as $mode) {
					if (rtrim(trim($mode)) != '') {
						print '<option value="' . $mode . '">' . $mode . '</option>';
					}
				}

				print '</select>';
			}
			break;

		case 'fk_soc':
			print '<td class="liste_titre' . ($align ? ' ' . $align : '') . '">' . $form->select_thirdparty_list($search['fk_soc'], 'search_fk_soc', '', 1, 0, 0, null, '', 0, 0, 'maxwidth100') . '</td>';

			break;
		case 'fk_user_creat':
			if ($user->rights->contacttracking->readall)
				print '<td class="liste_titre' . ($align ? ' ' . $align : '') . '">' . $form->select_dolusers($search['fk_user_creat'], 'search_fk_user_creat', 1, 1, null, null, null, null, null, null, null, null, null, 'maxwidth100') . '</td>';
			else
				print '<td class="liste_titre' . ($align ? ' ' . $align : '') . '"></td>';
			break;

		case 'fk_event':
			print '<td></td>';
			//print '<td></td>';
			break;

		default:
			$align = '';

			if (in_array($val['type'], array('date', 'datetime', 'timestamp')))
				$align .= ($align ? ' ' : '') . 'center';
			if (in_array($val['type'], array('timestamp')))
				$align .= ($align ? ' ' : '') . 'nowrap';
			if ($key == 'status')
				$align .= ($align ? ' ' : '') . 'center';
			if (!empty($arrayfields['t.' . $key]['checked']))
				print '<td class="liste_titre' . ($align ? ' ' . $align : '') . '"><input type="text" class="flat maxwidth75" name="search_' . $key . '" value="' . dol_escape_htmltag($search[$key]) . '"></td>';

			break;
	}
}

// Extra fields
@include DOL_DOCUMENT_ROOT . '/core/tpl/extrafields_list_search_title.tpl.php';
// Fields from hook
$parameters = array('arrayfields' => $arrayfields);
$reshook = $hookmanager->executeHooks('printFieldListOption', $parameters, $object);    // Note that $action and $object may have been modified by hook
print $hookmanager->resPrint;
// Action column
print '<td class="liste_titre" align="right">';
$searchpicto = method_exists($form, 'showFilterButtons') ? $form->showFilterButtons() : '';
print $searchpicto;
print '</td>';
print '</tr>' . "\n";


// Detect if we need a fetch on each output line
$needToFetchEachLine = 0;
if (isset($extrafields->attribute_computed) && is_array($extrafields->attribute_computed)) {
	foreach ($extrafields->attribute_computed as $key => $val) {
		if (preg_match('/\$object/', $val))
			$needToFetchEachLine++;  // There is at least one compute field that use $object
	}
}

// Fields title label
// --------------------------------------------------------------------
print '<tr class="liste_titre">';
foreach ($object->fields as $key => $val) {
	$align = '';
	if (in_array($val['type'], array('date', 'datetime', 'timestamp')))
		$align .= ($align ? ' ' : '') . 'center';
	if (in_array($val['type'], array('timestamp')))
		$align .= ($align ? ' ' : '') . 'nowrap';
	if ($key == 'status')
		$align .= ($align ? ' ' : '') . 'center';
	if (!empty($arrayfields['t.' . $key]['checked']))
		if ($key == 'fk_product') {
			if (!empty($conf->global->contacttracking_CONTACTPRODUCT)) {
				print getTitleFieldOfList($langs->trans($arrayfields['t.' . $key]['label']), 0, $_SERVER['PHP_SELF'], 't.' . $key, '', $param, ($align ? 'class="' . $align . '"' : ''), $sortfield, $sortorder, $align . ' ') . "\n";
			}
		} else if ($key == 'fk_event') {
			print getTitleFieldOfList($langs->trans($arrayfields['t.' . $key]['label']), 0, $_SERVER['PHP_SELF'], 't.' . $key, '', $param, ($align ? 'class="' . $align . '"' : ''), $sortfield, $sortorder, $align . ' ') . "\n";
			print '<td>' . $langs->trans('TextRelance') . '</td>';
			print '<td>' . $langs->trans('Date Relance') . '</td>';
		} else {
			print getTitleFieldOfList($langs->trans($arrayfields['t.' . $key]['label']), 0, $_SERVER['PHP_SELF'], 't.' . $key, '', $param, ($align ? 'class="' . $align . '"' : ''), $sortfield, $sortorder, $align . ' ') . "\n";
			
		}
		
	
		
		
}

// Hook fields
$parameters = array('arrayfields' => $arrayfields);
$reshook = $hookmanager->executeHooks('printFieldListTitle', $parameters, $object);    // Note that $action and $object may have been modified by hook
print $hookmanager->resPrint;
print getTitleFieldOfList($selectedfields, 0, $_SERVER["PHP_SELF"], '', '', '', 'align="center"', $sortfield, $sortorder, 'maxwidthsearch ') . "\n";

print '</tr>';

// Detect if we need a fetch on each output line
$needToFetchEachLine = 0;
if (isset($extrafields->attribute_computed) && is_array($extrafields->attribute_computed)) {
	foreach ($extrafields->attribute_computed as $key => $val) {
		if (preg_match('/\$object/', $val))
			$needToFetchEachLine++;  // There is at least one compute field that use $object
	}
}


// Loop on record
// --------------------------------------------------------------------
$i = 0;
if ((int)DOL_VERSION < 7) {
	$i--;
}

$totalarray = array();
while ($i < min($num, $limit)) {
	$obj2 = $db->fetch_object($resql);
	$obj = new Contacttracking($db);
	$obj->fetch($obj2->rowid);
	if (empty($obj))
		break;  // Should not happen

// Store properties in $object
	$object->id = $obj->rowid;
	foreach ($object->fields as $key => $val) {
		if (isset($obj->$key))
			$object->$key = $obj->$key;
	}

	// Show here line of result
	print '<tr class="oddeven">';
	foreach ($object->fields as $key => $val) {
		$align = '';
		if (in_array($val['type'], array('date', 'datetime', 'timestamp')))
			$align .= ($align ? ' ' : '') . 'center';
		if (in_array($val['type'], array('timestamp')))
			$align .= ($align ? ' ' : '') . 'nowrap';
		if ($key == 'status')
			$align .= ($align ? ' ' : '') . 'center';
		if (!empty($arrayfields['t.' . $key]['checked'])) {
			if ($key == 'fk_product') {
				if (!empty($conf->global->contacttracking_CONTACTPRODUCT)) {
					print '<td';
					if ($align)
						print ' class="' . $align . '"';
					print '>';
					if (!empty($obj->$key)) {
						$prods = explode(',', $obj->$key);
						foreach ($prods as $p) {
							$pr = new Product($db);
							$pr->fetch($p);
							echo $pr->getNomUrl(1) . "</br>";
						}
					}

					print '</td>';
				}
			} else {
				print '<td';
				if ($align)
					print ' class="' . $align . '"';
				print '>';

				if ($key == 'type_contact' && ($obj->$key == 1 || $obj->$key == 2)) {
					if ($obj->$key == 1) {
						echo $langs->trans("contacttracking_INCOMING");
					} elseif ($obj->$key == 2) {
						echo $langs->trans("contacttracking_OUTCOMING");
					}
				} elseif ($key == 'mode_contact' && is_numeric($obj->$key)) {
					$object->fetch($obj->$key);
					echo $cactioncomm->mode_contact;
				} elseif ($key == 'fk_element_id' && isset($objecttab[$obj->element_type])) {
					$objecttab[$obj->element_type]->fetch($obj->$key);
					echo $objecttab[$obj->element_type]->getNomUrl();
				} elseif ($key == 'fk_user_creat') {
					$userdef->fetch($obj->$key);
					echo $userdef->getNomUrl();
				} elseif ($key == 'rowid') {
					echo $obj->getNomUrl();
				} elseif ($key == 'fk_event') {
					$o = new ActionComm($db);
					$o->fetch($obj->fk_event);
					echo $o->getNomUrl();
					print '</td>';
					print '<td';
					if ($align)
						print ' class="' . $align . '"';
					print '>';
					echo $o->note;
					print '</td>';
					print '<td';
					if ($align)
						print ' class="' . $align . '"';
					print '>';
					echo dol_print_date($o->datep,'%d/%m/%Y');
					print '</td>';
				} elseif ($key == 'type_event') {
					if ($obj->$key !== '0') {
						$cactioncomm->fetch($obj->$key);
						if (!empty($cactioncomm->code)) {
							$label = $langs->trans("Action" . $cactioncomm->code);
							if ($label == 'Action' . $cactioncomm->code) {
								print '<input type="hidden" name="actioncode" value="' . $cactioncomm->code . '">' . $cactioncomm->libelle;
							} else {
								print '<input type="hidden" name="actioncode" value="' . $cactioncomm->code . '">' . $langs->trans("Action" . $cactioncomm->code);
							}
						}
					}
				} elseif ($key == 'comment') {
					if (method_exists($form, 'editfieldval')) {
						print $form->editfieldval("Commentaire", 'comment', $obj->$key, $obj, 1);
					} else {
						echo $obj->$key;
					}
				} else {
					if ((int)DOL_VERSION > '7') {
						if ($key !== 'fk_element_id') {
							print $object->showOutputField($val, $key, $obj->$key, '');
						}
					} else {
						switch ($key) {
							case 'fk_soc' :
								$societedef->fetch($obj->$key);
								echo $societedef->getNomUrl();
								break;
							default :
								print $obj->$key;
								break;
						}
					}
				}

				print '</td>';
				if (!$i)
					$totalarray['nbfield']++;
				if (!empty($val['isameasure'])) {
					if (!$i)
						$totalarray['pos'][$totalarray['nbfield']] = 't.' . $key;
					$totalarray['val']['t.' . $key] += $obj->$key;
				}
			}
		}
	}

	// Extra fields
	@include DOL_DOCUMENT_ROOT . '/core/tpl/extrafields_list_print_fields.tpl.php';
	// Fields from hook
	$parameters = array('arrayfields' => $arrayfields, 'obj' => $obj);
	$reshook = $hookmanager->executeHooks('printFieldListValue', $parameters, $object);    // Note that $action and $object may have been modified by hook
	print $hookmanager->resPrint;
	// Action column
	print '<td class="nowrap ' . $i . '" align="center">';
	if ($massactionbutton || $massaction) {   // If we are in select mode (massactionbutton defined) or if we have already selected and sent an action ($massaction) defined
		$selected = 0;
		//TO DO
		if (in_array($obj->id, $arrayofselected)) {
			$selected = 1;
		}
		print '<input id="cb' . $obj->id . '" class="flat checkforselect" type="checkbox" name="toselect[]" value="' . $obj->id . '"' . ($selected ? ' checked="checked"' : '') . '>';
	}

	print '</td>';
	if (!$i)
		$totalarray['nbfield']++;

	print '</tr>';

	$i++;
}

// Show total line
if (isset($totalarray['pos'])) {
	print '<tr class="liste_total">';
	$i = 0;
	while ($i < $totalarray['nbfield']) {
		$i++;
		if (!empty($totalarray['pos'][$i]))
			print '<td align="right">' . price($totalarray['val'][$totalarray['pos'][$i]]) . '</td>';
		else {
			if ($i == 1) {
				if ($num < $limit)
					print '<td align="left">' . $langs->trans("Total") . '</td>';
				else
					print '<td align="left">' . $langs->trans("Totalforthispage") . '</td>';
			} else
				print '<td></td>';
		}
	}

	print '</tr>';
}

// If no record found
if ($num == 0) {
	$colspan = 1;
	foreach ($arrayfields as $key => $val) {
		if (!empty($val['checked']))
			$colspan++;
	}

	print '<tr><td colspan="' . $colspan . '" class="opacitymedium">' . $langs->trans("NoRecordFound") . '</td></tr>';
}

$db->free($resql);

$parameters = array('arrayfields' => $arrayfields, 'sql' => $sql);
$reshook = $hookmanager->executeHooks('printFieldListFooter', $parameters, $object);    // Note that $action and $object may have been modified by hook
print $hookmanager->resPrint;

print '</table>' . "\n";
print '</div>' . "\n";
print '</form>' . "\n";

if (in_array('builddoc', $arrayofmassactions) && ($nbtotalofrecords === '' || $nbtotalofrecords)) {
	if ($massaction == 'builddoc' || $action == 'remove_file' || $show_files) {
		require_once DOL_DOCUMENT_ROOT . '/core/class/html.formfile.class.php';
		$formfile = new FormFile($db);

		// Show list of available documents
		$urlsource = $_SERVER['PHP_SELF'] . '?sortfield=' . $sortfield . '&sortorder=' . $sortorder;
		$urlsource .= str_replace('&amp;', '&', $param);

		$filedir = $diroutputmassaction;
		$genallowed = $user->rights->contacttracking->read;
		$delallowed = $user->rights->contacttracking->create;

		print $formfile->showdocuments('massfilesarea_contacttracking', '', $filedir, $urlsource, 0, $delallowed, '', 1, 1, 0, 48, 1, $param, $title, '');
	} else {
		print '<br><a name="show_files"></a><a href="' . $_SERVER["PHP_SELF"] . '?show_files=1' . $param . '#show_files">' . $langs->trans("ShowTempMassFilesArea") . '</a>';
	}
}

// End of page
llxFooter();
$db->close();
