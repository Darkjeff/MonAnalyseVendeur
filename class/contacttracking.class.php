<?php

/* Copyright (C) 2017  Laurent Destailleur <eldy@users.sourceforge.net>
 * Copyright (C) 2018 Nicolas ZABOURI   <info@inovea-conseil.com>
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
 * \file        class/contacttracking.class.php
 * \ingroup     contacttrackingObjet	￼
 * \brief       This file is a CRUD class file for Contacttracking (Create/Read/Update/Delete)
 */
// Put here all includes required by your class file
require_once DOL_DOCUMENT_ROOT . '/core/class/commonobject.class.php';
//require_once DOL_DOCUMENT_ROOT . '/societe/class/societe.class.php';
//require_once DOL_DOCUMENT_ROOT . '/product/class/product.class.php';

/**
 * Class for Contacttracking
 */
class Contacttracking extends CommonObject
{

    /**
     * @var string ID to identify managed object
     */
    public $element = 'contacttracking';

    /**
     * @var string Name of table without prefix where object is stored
     */
    public $table_element = 'contacttracking';

    /**
     * @var int  Does contacttracking support multicompany module ? 0=No test on entity, 1=Test with field entity, 2=Test with link by societe
     */
    public $ismultientitymanaged = 0;

    /**
     * @var int  Does contacttracking support extrafields ? 0=No, 1=Yes
     */
    public $isextrafieldmanaged = 0;

    /**
     * @var string String with name of icon for contacttracking. Must be the part after the 'object_' into object_contacttracking.png
     */
    public $picto = 'contacttracking@contacttracking';

