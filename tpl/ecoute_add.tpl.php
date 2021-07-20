<?php
/* Copyright (C) 2017  Laurent Destailleur  <eldy@users.sourceforge.net>
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
 *
 * Need to have following variables defined:
 * $object (invoice, order, ...)
 * $action
 * $conf
 * $langs
 * $form
 */

// Protection to avoid direct call of template
if (empty($conf) || !is_object($conf))
{
	print "Error, template page can't be called as URL";
	exit;
}

?>
<!-- BEGIN PHP TEMPLATE commonfields_add.tpl.php -->
<?php

$object->fields = dol_sort_array($object->fields, 'position');

foreach ($object->fields as $key => $val)
{

	// Discard if extrafield is a hidden field on form
	if (is_int($val['visible'])) {
		$visible = 	$val['visible'];
	} else {
		$visible=dol_eval($val['visible'],1);
	}
	if (abs($visible) != 1 && abs($visible) != 3) continue;



	if (array_key_exists('enabled', $val) && isset($val['enabled']) && !verifCond($val['enabled'])) continue; // We don't want this field

	if ($key=='salesman') {
		print '<tr id="field_' . $key . '">';
		print '<td>RPV</td><td>'.$user->getFullName($langs).'</td>';
		print '</tr>';
		print '<tr id="field_'.$key.'">';
		print '<td';
		print ' class="titlefieldcreate';
		if ($val['notnull'] > 0) print ' fieldrequired';
		print '"';
		print '>';
		print $langs->trans($val['label']);
		print '</td>';
		print '<td>';
		echo $form->select_dolusers('', 'salesman', 0, null, 0, 'hierarchy', '', 0, 0, 0, '', 0, '', '', 1);
		print '</td>';
		print '</tr>';
	} elseif(strpos($key, 'fk_product_univers')!==false) {
		$product_univ=array();
		if ($key=='fk_product_univers_fix') {
			$txt=$langs->trans('MONANALYSEVENDEUR_PRODUCT_ECOUTE_UNIVFIX');
			$product_univ=explode(',', $conf->global->MONANALYSEVENDEUR_PRODUCT_ECOUTE_UNIVFIX);
		} elseif($key=='fk_product_univers_mob') {
			$txt=$langs->trans('MONANALYSEVENDEUR_PRODUCT_ECOUTE_UNIVMOB');
			$product_univ=explode(',', $conf->global->MONANALYSEVENDEUR_PRODUCT_ECOUTE_UNIVMOB);
		} elseif($key=='fk_product_univers_add') {
			$txt=$langs->trans('MONANALYSEVENDEUR_PRODUCT_ECOUTE_UNIVADD');
			$product_univ=explode(',', $conf->global->MONANALYSEVENDEUR_PRODUCT_ECOUTE_UNIVADD);
		}
		print '<tr id="field_' . $key . '">';
		print '<td>'.$txt.'</td>';
		print '<td>';

		if (!empty($product_univ)) {
			print '<table>';
			print '<tr>';
			foreach ($product_univ as $prod) {
				if (rtrim(trim($prod)) != '') {
					$p = new Product($db);
					$p->fetch($prod);
					print '<td><input type="checkbox" name="'.$key.'[]" value="' . $p->id . '">&nbsp;' . $p->label . "</td>";
					if ($o > 0 && $o % 3 === 0) {
						print '</tr>';
						print '<tr>';
					}

					$o++;
				}
			}
			print '</table>';
		}
		print '</td>';
		print '</tr>';
	} else {
		print '<tr id="field_'.$key.'">';
		print '<td';
		print ' class="titlefieldcreate';
		if ($val['notnull'] > 0) print ' fieldrequired';
		if ($val['type'] == 'text' || $val['type'] == 'html') print ' tdtop';
		print '"';
		print '>';
		if (!empty($val['help'])) print $form->textwithpicto($langs->trans($val['label']), $langs->trans($val['help']));
		else print $langs->trans($val['label']);
		print '</td>';
		print '<td>';
		if (in_array($val['type'], array('int', 'integer'))) $value = GETPOST($key, 'int');
		elseif ($val['type'] == 'text' || $val['type'] == 'html') $value = GETPOST($key, 'none');
		elseif ($val['type'] == 'date') $value = dol_mktime(12, 0, 0, GETPOST($key.'month', 'int'), GETPOST($key.'day', 'int'), GETPOST($key.'year', 'int'));
		elseif ($val['type'] == 'datetime') $value = dol_mktime(GETPOST($key.'hour', 'int'), GETPOST($key.'min', 'int'), 0, GETPOST($key.'month', 'int'), GETPOST($key.'day', 'int'), GETPOST($key.'year', 'int'));
		elseif ($val['type'] == 'boolean') $value = (GETPOST($key) == 'on' ? 1 : 0);
		else $value = GETPOST($key, 'alphanohtml');
		if ($val['noteditable']) print $object->showOutputField($val, $key, $value, '', '', '', 0);
		else print $object->showInputField($val, $key, $value, '', '', '', 0);
		print '</td>';
		print '</tr>';
	}


}

?>
<!-- END PHP TEMPLATE commonfields_add.tpl.php -->
