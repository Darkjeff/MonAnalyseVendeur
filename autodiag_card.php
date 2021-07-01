<?php
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

include_once DOL_DOCUMENT_ROOT . '/core/class/html.formcompany.class.php';
include_once DOL_DOCUMENT_ROOT . '/core/class/html.formfile.class.php';
include_once DOL_DOCUMENT_ROOT . '/product/class/product.class.php';
require_once DOL_DOCUMENT_ROOT . '/core/class/html.formactions.class.php';
dol_include_once('/monanalysevendeur/class/contacttracking.class.php');

$object = new Contacttracking($db);
$formactions = new FormActions($db);


// Get parameters
$id = GETPOST('id', 'int');
$ref = GETPOST('ref', 'alpha');
$action = GETPOST('action', 'alpha');
$confirm = GETPOST('confirm', 'alpha');
$cancel = GETPOST('cancel', 'aZ09');
$contextpage = GETPOST('contextpage', 'aZ') ? GETPOST('contextpage', 'aZ') : 'contacttrackingcard';   // To manage different context of search
$backtopage = GETPOST('backtopage', 'alpha');

include DOL_DOCUMENT_ROOT . '/core/actions_fetchobject.inc.php';  // Must be include, not include_once  // Must be include, not include_once. Include fetch and fetch_thirdparty but not fetch_optionals

$object->fields['fk_event']['enabled'] = GETPOST('relance', 'int');
$object->fields['type_event']['enabled'] = GETPOST('relance', 'int');
$object->fields['element_type']['visible'] = 0;
$object->fields['fk_element_id']['visible'] = 0;


// Load traductions files requiredby by page
if (method_exists($langs, 'loadLangs')) {
	$langs->loadLangs(array("contacttracking@monanalysevendeur", "other", "companies"));
} else {
	$langs->load('other');
	$langs->load('companies');
	$langs->load('contacttracking@monanalysevendeur');
}
// AJout
if ($action == 'add') {

	$object->date_creation = strtotime(GETPOST('date_creationyear') . '-' . GETPOST('date_creationmonth') . '-' . GETPOST('date_creationday') . ' ' . GETPOST('date_creationhour') . ':' . GETPOST('date_creationmin') . ':00');
	$object->mode_contact = GETPOST('mode_contact');
	$object->object = GETPOST('object');
	$object->fk_soc = GETPOST('fk_soc');
	$object->fk_user_creat = GETPOST('fk_user_creat');
	$object->fk_category_user = GETPOST('fk_category_user');
	$object->fk_contact = !GETPOST('fk_contact') ? 'NULL' : GETPOST('fk_contact');
	$object->comment = GETPOST('comment');
	if (!empty($conf->global->AGENDA_USE_EVENT_TYPE)) {
		$object->type_event = GETPOST('actioncode');
	} else {
		$object->type_event = "AC_RDV";
	}

	$object->type_contact = GETPOST('type_contact');
	if (GETPOST('product') != null) {
		foreach (GETPOST('product') as $pr => $empty) {
			if (!empty($object->fk_product))
				$object->fk_product .= ',';
			$object->fk_product .= $pr;
		}
	}

	//element
	$element_post = explode('-', GETPOST('fk_element_id'));
	if (count($element_post) == 2) {
		$object->element_type = $element_post[0];
		$object->fk_element_id = $element_post[1];
	}

	$err = 0;
	$result = $object->create($user);
	if ($result < 0) {
		setEventMessage($object->error, 'errors');
		$err++;
	} elseif (GETPOST('re')) {
		// On créé une relance client
		require_once DOL_DOCUMENT_ROOT . '/comm/action/class/actioncomm.class.php';
		require_once DOL_DOCUMENT_ROOT . '/societe/class/societe.class.php';
		$soc = new Societe($db);
		if (!empty(GETPOST('fk_soc_contact'))) {
			$soc->fetch(GETPOST('fk_soc_contact'));
		} else {
			$soc->fetch(GETPOST('fk_soc'));
		}

		$actionComm = new ActionComm($db);

		$hour = GETPOST('rehour');
		if ($hour == '00' && GETPOST('remin' == '00')) {
			$hour = '10';
		}
		$datep = dol_mktime($hour, GETPOST('remin'), 0, GETPOST("remonth"), GETPOST("reday"), GETPOST("reyear"));

		$actionComm->datep = $datep;
		$actionComm->elementtype = $object->element_type;
		if ($object->element_type == 'projet')
			$actionComm->fk_project = $object->fk_element_id;
		$actionComm->fk_element = $object->fk_element_id;
		if (!empty(GETPOST('fk_soc_contact'))) {
			$actionComm->socid = GETPOST('fk_soc_contact');
		} else {
			$actionComm->socid = GETPOST('fk_soc');
		}

		$actionComm->contactid = $object->fk_contact;
		$actionComm->type_code = "AC_RDV";
		if (!empty($conf->global->AGENDA_USE_EVENT_TYPE) && GETPOST('actioncode') != '0') {
			$actionComm->type_code = GETPOST('actioncode');
		}

		$actionComm->label = $langs->trans('relance') . ' : ' . $soc->getFullName($langs);
		if ($conf->global->contacttracking_CHOOSEUSER == 1) {
			$actionComm->userownerid = GETPOST('fk_user_creat');
		} else {
			$actionComm->userownerid = $user->id;
		}

		if (!empty(GETPOST('reminder')))
			$actionComm->note = GETPOST('reminder');
		else
			$actionComm->note = GETPOST('comment');

		$actionComm->array_options['options_relance_done'] = GETPOST('relance_done');
		$actionComm->array_options['options_sales_done'] = GETPOST('sales_done');

		$event = $actionComm->create($user);
		if ($event < 0) {
			$err++;
		} else {
			$object->fetch($object->id);
			$object->fk_event = $event;
			$result = $object->update($user);
			if ($result < 0) {
				setEventMessages(null, $object->errors, 'errors');
			}
		}
	}

	if ($err > 0) {
		echo $db->lasterror();
	} else {
		setEventMessage($langs->trans('Success'));
		Header("Location: autodiag_list.php?relance=" . GETPOST('relance', 'int'));
		exit();
	}
}