    /**
     *  'type' if the field format.
     *  'label' the translation key.
     *  'enabled' is a condition when the field must be managed.
     *  'visible' says if field is visible in list (Examples: 0=Not visible, 1=Visible on list and create/update/view forms, 2=Visible on list only. Using a negative value means field is not shown by default on list but can be selected for viewing)
     *  'notnull' is set to 1 if not null in database. Set to -1 if we must set data to null if empty ('' or 0).
     *  'index' if we want an index in database.
     *  'foreignkey'=>'tablename.field' if the field is a foreign key (it is recommanded to name the field fk_...).
     *  'position' is the sort order of field.
     *  'searchall' is 1 if we want to search in this field when making a search from the quick search button.
     *  'isameasure' must be set to 1 if you want to have a total on list for this field. Field type must be summable like integer or double(24,8).
     *  'help' is a string visible as a tooltip on field
     *  'comment' is not used. You can store here any text of your choice. It is not used by application.
     *  'default' is a default value for creation (can still be replaced by the global setup of default values)
     *  'showoncombobox' if field must be shown into the label of combobox
     */
    // BEGIN MODULEBUILDER PROPERTIES
	/**
	 * @var array  Array with all fields and their property. Do not use it as a static var. It may be modified by constructor.
	 */
	public $fields=array(
		'rowid' => array('type'=>'integer', 'label'=>'TechnicalID', 'enabled'=>'1', 'position'=>1, 'notnull'=>1, 'visible'=>1, 'index'=>1,),
		'entity' => array('type'=>'integer', 'label'=>'Entity', 'enabled'=>'0', 'position'=>20, 'notnull'=>1, 'visible'=>-1, 'index'=>1,),
		'date_creation' => array('type'=>'datetime', 'label'=>'DateCreation', 'enabled'=>'1', 'position'=>500, 'notnull'=>1, 'visible'=>1,),
		'type_contact' => array('type'=>'integer', 'label'=>'Type', 'enabled'=>'1', 'position'=>504, 'notnull'=>1, 'visible'=>1, 'comment'=>"Type de contact (entrant ou sortant)"),
		'tms' => array('type'=>'timestamp', 'label'=>'DateModification', 'enabled'=>'1', 'position'=>501, 'notnull'=>1, 'visible'=>-2,),
		'fk_user_modif' => array('type'=>'integer', 'label'=>'UserModif', 'enabled'=>'1', 'position'=>511, 'notnull'=>-1, 'visible'=>-2,),
		'import_key' => array('type'=>'varchar(14)', 'label'=>'ImportId', 'enabled'=>'1', 'position'=>1000, 'notnull'=>-1, 'visible'=>-2,),
		'mode_contact' => array('type'=>'varchar(255)', 'label'=>'ContactMode', 'enabled'=>'1', 'position'=>505, 'notnull'=>-1, 'visible'=>1, 'searchall'=>1, 'comment'=>"Mode de contact"),
		'fk_product' => array('type'=>'text', 'label'=>'Produits', 'enabled'=>'1', 'position'=>500, 'notnull'=>-1, 'visible'=>1, 'index'=>1,),
		'fk_soc' => array('type'=>'integer:Societe:societe/class/societe.class.php', 'label'=>'ThirdParty', 'enabled'=>'1', 'position'=>500, 'notnull'=>1, 'visible'=>1, 'index'=>1, 'searchall'=>1, 'help'=>"LinkToThirparty",),
		'fk_contact' => array('type'=>'integer:Contact:contact/class/contact.class.php', 'label'=>'Contact', 'enabled'=>'1', 'position'=>503, 'notnull'=>-1, 'visible'=>0, 'searchall'=>1,),
		'fk_user_creat' => array('type'=>'integer', 'label'=>'User', 'enabled'=>'1', 'position'=>501, 'notnull'=>1, 'visible'=>1,),
		'element_type' => array('type'=>'varchar(255)', 'label'=>'ElementType', 'enabled'=>'1', 'position'=>506, 'notnull'=>-1, 'visible'=>1, 'searchall'=>1, 'comment'=>"Type de l'element concerné (exemple : propal, invoice, etc...)"),
		'fk_element_id' => array('type'=>'integer', 'label'=>'ElementID', 'enabled'=>'1', 'position'=>506, 'notnull'=>-1, 'visible'=>1,),
		'object' => array('type'=>'varchar(255)', 'label'=>'ObjectContact', 'enabled'=>'1', 'position'=>507, 'notnull'=>-1, 'visible'=>1, 'searchall'=>1, 'comment'=>"Objet de l'échange"),
		'comment' => array('type'=>'text', 'label'=>'Comment', 'enabled'=>'1', 'position'=>508, 'notnull'=>-1, 'visible'=>1, 'searchall'=>1, 'comment'=>"Commentaire concernant l'echange"),
		'type_event' => array('type'=>'varchar(255)', 'label'=>'TypeEvent', 'enabled'=>'1', 'position'=>507, 'notnull'=>1, 'visible'=>1, 'searchall'=>1, 'comment'=>"Type de l'evenement"),
		'fk_event' => array('type'=>'integer:ActionComm:comm/action/class/actioncomm.class.php', 'label'=>'Event', 'enabled'=>'1', 'position'=>1000, 'notnull'=>-1, 'visible'=>1, 'index'=>1, 'searchall'=>1,),
		//'fk_event' => array('type'=>'integer:ActionComm:comm/action/class/actioncomm.class.php', 'label'=>'Event', 'enabled'=>'1', 'position'=>1000, 'notnull'=>-1, 'visible'=>1, 'index'=>1, 'searchall'=>1,),
		//'fk_event' => array('type'=>'integer:ActionComm:comm/action/class/actioncomm.class.php', 'label'=>'Event', 'enabled'=>'1', 'position'=>1000, 'notnull'=>-1, 'visible'=>1, 'index'=>1, 'searchall'=>1,),
	);
	public $rowid;
	public $entity;
	public $date_creation;
	public $type_contact;
	public $tms;
	public $fk_user_modif;
	public $import_key;
	public $mode_contact;
	public $fk_product;
	public $fk_soc;
	public $fk_contact;
	public $fk_user_creat;
	public $element_type;
	public $fk_element_id;
	public $object;
	public $comment;
	public $type_event;
	public $fk_event;
	// END MODULEBUILDER PROPERTIES
    // If this object has a subtable with lines

    /**
     * @var int    Name of subtable line
     */
    //public $table_element_line = 'contacttrackingdet';
    /**
     * @var int    Field with ID of parent key if this field has a parent
     */
    //public $fk_element = 'fk_contacttracking';
    /**
     * @var int    Name of subtable class that manage subtable lines
     */
    //public $class_element_line = 'Contacttrackingline';
    /**
     * @var array  Array of child tables (child tables to delete before deleting a record)
     */
    //protected $childtables=array('contacttrackingdet');
    /**
     * @var ContacttrackingLine[]     Array of subtable lines
     */
    //public $lines = array();

    /**
     * Constructor
     *
     * @param int $idshow
     * @param DoliDb $db Database handler
     */
    public function __construct(DoliDB $db, $idshow = 0)
    {
        global $conf;

        $this->db = $db;

        if (empty($conf->global->MAIN_SHOW_TECHNICAL_ID) || $idshow)
            $this->fields['rowid']['visible'] = 1;
        if (empty($conf->multicompany->enabled))
            $this->fields['entity']['enabled'] = 0;
    }

