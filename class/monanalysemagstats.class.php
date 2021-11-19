<?php
/* Copyright (C) 2003      Rodolphe Quiedeville <rodolphe@quiedeville.org>
 * Copyright (c) 2005-2013 Laurent Destailleur  <eldy@users.sourceforge.net>
 * Copyright (C) 2005-2012 Regis Houssin        <regis.houssin@inodbox.com>
 * Copyright (C) 2012      Marcos García        <marcosgdf@gmail.com>
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


include_once DOL_DOCUMENT_ROOT.'/core/class/stats.class.php';
include_once DOL_DOCUMENT_ROOT.'/core/lib/date.lib.php';
dol_include_once('/monanalysevendeur/class/rapportjournalier.class.php');

/**
 *    Class to manage intervention statistics
 */
class MonAnayseVendeurStats
{
    /**
     * @var string Name of table without prefix where object is stored
     */
    public $table_element;

    public $data_traitement;
    public $data_transfo;
    public $data_row;
    public $data_legend;

    /**
     * Constructor
     *
     * @param 	DoliDB	$db		   Database handler
     */
    public function __construct($db)
    {
        global $user, $conf;

        $this->data_traitement=array();
		$this->data_transfo=array();
		$this->data_row=array();
		$this->data_legend=array();

        $this->db = $db;
    }

    public function getData($users, $user_tags, $period_type, $from_date, $to_date) {

    	if ($period_type=='day') {
			$result = $this->getNb($users, $user_tags, '%d/%m/%Y', $from_date, $to_date);
		}
		if ($period_type=='week') {
			$result = $this->getNb($users, $user_tags, '%U', $from_date, $to_date);
		}
		if ($period_type=='month') {
			$result = $this->getNb($users, $user_tags, '%m', $from_date, $to_date);
		}
		return $result;
	}

