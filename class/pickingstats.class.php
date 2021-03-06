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
require_once DOL_DOCUMENT_ROOT.'/fichinter/class/fichinter.class.php';

/**
 *    Class to manage intervention statistics
 */
class PickingStats
{
    /**
     * @var string Name of table without prefix where object is stored
     */
    public $table_element;

    public $data_potentiel;
    public $data_pickcount;
    public $data_valide;
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

        $this->data_potentiel=array();
		$this->data_valide=array();
		$this->data_pickcount=array();
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
		$object_static=new Fichinter($this->db);
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

			///// todo avoir compte des oui ou non et pas la somme car valeur 1 ou 2 dans colonne

		$sql = "SELECT date_format(s.tms,'".$period_type."') as dm, t.vendeur, count(t.fk_object) as cntinter, count(case when t.potentielbox=1 then t.fk_object end) as potentiel, count(case when t.boxvalidee=1 then t.fk_object end) as valide";
		$sql .= ' FROM '.MAIN_DB_PREFIX.'fichinter as s INNER JOIN '.MAIN_DB_PREFIX.'fichinter_extrafields as t ON s.rowid=t.fk_object';
		if (!empty($user_tags)) {
			$sql .= ' INNER JOIN '.MAIN_DB_PREFIX.'categorie_user as tagu ON tagu.fk_user=t.vendeur';
		}
		$sql_where[] = " t.tms BETWEEN '".$this->db->idate($from_date)."' AND '".$this->db->idate($to_date)."'";
		if (!empty($users)) {
			$sql_where[] = ' t.vendeur IN (' . implode(',', $users) . ')';
		}
		if (!empty($user_tags)) {
			$sql_where[] = ' tagu.fk_categorie IN (' . implode(',', $user_tags) . ')';

		}
		if (!empty($sql_where)) {
			$sql .= ' WHERE '.implode( ' AND ',$sql_where);
		}

		$sql .= " GROUP BY dm, t.vendeur";
		$sql .= $this->db->order('dm,t.vendeur', 'DESC');

		//print $sql;
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

		$data_pick=$data_r;
		$data_valid=$data_r;
		$data_potentiel=$data_r;
		foreach($data_r as $i=>$data_st) {
			foreach($result as $data_src) {
				if ($data_src[0]==$data_st[0]) {
					$data_r[$i][array_search($data_src[1], $user_data)]=$data_src[2];
					if ($data_src[2]!=0) {
						$data_pick[$i][array_search($data_src[1], $user_data)] = $data_src[2];
					}
					if ($data_src[3]!=0) {
						$data_potentiel[$i][array_search($data_src[1], $user_data)] = $data_src[3];
					}
					if ($data_src[4]!=0) {
						$data_valid[$i][array_search($data_src[1], $user_data)] = $data_src[4];
					}
				}
			}
		}

		$this->data_pickcount = $data_pick;
		$this->data_valide = $data_valid;
		$this->data_potentiel = $data_potentiel;
		$this->data_row = $result;
		$this->data_legend = $user_data;
		return 1;
	}
}
