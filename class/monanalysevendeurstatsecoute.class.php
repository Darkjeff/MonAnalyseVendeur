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

	public function getDataStatVendeur($from_date, $to_date, $categid=0) {
    	$data = array();

    	$sql = 'SELECT count(ec.rowid) as nb, usr.lastname as name, ec.salesman  ';
		$sql .= ',IFNULL(SUM(CASE WHEN ect.foyerequip LIKE \'%1%\' THEN 1 ELSE 0 END),0) as okfoyerequip';
		$sql .= ',IFNULL(SUM(CASE WHEN ect.foyerequip LIKE \'%2%\' THEN 1 ELSE 0 END),0) as kofoyerequip';
		$sql .= ',IFNULL(SUM(CASE WHEN ect.foyercompo LIKE \'%1%\' THEN 1 ELSE 0 END),0) as okfoyercompo';
		$sql .= ',IFNULL(SUM(CASE WHEN ect.foyercompo LIKE \'%2%\' THEN 1 ELSE 0 END),0) as kofoyercompo';
		$sql .= ',IFNULL(SUM(CASE WHEN ect.foyerfai LIKE \'%1%\' THEN 1 ELSE 0 END),0) as okfoyerfai';
		$sql .= ',IFNULL(SUM(CASE WHEN ect.foyerfai LIKE \'%2%\' THEN 1 ELSE 0 END),0) as kofoyerfai';
		$sql .= ',IFNULL(SUM(CASE WHEN ect.foyereli LIKE \'%1%\' THEN 1 ELSE 0 END),0) as okfoyereli';
		$sql .= ',IFNULL(SUM(CASE WHEN ect.foyereli LIKE \'%2%\' THEN 1 ELSE 0 END),0) as kofoyereli';
		$sql .= ',IFNULL(SUM(CASE WHEN ect.propcoh LIKE \'%1%\' THEN 1 ELSE 0 END),0) as okpropcoh';
		$sql .= ',IFNULL(SUM(CASE WHEN ect.propcoh LIKE \'%2%\' THEN 1 ELSE 0 END),0) as kopropcoh';
		$sql .= ',IFNULL(SUM(CASE WHEN ect.proprotv LIKE \'%1%\' THEN 1 ELSE 0 END),0) as okproprotv';
		$sql .= ',IFNULL(SUM(CASE WHEN ect.proprotv LIKE \'%2%\' THEN 1 ELSE 0 END),0) as koproprotv';
		$sql .= ',IFNULL(SUM(CASE WHEN ect.proprooption LIKE \'%1%\' THEN 1 ELSE 0 END),0) as okproprooption';
		$sql .= ',IFNULL(SUM(CASE WHEN ect.proprooption LIKE \'%2%\' THEN 1 ELSE 0 END),0) as koproprooption';
		$sql .= ',IFNULL(SUM(CASE WHEN ect.propro5g LIKE \'%1%\' THEN 1 ELSE 0 END),0) as okpropro5g';
		$sql .= ',IFNULL(SUM(CASE WHEN ect.propro5g LIKE \'%2%\' THEN 1 ELSE 0 END),0) as kopropro5g';
		$sql .= ',IFNULL(SUM(CASE WHEN ect.ventespartner LIKE \'%1%\' THEN 1 ELSE 0 END),0) as okventespartner';
		$sql .= ',IFNULL(SUM(CASE WHEN ect.ventespartner LIKE \'%2%\' THEN 1 ELSE 0 END),0) as koventespartner';
		$sql .= ',IFNULL(SUM(CASE WHEN ect.venteschubb LIKE \'%1%\' THEN 1 ELSE 0 END),0) as okventeschubb';
		$sql .= ',IFNULL(SUM(CASE WHEN ect.venteschubb LIKE \'%2%\' THEN 1 ELSE 0 END),0) as koventeschubb';
		$sql .= ',IFNULL(SUM(CASE WHEN ect.ventesaccess LIKE \'%1%\' THEN 1 ELSE 0 END),0) as okventesaccess';
		$sql .= ',IFNULL(SUM(CASE WHEN ect.ventesaccess LIKE \'%2%\' THEN 1 ELSE 0 END),0) as koventesaccess';
		$sql .= ',IFNULL(SUM(CASE WHEN ect.expsfr LIKE \'%1%\' THEN 1 ELSE 0 END),0) as okexpsfr';
		$sql .= ',IFNULL(SUM(CASE WHEN ect.expsfr LIKE \'%2%\' THEN 1 ELSE 0 END),0) as koexpsfr';
		$sql .= ',IFNULL(SUM(CASE WHEN ect.expenqu LIKE \'%1%\' THEN 1 ELSE 0 END),0) as okexpenqu';
		$sql .= ',IFNULL(SUM(CASE WHEN ect.expenqu LIKE \'%2%\' THEN 1 ELSE 0 END),0) as koexpenqu';
		$sql .= ',IFNULL(SUM(CASE WHEN ect.expremise LIKE \'%1%\' THEN 1 ELSE 0 END),0) as okexpremise';
		$sql .= ',IFNULL(SUM(CASE WHEN ect.expremise LIKE \'%2%\' THEN 1 ELSE 0 END),0) as koexpremise';
		$sql .= ',IFNULL(SUM(CASE WHEN ect.exprdv LIKE \'%1%\' THEN 1 ELSE 0 END),0) as okexprdv';
		$sql .= ',IFNULL(SUM(CASE WHEN ect.exprdv LIKE \'%2%\' THEN 1 ELSE 0 END),0) as koexprdv';
		$sql .= ',IFNULL(SUM(CASE WHEN ect.rebondbox LIKE \'%1%\' THEN 1 ELSE 0 END),0) as okrebondbox';
		$sql .= ',IFNULL(SUM(CASE WHEN ect.rebondbox LIKE \'%2%\' THEN 1 ELSE 0 END),0) as korebondbox';
		$sql .= ',IFNULL(SUM(CASE WHEN ect.rebondabo LIKE \'%1%\' THEN 1 ELSE 0 END),0) as okrebondabo';
		$sql .= ',IFNULL(SUM(CASE WHEN ect.rebondabo LIKE \'%2%\' THEN 1 ELSE 0 END),0) as korebondabo';
		$sql .= ',IFNULL(SUM(CASE WHEN ect.rebondrmd LIKE \'%1%\' THEN 1 ELSE 0 END),0) as okrebondrmd';
		$sql .= ',IFNULL(SUM(CASE WHEN ect.rebondrmd LIKE \'%2%\' THEN 1 ELSE 0 END),0) as korebondrmd';
		$sql .= ',IFNULL(SUM(CASE WHEN ect.rebondoptions LIKE \'%1%\' THEN 1 ELSE 0 END),0) as okrebondoptions';
		$sql .= ',IFNULL(SUM(CASE WHEN ect.rebondoptions LIKE \'%2%\' THEN 1 ELSE 0 END),0) as korebondoptions';
		$sql .= ',IFNULL(SUM(CASE WHEN ect.moyensdevis LIKE \'%1%\' THEN 1 ELSE 0 END),0) as okmoyensdevis';
		$sql .= ',IFNULL(SUM(CASE WHEN ect.moyensdevis LIKE \'%2%\' THEN 1 ELSE 0 END),0) as komoyensdevis';
		$sql .= ',IFNULL(SUM(CASE WHEN ect.moyensdouble LIKE \'%1%\' THEN 1 ELSE 0 END),0) as okmoyensdouble';
		$sql .= ',IFNULL(SUM(CASE WHEN ect.moyensdouble LIKE \'%2%\' THEN 1 ELSE 0 END),0) as komoyensdouble';
		$sql .= ',IFNULL(SUM(CASE WHEN ect.moyensreprise LIKE \'%1%\' THEN 1 ELSE 0 END),0) as okmoyensreprise';
		$sql .= ',IFNULL(SUM(CASE WHEN ect.moyensreprise LIKE \'%2%\' THEN 1 ELSE 0 END),0) as komoyensreprise';
		$sql .= ',IFNULL(SUM(CASE WHEN ect.moyensfloa LIKE \'%1%\' THEN 1 ELSE 0 END),0) as okmoyensfloa';
		$sql .= ',IFNULL(SUM(CASE WHEN ect.moyensfloa LIKE \'%2%\' THEN 1 ELSE 0 END),0) as komoyensfloa';
		$sql .= ',IFNULL(SUM(CASE WHEN ect.moyensfamily LIKE \'%1%\' THEN 1 ELSE 0 END),0) as okmoyensfamily';
		$sql .= ',IFNULL(SUM(CASE WHEN ect.moyensfamily LIKE \'%2%\' THEN 1 ELSE 0 END),0) as komoyensfamily';
		$sql .= ',IFNULL(SUM(CASE WHEN ect.moyenspropo LIKE \'%1%\' THEN 1 ELSE 0 END),0) as okmoyenspropo';
		$sql .= ',IFNULL(SUM(CASE WHEN ect.moyenspropo LIKE \'%2%\' THEN 1 ELSE 0 END),0) as komoyenspropo';
		$sql .= ' FROM ' . MAIN_DB_PREFIX . 'monanalysevendeur_ecoute as ec';
		$sql .= ' JOIN ' . MAIN_DB_PREFIX . 'monanalysevendeur_ecoute_extrafields as ect ON (ect.fk_object = ec.rowid)';
		$sql .= ' JOIN ' . MAIN_DB_PREFIX . 'user as usr ON (usr.rowid = ec.salesman)';
		$sql .= " WHERE ec.date_creation BETWEEN '".$this->db->idate($from_date)."' AND '".$this->db->idate($to_date)."'";
		$sql .= " GROUP BY ec.salesman";
		

		$resql = $this->db->query($sql);
		if ($resql)
		{
			
			while ($obj = $this->db->fetch_object($resql))
			{
				$data[$obj->salesman] = array(
					'nbt'=>$obj->nb,
					'name'=>$obj->name,
					'foyerequip'=>($obj->okfoyerequip/($obj->okfoyerequip+$obj->kofoyerequip))*100,
					'foyercompo'=>($obj->okfoyercompo/($obj->okfoyercompo+$obj->kofoyercompo))*100,
					'foyerfai'=>($obj->okfoyerfai/($obj->okfoyerfai+$obj->kofoyerfai))*100,
					'foyereli'=>($obj->okfoyereli/($obj->okfoyerfai+$obj->kofoyereli))*100,
					'propcoh'=>($obj->okpropcoh/($obj->okpropcoh+$obj->kopropcoh))*100,
					'proprotv'=>($obj->okproprotv/($obj->okproprotv+$obj->koproprotv))*100,
					'proprooption'=>($obj->okproprooption/($obj->okproprooption+$obj->koproprooption))*100,
					'propropro5g'=>($obj->okpropro5g/($obj->okpropro5g+$obj->kopropro5g))*100,
					'ventespartner'=>($obj->okventespartner/($obj->okventespartner+$obj->koventespartner))*100,
					'venteschubb'=>($obj->okventeschubb/($obj->okventeschubb+$obj->koventeschubb))*100,
					'ventesaccess'=>($obj->okventesaccess/($obj->okventesaccess+$obj->koventesaccess))*100,
					'expsfr'=>($obj->okexpsfr/($obj->okexpsfr+$obj->koexpsfr))*100,
					'expenqu'=>($obj->okexpenqu/($obj->okexpenqu+$obj->koexpenqu))*100,
					'expremise'=>($obj->okexpremise/($obj->okexpremise+$obj->koexpremise))*100,
					'exprdv'=>($obj->okexprdv/($obj->okexprdv+$obj->koexprdv))*100,
					'rebondbox'=>($obj->okrebondbox/($obj->okrebondbox+$obj->korebondbox))*100,
					'rebondabo'=>($obj->okrebondabo/($obj->okrebondabo+$obj->korebondabo))*100,
					'rebondrmd'=>($obj->okrebondrmd/($obj->okrebondrmd+$obj->korebondrmd))*100,
					'rebondoptions'=>($obj->okrebondoptions/($obj->okrebondoptions+$obj->korebondoptions))*100,
					'moyensdevis'=>($obj->okmoyensdevis/($obj->okmoyensdevis+$obj->komoyensdevis))*100,
					'moyensdouble'=>($obj->okmoyensdouble/($obj->okmoyensdouble+$obj->komoyensdouble))*100,
					'moyensreprise'=>($obj->okmoyensreprise/($obj->okmoyensreprise+$obj->komoyensreprise))*100,
					'moyensfloa'=>($obj->okmoyensfloa/($obj->okmoyensfloa+$obj->komoyensfloa))*100,
					'moyensfamily'=>($obj->okmoyensfamily/($obj->okmoyensfamily+$obj->komoyensfamily))*100,
					'moyenspropo'=>($obj->okmoyenspropo/($obj->okmoyenspropo+$obj->komoyenspropo))*100,
					'picking'=>0,
					'potbox'=>0,
					'box'=>0,
					'txbb'=>0,
					'ecoute'=>0
				);
			}
		} else {
			$this->error=$this->db->lasterror;
			return -1;
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