	public function getDataStatVendeur($from_date, $to_date) {
    	$data = array();

    	$sql = 'SELECT catu.fk_categorie as cate, cat.label as mag, SUM(IFNULL(rpj.nb_traitement,0)) as nbt, SUM(IFNULL(rpj.nb_box,0)) as nbb, ';
		$sql .= ' SUM(IFNULL(rpj.nb_abohv,0)) as nba, SUM(IFNULL(rpj.nb_service,0)) as nbs, count(rpj.rowid) as vendeur ';
		$sql .= ' FROM '.MAIN_DB_PREFIX.'monanalysevendeur_rapportjournalier as rpj';
		$sql .= ' INNER JOIN ' . MAIN_DB_PREFIX . 'categorie_user as catu ON rpj.fk_user_creat=catu.fk_user ';
		$sql .= ' INNER JOIN ' . MAIN_DB_PREFIX . 'categorie as cat ON catu.fk_categorie=cat.rowid ';
		$sql .= " WHERE rpj.date BETWEEN '".$this->db->idate($from_date)."' AND '".$this->db->idate($to_date)."' ";
		$sql .= " GROUP BY catu.fk_categorie";
		//$sql .= $this->db->order('dm,t.fk_user_creat', 'DESC');

		$resql = $this->db->query($sql);
		if ($resql)
		{
			while ($obj = $this->db->fetch_object($resql))
			{
				$data[$obj->cate] = array(
					'mag'=>$obj->mag,
					'vendeur'=>$obj->vendeur,
					'nbt'=>$obj->nbt,
					'nbb'=>$obj->nbb,
					'nba'=>$obj->nba,
					'nbs'=>$obj->nbs,
					'txtb'=>round(($obj->nbb / (empty($obj->nbt)?1:$obj->nbt)) * 100),
					'txta'=>round(($obj->nba / (empty($obj->nbt)?1:$obj->nbt)) * 100),
					'txts'=>round(($obj->nbs / (empty($obj->nbt)?1:$obj->nbt)) * 100),
					'relance'=>0,
					'picking'=>0,
					'potbox'=>0,
					'box'=>0,
					'txbb'=>0,
					'dilax'=>0,
					'ecoute'=>0,
					'autodiag'=>0,
					'sketch'=>0
				);
			}
		} else {
			$this->error=$this->db->lasterror;
			return -1;
		}

		foreach($data as $key=>$detail) {
			//Relance
			$sql = 'SELECT count(ct.rowid) as nb FROM ' . MAIN_DB_PREFIX . 'contacttracking as ct';
			$sql .= ' INNER JOIN ' . MAIN_DB_PREFIX . 'actioncomm as ac ON (ac.id = ct.fk_event) ';
			$sql .= ' INNER JOIN ' . MAIN_DB_PREFIX . 'categorie_user as catu ON ct.fk_user_creat=catu.fk_user ';
			//$sql .= ' WHERE ct.fk_user_creat='.$key;
			$sql .= " WHERE ac.datec BETWEEN '".$this->db->idate($from_date)."' AND '".$this->db->idate($to_date)."'";
			$sql .= ' AND catu.fk_categorie='.$key;
			$resql = $this->db->query($sql);
			if ($resql)
			{
				while ($obj = $this->db->fetch_object($resql))
				{
					$data[$key]['relance'] = $obj->nb;
				}
			} else {
				$this->error=$this->db->lasterror;
				return -1;
			}
			
			//dilax
			$sql = 'SELECT SUM(IFNULL(dil.qty,0)) as nbd FROM ' . MAIN_DB_PREFIX . 'monanalysevendeur_dilax as dil';
			//$sql .= ' INNER JOIN ' . MAIN_DB_PREFIX . 'categorie_user as catu ON dil.fk_category_user=catu.fk_categorie ';
			//$sql .= ' WHERE ct.fk_user_creat='.$key;
			$sql .= " WHERE dil.date BETWEEN '".$this->db->idate($from_date)."' AND '".$this->db->idate($to_date)."'";
			$sql .= ' AND dil.fk_category_user='.$key;
			$sql .= " GROUP BY dil.fk_category_user";
			$resql = $this->db->query($sql);
			if ($resql)
			{
				while ($obj = $this->db->fetch_object($resql))
				{
					$data[$key]['dilax'] = $obj->nbd;
				}
			} else {
				$this->error=$this->db->lasterror;
				return -1;
			}
			

			//Picking potbox box
			$sql = 'SELECT count(fi.rowid) as nb';
			$sql .= ',IFNULL(SUM(CASE WHEN ext.potentielbox LIKE \'%1%\' THEN 1 ELSE 0 END),0) as potbox';
			$sql .= ',IFNULL(SUM(CASE WHEN ext.boxvalidee LIKE \'%1%\' THEN 1 ELSE 0 END),0) as box';
			$sql .= ' FROM ' . MAIN_DB_PREFIX . 'fichinter as fi';
			$sql .= ' INNER JOIN ' . MAIN_DB_PREFIX . 'fichinter_extrafields as ext ON (ext.fk_object = fi.rowid)';
			$sql .= ' INNER JOIN ' . MAIN_DB_PREFIX . 'categorie_user as catu ON ext.vendeur=catu.fk_user ';
			//$sql .= ' WHERE ext.vendeur='.$key;
			$sql .= "WHERE fi.dateo BETWEEN '".$this->db->idate($from_date)."' AND '".$this->db->idate($to_date)."'";
			$sql .= ' AND catu.fk_categorie='.$key;
			$resql = $this->db->query($sql);
			if ($resql)
			{
				while ($obj = $this->db->fetch_object($resql))
				{
					$data[$key]['potbox'] = $obj->potbox;
					$data[$key]['picking'] = $obj->nb;
					$data[$key]['box'] = $obj->box;
					$data[$key]['txbb']=round(($data[$key]['box'] / (empty($data[$key]['potbox'])?1:$data[$key]['potbox'])) * 100);
				}
			} else {
				$this->error=$this->db->lasterror;
				return -1;
			}

			//ecoute
			$sql = 'SELECT count(ec.rowid) as nb  FROM ' . MAIN_DB_PREFIX . 'monanalysevendeur_ecoute as ec';
			$sql .= ' INNER JOIN ' . MAIN_DB_PREFIX . 'categorie_user as catu ON ec.salesman=catu.fk_user ';
			$sql .= " WHERE ec.date_creation BETWEEN '".$this->db->idate($from_date)."' AND '".$this->db->idate($to_date)."'";
			$sql .= ' AND catu.fk_categorie='.$key;
			$resql = $this->db->query($sql);
			if ($resql)
			{
				while ($obj = $this->db->fetch_object($resql))
				{
					$data[$key]['ecoute'] = $obj->nb;
				}
			} else {
				$this->error=$this->db->lasterror;
				return -1;
			}
			//AutoDiag
			$sql = 'SELECT count(ct.rowid) as nb FROM ' . MAIN_DB_PREFIX . 'contacttracking as ct';
			$sql .= ' INNER JOIN ' . MAIN_DB_PREFIX . 'categorie_user as catu ON ct.fk_user_creat=catu.fk_user ';
			$sql .= " WHERE ac.datec BETWEEN '".$this->db->idate($from_date)."' AND '".$this->db->idate($to_date)."'";
			$sql .= ' AND ct.fk_event is null';
			$sql .= ' AND catu.fk_categorie='.$key;
			$resql = $this->db->query($sql);
			if ($resql)
			{
				while ($obj = $this->db->fetch_object($resql))
				{
					$data[$key]['autodiag'] = $obj->nb;
				}
			} else {
				$this->error=$this->db->lasterror;
				return -1;
			}
			
			//Sketches
			$sql = 'SELECT count(ske.rowid) as nbd FROM ' . MAIN_DB_PREFIX . 'monanalysevendeur_sketchsvendeur as ske';
			$sql .= ' INNER JOIN ' . MAIN_DB_PREFIX . 'categorie_user as catu ON ske.fk_user=catu.fk_user ';
			$sql .= " WHERE ske.date BETWEEN '".$this->db->idate($from_date)."' AND '".$this->db->idate($to_date)."'";
			$sql .= ' AND catu.fk_category_user='.$key;
			$sql .= " GROUP BY catu.fk_category_user";
			$resql = $this->db->query($sql);
			if ($resql)
			{
				while ($obj = $this->db->fetch_object($resql))
				{
					$data[$key]['sketch'] = $obj->nbd;
				}
			} else {
				$this->error=$this->db->lasterror;
				return -1;
			}
			
			
			
}
		return $data;
		
	}

