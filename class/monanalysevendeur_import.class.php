<?php
/* Copyright (C) 2017  Laurent Destailleur <eldy@users.sourceforge.net>
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
 * along with this program. If not, see <https://www.gnu.org/licenses/>.
 */

/**
 * \file        class/monanalysevendeur_import.class.php
 * \ingroup     monanalysevendeur
 * \brief       This file is a CRUD class file for MonAnalyseVendeur_import (Create/Read/Update/Delete)
 */

// Put here all includes required by your class file
require_once DOL_DOCUMENT_ROOT . '/core/class/commonobject.class.php';
require_once DOL_DOCUMENT_ROOT . '/societe/class/societe.class.php';
require_once DOL_DOCUMENT_ROOT . '/contact/class/contact.class.php';
require_once DOL_DOCUMENT_ROOT . '/comm/action/class/actioncomm.class.php';
require_once DOL_DOCUMENT_ROOT.'/categories/class/categorie.class.php';
require_once DOL_DOCUMENT_ROOT . '/core/modules/import/import_xlsx.modules.php';

/**
 * Class for MonAnalyseVendeur_import
 */
class MonAnalyseVendeur_import extends CommonObject
{
	/**
	 * @var string ID of module.
	 */
	public $module = 'monanalysevendeur';

	/**
	 * @var string ID to identify managed object.
	 */
	public $element = 'monanalysevendeur_import';


	/**
	 * @var int  Does this object support multicompany module ?
	 * 0=No test on entity, 1=Test with field entity, 'field@table'=Test with link by field@table
	 */
	public $ismultientitymanaged = 0;

	/**
	 * @var int  Does object support extrafields ? 0=No, 1=Yes
	 */
	public $isextrafieldmanaged = 1;

	/**
	 * @var string String with name of icon for monanalysevendeur_import. Must be the part after the 'object_' into object_monanalysevendeur_import.png
	 */
	public $picto = 'monanalysevendeur_import@monanalysevendeur';

	// BEGIN MODULEBUILDER PROPERTIES
	/**
	 * @var array  Array with all fields and their property. Do not use it as a static var. It may be modified by constructor.
	 */
	/*public $fields = array(
		'rowid' => array('type' => 'integer', 'label' => 'TechnicalID', 'enabled' => '1', 'position' => 1, 'notnull' => 1, 'visible' => 0, 'noteditable' => '1', 'index' => 1, 'css' => 'left', 'comment' => "Id"),
		'ref' => array('type' => 'varchar(128)', 'label' => 'Ref', 'enabled' => '1', 'position' => 10, 'notnull' => 1, 'visible' => 1, 'index' => 1, 'searchall' => 1, 'showoncombobox' => '1', 'comment' => "Reference of object"),
		'description' => array('type' => 'text', 'label' => 'Description', 'enabled' => '1', 'position' => 60, 'notnull' => 0, 'visible' => 3,),
		'date_creation' => array('type' => 'datetime', 'label' => 'DateCreation', 'enabled' => '1', 'position' => 500, 'notnull' => 1, 'visible' => -2,),
		'tms' => array('type' => 'timestamp', 'label' => 'DateModification', 'enabled' => '1', 'position' => 501, 'notnull' => 0, 'visible' => -2,),
		'fk_user_creat' => array('type' => 'integer:User:user/class/user.class.php', 'label' => 'UserAuthor', 'enabled' => '1', 'position' => 510, 'notnull' => 1, 'visible' => -2, 'foreignkey' => 'user.rowid',),
		'fk_user_modif' => array('type' => 'integer:User:user/class/user.class.php', 'label' => 'UserModif', 'enabled' => '1', 'position' => 511, 'notnull' => -1, 'visible' => -2,),
		'import_key' => array('type' => 'varchar(14)', 'label' => 'ImportId', 'enabled' => '1', 'position' => 1000, 'notnull' => -1, 'visible' => -2,),
		'status' => array('type' => 'smallint', 'label' => 'Status', 'enabled' => '1', 'position' => 1000, 'notnull' => 1, 'visible' => 1, 'index' => 1, 'arrayofkeyval' => array('0' => 'Brouillon', '1' => 'Valid&eacute;', '9' => 'Annul&eacute;'),),
	);
	public $rowid;
	public $ref;
	public $description;
	public $date_creation;
	public $tms;
	public $fk_user_creat;
	public $fk_user_modif;
	public $import_key;
	public $status;*/
	// END MODULEBUILDER PROPERTIES