if ($action == 'update') {

	//$object->date_creation = strtotime(GETPOST('date_creationyear') . '-' . GETPOST('date_creationmonth') . '-' . GETPOST('date_creationday') . ' ' . GETPOST('date_creationhour') . ':' . GETPOST('date_creationmin') . ':00');
	$object->mode_contact = GETPOST('mode_contact');
	$object->object = GETPOST('object');
	$object->fk_soc = GETPOST('fk_soc');
	//$object->fk_user_creat = GETPOST('fk_user_creat');
	$object->fk_category_user = GETPOST('fk_category_user');
	$object->fk_contact = !GETPOST('fk_contact') ? 'NULL' : GETPOST('fk_contact');
	$object->comment = GETPOST('comment');
	if (!empty($conf->global->AGENDA_USE_EVENT_TYPE)) {
		$object->type_event = GETPOST('actioncode');
	} else {
		$object->type_event = "AC_RDV";
	}

	$object->type_contact = GETPOST('type_contact');
	if (GETPOST('product') != null) {
		foreach (GETPOST('product') as $pr => $empty) {
			if (!empty($object->fk_product))
				$object->fk_product .= ',';
			$object->fk_product .= $pr;
		}
	}

}

$title = $langs->trans("Autodiag");
$help_url = '';
llxHeader('', $title, $help_url);
dol_fiche_head(array(), '');


