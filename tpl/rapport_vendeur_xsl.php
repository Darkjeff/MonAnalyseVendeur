<?php
$output.= '<meta charset="utf-8" />';
	global $db,$form, $formother, $pointage,$pointagemod_sal, $taskstatic;
	global $periodpointmodyear,$periodpointmodmonth,$users,$pointmodsearch_datef,$periodpointmodweek,$pointmodsearch_dated,$pointage;
	global $lines,$langs;
	global $numlines,$user_array,$project;

	
     $filter="and year_point=".$periodpointmodyear;
	if (empty($pointmodsearch_datef) && empty($pointmodsearch_dated) )
     $filter.=" and month_point=".$periodpointmodmonth;

     $sd = 0 ;
     $ed = 0 ;
    
     if (isset($pointmodsearch_dated) && !empty($pointmodsearch_dated)) {
	list($sd, $sm, $sy)  = explode("/", $pointmodsearch_dated);
	if (isset($pointmodsearch_datef) && !empty($pointmodsearch_datef)) {
	list($ed, $em, $ey)  = explode("/", $pointmodsearch_datef);
	/*$filter .=  " AND month_point BETWEEN ". $sm ." AND ". $em ;
	$filter .=  " AND jour BETWEEN ". $sd ." AND ". $ed ;*/
}
}
$sd = intval($sd);
$ed = intval($ed);	
$numtd = 0 ;


$all_users['salary'] = $pointagemod_sal->getUsersWithS(true,$periodpointmodyear,$periodpointmodmonth);
$all_users['nosalary'] = $pointagemod_sal->getUsersWithS(false,$periodpointmodyear,$periodpointmodmonth);


$var=true;
$array=['J'];
$numlines=count($array);
// $num=count($lines);
$nbdaymonth = 0;

$monthArray = array(
	     1 => 'Janvier',
	     2 => 'Février',
	     3 => 'Mars',
	     4 => 'Avril',
	     5 => 'Mai',
	     6 => 'Juin',
	     7 => 'Juillet',
	     8 => 'Août',
	     9=> 'Septembre',
	    10=> 'Octobre',
	    11 => 'Novembre',
	    12 => 'Décembre'
	);
$total_day = array();
$total_month = array();

$test = 0;
            

$time = mktime(0, 0, 0, $periodpointmodmonth+1, 1, $periodpointmodyear); // premier jour du mois suivant
$time--; // Recule d'une seconde
$nbdaymonth=date('d', $time); 





$weektit = '';
if($periodpointmodweek){
	$ft = explode("-", $periodpointmodweek);

	$fdaymonth = ($ft[0]+0);
	$nbdaymonth = ($ft[1]+0);
	
	$first = $ft[0].'/'.sprintf("%02d", $periodpointmodmonth).'/'.$periodpointmodyear;
	$second .= $ft[1].'/'.sprintf("%02d", $periodpointmodmonth).'/'.$periodpointmodyear;
	$weektit .= $first;
	if($first != $second){
		$weektit .= ' - ';
		$weektit .= $second;
	}
	$weektit = '('.$weektit.')';
}

$numtd = ($nbdaymonth-$fdaymonth)+3;

$mtwodi = sprintf("%02d", $periodpointmodmonth);

global $periodprojectselected, $projectactivated;
if($projectactivated && $periodprojectselected){
	$obj = new Project($db);
	$obj->fetch($periodprojectselected);
	$porojecttit = '';
	$porojecttit .= $obj->ref;
	if($obj->title)
	$porojecttit .= ' - '.$obj->title;

	$output.= '<h2 align="center"> '.$langs->trans("Project").' : '.$porojecttit.'</h2>';
}

$output.= '<h3 align="center"> '.$langs->trans("pointagemod2").' - '.$langs->trans("Month".sprintf("%02d", $periodpointmodmonth)).'/'.$periodpointmodyear.' '.$weektit.'</h3>';

$output.= '<table border="1" width="100%" >';
$output.='<tr><td bgcolor="#dddddd" colspan="'.intval($numtd+3).'" align="center"><h3><strong>'.$langs->trans('Month'.$mtwodi).'</strong></h3></td></tr>';
$output.= '<tr class="liste_titre">';
$output.= '<td  align="center" >'.$langs->trans("Names").'</td>';
$output.= '<td  align="center">'.$langs->trans("t.h").'</td>';
$output.= '<td  align="center">'.$langs->trans("Salaries").'</td>';