	protected $indexColData = array();

	protected $import_type;

	protected $tempTable;

	public $warnings = array();


	// If this object has a subtable with lines

	/**
	 * @var int    Name of subtable line
	 */
	//public $table_element_line = 'monanalysevendeur_monanalysevendeur_importline';

	/**
	 * @var int    Field with ID of parent key if this object has a parent
	 */
	//public $fk_element = 'fk_monanalysevendeur_import';

	/**
	 * @var int    Name of subtable class that manage subtable lines
	 */
	//public $class_element_line = 'MonAnalyseVendeur_importline';

	/**
	 * @var array    List of child tables. To test if we can delete object.
	 */
	//protected $childtables = array();

	/**
	 * @var array    List of child tables. To know object to delete on cascade.
	 *               If name matches '@ClassNAme:FilePathClass;ParentFkFieldName' it will
	 *               call method deleteByParentField(parentId, ParentFkFieldName) to fetch and delete child object
	 */
	//protected $childtablesoncascade = array('monanalysevendeur_monanalysevendeur_importdet');

	/**
	 * @var MonAnalyseVendeur_importLine[]     Array of subtable lines
	 */
	//public $lines = array();


	/**
	 * Constructor
	 *
	 * @param DoliDb $db Database handler
	 */
	public function __construct(DoliDB $db)
	{
		global $conf, $langs;

		$this->db = $db;

		if (empty($conf->global->MAIN_SHOW_TECHNICAL_ID) && isset($this->fields['rowid'])) $this->fields['rowid']['visible'] = 0;
		if (empty($conf->multicompany->enabled) && isset($this->fields['entity'])) $this->fields['entity']['enabled'] = 0;

		$this->indexColData = array('Civ' => array('key' => '<TITULAIRE><CIVILITE>', 'type' => 'varchar', 'index' => null),
			'Nom' => array('key' => '<TITULAIRE><NOM>', 'index' => null),
			'Prenom' => array('key' => '<TITULAIRE><PRENOM>', 'index' => null),
			'NumVoie1' => array('key' => '<TITULAIRE><NUMEROETVOIE>', 'index' => null),
			'NumVoie2' => array('key' => '<ADRESSEFACTURATION><NUMEROVOIE>', 'index' => null),
			'Zip1' => array('key' => '<TITULAIRE><CODEPOSTALE>', 'index' => null),
			'Zip2' => array('key' => '<ADRESSEFACTURATION><CODEPOSTAL>', 'index' => null),
			'Town1' => array('key' => '<TITULAIRE><CITY>', 'index' => null),
			'Town2' => array('key' => '<ADRESSEFACTURATION><VILLE>', 'index' => null),
			'Phone1' => array('key' => '<TITULAIRE><NUMEROTELEPHONEFIXE>', 'index' => null),
			'Phone2' => array('key' => '<CSU><NUMAPPEL>', 'index' => null),
			'Email' => array('key' => '<TITULAIRE><EMAILCONTACT>', 'index' => null),
			'BirthDay1' => array('key' => '<TITULAIRE><DATENAISSANCETITULAIRE>', 'index' => null),
			'BirthDay2' => array('key' => '<TITULAIRE><DATENAISSANCE>', 'index' => null),
			'Shop1' => array('key' => '<ENTETE><LIBELLEPOINTDEVENTE>', 'index' => null),
			'Shop2' => array('key' => '<EXTRACTCOMMANDE><NOMPOINTVENTE>', 'index' => null),
			'DateAction' => array('key' => 'DATE', 'index' => null),
			'LabelAction' => array('key' => '<CONTRAT><TYPEACTE>', 'index' => null),
			'MarqueMobile' => array('key' => '<MARQUEMOBILE>', 'index' => null),
			'ModeleMobile' => array('key' => '<MODELEMOBILE>', 'index' => null),
			'EligeFibre' => array('key' => '<LIGNEADSLELIGIBILITETHD>', 'index' => null),
		);

		$this->import_type = '3gwin';
	}

	/**
	 * Create object into database
	 *
	 * @param User $user User that creates
	 * @param bool $notrigger false=launch triggers after, true=disable triggers
	 * @return int             <0 if KO, Id of created object if OK
	 */
	public function create(User $user, $notrigger = false)
	{
		return $this->createCommon($user, $notrigger);
	}