// Part to create
if ($action == 'create') {
	if (empty($user->rights->contacttracking->write) && empty($user->rights->monanalysevendeur->write)) {
		print '<div class="error"><?php echo $langs->trans("contacttracking_NORIGHT") ?>.</div>';
	} else {
		print load_fiche_titre($langs->trans("contacttracking", $langs->transnoentitiesnoconv("contacttracking")));

		$form = new Form($db);
		$formfile = new FormFile($db);

		print '<form method="POST" action="' . $_SERVER["PHP_SELF"] . '">';
		print '<input type="hidden" name="token" value="' . $_SESSION['newtoken'] . '">';
		print '<input type="hidden" name="action" value="add">';
		print '<input type="hidden" name="actioncode" value="' . $object->type_code . '">';
		print '<input type="hidden" name="relance" value="' . GETPOST('relance', 'int') . '">';

		// print '<center><h2>' . $langs->trans('Exchanges') . '</h2></center>';
		print '<table class="border centpercent">' . "\n";
		foreach ($object->fields as $key => $val) {

			// Discard if extrafield is a hidden field on form
			if (abs($val['visible']) != 1)
				continue;

			if (array_key_exists('enabled', $val) && isset($val['enabled']) && !$val['enabled']) {
				continue; // We don't want this field
			}

			switch ($key) {
				case 'type_contact' :
					print '<td>';
					print $langs->trans("Client");
					print '</td>';
					print '<td>';
					////todo select the good customer after creation
					if (empty(GETPOST('socid'))) {
						echo $form->select_company($val, 'fk_soc', '', '', 0, 1);
					} else {
						echo $form->select_company($val, 'fk_soc', '', '', 0, 1, '', '', '', '', 'socid');
					}
					if (GETPOST('relance', 'int') == 1) {
						echo ' <a href="' . DOL_URL_ROOT . '/societe/card.php?action=create&backtopage=' . urlencode($_SERVER["PHP_SELF"] . '?action=create&relance=1') . '"><span class="fa fa-plus-circle valignmiddle paddingleft" title="' . $langs->trans("AddThirdParty") . '"></span></a>';
					} else {
						echo ' <a href="' . DOL_URL_ROOT . '/societe/card.php?action=create&backtopage=' . urlencode($_SERVER["PHP_SELF"] . '?action=create&relance=0') . '"><span class="fa fa-plus-circle valignmiddle paddingleft" title="' . $langs->trans("AddThirdParty") . '"></span></a>';
					}

					print '<tr id="field_' . $key . '">';
					print '<td';
					print ' class="titlefieldcreate';
					if ($val['notnull'] > 0)
						print ' fieldrequired';
					if ($val['type'] == 'text' || $val['type'] == 'html')
						print ' tdtop';
					print '"';
					print '>';
					print $langs->trans($val['label']);
					print '</td>';

					print '<td>';

					print '<label>';
					print '<input type="radio" name="' . $key . '" value="2" checked="checked" />';
					print $langs->trans("contacttracking_OUTCOMING");
					print '</label>';

					print '&nbsp;&nbsp;&nbsp;<label>';
					print '<input type="radio" name="' . $key . '" value="1" />';
					print $langs->trans("contacttracking_INCOMING");
					print '</label>';

					print '</td>';
					print '</tr>';
					break;

				case 'fk_category_user':
					print '<tr id="field_' . $key . '">';
					print '<td';
					print ' class="titlefieldcreate';
					if ($val['notnull'] > 0)
						print ' fieldrequired';
					if ($val['type'] == 'text' || $val['type'] == 'html')
						print ' tdtop';
					print '"';
					print '>';
					print $langs->trans($val['label']);
					print '</td>';
					print '<td>';

					print $form->select_all_categories('user', GETPOSTISSET($key) ? GETPOST($key, 'int') : $object->$key , $key, null, null, 0);

					print '</td>';
					print '</tr>';
					break;

				case 'type_event':

					print '</table>';
					print '<hr>';
					print '<center><h3>' . $langs->trans('ReminderTitleContacttracking') . '</h3></center>';
					print '<table  class="border centpercent">' . "\n";
					if (!empty($conf->global->AGENDA_USE_EVENT_TYPE)) {
						print '<tr  id="field_' . $key . '">';
						print '<td class="titlefieldcreate';
						if ($val['notnull'] > 0)
							print ' fieldrequired';
						if ($val['type'] == 'text' || $val['type'] == 'html')
							print ' tdtop';
						print '">';
						print '<span class="field">' . $langs->trans($val['label']) . '</span>';
						print '</b></td><td>';
						$default = (empty($conf->global->AGENDA_USE_EVENT_TYPE_DEFAULT) ? 'AC_RDV' : $conf->global->AGENDA_USE_EVENT_TYPE_DEFAULT);
						//WORKFLOW prospecting event
						$formactions->select_type_actions(GETPOST("actioncode") ? GETPOST("actioncode") : ($object->type_code ? $object->type_code : $default), "actioncode", "systemauto", 0, -1);
						print '</td></tr>'; //AC_RDV
					}
					break;

				case 'rowid':
					break;

				case 'mode_contact' :
					print '<tr id="field_' . $key . '">';
					print '<td';
					print ' class="titlefieldcreate';
					if ($val['notnull'] > 0)
						print ' fieldrequired';
					if ($val['type'] == 'text' || $val['type'] == 'html')
						print ' tdtop';
					print '"';
					print '>';
					print $langs->trans($val['label']);
					print '</td>';

					print '<td>';
					print '<select name="' . $key . '" class="minwidth200">';
					$modes = explode(PHP_EOL, $conf->global->contacttracking_CONTACTMODE);
					foreach ($modes as $mode) {
						if (rtrim(trim($mode)) != '') {
							print '<option>' . $mode . '</option>';
						}
					}

					print '</select>';
					print '</label>';

					print '</td>';
					print '</tr>';
					break;

				case 'fk_event':
					break;

				case 'fk_product' :
					if (empty($conf->global->contacttracking_CONTACTPRODUCT)) {
						break;
					}

					print '<tr id="field_' . $key . '">';
					print '<td';
					print ' class="titlefieldcreate';
					if ($val['notnull'] > 0)
						print ' fieldrequired';
					if ($val['type'] == 'text' || $val['type'] == 'html')
						print ' tdtop';
					print '"';
					print '>';
					print $langs->trans($val['label']);
					print '</td>';
					print '<td>';
					$modes = explode(",", $conf->global->contacttracking_CONTACTPRODUCT);
					$o = 1;
					print '<table>';
					print '<tr>';
					foreach ($modes as $mode) {
						if (rtrim(trim($mode)) != '') {
							$p = new Product($db);
							$p->fetch($mode);
							print '<td><input type="checkbox" name="product[' . $mode . ']" value="' . $p->rowid . '">&nbsp;' . $p->label . "</td>";
							if ($o > 0 && $o % 3 === 0) {
								print '</tr>';
								print '<tr>';
							}

							$o++;
						}
					}

					print '</table>';
					print '</td>';
					print '</tr>';
					break;

				case 'object' :
					if (isset($conf->global->contacttracking_CONTACTPRODUCT) && empty($conf->global->contacttracking_CONTACTPRODUCT)) {
						break;
					}

					print '<tr id="field_' . $key . '">';
					print '<td';
					print ' class="titlefieldcreate';
					if ($val['notnull'] > 0)
						print ' fieldrequired';
					if ($val['type'] == 'text' || $val['type'] == 'html')
						print ' tdtop';
					print '"';
					print '>';
					print $langs->trans($val['label']);
					print '</td>';

					print '<td>';
					print '<select name="' . $key . '" class="minwidth200">';
					$modes = explode(PHP_EOL, $conf->global->contacttracking_CONTACTOBJECT);
					foreach ($modes as $mode) {
						if (rtrim(trim($mode)) != '') {
							print '<option>' . $mode . '</option>';
						}
					}

					print '</select>';
					print '</label>';

					print '</td>';
					print '</tr>';
					break;

				case 'fk_user_creat' :
					if ($conf->global->contacttracking_CHOOSEUSER == 1) {
						print '<tr id="field_' . $key . '">';
						print '<td';
						print ' class="titlefieldcreate';
						if ($val['notnull'] > 0)
							print ' fieldrequired';
						if ($val['type'] == 'text' || $val['type'] == 'html')
							print ' tdtop';
						print '"';
						print '>';
						print $langs->trans($val['label']);
						print '</td>';

						print '<td>';

						echo $form->select_dolusers($user->id, 'fk_user_creat', 0, null, 0, '', '', 0, 0, 0, '', 0, '', '', 1);

						print '</td>';
						print '</tr>';
					} else {
						print '<input type="hidden" name="' . $key . '" value="' . $user->id . '" />';
					}
					break;

				case 'element_type' :

				case 'fk_element_id' :
					print '<tr id="field_' . $key . '">';
					print '<td';
					print ' class="titlefieldcreate';
					if ($val['notnull'] > 0)
						print ' fieldrequired';
					if ($val['type'] == 'text' || $val['type'] == 'html')
						print ' tdtop';
					print '"';
					print '>';
					print $langs->trans($val['label']);
					print '</td>';

					print '<td>';
					print '<select class="flat minwidth300" name="' . $key . '" id="' . $key . '">';
					print '<option value="0"></option>';
					print '</select>';
					print '</td>';
					print '</tr>';

					break;

				case  'fk_soc' :
					break;

				default :
					if (!$user->rights->societe->contact->lire && $key == "fk_contact") {
						break;
					}
					print '<tr id="field_' . $key . '">';
					print '<td';
					print ' class="titlefieldcreate';
					if ($val['notnull'] > 0)
						print ' fieldrequired';
					if ($val['type'] == 'text' || $val['type'] == 'html')
						print ' tdtop';
					print '"';
					print '>';
					print $langs->trans($val['label']);
					print '</td>';
					print '<td>';
					if (in_array($val['type'], array('int', 'integer', 'booolean')))
						$value = GETPOST($key, 'int');
					elseif ($val['type'] == 'text' || $val['type'] == 'html')
						$value = GETPOST($key, 'none');
					else
						$value = GETPOST($key, 'alpha');


					switch ($key) {
						case 'date_creation' :
							$form->selectdate('', 'date_creation', 1, 'SelectThirdParty', 0, "date_creation");
							break;
						case 'fk_contact' :
							// $form->select_contacts(0, '', 'fk_contact', 1, '', '', 0, 'minwidth300');
							break;
						case 'comment' :
							print '<textarea name="comment" class="flat minwidth300" rows="5">';
							print '</textarea>';
							break;
						case 'relance_done' :
							$checked = '';
							if (!empty($value)) {
								$checked = ' checked value="1" ';
							} else {
								$checked = ' value="1" ';
							}
							print '<input type="checkbox" class="flat maxwidthonsmartphone" name="'.$key.'" id="'.$key.'" '.$checked.'>';
							break;
						case 'sales_done' :
							$checked = '';
							if (!empty($value)) {
								$checked = ' checked value="1" ';
							} else {
								$checked = ' value="1" ';
							}
							print '<input type="checkbox" class="flat maxwidthonsmartphone" name="'.$key.'" id="'.$key.'" '.$checked.'>';
							break;
					}

					print '</td>';
					print '</tr>';
			}
		}

		if ($conf->global->contacttracking_CHOOSEUSER == 1 && GETPOST('relance', 'int') == 1) {
			//print '<tr id="field_userreminder">';
			//print '<td';
			//print ' class="titlefieldcreate';
			//if ($val['notnull'] > 0)
			//    print ' fieldrequired';
			//if ($val['type'] == 'text' || $val['type'] == 'html')
			//    print ' tdtop';
			//print '"';
			//print '>';
			//print $langs->trans('UserReminder');
			//print '</td>';

			//print '<td>';

			//echo $form->select_dolusers($user->id, 'userreminder', 0, null, 0, '', '', 0, 0, 0, '', 0, '', '', 1);

			//print '</td>';
			//print '</tr>';
		}

		/*
		//Choose the society to link to the reminder (if different of the society contacted

		print '<tr id="field_soc_contact">';
		print '<td class="titlefieldcreate">';
		print 'Client à contacter';
		print '<td>';
		echo $form->select_company(null, 'fk_soc_contact', '', 'SelectThirdParty');
		print '</td>';
		*/
		if (GETPOST('relance', 'int') == 1) {
			print '<tr id="field_relance">';
			print '<td';
			print ' class="titlefieldcreate fieldrequired';
			print '"';
			print '>';
			print $langs->trans('DateRelance');
			print '</td>';
			print '<td>';
			print $form->select_date(-1, 're', 1, 1, 0, "", 1);
			print '</td>';
			print '</tr>';
		}


		print '</table>' . "\n";

		dol_fiche_end();

		print '<div class="center">';
		print '<input type="submit" class="button" name="add" value="' . dol_escape_htmltag($langs->trans("Create")) . '">';
		print '&nbsp; ';
		print '<input type="button" class="button" name="cancel" value="' . dol_escape_htmltag($langs->trans("Cancel")) . '" onclick="window.parent.$(\'#modal-contact\').dialog(\'close\');">'; // Cancel for create does not post form if we don't know the backtopage
		print '</div>';

		print '</form>';
	}
}

