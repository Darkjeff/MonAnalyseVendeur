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

        $this->db = $db;
    }

    public function getData($users, $user_tags, $period_type, $from_date, $to_date) {

    	if ($period_type=='day') {
			$result = $this->getNb($users, $user_tags, '%d/%m/%Y', $from_date, $to_date);
		}
		if ($period_type=='week') {
			$result = $this->getNb($users, $user_tags, '%u', $from_date, $to_date);
		}
		if ($period_type=='week') {
			$result = $this->getNb($users, $user_tags, '%u', $from_date, $to_date);
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
		} elseif($period_type=='%u') {
			$nbtime_diff=$nbday_between/7;
			//TODO format week
			$time_array=array();
			for ($i = 0; $i <= $nbtime_diff; $i++)
			{
				$time_array[$i]=dol_time_plus_duree($from_date,1,'w');
			}
		} elseif($period_type=='%u') {
			$nbtime_diff=$nbday_between/30;
			$time_array=array();
			for ($i = 0; $i <= $nbtime_diff; $i++)
			{
				$time_array[$i]=dol_time_plus_duree($from_date,1,'m');
			}
		}

		$sql = "SELECT date_format(t.date_creation,'".$period_type."') as dm, t.fk_user_creat, SUM(t.nb_traitement) as nb, SUM(t.nb_box) as nbbox";
		$sql .= " FROM ".MAIN_DB_PREFIX.$object_static->table_element . ' as t';
		if (!empty($user_tags)) {
			$sql .= 'INNER JOIN llx_categorie_user as tagu ON tagu.fk_user=t.fk_user_creat';
		}
		$sql_where[] = " t.date_creation BETWEEN '".$this->db->idate($from_date)."' AND '".$this->db->idate($to_date)."'";
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
			return -1;
			$this->error=$this->db->lasterror;
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