	/**
	 * Load object in memory from the database
	 *
	 * @param int $id Id object
	 * @param string $ref Ref
	 * @return int         <0 if KO, 0 if not found, >0 if OK
	 */
	public function fetch($id, $ref = null)
	{
		$result = $this->fetchCommon($id, $ref);
		if ($result > 0 && !empty($this->table_element_line)) $this->fetchLines();
		return $result;
	}

	/**
	 * Load object lines in memory from the database
	 *
	 * @return int         <0 if KO, 0 if not found, >0 if OK
	 */
	public function fetchLines()
	{
		$this->lines = array();

		$result = $this->fetchLinesCommon();
		return $result;
	}


	/**
	 * Load list of objects in memory from the database.
	 *
	 * @param string $sortorder Sort Order
	 * @param string $sortfield Sort field
	 * @param int $limit limit
	 * @param int $offset Offset
	 * @param array $filter Filter array. Example array('field'=>'valueforlike', 'customurl'=>...)
	 * @param string $filtermode Filter mode (AND or OR)
	 * @return array|int                 int <0 if KO, array of pages if OK
	 */
	public function fetchAll($sortorder = '', $sortfield = '', $limit = 0, $offset = 0, array $filter = array(), $filtermode = 'AND')
	{
		global $conf;

		dol_syslog(__METHOD__, LOG_DEBUG);

		$records = array();

		$sql = 'SELECT ';
		$sql .= $this->getFieldList();
		$sql .= ' FROM ' . MAIN_DB_PREFIX . $this->table_element . ' as t';
		if (isset($this->ismultientitymanaged) && $this->ismultientitymanaged == 1) $sql .= ' WHERE t.entity IN (' . getEntity($this->table_element) . ')';
		else $sql .= ' WHERE 1 = 1';
		// Manage filter
		$sqlwhere = array();
		if (count($filter) > 0) {
			foreach ($filter as $key => $value) {
				if ($key == 't.rowid') {
					$sqlwhere[] = $key . '=' . $value;
				} elseif (in_array($this->fields[$key]['type'], array('date', 'datetime', 'timestamp'))) {
					$sqlwhere[] = $key . ' = \'' . $this->db->idate($value) . '\'';
				} elseif ($key == 'customsql') {
					$sqlwhere[] = $value;
				} elseif (strpos($value, '%') === false) {
					$sqlwhere[] = $key . ' IN (' . $this->db->sanitize($this->db->escape($value)) . ')';
				} else {
					$sqlwhere[] = $key . ' LIKE \'%' . $this->db->escape($value) . '%\'';
				}
			}
		}
		if (count($sqlwhere) > 0) {
			$sql .= ' AND (' . implode(' ' . $filtermode . ' ', $sqlwhere) . ')';
		}

		if (!empty($sortfield)) {
			$sql .= $this->db->order($sortfield, $sortorder);
		}
		if (!empty($limit)) {
			$sql .= ' ' . $this->db->plimit($limit, $offset);
		}

		$resql = $this->db->query($sql);
		if ($resql) {
			$num = $this->db->num_rows($resql);
			$i = 0;
			while ($i < ($limit ? min($limit, $num) : $num)) {
				$obj = $this->db->fetch_object($resql);

				$record = new self($this->db);
				$record->setVarsFromFetchObj($obj);

				$records[$record->id] = $record;

				$i++;
			}
			$this->db->free($resql);

			return $records;
		} else {
			$this->errors[] = 'Error ' . $this->db->lasterror();
			dol_syslog(__METHOD__ . ' ' . join(',', $this->errors), LOG_ERR);

			return -1;
		}
	}

	/**
	 * Update object into database
	 *
	 * @param User $user User that modifies
	 * @param bool $notrigger false=launch triggers after, true=disable triggers
	 * @return int             <0 if KO, >0 if OK
	 */
	public function update(User $user, $notrigger = false)
	{
		return $this->updateCommon($user, $notrigger);
	}

	/**
	 * Delete object in database
	 *
	 * @param User $user User that deletes
	 * @param bool $notrigger false=launch triggers after, true=disable triggers
	 * @return int             <0 if KO, >0 if OK
	 */
	public function delete(User $user, $notrigger = false)
	{
		return $this->deleteCommon($user, $notrigger);
		//return $this->deleteCommon($user, $notrigger, 1);
	}