    /**
     * Create object into database
     *
     * @param  User $user      User that creates
     * @param  bool $notrigger false=launch triggers after, true=disable triggers
     * @return int             <0 if KO, Id of created object if OK
     */
    public function create(User $user, $notrigger = false)
    {
        if (method_exists($this, 'createCommon')) {
            return $this->createCommon($user, $notrigger);
        } else {
            global $conf;
            $error = 0;

            $now = dol_now();

            $queryarray = array();
            foreach ($this->fields as $field => $info) { // Loop on definition of fields
                // Depending on field type ('datetime', ...)
                if ($info['type'] == 'datetime') {
                    if (empty($this->{$field})) {
                        $queryarray[$field] = null;
                    } else {
                        $queryarray[$field] = $this->db->idate($this->{$field});
                    }
                } else if ($info['type'] == 'integer') {
                    if ($field == 'entity' && is_null($this->{$field}))
                        $queryarray[$field] = $conf->entity;
                    else {
                        $queryarray[$field] = (int) price2num($this->{$field});
                        if (empty($queryarray[$field]))
                            $queryarray[$field] = 0;  // May be rest to null later if property 'nullifempty' is on for this field.
                    }
                }
                else {
                    $queryarray[$field] = $this->{$field};
                }

                if ($info['type'] == 'timestamp' && empty($queryarray[$field]))
                    unset($queryarray[$field]);
                if (!empty($info['nullifempty']) && empty($queryarray[$field]))
                    $queryarray[$field] = null;
            }

            $fieldvalues = $queryarray;
            if (array_key_exists('date_creation', $fieldvalues) && empty($fieldvalues['date_creation']))
                $fieldvalues['date_creation'] = $this->db->idate($now);
            unset($fieldvalues['rowid']); // We suppose the field rowid is reserved field for autoincrement field.

            $keys = array();
            $values = array();
            foreach ($fieldvalues as $k => $v) {
                $keys[] = $k;

                if (is_null($v))
                    $values[] = 'NULL';
                else if (preg_match('/^(int|double|real)/i', $this->fields[$k]['type']))
                    $values[] = $this->db->escape("$v");
                else
                    $values[] = "'" . $this->db->escape($v) . "'";
            }

            $this->db->begin();

            if (!$error) {
                $sql = 'INSERT INTO ' . MAIN_DB_PREFIX . $this->table_element;
                $sql .= ' (' . implode(", ", $keys) . ')';
                $sql .= ' VALUES (' . implode(", ", $values) . ')';

                $res = $this->db->query($sql);
                if ($res === false) {
                    $error++;
                    $this->errors[] = $this->db->lasterror();
                }
            }

            if (!$error && !$notrigger) {
                $this->id = $this->db->last_insert_id(MAIN_DB_PREFIX . $this->table_element);

                if (!$notrigger) {
                    // Call triggers
                    $result = $this->call_trigger(strtoupper(get_class($this)) . '_CREATE', $user);
                    if ($result < 0) {
                        $error++;
                    }

                    // End call triggers
                }
            }

            // Commit or rollback
            if ($error) {
                $this->db->rollback();
                return -1;
            } else {
                $this->db->commit();
                return $this->id;
            }
        }
    }

    /**
     * Clone and object into another one
     *
     * @param  	User 	$user      	User that creates
     * @param  	int 	$fromid     Id of object to clone
     * @return 	mixed 				New object created, <0 if KO
     */
    public function createFromClone(User $user, $fromid)
    {
        global $hookmanager, $langs;
        $error = 0;

        dol_syslog(__METHOD__, LOG_DEBUG);

        $object = new self($this->db);

        $this->db->begin();

        // Load source object
        $object->fetchCommon($fromid);
        // Reset some properties
        unset($object->id);
        unset($object->fk_user_creat);
        unset($object->import_key);

        // Clear fields
        $object->ref = "copy_of_" . $object->ref;
        $object->title = $langs->trans("CopyOf") . " " . $object->title;
        // ...
        // Create clone
        $object->context['createfromclone'] = 'createfromclone';
        $result = $object->createCommon($user);
        if ($result < 0) {
            $error++;
            $this->error = $object->error;
            $this->errors = $object->errors;
        }

        // End
        if (!$error) {
            $this->db->commit();
            return $object;
        } else {
            $this->db->rollback();
            return -1;
        }
    }