if (($id || $ref) && $action == 'edit') {
	print load_fiche_titre($langs->trans("contacttracking"));

	print '<form method="POST" action="' . $_SERVER["PHP_SELF"] . '">';
	print '<input type="hidden" name="token" value="' . $_SESSION['newtoken'] . '">';
	print '<input type="hidden" name="action" value="update">';
	print '<input type="hidden" name="backtopage" value="' . $backtopage . '">';
	print '<input type="hidden" name="id" value="' . $object->id . '">';
	print '<input type="hidden" name="type" id="type" value="' . $object->element_type . '">';
	print '<input type="hidden" name="idelem" id="idelem" value="' . $object->fk_element_id . '">';
	print '<div id="hide"></div>';
	dol_fiche_head();

	//print '<center><h2>' . $langs->trans('Exchanges') . '</h2></center>';
	print '<table class="border centpercent">' . "\n";
//var_dump($object->fields);
	foreach ($object->fields as $key => $val) {
		// Discard if extrafield is a hidden field on form
		if (abs($val['visible']) != 1)
			continue;

		if (array_key_exists('enabled', $val) && isset($val['enabled']) && !$val['enabled'])
			continue; // We don't want this field

		switch ($key) {
			case 'rowid':
			case 'element_type':
				break;

			case 'fk_user_creat' :
				if ($conf->global->contacttracking_CHOOSEUSER == 1) {
					print '<tr id="field_' . $key . '">';
					print '<td';
					print ' class="titlefieldcreate';
					if ($val['notnull'] > 0)
						print ' fieldrequired';
					if ($val['type'] == 'text' || $val['type'] == 'html')
						print ' tdtop';
					print '"';
					print '>';
					print $langs->trans($val['label']);
					print '</td>';

					print '<td>';

					echo $form->select_dolusers($user->id, 'fk_user_creat', 0, null, 0, '', '', 0, 0, 0, '', 0, '', '', 1);

					print '</td>';
					print '</tr>';
				} else {
					print '<input type="hidden" name="' . $key . '" value="' . $user->id . '" />';
				}
				break;

			case 'date_creation':
				print '<tr id="field_' . $key . '">';
				print '<td';
				print ' class="titlefieldcreate';
				if ($val['notnull'] > 0)
					print ' fieldrequired';
				if ($val['type'] == 'text' || $val['type'] == 'html')
					print ' tdtop';
				print '"';
				print '>';
				print $langs->trans($val['label']);
				print '</td>';
				print '<td>';
				$form->select_date($object->$key, 'date_creation', 1, 1, 0, "date_creation");
				print '</td>';
				break;

			case 'fk_event':
				/*
				  // Modify the society link to the reminder (when different of the society contacted)

				  $actioncomm = new ActionComm($db);
				  $actioncomm->fetch($object->$key);

				  print '<tr id="field_fk_soc_contact">';
				  print '<td class="titlefieldcreate">';
				  print 'Client a contacter';
				  print '</td>';
				  print '<td>';
				  echo $form->select_company($actioncomm->socid, 'fk_soc_contact', '', 'SelectThirdParty');
				  print '</td>';
				  print '</tr>';
				 */
				break;

			case 'fk_category_user':
				print '<tr id="field_' . $key . '">';
				print '<td';
				print ' class="titlefieldcreate';
				if ($val['notnull'] > 0)
					print ' fieldrequired';
				if ($val['type'] == 'text' || $val['type'] == 'html')
					print ' tdtop';
				print '"';
				print '>';
				print $langs->trans($val['label']);
				print '</td>';
				print '<td>';

				print $form->select_all_categories('user', GETPOSTISSET($key) ? GETPOST($key, 'int') : $object->$key , $key, null, null, 0);

				print '</td>';
				print '</tr>';
				break;

			case 'type_event':
				/*
				 print '</table>';
				 print '<hr>';
				 print '<center><h3>' . $langs->trans('Reminderplaned') . '</h3></center>';
				 print '<table  class="border centpercent">' . "\n";
				 print '<tr id="field_' . $key . '">';
				 print '<td';
				 print ' class="titlefieldcreate';
				 if ($val['notnull'] > 0)
					 print ' fieldrequired';
				 if ($val['type'] == 'text' || $val['type'] == 'html')
					 print ' tdtop';
				 print '"';
				 print '>';
				 print $langs->trans($val['label']);
				 print '</td>';
				 print '<td>';
				 $default = (empty($conf->global->AGENDA_USE_EVENT_TYPE_DEFAULT) ? '' : $conf->global->AGENDA_USE_EVENT_TYPE_DEFAULT);
				 $formactions->select_type_actions(($object->type_event ? $object->type_event : $default), "actioncode", "systemauto", 0, -1);
				 print '</td>';
				*/
				break;

			case 'mode_contact' :
				print '<tr id="field_' . $key . '">';
				print '<td';
				print ' class="titlefieldcreate';
				if ($val['notnull'] > 0)
					print ' fieldrequired';
				if ($val['type'] == 'text' || $val['type'] == 'html')
					print ' tdtop';
				print '"';
				print '>';
				print $langs->trans($val['label']);
				print 'sss</td>';

				print '<td>';
				print '<select name="' . $key . '" class="minwidth200">';
				$modes = explode(PHP_EOL, $conf->global->contacttracking_CONTACTMODE);

				foreach ($modes as $mode) {
					if ($mode == $object->$key) {
						$select = "selected";
					} else {
						$select = "";
					}

					if (rtrim(trim($mode)) != '') {
						print '<option ' . $select . '>' . $mode . '</option>';
					}
				}

				print '</select>';
				print '</label>';

				print '</td>';
				print '</tr>';
				break;

			case 'fk_product' :
				print '<tr id="field_' . $key . '">';
				print '<td';
				print ' class="titlefieldcreate';
				if ($val['notnull'] > 0)
					print ' fieldrequired';
				if ($val['type'] == 'text' || $val['type'] == 'html')
					print ' tdtop';
				print '"';
				print '>';
				print $langs->trans($val['label']);
				print '</td>';

				print '<td>';
				$modes = explode(",", $conf->global->contacttracking_CONTACTPRODUCT);
				$products = explode(',', $object->$key);

				$o = 1;
				print '<table>';
				print '<tr>';
				foreach ($modes as $mode) {
					if (rtrim(trim($mode)) != '') {
						$p = new Product($db);
						$p->fetch($mode);
						if (in_array($mode, $products)) {
							print '<td><input type="checkbox" name="product[' . $mode . ']" value="' . $p->id . '" checked="checked">&nbsp;' . $p->label . "</td>";
						} else
							print '<td><input type="checkbox" name="product[' . $mode . ']" value="' . $p->id . '">&nbsp;' . $p->label . "</td>";
						if ($o > 0 && $o % 3 === 0) {
							;
							print '</tr>';
							print '<tr>';
						}

						$o++;
					}
				}

				print '</table>';

				print '</td>';
				print '</tr>';
				break;

			case 'object' :
				print '<tr id="field_' . $key . '">';
				print '<td';
				print ' class="titlefieldcreate';
				if ($val['notnull'] > 0)
					print ' fieldrequired';
				if ($val['type'] == 'text' || $val['type'] == 'html')
					print ' tdtop';
				print '"';
				print '>';
				print $langs->trans($val['label']);
				print '</td>';

				print '<td>';
				print '<select name="' . $key . '" class="minwidth200">';
				$modes = explode(PHP_EOL, $conf->global->contacttracking_CONTACTOBJECT);
				foreach ($modes as $mode) {
					if ($mode == $object->$key) {
						$select = "selected";
					} else {
						$select = "";
					}
					if (rtrim(trim($mode)) != '') {
						print '<option>' . $mode . '</option>';
					}
				}

				print '</select>';
				print '</label>';

				print '</td>';
				print '</tr>';
				break;

			case 'fk_element_id' :
				print '<tr id="field_' . $key . '">';
				print '<td';
				print ' class="titlefieldcreate';
				if ($val['notnull'] > 0)
					print ' fieldrequired';
				if ($val['type'] == 'text' || $val['type'] == 'html')
					print ' tdtop';
				print '"';
				print '>';
				print $langs->trans($val['label']);
				print '</td>';

				print '<td>';


				/* if($object->element_type=='facture'){
				  $array = $object->liste_array_facture(2,0,0,$object->fk_soc);
				  echo $form->selectarray("fk_element_id", $array, $object->$key);
				  } */


				print '</td>';
				print '</tr>';

				break;

			default :
				print '<tr id="field_' . $key . '">';
				print '<td';
				print ' class="titlefieldcreate';
				if ($val['notnull'] > 0)
					print ' fieldrequired';
				if ($val['type'] == 'text' || $val['type'] == 'html')
					print ' tdtop';
				print '"';
				print '>';
				print $langs->trans($val['label']);
				print '</td>';
				print '<td>';
				if (in_array($val['type'], array('int', 'integer')))
					$value = GETPOST($key, 'int');
				elseif ($val['type'] == 'text' || $val['type'] == 'html')
					$value = GETPOST($key, 'none');
				else
					$value = GETPOST($key, 'alpha');
				if (0 && (int) DOL_VERSION > '7') {
					print $object->showInputField($val, $key, $value, '', '', '', 0);
				} else {
					switch ($key) {
						case 'fk_soc' :
							echo $form->select_company($object->$key, 'fk_soc', '', 'SelectThirdParty');
							break;
						case 'fk_contact' :
							$form->select_contacts($object->fk_soc, $object->$key, 'fk_contact', 1, '', '', 0, 'minwidth300');
							break;
						case 'comment' :
							print '<textarea name="comment" class="flat minwidth300" rows="5">' . $object->$key;
							print '</textarea>';
							break;
					}
				}

				print '</td>';
				print '</tr>';
		}
	}

	$path = dol_buildpath("/contacttracking/ajax/loadData.php?method=getElementByTiers2&fk_soc=" . $object->fk_soc, 1);

	print "<script type='text/javascript'>
               $(document).ready(function() {
               typ = $('#type').val();
               el = $('#idelem').val();

                var select='<select name=\'fk_element_id\' id=\'fk_element_id\'>';
                    $.getJSON('" . $path . "',function(data){
                       $.each(data,function(key,val){
                        if(typ==val.type && el==val.id){
                            select+= '<option value=\''+val.id+'\' data-type=\''+val.type+'\' selected >'+val.text+'</option>';
                        }
                        else{
                            select+= '<option value=\''+val.id+'\' data-type=\''+val.type+'\'>'+val.text+'</option>';
                        }
                       });
                       select+='</select>';
                      $('#field_fk_element_id td:nth-child(2)').html(select);

                    });
                    $('#field_fk_element_id').change(function(){
                        elm = $('#fk_element_id option:selected').attr('data-type');
                        $('#hide').html('<input type=\"hidden\" name=\"element_type\" value=\"'+elm+'\" />');

                    });
                });";
	print '</script>';

	print '</table>';

	dol_fiche_end();

	print '<div class="center"><input type="submit" class="button" name="save" value="' . $langs->trans("Save") . '">';
	print ' &nbsp; <input type="submit" class="button" name="cancel" value="' . $langs->trans("Cancel") . '">';
	print '</div>';

	print '</form>';
}