	/**
	 *  Delete a line of object in database
	 *
	 * @param User $user User that delete
	 * @param int $idline Id of line to delete
	 * @param bool $notrigger false=launch triggers after, true=disable triggers
	 * @return int                >0 if OK, <0 if KO
	 */
	public function deleteLine(User $user, $idline, $notrigger = false)
	{
		if ($this->status < 0) {
			$this->error = 'ErrorDeleteLineNotAllowedByObjectStatus';
			return -2;
		}

		return $this->deleteLineCommon($user, $idline, $notrigger);
	}

	/**
	 * Initialise object with example values
	 * Id must be 0 if object instance is a specimen
	 *
	 * @return void
	 */
	public function initAsSpecimen()
	{
		$this->initAsSpecimenCommon();
	}

	/**
	 *    Create an array of lines
	 *
	 * @return array|int        array of lines if OK, <0 if KO
	 */
	public function getLinesArray()
	{
		$this->lines = array();

		$objectline = new MonAnalyseVendeur_importLine($this->db);
		$result = $objectline->fetchAll('ASC', 'position', 0, 0, array('customsql' => 'fk_monanalysevendeur_import = ' . $this->id));

		if (is_numeric($result)) {
			$this->error = $this->error;
			$this->errors = $this->errors;
			return $result;
		} else {
			$this->lines = $result;
			return $this->lines;
		}
	}


	public function importFile($file)
	{
		global $conf, $langs;

		$dol_impoprt_xlsx = new ImportXlsx($this->db, null);

		$error = 0;
		$this->output = '';
		$this->error = '';

		dol_syslog(__METHOD__, LOG_DEBUG);

		$now = dol_now();

		$this->db->begin();

		if (file_exists($file)) {

			$this->initFile(basename($file));
			$result = $this->createTempTable();
			if ($result < 0) {
				return $result;
			}

			$nb_lines = $dol_impoprt_xlsx->import_get_nb_of_lines($file);

			//For debug
			//$nb_lines = 50;

			$dol_impoprt_xlsx->import_open_file($file);
			$dol_impoprt_xlsx->import_read_header();

			for ($currentline = 1; $currentline <= $nb_lines; $currentline++) {
				$record = $dol_impoprt_xlsx->import_read_record();
				$colname = array();
				$values = array();
				//Find column index on first row
				if (!empty($record) && $currentline == 1) {
					foreach ($record as $colindex => $data) {
						foreach ($this->indexColData as $dataname => $datacolumnname) {
							if ($data['val'] == $datacolumnname['key']) {
								$this->indexColData[$dataname]['index'] = $colindex;
							}
						}
					}
					foreach ($this->indexColData as $dataname => $datacolumnname) {
						if ($datacolumnname['index'] == null) {
							$this->errors[] = $langs->transnoentities('MAVIndexNotFound', dol_htmlentities($datacolumnname['key']));
							$error++;
						}

					}
				}
				if (!empty($error)) {
					$dol_impoprt_xlsx->import_close_file();
					return -1;
				}

				if (!empty($record) && $currentline > 1) {
					$sql = 'INSERT INTO ' . $this->tempTable . '(';
					foreach ($this->indexColData as $dataname => $datacolumnname) {
						$colname[] = $dataname;
					}
					$sql .= implode(',', $colname);
					$sql .= ')';
					$sql .= ' VALUES (';
					foreach ($this->indexColData as $dataname => $datacolumnname) {
						if (empty($record[$datacolumnname['index']]['val'])) {
							$values[] = 'NULL';
						} else {
							//var_dump($datacolumnname['key'],$record[$datacolumnname['index']]);
							$values[] = '\'' . $this->db->escape(trim($record[$datacolumnname['index']]['val'])) . '\'';
						}
					}
					$sql .= implode(',', $values);
					$sql .= ')';
					$resql = $this->db->query($sql);
					if (!$resql) {
						$this->errors[] = $this->db->lasterror;
						$error++;
					}
				}
				if (!empty($error)) {
					$dol_impoprt_xlsx->import_close_file();
					return -1;
				}
			}

			$dol_impoprt_xlsx->import_close_file();

			$result=$this->findThirdparty();
			if ($result < 0) {
				return $result;
			}

			$result=$this->createThirdparty();
			if ($result < 0) {
				var_dump($result);
				return $result;
			}

			$result=$this->createContactPhone();
			if ($result < 0) {
				return $result;
			}

		} else {
			$this->error = $langs->trans('FileNotFound');
			return -1;
		}
	}