    /**
     * Load object in memory from the database
     *
     * @param int    $id   Id object
     * @param string $ref  Ref
     * @return int         <0 if KO, 0 if not found, >0 if OK
     */
    public function fetch($id, $ref = null)
    {
        $result = $this->fetchCommon($id, $ref);
        if ($result > 0 && !empty($this->table_element_line))
            $this->fetchLines();
        return $result;
    }

    /**
     * Load object lines in memory from the database
     *
     * @return int         <0 if KO, 0 if not found, >0 if OK
     */
    /* public function fetchLines()
      {
      $this->lines=array();

      // Load lines with object ContacttrackingLine

      return count($this->lines)?1:0;
      } */

    /**
     * Update object into database
     *
     * @param  User $user      User that modifies
     * @param  bool $notrigger false=launch triggers after, true=disable triggers
     * @return int             <0 if KO, >0 if OK
     */
    public function update(User $user, $notrigger = false)
    {
        return $this->updateCommon($user, $notrigger);
    }

    /**
     * Delete object in database
     *
     * @param User $user       User that deletes
     * @param bool $notrigger  false=launch triggers after, true=disable triggers
     * @return int             <0 if KO, >0 if OK
     */
    public function delete(User $user, $notrigger = false)
    {
        return $this->deleteCommon($user, $notrigger);
    }

    /**
     *  Return a link to the object card (with optionaly the picto)
     *
     * 	@param	int		$withpicto					Include picto in link (0=No picto, 1=Include picto into link, 2=Only picto)
     * 	@param	string	$option						On what the link point to ('nolink', ...)
     *  @param	int  	$notooltip					1=Disable tooltip
     *  @param  string  $morecss            		Add more css on link
     *  @param  int     $save_lastsearch_value    	-1=Auto, 0=No save of lastsearch_values when clicking, 1=Save lastsearch_values whenclicking
     * 	@return	string								String with URL
     */
    function getNomUrl($withpicto = 0, $option = '', $notooltip = 0, $morecss = '', $save_lastsearch_value = -1)
    {
        global $db, $conf, $langs;
        global $dolibarr_main_authentication, $dolibarr_main_demo;
        global $menumanager;

        if (!empty($conf->dol_no_mouse_hover))
            $notooltip = 1;   // Force disable tooltips

        $result = '';
        $companylink = '';

        $label = '<u>' . $langs->trans("Contacttracking") . '</u>';
        $label .= '<br>';
        $label .= '<b>' . $langs->trans('Ref') . ':</b> ' . $this->id;

        $url = dol_buildpath('/monanalysevendeur/autodiag_card.php', 1) . '?id=' . $this->id;

        if ($option != 'nolink') {
            // Add param to save lastsearch_values or not
            $add_save_lastsearch_values = ($save_lastsearch_value == 1 ? 1 : 0);
            if ($save_lastsearch_value == -1 && preg_match('/list\.php/', $_SERVER["PHP_SELF"]))
                $add_save_lastsearch_values = 1;
            if ($add_save_lastsearch_values)
                $url .= '&save_lastsearch_values=1';
        }

        $linkclose = '';
        if (empty($notooltip)) {
            if (!empty($conf->global->MAIN_OPTIMIZEFORTEXTBROWSER)) {
                $label = $langs->trans("ShowContacttracking");
                $linkclose .= ' alt="' . dol_escape_htmltag($label, 1) . '"';
            }

            $linkclose .= ' title="' . dol_escape_htmltag($label, 1) . '"';
            $linkclose .= ' class="classfortooltip' . ($morecss ? ' ' . $morecss : '') . '"';
        } else
            $linkclose = ($morecss ? ' class="' . $morecss . '"' : '');

        $linkstart = '<a href="' . $url . '"';
        $linkstart .= $linkclose . '>';
        $linkend = '</a>';

        $result .= $linkstart;
        if ($withpicto)
            $result .= img_object(($notooltip ? '' : $label), ($this->picto ? $this->picto : 'generic'), ($notooltip ? (($withpicto != 2) ? 'class="paddingright"' : '') : 'class="' . (($withpicto != 2) ? 'paddingright ' : '') . 'classfortooltip"'), 0, 0, $notooltip ? 0 : 1);
        if ($withpicto != 2)
            $result .= "Contact " . $this->id;
        $result .= $linkend;
        //if ($withpicto != 2) $result.=(($addlabel && $this->label) ? $sep . dol_trunc($this->label, ($addlabel > 1 ? $addlabel : 0)) : '');

        return $result;
    }