	/**
	 * Return intervention number by month for a year
	 *
	 * @param	int		$year		Year to scan
	 *	@param	int		$format		0=Label of abscissa is a translated text, 1=Label of abscissa is month number, 2=Label of abscissa is first letter of month
	 * @return	array				Array with number by month
	 */
	public function getNb($users, $user_tags, $period_type, $from_date, $to_date)
	{
		global $user;
		$object_static=new Rapportjournalier($this->db);
		$sql_where=array();
		$nbday_between=num_between_day($from_date, $to_date, 1);


		if ($period_type=='%d/%m/%Y') {
			$nbtime_diff=$nbday_between;

			$time_array=array();
			for ($i = 0; $i <= $nbtime_diff; $i++)
			{
				$time_array[$i]=dol_print_date(dol_time_plus_duree($from_date,$i,'d'),'%d/%m/%Y');
			}
		} elseif($period_type=='%U') {
			$nbtime_diff=$nbday_between/7;
			//TODO format week
			$time_array=array();
			for ($i = 0; $i <= $nbtime_diff; $i++)
			{
				$time_array[$i]=dol_print_date(dol_time_plus_duree($from_date,$i,'w'),'%U');
			}
		} elseif($period_type=='%m') {
			$nbtime_diff=$nbday_between/30;
			$time_array=array();
			for ($i = 0; $i <= $nbtime_diff; $i++)
			{
				$time_array[$i]=dol_print_date(dol_time_plus_duree($from_date,$i,'m'),'%m');
			}
		}

		$sql = "SELECT date_format(t.date,'".$period_type."') as dm, t.fk_user_creat, SUM(t.nb_traitement) as nb, SUM(t.nb_box) as nbbox, SUM(t.nb_abohv) as nbabohv, SUM(t.nb_service) as nbservice";
		$sql .= " FROM ".MAIN_DB_PREFIX.$object_static->table_element . ' as t';
		if (!empty($user_tags)) {
			$sql .= ' INNER JOIN '.MAIN_DB_PREFIX.'categorie_user as tagu ON tagu.fk_user=t.fk_user_creat';
		}
		$sql_where[] = " t.date BETWEEN '".$this->db->idate($from_date)."' AND '".$this->db->idate($to_date)."'";
		if (!empty($users)) {
			$sql_where[] = ' t.fk_user_creat IN (' . implode(',', $users) . ')';
		}
		if (!empty($user_tags)) {
			$sql_where[] = ' tagu.fk_categorie IN (' . implode(',', $user_tags) . ')';

		}
		if (!empty($sql_where)) {
			$sql .= ' WHERE '.implode( ' AND ',$sql_where);
		}

		$sql .= " GROUP BY dm, t.fk_user_creat";
		$sql .= $this->db->order('dm,t.fk_user_creat', 'DESC');


		$result=array();
		$resql = $this->db->query($sql);

		if ($resql)
		{
			$num = $this->db->num_rows($resql);
			$i=0;
			while ($row = $this->db->fetch_row($resql))
			{
				$result[$i] = $row;
				$i++;
			}
		} else {
			$this->error=$this->db->lasterror;
			return -1;
		}

		$user_data=array();
		$i=1;
		foreach($result as $time=>$data) {
			if (!in_array($data[1],$user_data)) {
				$user_data[$i] = $data[1];
				$i++;
			}
		}


		$data_r=array();
		foreach($time_array as $i=>$time) {
			$t[0]=$time;
			$u=1;
			foreach($user_data as $userid) {
				$t[$u]=0;
				$u++;
			}
			$data_r[]=$t;
		}

		$data_tx=$data_r;
		foreach($data_r as $i=>$data_st) {
			foreach($result as $data_src) {
				if ($data_src[0]==$data_st[0]) {
					$data_r[$i][array_search($data_src[1], $user_data)]=$data_src[2];
					if ($data_src[2]!=0) {
						$data_tx[$i][array_search($data_src[1], $user_data)] = round(($data_src[3] / $data_src[2]) * 100);
					}
				}
			}
		}

		$this->data_traitement = $data_r;
		$this->data_transfo = $data_tx;
		$this->data_row = $result;
		$this->data_legend = $user_data;
		return 1;
	}
}