	/**
	 *
	 */
	protected function findThirdparty()
	{
		$error = 0;
		$sql = 'UPDATE ' . $this->tempTable . ' as dest, ' . MAIN_DB_PREFIX . 'societe as src';
		$sql .= ' SET dest.fk_soc=src.rowid ';
		$sql .= ' WHERE src.nom=CONCAT(dest.Nom,\' \',dest.Prenom)';
		$sql .= ' AND src.zip=IFNULL(dest.Zip1,dest.Zip2)';
		$sql .= ' AND src.town=IFNULL(dest.Town1,dest.Town2)';
		$sql .= ' AND src.address=IFNULL(dest.NumVoie1,dest.NumVoie2)';

		$resql = $this->db->query($sql);
		if (!$resql) {
			$this->errors[] = $this->db->lasterror;
			return -1;
		} else {
			return 1;
		}
	}

	/**
	 * @return float|int
	 */
	protected function createContactPhone()
	{
		global $user, $langs;
		$contact_created = 0;
		$error=0;

		$sql = "SELECT rowid,fk_soc,";
		foreach ($this->indexColData as $dataname => $datacolumnname) {
			$colname[] = $dataname;
		}
		$sql .= implode(',', $colname);
		$sql .= ' FROM ' . $this->tempTable . ' WHERE fk_soc IS NOT NULL';
		$resql = $this->db->query($sql);
		if (!$resql) {
			$this->errors[] = $this->db->lasterror;
			return -1;
		} else {
			while ($obj = $this->db->fetch_object($resql)) {
				$phone=(!empty($obj->Phone1)?$obj->Phone1:$obj->Phone2);
				$sql_phone = 'SELECT rowid, lastname, fk_soc, phone FROM '.MAIN_DB_PREFIX.'socpeople';
				$sql_phone .= ' WHERE LPAD(phone,10,\'0\')=\''.str_pad(trim($phone), 10, '0',STR_PAD_LEFT).'\'';
				$resql_phone = $this->db->query($sql_phone);
				if (!$resql_phone) {
					$this->errors[] = $this->db->lasterror;
					return -1;
				} else {
					$num=$this->db->num_rows($resql_phone);
					if ($num==0) {
						$sql_cnt='select count(rowid) as cnt from '.MAIN_DB_PREFIX.'socpeople WHERE fk_soc='.$obj->fk_soc;
						$resql_cnt = $this->db->query($sql_cnt);
						if (!$resql_cnt) {
							$this->errors[] = $this->db->lasterror;
							$error++;
						} else {
							if ($obj_cnt = $this->db->fetch_object($resql_cnt)) {
								$name=$langs->trans('Phone'). " #".(((int) $obj_cnt->cnt)+1);
							}
							$contact = new Contact($this->db);
							$contact->phone_pro=str_pad(trim($phone), 10, '0', STR_PAD_LEFT);
							$contact->socid=$obj->fk_soc;
							$contact->lastname=$name;
							$contact->import_key = dol_now();
							$contact->array_options['options_mav_contact_brandmob'] = $obj->MarqueMobile;
							$contact->array_options['options_mav_contact_modelmobile'] = $obj->ModeleMobile;
							$result = $contact->create($user);
							if ($result < 0) {
								$this->errors[] = $contact->error;
								$error++;
							} else {
								$contact_created++;
							}
						}
					}
				}
			}
		}

		if (empty($error)) {
			$this->db->commit();
			return $contact_created;
		} else {
			$this->db->rollback();
			return - 1 * $error;
		}
	}