    /**
     *  Retourne le libelle du status d'un user (actif, inactif)
     *
     *  @param	int		$mode          0=libelle long, 1=libelle court, 2=Picto + Libelle court, 3=Picto, 4=Picto + Libelle long, 5=Libelle court + Picto
     *  @return	string 			       Label of status
     */
    function getLibStatut($mode = 0)
    {
        return $this->LibStatut($this->status, $mode);
    }

    /**
     *  Return the status
     *
     *  @param	int		$status        	Id status
     *  @param  int		$mode          	0=long label, 1=short label, 2=Picto + short label, 3=Picto, 4=Picto + long label, 5=Short label + Picto, 6=Long label + Picto
     *  @return string 			       	Label of status
     */
    static function LibStatut($status, $mode = 0)
    {
        global $langs;

        if ($mode == 0) {
            $prefix = '';
            if ($status == 1)
                return $langs->trans('Enabled');
            if ($status == 0)
                return $langs->trans('Disabled');
        }

        if ($mode == 1) {
            if ($status == 1)
                return $langs->trans('Enabled');
            if ($status == 0)
                return $langs->trans('Disabled');
        }

        if ($mode == 2) {
            if ($status == 1)
                return img_picto($langs->trans('Enabled'), 'statut4') . ' ' . $langs->trans('Enabled');
            if ($status == 0)
                return img_picto($langs->trans('Disabled'), 'statut5') . ' ' . $langs->trans('Disabled');
        }

        if ($mode == 3) {
            if ($status == 1)
                return img_picto($langs->trans('Enabled'), 'statut4');
            if ($status == 0)
                return img_picto($langs->trans('Disabled'), 'statut5');
        }

        if ($mode == 4) {
            if ($status == 1)
                return img_picto($langs->trans('Enabled'), 'statut4') . ' ' . $langs->trans('Enabled');
            if ($status == 0)
                return img_picto($langs->trans('Disabled'), 'statut5') . ' ' . $langs->trans('Disabled');
        }

        if ($mode == 5) {
            if ($status == 1)
                return $langs->trans('Enabled') . ' ' . img_picto($langs->trans('Enabled'), 'statut4');
            if ($status == 0)
                return $langs->trans('Disabled') . ' ' . img_picto($langs->trans('Disabled'), 'statut5');
        }

        if ($mode == 6) {
            if ($status == 1)
                return $langs->trans('Enabled') . ' ' . img_picto($langs->trans('Enabled'), 'statut4');
            if ($status == 0)
                return $langs->trans('Disabled') . ' ' . img_picto($langs->trans('Disabled'), 'statut5');
        }
    }

    /**
     * 	Charge les informations d'ordre info dans l'objet commande
     *
     * 	@param  int		$id       Id of order
     * 	@return	void
     */
    function info($id)
    {
        $sql = 'SELECT rowid, date_creation as datec, tms as datem,';
        $sql .= ' fk_user_creat, fk_user_modif';
        $sql .= ' FROM ' . MAIN_DB_PREFIX . $this->table_element . ' as t';
        $sql .= ' WHERE t.rowid = ' . $id;
        $result = $this->db->query($sql);
        if ($result) {
            if ($this->db->num_rows($result)) {
                $obj = $this->db->fetch_object($result);
                $this->id = $obj->rowid;
                if ($obj->fk_user_author) {
                    $cuser = new User($this->db);
                    $cuser->fetch($obj->fk_user_author);
                    $this->user_creation = $cuser;
                }

                if ($obj->fk_user_valid) {
                    $vuser = new User($this->db);
                    $vuser->fetch($obj->fk_user_valid);
                    $this->user_validation = $vuser;
                }

                if ($obj->fk_user_cloture) {
                    $cluser = new User($this->db);
                    $cluser->fetch($obj->fk_user_cloture);
                    $this->user_cloture = $cluser;
                }

                $this->date_creation = $this->db->jdate($obj->datec);
                $this->date_modification = $this->db->jdate($obj->datem);
                $this->date_validation = $this->db->jdate($obj->datev);
            }

            $this->db->free($result);
        } else {
            dol_print_error($this->db);
        }
    }