// Part to show record
if ($object->id > 0 && (empty($action) || ($action != 'edit' && $action != 'create'))) {
	$res = $object->fetch_optionals();

	//$head = contacttrackingPrepareHead($object);
	//dol_fiche_head($head, 'card', $langs->trans("contacttracking"), -1, 'contacttracking@contacttracking');

	$formconfirm = '';

	// Confirmation to delete
	if ($action == 'delete') {
		$formconfirm = $form->formconfirm($_SERVER["PHP_SELF"] . '?id=' . $object->id, $langs->trans('Deletecontacttracking'), $langs->trans('ConfirmDeletecontacttracking'), 'confirm_delete', '', 0, 1);
	}

	// Clone confirmation
	if ($action == 'clone') {
		// Create an array for form
		$formquestion = array();
		$formconfirm = $form->formconfirm($_SERVER["PHP_SELF"] . '?id=' . $object->id, $langs->trans('Clonecontacttracking'), $langs->trans('ConfirmClonecontacttracking', $object->ref), 'confirm_clone', $formquestion, 'yes', 1);
	}

	// Confirmation of action xxxx
	if ($action == 'xxx') {
		$formquestion = array();
		/*
		  $forcecombo=0;
		  if ($conf->browser->name == 'ie') $forcecombo = 1;	// There is a bug in IE10 that make combo inside popup crazy
		  $formquestion = array(
		  // 'text' => $langs->trans("ConfirmClone"),
		  // array('type' => 'checkbox', 'name' => 'clone_content', 'label' => $langs->trans("CloneMainAttributes"), 'value' => 1),
		  // array('type' => 'checkbox', 'name' => 'update_prices', 'label' => $langs->trans("PuttingPricesUpToDate"), 'value' => 1),
		  // array('type' => 'other',    'name' => 'idwarehouse',   'label' => $langs->trans("SelectWarehouseForStockDecrease"), 'value' => $formproduct->selectWarehouses(GETPOST('idwarehouse')?GETPOST('idwarehouse'):'ifone', 'idwarehouse', '', 1, 0, 0, '', 0, $forcecombo))
		  );
		 */
		$formconfirm = $form->formconfirm($_SERVER["PHP_SELF"] . '?id=' . $object->id, $langs->trans('XXX'), $text, 'confirm_xxx', $formquestion, 0, 1, 220);
	}

	if (!$formconfirm) {
		$parameters = array('lineid' => $lineid);
		$reshook = $hookmanager->executeHooks('formConfirm', $parameters, $object, $action); // Note that $action and $object may have been modified by hook
		if (empty($reshook))
			$formconfirm .= $hookmanager->resPrint;
		elseif ($reshook > 0)
			$formconfirm = $hookmanager->resPrint;
	}

	// Print form confirm
	print $formconfirm;


	// Object card
	// ------------------------------------------------------------
	$linkback = '<a href="' . dol_buildpath('/contacttracking/contacttracking_list.php', 1) . '?restore_lastsearch_values=1' . (!empty($socid) ? '&socid=' . $socid : '') . '">' . $langs->trans("BackToList") . '</a>';

	$morehtmlref = '<div class="refidno">';
	$morehtmlref .= '</div>';


	dol_banner_tab($object, 'ref', $linkback, 1, 'ref', 'ref', $morehtmlref);


	print '<div class="fichecenter">';
	print '<div class="fichehalfleft">';
	print '<div class="underbanner clearboth"></div>';
	print '<table class="border centpercent">' . "\n";


	// Common attributes
	//$keyforbreak='fieldkeytoswithonsecondcolumn';
	foreach ($object->fields as $key => $val) {

		// Discard if extrafield is a hidden field on form
		if (abs($val['visible']) != 1)
			continue;

		if (array_key_exists('enabled', $val) && isset($val['enabled']) && !$val['enabled'])
			continue; // We don't want this field
		switch ($key) {
			case 'rowid':
			case 'element_type' :
				break;

			case 'mode_contact' :
				print '<tr id="field_' . $key . '">';
				print '<td';
				print ' class="titlefieldcreate';
				if ($val['notnull'] > 0)
					print ' fieldrequired';
				if ($val['type'] == 'text' || $val['type'] == 'html')
					print ' tdtop';
				print '"';
				print '>';
				print $langs->trans($val['label']);
				print '</td>';

				print '<td>';
				if (!empty($object->$key)) {
					print $object->$key;
				} else {
					echo '-';
				}

				print '</td>';
				print '</tr>';
				break;

			case 'object' :
				print '<tr></tr>';
				print '<tr id="field_' . $key . '">';
				print '<td';
				print ' class="titlefieldcreate';
				if ($val['notnull'] > 0)
					print ' fieldrequired';
				if ($val['type'] == 'text' || $val['type'] == 'html')
					print ' tdtop';
				print '"';
				print '>';
				print $langs->trans($val['label']);
				print '</td>';

				print '<td>';
				if (!empty($object->$key)) {
					print $object->$key;
				} else {
					echo '-';
				}

				print '</label>';

				print '</td>';
				print '</tr>';
				break;

			case 'fk_user_creat' :
				if ($conf->global->contacttracking_CHOOSEUSER == 1) {
					print '<tr id="field_' . $key . '">';
					print '<td';
					print ' class="titlefieldcreate';
					if ($val['notnull'] > 0)
						print ' fieldrequired';
					if ($val['type'] == 'text' || $val['type'] == 'html')
						print ' tdtop';
					print '"';
					print '>';
					print $langs->trans($val['label']);
					print '</td>';

					print '<td>';
					if (!empty($object->$key)) {
						$userdef->fetch($object->$key);
						echo $userdef->getNomUrl();
					} else {
						echo '-';
					}

					print '</td>';
					print '</tr>';
				} else {
					print '<input type="hidden" name="' . $key . '" value="' . $user->id . '" />';
				}
				break;

			case 'type_event':
				print '<tr id="field_' . $key . '">';
				print '<td';
				print ' class="titlefieldcreate';
				if ($val['notnull'] > 0)
					print ' fieldrequired';
				if ($val['type'] == 'text' || $val['type'] == 'html')
					print ' tdtop';
				print '"';
				print '>';
				print $langs->trans($val['label']);
				print '</td>';

				print '<td>';
				if (!empty($object->$key)) {
					$cactioncomm->fetch($object->$key);
					if (!empty($cactioncomm->code)) {
						echo $langs->trans("Action" . $cactioncomm->code);
					}
				} else {
					echo '-';
				}

				print '</td>';
				print '</tr>';
				break;

			case 'fk_event':
				require_once DOL_DOCUMENT_ROOT . '/comm/action/class/actioncomm.class.php';

				require_once DOL_DOCUMENT_ROOT . '/societe/class/societe.class.php';
				$actioncomm = new ActionComm($db);
				$actioncomm->fetch($object->$key);

				/*

				  // Print society link to the reminder

				  print '<tr id="field_fk_soc_contact">';
				  print '<td class="titlefieldcreate">';
				  print 'Client concerné';
				  print '</td>';
				  print '<td>';
				  $soc = new Societe($db);
				  $soc->fetch($actioncomm->socid);
				  echo $soc->getNomUrl(1);
				  print '</td>';
				  print '</tr>';
				*/

				// Print the date of the reminder

				print '<tr id="field_dater">';
				print '<td class="titlefieldcreate">';
				print 'Date de la relance';
				print '</td>';
				print '<td>';
				echo dol_print_date($actioncomm->datep, 'dayhourtextshort');
				print '</td>';
				print '</tr>';


				print '<tr id="field_' . $key . '">';
				print '<td';
				print ' class="titlefieldcreate';
				if ($val['notnull'] > 0)
					print ' fieldrequired';
				if ($val['type'] == 'text' || $val['type'] == 'html')
					print ' tdtop';
				print '"';
				print '>';
				print $langs->trans($val['label']);
				print '</td>';

				print '<td>';
				if (!empty($object->$key)) {
					$o = new ActionComm($db);
					$o->fetch($object->$key);
					echo $o->getNomUrl();
				} else {
					echo '-';
				}

				print '</td>';
				print '</tr>';
				break;

			case 'fk_product' :
				print '<tr id="field_' . $key . '">';
				print '<td';
				print ' class="titlefieldcreate';
				if ($val['notnull'] > 0)
					print ' fieldrequired';
				if ($val['type'] == 'text' || $val['type'] == 'html')
					print ' tdtop';
				print '"';
				print '>';
				print $langs->trans($val['label']);
				print '</td>';

				print '<td>';
				if (!empty($object->$key)) {
					$products = explode(',', $object->$key);
					foreach ($products as $p) {
						$pr = new Product($db);
						$pr->fetch($p);
						print $langs->trans($pr->getNomUrl(1)) . "<br />";
					}
				} else {
					echo '-';
				}

				print '</td>';
				/* if($object->element_type=='facture'){
				  $array = $object->liste_array_facture(2,0,0,$object->fk_soc);
				  echo $form->selectarray("fk_element_id", $array, $object->$key);
				  } */
				print '</tr>';

				break;

			case 'type_contact' :
				print '<tr id="field_' . $key . '">';
				print '<td';
				print ' class="titlefieldcreate';
				if ($val['notnull'] > 0)
					print ' fieldrequired';
				if ($val['type'] == 'text' || $val['type'] == 'html')
					print ' tdtop';
				print '"';
				print '>';
				print $langs->trans($val['label']);
				print '</td>';
				print '<td>';
				if (isset($objecttab[$object->element_type])) {
					$objecttab[$object->element_type]->fetch($object->$key);
					echo $objecttab[$object->element_type]->getNomUrl();
				} else {
					print '-';
				}

				print '</td>';
				print '</tr>';
				break;

			case 'fk_element_id' :
				print '<tr id="field_' . $key . '">';
				print '<td';
				print ' class="titlefieldcreate';
				if ($val['notnull'] > 0)
					print ' fieldrequired';
				if ($val['type'] == 'text' || $val['type'] == 'html')
					print ' tdtop';
				print '"';
				print '>';
				print $langs->trans($val['label']);
				print '</td>';

				print '<td>';
				if (isset($objecttab[$object->element_type])) {
					$objecttab[$object->element_type]->fetch($object->$key);
					echo $objecttab[$object->element_type]->getNomUrl();
				} else {
					print '-';
				}

				print '</td>';
				print '</tr>';

				break;

			case 'fk_category_user' :
				if (!empty($object->$key)) {
					print $object->$key;
					/*$sqlCat = "SELECT c.label, c.rowid";
					$sqlCat .= " FROM " . MAIN_DB_PREFIX . "categorie as c";
					$sqlCat .= " WHERE c.rowid=" . $object->$key;
					$resqlCat = $db->query($sqlCat);
					if ($resqlCat) {
						if ($obj = $db->fetch_object($resqlCat)) {
							print $obj->label;
						}
					} else {
						setEventMessage($db->lasterror, 'errors');
					}*/
				}

			default :
				print '<tr id="field_' . $key . '">';
				print '<td';
				print ' class="titlefieldcreate';
				if ($val['notnull'] > 0)
					print ' fieldrequired';
				if ($val['type'] == 'text' || $val['type'] == 'html')
					print ' tdtop';
				print '"';
				print '>';
				print $langs->trans($val['label']);
				print '</td>';
				print '<td>';
				if (in_array($val['type'], array('int', 'integer')))
					$value = GETPOST($key, 'int');
				elseif ($val['type'] == 'text' || $val['type'] == 'html')
					$value = GETPOST($key, 'none');
				else
					$value = GETPOST($key, 'alpha');
				if (0 && DOL_VERSION > '7') {
					print $object->showInputField($val, $key, $value, '', '', '', 0);
				} else {
					switch ($key) {
						case 'date_creation' :
							echo dol_print_date($object->$key, 'dayhour');
							break;
						case 'fk_soc' :
							require_once DOL_DOCUMENT_ROOT . '/societe/class/societe.class.php';
							if (!empty($object->$key)) {
								$soc = new Societe($db);
								$soc->fetch($object->$key);
								echo $soc->getNomUrl(1);
							} else {
								echo '-';
							}
							break;
						case 'fk_contact' :
							require_once DOL_DOCUMENT_ROOT . '/contact/class/contact.class.php';
							if (!empty($object->$key)) {
								$soc = new Contact($db);
								$soc->fetch($object->$key);
								echo $soc->getNomUrl(1);
								//$form->select_contacts($object->fk_soc, $object->$key, 'fk_contact' , 1, '', '', 0, 'minwidth300');
							} else {
								echo '-';
							}
							break;
						case 'comment' :
							if (!empty($object->$key)) {
								print $object->$key;
							} else {
								echo '-';
							}
							break;
					}
				}

				print '</td>';
				print '</tr>';
		}
	}

	$path = dol_buildpath("/contacttracking/ajax/loadData.php?method=getElementByTiers2&fk_soc=" . $object->fk_soc, 1);

	//echo "<pre>".print_r($object,1)."</pre>";
	print "<script type='text/javascript'>
               $(document).ready(function() {
               typ = '" . $object->element_type . "';
               el = '" . $object->fk_element_id . "';

                var select='';
                    $.getJSON('" . $path . "',function(data){
                       $.each(data,function(key,val){
                        if(typ==val.type && el==val.id){
                            select+=val.text;
                        }

                       });
                });";
	print '</script>';
	print '</table>';
	print '</div>';
	print '</div>';

	print '<div class="clearboth"></div><br>';

	dol_fiche_end();


	// Buttons for actions
	if ($action != 'presend' && $action != 'editline') {
		print '<div class="tabsAction">' . "\n";
		$parameters = array();
		$reshook = $hookmanager->executeHooks('addMoreActionsButtons', $parameters, $object, $action);    // Note that $action and $object may have been modified by hook
		if ($reshook < 0)
			setEventMessages($hookmanager->error, $hookmanager->errors, 'errors');

		if (empty($reshook)) {
			// Modify
			if (($user->rights->contacttracking->write && $object->fk_user_create == $user->id) || $user->rights->contacttracking->writeall) {
				print '<a class="butAction" href="' . $_SERVER["PHP_SELF"] . '?id=' . $object->id . '&amp;action=edit">' . $langs->trans("Modify") . '</a>' . "\n";
			} else {
				print '<a class="butActionRefused" href="#" title="' . dol_escape_htmltag($langs->trans("NotEnoughPermissions")) . '">' . $langs->trans('Modify') . '</a>' . "\n";
			}

			/* Clone not used
			if ($user->rights->contacttracking->write) {
				//print '<div class="inline-block divButAction"><a class="butAction" href="' . $_SERVER['PHP_SELF'] . '?id=' . $object->id . '&amp;socid=' . $object->socid . '&amp;action=clone&amp;object=order">' . $langs->trans("ToClone") . '</a></div>';
			}
			*/

			/*
			  if ($user->rights->contacttracking->write)
			  {
			  if ($object->status == 1)
			  {
			  print '<a class="butActionDelete" href="'.$_SERVER["PHP_SELF"].'?id='.$object->id.'&amp;action=disable">'.$langs->trans("Disable").'</a>'."\n";
			  }
			  else
			  {
			  print '<a class="butAction" href="'.$_SERVER["PHP_SELF"].'?id='.$object->id.'&amp;action=enable">'.$langs->trans("Enable").'</a>'."\n";
			  }
			  }
			 */

			if ((($user->rights->contacttracking->delete && $object->fk_user_create == $user->id) || $user->rights->contacttracking->deleteall)) {
				print '<a class="butActionDelete" href="' . $_SERVER["PHP_SELF"] . '?id=' . $object->id . '&amp;action=delete">' . $langs->trans('Delete') . '</a>' . "\n";
			} else {
				print '<a class="butActionRefused" href="#" title="' . dol_escape_htmltag($langs->trans("NotEnoughPermissions")) . '">' . $langs->trans('Delete') . '</a>' . "\n";
			}
		}

		print '</div>' . "\n";
	}


	// Select mail models is same action as presend
	if (GETPOST('modelselected')) {
		$action = 'presend';
	}

	if ($action != 'presend') {
		print '<div class="fichecenter"><div class="fichehalfleft">';
		print '<a name="builddoc"></a>'; // ancre
		// Documents
		/* $objref = dol_sanitizeFileName($object->ref);
		  $relativepath = $comref . '/' . $comref . '.pdf';
		  $filedir = $conf->contacttracking->dir_output . '/' . $objref;
		  $urlsource = $_SERVER["PHP_SELF"] . "?id=" . $object->id;
		  $genallowed = $user->rights->contacttracking->read;	// If you can read, you can build the PDF to read content
		  $delallowed = $user->rights->contacttracking->create;	// If you can create/edit, you can remove a file on card
		  print $formfile->showdocuments('contacttracking', $objref, $filedir, $urlsource, $genallowed, $delallowed, $object->modelpdf, 1, 0, 0, 28, 0, '', '', '', $soc->default_lang);
		 */

		// Show links to link elements
		//  $linktoelem = $form->showLinkToObjectBlock($object, null, array('contacttracking'));
		//  $somethingshown = $form->showLinkedObjectBlock($object, $linktoelem);

		/*
		  print '</div><div class="fichehalfright"><div class="ficheaddleft">';

		  $MAXEVENT = 10;

		  $morehtmlright = '<a href="'.dol_buildpath('/contacttracking/contacttracking_info.php', 1).'?id='.$object->id.'">';
		  $morehtmlright.= $langs->trans("SeeAll");
		  $morehtmlright.= '</a>';

		  // List of actions on element
		  include_once DOL_DOCUMENT_ROOT . '/core/class/html.formactions.class.php';
		  $formactions = new FormActions($db);
		  $somethingshown = $formactions->showactions($object, 'contacttracking', $socid, 1, '', $MAXEVENT, '', $morehtmlright);

		  print '</div></div></div>'; */
	}

	//Select mail models is same action as presend
	/*
	  if (GETPOST('modelselected')) $action = 'presend';

	  // Presend form
	  $modelmail='inventory';
	  $defaulttopic='InformationMessage';
	  $diroutput = $conf->product->dir_output.'/inventory';
	  $trackid = 'stockinv'.$object->id;

	  include DOL_DOCUMENT_ROOT.'/core/tpl/card_presend.tpl.php';
	 */
}

// End of page
llxFooter();
$db->close();
