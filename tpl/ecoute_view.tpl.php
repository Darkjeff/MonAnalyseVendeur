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
 *
 * $keyforbreak may be defined to key to switch on second column
 */
// Protection to avoid direct call of template
if (empty($conf) || !is_object($conf))
{
	print "Error, template page can't be called as URL";
	exit;
}
if (!is_object($form)) $form = new Form($db);

?>
<!-- BEGIN PHP TEMPLATE commonfields_view.tpl.php -->
<?php

$object->fields = dol_sort_array($object->fields, 'position');

foreach ($object->fields as $key => $val)
{
	if (!empty($keyforbreak) && $key == $keyforbreak) break; // key used for break on second column

	// Discard if extrafield is a hidden field on form
	if (abs($val['visible']) != 1 && abs($val['visible']) != 3 && abs($val['visible']) != 4 && abs($val['visible']) != 5) continue;

	if (array_key_exists('enabled', $val) && isset($val['enabled']) && !verifCond($val['enabled'])) continue; // We don't want this field
	if (in_array($key, array('ref', 'status'))) continue; // Ref and status are already in dol_banner

	if ($key=='salesman') {
		print '<tr id="field_' . $key . '">';
		$user_resp = new User($db);
		$user_resp->fetch($object->fk_user_creat);
		print '<td>RPV</td><td>'.$user_resp->getFullName($langs).'</td>';
		print '</tr>';
		print '<tr id="field_'.$key.'">';
		print '<td>';
		print $langs->trans($val['label']);
		print '</td>';
		print '<td>';
		$user_saleman = new User($db);
		$user_saleman->fetch($object->salesman);
		print $user_saleman->getFullName($langs);
		print '</td>';
		print '</tr>';
	} elseif(strpos($key, 'fk_product_univers')!==false) {
		$product_univ=array();
		if ($key=='fk_product_univers_fix') {
			$txt=$langs->trans('MONANALYSEVENDEUR_PRODUCT_ECOUTE_UNIVFIX');
			$product_univ=explode(',', $conf->global->MONANALYSEVENDEUR_PRODUCT_ECOUTE_UNIVFIX);
			$product_univ_done=explode(',', $object->fk_product_univers_fix);
		} elseif($key=='fk_product_univers_mob') {
			$txt=$langs->trans('MONANALYSEVENDEUR_PRODUCT_ECOUTE_UNIVMOB');
			$product_univ=explode(',', $conf->global->MONANALYSEVENDEUR_PRODUCT_ECOUTE_UNIVMOB);
			$product_univ_done=explode(',', $object->fk_product_univers_mob);
		} elseif($key=='fk_product_univers_add') {
			$txt=$langs->trans('MONANALYSEVENDEUR_PRODUCT_ECOUTE_UNIVADD');
			$product_univ=explode(',', $conf->global->MONANALYSEVENDEUR_PRODUCT_ECOUTE_UNIVADD);
			$product_univ_done=explode(',', $object->fk_product_univers_add);
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
					$checked = '';
					if (!empty($product_univ_done) && in_array($p->id,$product_univ_done)) {$checked = ' checked="checked" ';}
					print '<td><input type="checkbox" disabled name="'.$key.'[]" '.$checked.' value="' . $p->id . '">&nbsp;' . $p->label . "</td>";
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
		$value = $object->$key;
		print '<tr><td';
		print ' class="titlefield fieldname_'.$key;
		//if ($val['notnull'] > 0) print ' fieldrequired';     // No fieldrequired on the view output
		if ($val['type'] == 'text' || $val['type'] == 'html') print ' tdtop';
		print '">';
		if (!empty($val['help'])) print $form->textwithpicto($langs->trans($val['label']), $langs->trans($val['help']));
		else print $langs->trans($val['label']);
		print '</td>';
		print '<td class="valuefield fieldname_'.$key;
		if ($val['type'] == 'text') print ' wordbreak';
		print '">';
		print $object->showOutputField($val, $key, $value, '', '', '', 0);
	}
	//print dol_escape_htmltag($object->$key, 1, 1);
	print '</td>';
	print '</tr>';
}

print '</table>';

// We close div and reopen for second column
print '</div>';

?>
<!-- END PHP TEMPLATE commonfields_view.tpl.php -->