    /**
     *    Return list of inovice (eventually filtered on user) into an array
     *
     *    @param	int		$shortlist			0=Return array[id]=ref, 1=Return array[](id=>id,ref=>ref,name=>name)
     *    @param	int		$draft				0=not draft, 1=draft
     *    @param	int		$notcurrentuser		0=all user, 1=not current user
     *    @param    int		$socid				Id third pary
     *    @param    int		$limit				For pagination
     *    @param    int		$offset				For pagination
     *    @param    string	$sortfield			Sort criteria
     *    @param    string	$sortorder			Sort order
     *    @return	int		       				-1 if KO, array with result if OK
     */
    function liste_array_facture($shortlist = 0, $draft = 0, $notcurrentuser = 0, $socid = 0, $limit = 0, $offset = 0, $sortfield = 'p.datef', $sortorder = 'DESC')
    {
        global $conf, $user;

        $ga = array();

        $sql = "SELECT s.rowid, s.nom as name, s.client,";
        if ((int) DOL_VERSION >= 10) {
            $sql .= " p.rowid as factid, p.fk_statut, p.total, p.ref, p.remise, ";
        } else {
            $sql .= " p.rowid as factid, p.fk_statut, p.total, p.facnumber, p.remise, ";
        }
        $sql .= " p.datef as dp, p.date_lim_reglement as datelimite";
        if (!$user->rights->societe->client->voir && !$socid)
            $sql .= ", sc.fk_soc, sc.fk_user";
        $sql .= " FROM " . MAIN_DB_PREFIX . "societe as s, " . MAIN_DB_PREFIX . "facture as p, " . MAIN_DB_PREFIX . "c_propalst as c";
        if (!$user->rights->societe->client->voir && !$socid)
            $sql .= ", " . MAIN_DB_PREFIX . "societe_commerciaux as sc";
        $sql .= " WHERE p.entity = " . $conf->entity;
        $sql .= " AND p.fk_soc = s.rowid";
        $sql .= " AND p.fk_statut = c.id";
        if (!$user->rights->societe->client->voir && !$socid) { //restriction
            $sql .= " AND s.rowid = sc.fk_soc AND sc.fk_user = " . $user->id;
        }

        if ($socid)
            $sql .= " AND s.rowid = " . $socid;
        if ($draft)
            $sql .= " AND p.fk_statut = 1 ";
        if ($notcurrentuser > 0)
            $sql .= " AND p.fk_user_author <> " . $user->id;
        $sql .= $this->db->order($sortfield, $sortorder);
        $sql .= $this->db->plimit($limit, $offset);

        $result = $this->db->query($sql);
        if ($result) {
            $num = $this->db->num_rows($result);
            if ($num) {
                $i = 0;
                while ($i < $num) {
                    $obj = $this->db->fetch_object($result);

                    if ((int) DOL_VERSION>=10) {
                        if ($shortlist == 1) {
                            $ga[$obj->factid] = $obj->ref;
                        } else if ($shortlist == 2) {
                            $ga[$obj->factid] = $obj->ref . ' (' . $obj->name . ')';
                        } else {
                            $ga[$i]['id'] = $obj->factid;
                            $ga[$i]['ref'] = $obj->ref;
                            $ga[$i]['name'] = $obj->name;
                        }
                    } else {
                        if ($shortlist == 1) {
                            $ga[$obj->factid] = $obj->facnumber;
                        } else if ($shortlist == 2) {
                            $ga[$obj->factid] = $obj->facnumber . ' (' . $obj->name . ')';
                        } else {
                            $ga[$i]['id'] = $obj->factid;
                            $ga[$i]['ref'] = $obj->facnumber;
                            $ga[$i]['name'] = $obj->name;
                        }
                    }
                    $i++;
                }
            }

            return $ga;
        } else {
            dol_print_error($this->db);
            return -1;
        }
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
     * Action executed by scheduler
     * CAN BE A CRON TASK
     *
     * @return	int			0 if OK, <>0 if KO (this function is used also by cron so only 0 is OK)
     */
    public function doScheduledJob()
    {
        global $conf, $langs;

        $this->output = '';
        $this->error = '';

        dol_syslog(__METHOD__, LOG_DEBUG);

        // ...

        return 0;
    }
}

/**
 * Class ContacttrackingLine. You can also remove this and generate a CRUD class for lines objects.
 */
/*
class ContacttrackingLine
{
	// @var int ID
	public $id;
	// @var mixed Sample line property 1
	public $prop1;
	// @var mixed Sample line property 2
	public $prop2;
}
*/