for ($i = 0 ; $i <$numlines ; $i++){
	$nbrsec=0;

	for ($day=$fdaymonth;$day <= $nbdaymonth ;$day++)
	{ 

		if (!empty($pointmodsearch_datef) && !empty($pointmodsearch_dated) ) {
			if($day<$sd  || $day>$ed )
			continue ;
		}

		$curday=mktime(0, 0, 0, $periodpointmodmonth, $day, $periodpointmodyear);
		$bgcolor="";


		if($periodpointmodweek)
			$daynam = $langs->trans(date('l', $curday));
		else
			$daynam = substr($langs->trans(date('l', $curday)),0,1);


		if (date('N', $curday) == 6 || date('N', $curday) == 7)
		{
			$output.= '<td bgcolor="#dddddd" align="center">';
			$output.= $daynam." ".$day.'</td>';
		}else{
			$output.= '<td align="center">';
			$output.= $daynam." ".$day.'</td>';
		}

	}
}

$output.= '<td  align="center">'.$langs->trans("total").'</td>';
$output.= '<td  align="center">'.$langs->trans("netap").' ('.$langs->getCurrencySymbol($conf->currency).')</td>';
$output.= "</tr>\n";

$output.= '<tr><td></td><td></td><td></td>';
	
$output.= '</tr>';*/
$globseconds = 0;
foreach($all_users as $index=>$data) {
	asort($data);
	$total_day = array();
	foreach($data as $key=>$value) {
		$user_array->fetch($key);
		$user_arr = $pointage->nc_getUserInfo($key);
		if (isset($radio) && !empty($radio)) {
			if($radio=='declar' && $user_arr->declar==0 )
				continue;
			elseif($radio=='nodeclar' && $user_arr->declar==1)
				continue;
		}

		if(!empty($users) && is_array($users) && !in_array($key, $users))
			continue ;

		$alrdyshowd = array();
		
		if($test==0){

			$user_array = new User($db);
	     	$reslt4 = $user_array->fetch($key);

			if($reslt4){
			    $output.= '<tr>';
			   
				$output.= '<td align="left" style="white-space: nowrap;" >'.$user_array->getFullName($trans).'</td>';
					if($pointagemod_sal->getThm($periodpointmodmonth,$periodpointmodyear,$key))
					$thval = $pointagemod_sal->getThm($periodpointmodmonth,$periodpointmodyear,$key);
				else
					$thval = $user_array->thm;

				$thvaltop = number_format($thval,2,',',' ');

				$output.= '<td align="center">'.$thvaltop.'</td>';

				$retu = $pointagemod_sal->getSalary($periodpointmodmonth,$periodpointmodyear,$key);
				if(!empty($retu['rowid']))
					$salval =  $retu['salary'];
				else
					$salval = $user_array->salary;

				$saltop = number_format($salval,2,',',' ');

				if($salval <= 0){
					$saltop = 0;
				}

				$output.= '<td align="center">'.$saltop.'</td>';

				$toth = $totm = 0;

				for ($day=$fdaymonth;$day <= $nbdaymonth ;$day++)
				{ 
					if (!empty($pointmodsearch_datef) && !empty($pointmodsearch_dated) ) {
						if($day<$sd  || $day>$ed )
							continue ;
					}

					$curday=mktime(0, 0, 0, $periodpointmodmonth, $day, $periodpointmodyear);
					$bgcolor="";

					
					$retu = $pointage->getVal($periodpointmodmonth,$periodpointmodyear,$key,$array[$i],$day,$periodprojectselected);
					$val = $retu['val'];

					// $nbr=$nbr+$val;
					$heur = $retu['val'];
					$minu = $retu['minu'];

					if($heur <= 0) $heur = 0;
					if($minu <= 0) $minu = 0;
					if($minu > 59) $minu = 59;

					$dataid = "new";
					if(!empty($retu['rowid'])) $dataid = $retu['rowid'];

					$time = '';
					if(!empty($heur) || !empty($minu)) $time = sprintf("%02d", $heur).':'.sprintf("%02d", $minu);

					// $toth = $toth + $heur; $totm = $totm + $minu;
					$toth = $heur; $totm = $minu;

					$totsec = ($toth * 3600) + ($totm * 60);

					$nbrsec = $nbrsec + $totsec;

					if(!array_key_exists($periodpointmodmonth.'-'.$day, $total_day))
						$total_day[$periodpointmodmonth.'-'.$day] = $totsec;
					else
						$total_day[$periodpointmodmonth.'-'.$day] += $totsec;

					if($totsec >= 0)
						$globseconds = $globseconds + $totsec;

					if (date('N', $curday) == 6 || date('N', $curday) == 7)
					{
						$output.= '<td bgcolor="#dddddd" align="center" >'.$time.'</td>';
					}else{
						$output.= '<td align="center" >'.$time.'</td>';
					}
				}
			            
				for ($com=1; $com <$i ; $com++) {
					if(!empty($array[$i])) 
					$output.= '<td align="center"></td>';	
				}

				$total = 0;
				$output.= '<td  class="totheu" align="center">'.convertSecondToTime($nbrsec, 'allhourmin').'</td>';
				if($thval){
					$total = ($nbrsec/3600)*$thval;
					if($salval){
						$total = $total + $salval;
					}
					$output.= '<td  class="totheu" align="center">'.number_format($total,2,',',' ').'</td>';
					if(!array_key_exists($periodpointmodmonth.'_'.$index, $total_month))
						$total_month[$periodpointmodmonth.'_'.$index] = $total;
					else
						$total_month[$periodpointmodmonth.'_'.$index] += $total;
				}
				elseif($salval){
					$total = $salval;
					$output.= '<td align="center">'.number_format($total,2,',',' ').'</td>';
					if(!array_key_exists($periodpointmodmonth.'_'.$index, $total_month))
						$total_month[$periodpointmodmonth.'_'.$index] = $total;
					else
						$total_month[$periodpointmodmonth.'_'.$index] += $total;
				}
				else{
					$output.= '<td align="center"></td>';
				}
			}
		}

		$test = 0;
		$nbrsec = 0;
	}

	if(count($data) > 0){
		
		$output.= '<tr bgcolor="#dddddd"  class="liste_titre"><td colspan="'.intval($numtd-1).'" align="center">'.$langs->trans("total").'</td><td colspan="2"></td>';


		for ($i = 0 ; $i <$numlines ; $i++)
		{   
			$total_sec = 0;
			for ($day=$fdaymonth;$day <= $nbdaymonth ;$day++)
			{ 
				if (!empty($pointmodsearch_datef) && !empty($pointmodsearch_dated) ) {
					if($day<$sd  || $day>$ed )
						continue ;
				}
				$curday=mktime(0, 0, 0, $periodpointmodmonth, $day, $periodpointmodyear);

				$bgcolor="";
				if(isset($total_day[$periodpointmodmonth.'-'.$day])){
					$total_sec +=  $total_day[$periodpointmodmonth.'-'.$day];
				}
			}
		}

		$output.= '<td  align="center"><strong>'.convertSecondToTime($total_sec, 'allhourmin').'</strong></td>';
		$output.= '<td  align="center"><strong>'.number_format($total_month[$periodpointmodmonth.'_'.$index],2,',',' ').'</strong></td>';
		$output.= "</tr>\n";	

	}

}

$output.= '<tr class="liste_titre">';
$output.= '<td colspan="'.intval($numtd+1).'" align="center"><strong>TOTAL GlOBAL</strong></td>';
$output.= '<td  align="center">'.convertSecondToTime($globseconds, 'allhourmin').'</td>';
$total_global = 0;
foreach ($total_month as $value) {
	$total_global += $value;
}
$output.= '<td  align="center"><strong>'.number_format($total_global,2,',',' ').'</strong></td>';
$output.= "</tr>\n";

$output.= "</table>";

// print_r($users);
// die($output);

header("Content-Type: application/xls");
header("Content-Disposition: attachment; filename=".$filename."");
echo $output;
?>