	/**
	 * @return float|int
	 */
	protected function createThirdparty() {
		global $user;
		$soc_created=0;
		$alreadydone=array();
		$this->db->begin();
		$sql="SELECT rowid,";
		foreach ($this->indexColData as $dataname => $datacolumnname) {
			$colname[] = $dataname;
		}
		$sql .= implode(',', $colname);
		$sql .= ' FROM '.$this->tempTable.' WHERE fk_soc IS NULL';
		$resql = $this->db->query($sql);
		if (!$resql) {
			$this->errors[] = $this->db->lasterror;
			return -1;
		} else {
			while ($obj=$this->db->fetch_object($resql)) {
				if (!in_array(hash('md5', $obj->Nom.$obj->Prenom.$obj->address.$obj->zip.$obj->town), $alreadydone) && !empty($obj->Nom . $obj->Prenom)) {
					$soc = new Societe($this->db);
					$soc->name = $obj->Nom . ' ' . $obj->Prenom;
					$soc->client = 1;
					$soc->status = 1;
					$soc->country_id = 1;
					$soc->address = (!empty($obj->NumVoie1) ? $obj->NumVoie1 : $obj->NumVoie2);
					$soc->zip = (!empty($obj->Zip1) ? $obj->Zip1 : $obj->Zip2);
					$soc->town = (!empty($obj->Town1) ? $obj->Town1 : $obj->Town2);
					$soc->email = $obj->Email;
					$soc->code_client = 'auto';
					$soc->import_key = dol_now();

					//date bitrh
					/*if (!empty($obj->BirthDay1)) {
						var_dump($obj->BirthDay1);
						$timeZone = new DateTimeZone('Europe/Paris');
						$dateSrc = $obj->BirthDay1;
						$dtBirth = new DateTime($dateSrc ,$timeZone);
						$dtBirth=dol_mktime(0, 0, 0, $dtBirth->format('%m'), $dtBirth->format('%d'), $dtBirth->format('%Y'));
					} elseif (!empty($obj->BirthDay2)) {
						$dtBirth = new DateTime($obj->BirthDay2);
						$dtBirth=dol_mktime(0, 0, 0, $dtBirth->format('%m'), $dtBirth->format('%d'), $dtBirth->format('%Y'));
					}
					$soc->array_options['options_mav_thirdparty_birthday'] = $dtBirth;*/
					$soc->array_options['options_mav_thirdparty_eligbfilter'] = (!empty($obj->EligeFibre)?1:null);

					$result = $soc->create($user);
					if ($result < 0) {
						$this->errors[] = $soc->error;
						$error++;
					} else {
						$soc_created++;
						$alreadydone[] = hash('md5', $obj->Nom . $obj->Prenom . $obj->address . $obj->zip . $obj->town);
						$sql_upd = 'UPDATE ' . $this->tempTable . ' SET fk_soc=' . $soc->id . ' WHERE Nom=\'' . $this->db->escape($obj->Nom) . '\'';
						$sql_upd .= ' AND Prenom=\'' . $this->db->escape($obj->Prenom) . '\'';
						$sql_upd .= ' AND IFNULL(NumVoie1,NumVoie2)='.(empty($soc->address)?'NULL':'\'' . $this->db->escape($soc->address) . '\'');
						$sql_upd .= ' AND IFNULL(Zip1,Zip2)='.(empty($soc->zip)?'NULL':'\'' . $this->db->escape($soc->zip) . '\'');
						$sql_upd .= ' AND IFNULL(Town1,Town2)='.(empty($soc->town)?'NULL':'\'' . $this->db->escape($soc->town) . '\'');
						$resql_upd = $this->db->query($sql_upd);
						if (!$resql_upd) {
							$this->errors[] = $this->db->lasterror;
							$error++;
						}

						$shop=  (!empty($obj->Shop1) ? $obj->Shop1 : $obj->Shop2);
						if (!empty($shop)) {
							$cat = new Categorie($this->db);
							$result = $cat->fetch(0, $shop, Categorie::TYPE_CUSTOMER);
							if ($result<0) {
								$this->errors[]=$this->db->lasterror;
								$this->errors[]=$cat->error;
								$error++;
							}
							if (empty($cat->id)) {
								$cat = new Categorie($this->db);
								$cat->label=$shop;
								$cat->type=Categorie::TYPE_CUSTOMER;
								$result = $cat->create($user);
								if ($result<0) {
									$this->errors[]=$this->db->lasterror;
									$this->errors[]=$cat->error;
									$error++;
								}
							}
							if (!empty($cat->id)) {
								$result=$soc->setCategories(array($cat->id), Categorie::TYPE_CUSTOMER);
								if ($result<0) {
									$this->errors[]=$soc->error;
									$error++;
								}
							}

						}
					}
				}
			}
		}
		if (empty($error)) {
			$this->db->commit();
			return $soc_created;
		} else {
			$this->db->rollback();
			return - 1 * $error;
		}
	}

