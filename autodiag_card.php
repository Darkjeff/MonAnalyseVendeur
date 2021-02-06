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
if (GETPOST('action') == 'add') {
    // Actions cancel, add, update or delete
    include DOL_DOCUMENT_ROOT . '/core/actions_addupdatedelete.inc.php';

    $object->date_creation = strtotime(GETPOST('date_creationyear') . '-' . GETPOST('date_creationmonth') . '-' . GETPOST('date_creationday') . ' ' . GETPOST('date_creationhour') . ':' . GETPOST('date_creationmin') . ':00');
    $object->mode_contact = GETPOST('mode_contact');
    $object->object = GETPOST('object');
    $object->fk_soc = GETPOST('fk_soc');
    $object->fk_user_creat = GETPOST('fk_user_creat');
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
	$result=$object->create($user);
    if ($result < 0) {
        setEventMessage($object->error,'errors');
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
        if ($conf->global->contacttracking_CHOOSEUSER == 1){
            $actionComm->userownerid = GETPOST('fk_user_creat');
        } else {
            $actionComm->userownerid = $user->id;
        }

        if (!empty(GETPOST('reminder')))
            $actionComm->note = GETPOST('reminder');
        else
            $actionComm->note = GETPOST('comment');

        $event = $actionComm->create($user);
        if ($event < 0) {
            $err++;
        } else {
            $object->fk_event = $event;
            $object->update($user);
        }
    }

    if ($err > 0) {
    	echo $db->lasterror();
    } else {
    	setEventMessage($langs->trans('Success'));
		Header("Location: autodiag_list.php?relance=".GETPOST('relance', 'int'));
		exit();
	}
}

$title = $langs->trans("Autodiag");
$help_url = '';
llxHeader('', $title, $help_url);
dol_fiche_head(array(), '');
if (!$user->rights->contacttracking->write) :
    ?>
    <div class="error"><?php echo $langs->trans("contacttracking_NORIGHT") ?>.</div>
<?php else: ?>


    <?php
    $form = new Form($db);
    $formfile = new FormFile($db);

    print '<form method="POST" action="' . $_SERVER["PHP_SELF"] . '">';
    print '<input type="hidden" name="token" value="' . $_SESSION['newtoken'] . '">';
    print '<input type="hidden" name="action" value="add">';
    print '<input type="hidden" name="backtopage" value="' . $backtopage . '">';
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
				echo $form->select_company($val, 'fk_soc', '', '',0 ,1);
				if (GETPOST('relance', 'int')==1) {
				echo ' <a href="'.DOL_URL_ROOT.'/societe/card.php?action=create&backtopage='.urlencode($_SERVER["PHP_SELF"].'?action=create&relance=1').'"><span class="fa fa-plus-circle valignmiddle paddingleft" title="'.$langs->trans("AddThirdParty").'"></span></a>';
				} else {
				echo ' <a href="'.DOL_URL_ROOT.'/societe/card.php?action=create&backtopage='.urlencode($_SERVER["PHP_SELF"].'?action=create&relance=0').'"><span class="fa fa-plus-circle valignmiddle paddingleft" title="'.$langs->trans("AddThirdParty").'"></span></a>';
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
                if (in_array($val['type'], array('int', 'integer')))
                    $value = GETPOST($key, 'int');
                elseif ($val['type'] == 'text' || $val['type'] == 'html')
                    $value = GETPOST($key, 'none');
                else
                    $value = GETPOST($key, 'alpha');
                if ((int) DOL_VERSION > 7) {
                    // Hack permettant l'affichage et l'autocompletion d'un input meme si la constante COMPANY_USE_SEARCH_TO_SELECT > 0
                    if ($conf->global->COMPANY_USE_SEARCH_TO_SELECT > 0 && $key == 'fk_soc' && ! empty(GETPOST('testSoc'))) {
                        echo $form->select_company($val, 'fk_soc', '', '',0 ,1);
						echo ' <a href="'.DOL_URL_ROOT.'/societe/card.php?action=create&backtopage='.urlencode($_SERVER["PHP_SELF"].'?action=create').'"><span class="fa fa-plus-circle valignmiddle paddingleft" title="'.$langs->trans("AddThirdParty").'"></span></a>';
                    } else {
                        switch ($key) {
                            case 'date_creation' :
                                $form->select_date('', 'date_creation', 1, 'SelectThirdParty', 0, "date_creation");
                                break;
                            case 'fk_soc' :
                              //  echo $form->select_company('', 'fk_soc', '', '');
								//echo ' <a href="'.DOL_URL_ROOT.'/societe/card.php?action=create&backtopage='.urlencode($_SERVER["PHP_SELF"].'?action=create').'"><span class="fa fa-plus-circle valignmiddle paddingleft" title="'.$langs->trans("AddThirdParty").'"></span></a>';
                                break;
                            case 'fk_contact' :
                               // $form->select_contacts(0, '', 'fk_contact', 1, '', '', 0, 'minwidth300');
                                break;
                            case 'comment' :
                                print '<textarea name="comment" class="flat minwidth300" rows="5">';
                                print '</textarea>';
                                break;
                        }
                    }
                } else {
                    switch ($key) {
                        case 'date_creation' :
                            $form->select_date('', 'date_creation', 1, 1, 0, "date_creation");
                            break;
                        case 'fk_soc' :
                         //   echo $form->select_company('', 'fk_soc', '', '');
                          //  break;
                        case 'fk_contact' :
                           // $form->select_contacts(0, '', 'fk_contact', 1, '', '', 0, 'minwidth300');
                            break;
                        case 'comment' :
                            print '<textarea name="comment" class="flat minwidth300" rows="5">';
                            print '</textarea>';
                            break;
                    }
                }

                print '</td>';
                print '</tr>';
        }
    }

    if ($conf->global->contacttracking_CHOOSEUSER == 1 && GETPOST('relance', 'int')==1) {
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
	if (GETPOST('relance', 'int')==1) {
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
		//print '<tr id="field_reminder">';
		//print '<td';
		//print ' class="titlefieldcreate';
		//print '"';
		//print '>';
		//print $langs->trans('TextRelance');
		//print '</td>';
		//print '<td>';
		//print '<textarea name="reminder" id="reminder" class="flat minwidth300" style="margin-top: 5px; width: 90%" rows="5"></textarea>';
		//print '</td>';
		//print '</tr>';
	}


    print '</table>' . "\n";

    dol_fiche_end();

    print '<div class="center">';
    print '<input type="submit" class="button" name="add" value="' . dol_escape_htmltag($langs->trans("Create")) . '">';
    print '&nbsp; ';
    print '<input type="button" class="button" name="cancel" value="' . dol_escape_htmltag($langs->trans("Cancel")) . '" onclick="window.parent.$(\'#modal-contact\').dialog(\'close\');">'; // Cancel for create does not post form if we don't know the backtopage
    print '</div>';

    print '</form>';
endif;

dol_fiche_end();
?>

<script>

</script>
