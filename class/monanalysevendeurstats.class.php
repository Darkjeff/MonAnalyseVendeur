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

    public $data;

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

		$sql = "SELECT date_format(t.date_creation,'".$period_type."') as dm, t.fk_user_creat, SUM(t.nb_traitement) as nb";
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
		foreach($result as $time=>$data) {
			if (!in_array($data[1],$user_data)) {
				$user_data[$data[1]] = $data[1];
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
var_dump($result,$data_r);
		$dt = key($result[0][0]);
		$index_t=0;
		$index_stat_u=1;
		foreach($result as $time=>$data) {
			if ($dt!=$time) {
				$index_t++;
				$index_stat_u=1;
			}
			while ($dt==$time) {
				$data_r[$index_t][0] = $time;
				$data_r[$index_t][$index_stat_u] = $data[2];
				$dt = $time;//key($result[$index_t+1]);
				$index_stat_u++;
			}
		}

		var_dump($data_r);
		exit;
/*
		for ($i = 1; $i < $nbtime_diff; $i++)
		{
			$res[$i] = (isset($result[$i]) ? $result[$i] : 0);
		}

		$data = array();

		for ($i = 1; $i < $nbtime_diff; $i++)
		{
			//$month = 'unknown';
			//if ($format == 0) $month = $langs->transnoentitiesnoconv('MonthShort'.sprintf("%02d", $i));
			//elseif ($format == 1) $month = $i;
			//elseif ($format == 2) $month = $langs->transnoentitiesnoconv('MonthVeryShort'.sprintf("%02d", $i));
			//$month=dol_print_date(dol_mktime(12,0,0,$i,1,$year),($format?"%m":"%b"));
			//$month=dol_substr($month,0,3);
			$data[$i - 1] = array($i, $res[$i]);
		}
*/
		$this->data = $data_r;
		return 1;
	}

    /**
     * Return intervention number by month for a year
     *
     * @param	int		$year		Year to scan
     *	@param	int		$format		0=Label of abscissa is a translated text, 1=Label of abscissa is month number, 2=Label of abscissa is first letter of month
     * @return	array				Array with number by month
     */
    public function getNbByMonth($year, $format = 0)
    {
        global $user;

        $sql = "SELECT date_format(c.date_valid,'%m') as dm, COUNT(*) as nb";
        $sql .= " FROM ".$this->from;
        if (!$user->rights->societe->client->voir && !$this->socid) $sql .= ", ".MAIN_DB_PREFIX."societe_commerciaux as sc";
        $sql .= " WHERE c.date_valid BETWEEN '".$this->db->idate(dol_get_first_day($year))."' AND '".$this->db->idate(dol_get_last_day($year))."'";
        $sql .= " AND ".$this->where;
        $sql .= " GROUP BY dm";
        $sql .= $this->db->order('dm', 'DESC');

        $res = $this->_getNbByMonth($year, $sql, $format);
        return $res;
    }

    /**
     * Return interventions number per year
     *
     * @return	array	Array with number by year
     *
     */
    public function getNbByYear()
    {
        global $user;

        $sql = "SELECT date_format(c.date_valid,'%Y') as dm, COUNT(*) as nb, 0";
        $sql .= " FROM ".$this->from;
        if (!$user->rights->societe->client->voir && !$this->socid) $sql .= ", ".MAIN_DB_PREFIX."societe_commerciaux as sc";
        $sql .= " WHERE ".$this->where;
        $sql .= " GROUP BY dm";
        $sql .= $this->db->order('dm', 'DESC');

        return $this->_getNbByYear($sql);
    }

    /**
     * Return the intervention amount by month for a year
     *
     * @param	int		$year		Year to scan
     *	@param	int		$format		0=Label of abscissa is a translated text, 1=Label of abscissa is month number, 2=Label of abscissa is first letter of month
     * @return	array				Array with amount by month
     */
    public function getAmountByMonth($year, $format = 0)
    {
        global $user;

        $sql = "SELECT date_format(c.date_valid,'%m') as dm, 0";
        $sql .= " FROM ".$this->from;
        if (!$user->rights->societe->client->voir && !$this->socid) $sql .= ", ".MAIN_DB_PREFIX."societe_commerciaux as sc";
        $sql .= " WHERE c.date_valid BETWEEN '".$this->db->idate(dol_get_first_day($year))."' AND '".$this->db->idate(dol_get_last_day($year))."'";
        $sql .= " AND ".$this->where;
        $sql .= " GROUP BY dm";
        $sql .= $this->db->order('dm', 'DESC');

        $res = $this->_getAmountByMonth($year, $sql, $format);
        return $res;
    }

    /**
     * Return the intervention amount average by month for a year
     *
     * @param	int		$year	year for stats
     * @return	array			array with number by month
     */
    public function getAverageByMonth($year)
    {
        global $user;

        $sql = "SELECT date_format(c.date_valid,'%m') as dm, 0";
        $sql .= " FROM ".$this->from;
        if (!$user->rights->societe->client->voir && !$this->socid) $sql .= ", ".MAIN_DB_PREFIX."societe_commerciaux as sc";
        $sql .= " WHERE c.date_valid BETWEEN '".$this->db->idate(dol_get_first_day($year))."' AND '".$this->db->idate(dol_get_last_day($year))."'";
        $sql .= " AND ".$this->where;
        $sql .= " GROUP BY dm";
        $sql .= $this->db->order('dm', 'DESC');

        return $this->_getAverageByMonth($year, $sql);
    }

    /**
     *	Return nb, total and average
     *
     *	@return	array	Array of values
     */
    public function getAllByYear()
    {
        global $user;

        $sql = "SELECT date_format(c.date_valid,'%Y') as year, COUNT(*) as nb, 0 as total, 0 as avg";
        $sql .= " FROM ".$this->from;
        if (!$user->rights->societe->client->voir && !$this->socid) $sql .= ", ".MAIN_DB_PREFIX."societe_commerciaux as sc";
        $sql .= " WHERE ".$this->where;
        $sql .= " GROUP BY year";
        $sql .= $this->db->order('year', 'DESC');

        return $this->_getAllByYear($sql);
    }

    /**
     *  Return nb, amount of predefined product for year
     *
     *  @param	int		$year			Year to scan
     *  @param  int     $limit      	Limit
     *  @return	array					Array of values
     */
    public function getAllByProduct($year, $limit = 0)
    {
        global $user;

        $sql = "SELECT product.ref, COUNT(product.ref) as nb, 0 as total, 0 as avg";
        $sql .= " FROM ".$this->from.", ".$this->from_line.", ".MAIN_DB_PREFIX."product as product";
        //if (!$user->rights->societe->client->voir && !$user->socid) $sql.= ", ".MAIN_DB_PREFIX."societe_commerciaux as sc";
        $sql .= " WHERE ".$this->where;
        $sql .= " AND c.rowid = tl.fk_fichinter AND tl.fk_product = product.rowid";
        $sql .= " AND c.date_valid BETWEEN '".$this->db->idate(dol_get_first_day($year, 1, false))."' AND '".$this->db->idate(dol_get_last_day($year, 12, false))."'";
        $sql .= " GROUP BY product.ref";
        $sql .= $this->db->order('nb', 'DESC');
        //$sql.= $this->db->plimit(20);

        return $this->_getAllByProduct($sql, $limit);
    }
}