	/**
	 * @return int
	 * @throws Exception
	 */
	protected function createTempTable()
	{
		// Build sql temp table

		$sql = 'DROP TABLE IF EXISTS ' . $this->tempTable;
		dol_syslog(get_class($this) . '::' . __METHOD__ . ' Build sql temp table', LOG_DEBUG);
		$resql = $this->db->query($sql);
		if (!$resql) {
			$this->errors[] = $this->db->lasterror;
			return -1;
		} else {
			$sql = 'CREATE TABLE ' . $this->tempTable;
			$sql .= '(';
			$sql .= 'rowid integer NOT NULL auto_increment PRIMARY KEY,';
			$sql .= 'fk_soc integer DEFAULT NULL,';
			$sql .= 'integration_status integer DEFAULT NULL,';
			$sql .= 'integration_action varchar(20) DEFAULT NULL,';
			foreach ($this->indexColData as $dataname => $datacolumnname) {
				$sql .= $dataname . ' text,';
			}
			$sql .= 'tms timestamp NOT NULL';
			$sql .= ')ENGINE=InnoDB;';

			dol_syslog(get_class($this) . '::' . __METHOD__ . ' Build sql temp table', LOG_DEBUG);
			$resql = $this->db->query($sql);
			if (!$resql) {
				$this->errors[] = $this->db->lasterror;
				return -1;
			} else {
				return 1;
			}
		}
	}

	/**
	 */
	protected function dropTempTable()
	{
		$error=0;
		if (!empty($this->tempTable)) {
			$sql = ' DROP TABLE IF EXISTS ' . $this->tempTable;

			dol_syslog(get_class($this) . '::' . __METHOD__, LOG_DEBUG);
			$resql = $this->db->query($sql);
			if (!$resql) {
				$this->errors[] = $this->db->lasterror;
				$error++;
			}
		}

		if (empty($error)) {
			return 1;
		} else {
			return -1 * $error;
		}
	}

	/**
	 *
	 * @param unknown $filesource
	 */
	protected function initFile($filesource)
	{
		global $user;
		$this->filesource = $filesource;
		$this->tempTable = MAIN_DB_PREFIX . 'monanalysevendeur_tmp_' . $this->import_type . '_' . $user->id . '_' . dol_trunc($this->monananylsevendeur_string_nospecial(basename($this->filesource)), 10, 'right', 'UTF-8', 1);
	}

	protected function monananylsevendeur_string_nospecial($str, $newstr = '_', $badcharstoreplace = '')
	{
		dol_syslog(get_class($this) . '::' . __METHOD__, LOG_DEBUG);

		$forbidden_chars_to_replace = array(
			" ",
			"'",
			"/",
			"\\",
			":",
			"*",
			"?",
			"\"",
			"<",
			">",
			"|",
			"[",
			"]",
			",",
			";",
			"=",
			"°",
			"&",
			"-",
			".",
			"(",
			")",
			"%",
			"+"
		);
		$forbidden_chars_to_remove = array();
		if (is_array($badcharstoreplace))
			$forbidden_chars_to_replace = $badcharstoreplace;
		// $forbidden_chars_to_remove=array("(",")");

		$str = str_replace($forbidden_chars_to_replace, $newstr, str_replace($forbidden_chars_to_remove, "", $str));
		$str = str_replace('€', 'EUR', $str);
		$str = dol_string_unaccent($str);
		$str = dol_strtolower($str);

		return $str;
	}
}


require_once DOL_DOCUMENT_ROOT . '/core/class/commonobjectline.class.php';

/**
 * Class MonAnalyseVendeur_importLine. You can also remove this and generate a CRUD class for lines objects.
 */
class MonAnalyseVendeur_importLine extends CommonObjectLine
{
	// To complete with content of an object MonAnalyseVendeur_importLine
	// We should have a field rowid, fk_monanalysevendeur_import and position

	/**
	 * @var int  Does object support extrafields ? 0=No, 1=Yes
	 */
	public $isextrafieldmanaged = 0;

	/**
	 * Constructor
	 *
	 * @param DoliDb $db Database handler
	 */
	public function __construct(DoliDB $db)
	{
		$this->db = $db;
	}
}
