<?php
// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 * Cohort related management functions, this file needs to be included manually.
 *
 * @package    core_cohort
 * @copyright  2010 Petr Skoda  {@link http://skodak.org}
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

function separated_id($item){
    global $DB;
    $first_divided = substr($data, strpos($item, "(") + 1);
    $completed_id = str_replace(")","",$first_divided);
    echo $completed_id;
    return 0;
}

/**
 * Add new cohort.
 *
 * @param  stdClass $cohort
 * @return int new cohort id
 */
function isvalididnumber($idnumber)
{
    //echo $idnumber;
    if(strlen($idnumber)!=35)
        return false;
        $check=preg_match_all('/([A-Z,0-9]{3}||[A-Z]{3})( )[A-Z,0-9]{5}( \()[0-9]{2}(.)[0-9]{2}(.)[0-9]{4}( -)( )[0-9]{2}(.)[0-9]{2}(.)[0-9]{4}(\))/',$idnumber);
        return $check;
}
function cohort_add_cohort_phl($cohort) {
    global $DB;
    //var_dump($cohort);
    if (!isset($cohort->name)) {
        throw new coding_exception('Missing cohort name in cohort_add_cohort().');
    }
    if (!isset($cohort->idnumber)) {
        $cohort->idnumber = NULL;
    }
    if (!isset($cohort->description)) {
        $cohort->description = '';
    }
    if (!isset($cohort->descriptionformat)) {
        $cohort->descriptionformat = FORMAT_HTML;
    }
    if (!isset($cohort->visible)) {
        $cohort->visible = 1;
    }
    if (empty($cohort->component)) {
        $cohort->component = '';
    }
    if (!isset($cohort->timecreated)) {
        $cohort->timecreated = time();
    }
    if (!isset($cohort->timemodified)) {
        $cohort->timemodified = $cohort->timecreated;
    }
    
    $cohort->id = $DB->insert_record('cohort', $cohort);
    
    $event = \core\event\cohort_created::create(array(
        'context' => context::instance_by_id($cohort->contextid),
        'objectid' => $cohort->id,
    ));
    $event->add_record_snapshot('cohort', $cohort);
    $event->trigger();
    
    //PHL Cohort
    //$cohort->ngayhoc=$cohort->ngayhoc->month.
    
    
    $dateValue =date("Y-m-d",$cohort->ngayhoc);
    //echo $dateValue."YYY";
    $yr = date_parse($dateValue)['year'];
    $mon = date_parse($dateValue)['month'];
    $day = date_parse($dateValue)['day'];
    
    //$intNgayhoc=strtotime($yr."-".$mon."-".$day);
    
    $phlcohort = (object)array('cohortid' => $cohort->id,
        'ngayhoc' => $cohort->ngayhoc,'ngaythi' => $cohort->ngayhoc, 'khuvuc' => $cohort->khuvuc, 'khoahoc' => $cohort->khoahoc,'thangnam'=>$yr.$mon,'aduser'=> $cohort->aduser,'online'=>$cohort->online);
    //var_dump($phlcohort);
    cohort_add_phlcohort($phlcohort);
    
    
    return $cohort->id;
}
function cohortphl_isexpired($cohort) {
    $currentdate=strtotime(date("Y-m-d"));
    if($cohort->ngayhoc<$currentdate)
        return true;
        return false;
}
function cohort_upload_add_cohort($cohort) {
    global $DB;
    //var_dump($cohort);
    if($cohort->online=="Yes")
    {
        $cohort->khuvuc="Online";
        $cohort->quanhuyen="Online";
        $cohort->xaphuong = "Online";
        $cohort->contextid=2440;
    }
    else
    {
        $cohort->contextid=4409;
    }
    //   $myfile = fopen("before_var_dump.txt", "w") or die("Unable to open file!");
    //   fwrite($myfile, $cohort->khuvuc);
    //  fclose($myfile);
    var_dump($cohort);
    //  $myfile = fopen("trainer.txt", "w") or die("Unable to open file!");
    //  fwrite($myfile, $cohort->trainer);
    //   fclose($myfile);
    //$cohort->quanhuyen="Bruh";
    if($cohort->khuvuc != "Online"){
        //   $myfile = fopen("before_cohort_get_mien.txt", "w") or die("Unable to open file!");
        //   fwrite($myfile, $cohort->khuvuc);
        //  fclose($myfile);
        $mien=cohort_get_mien($cohort->khuvuc);
    }
    //select * from cohortphl_khuvuc
    //where tenkhuvuc = Ha Noi --> get duoc ten mien la 2 va id la 23
    if($cohort->khuvuc != "Online" && $mien != 0){
        //    $myfile = fopen("mien.txt", "w") or die("Unable to open file!");
        //   fwrite($myfile, $mien);
        //    fclose($myfile);
        $khuvuc=cohort_add_khuvuc($cohort->khuvuc,$mien);
        //   $myfile = fopen("khuvuc.txt", "w") or die("Unable to open file!");
        //  fwrite($myfile, $khuvuc);
        //    fclose($myfile);
    }
    // dung ten mien va khu vuc de truy ra id la 23
    //    $myfile = fopen("khuvuc.txt", "w") or die("Unable to open file!");
    //   fwrite($myfile, $khuvuc);
    //     fclose($myfile);
    
    // check if $cohort->quanhuyen da duoc dua len tu file excel dung cach
    //$quanhuyen = cohort_add_quanhuyen( Q. Bac Tu Liem (244) [excel] , 23 queried tu cohort_add_khuvuc
    if($cohort->quanhuyen != "Online"){
        $cohort->quanhuyen = substr($cohort->quanhuyen, 0, strpos($cohort->quanhuyen, "("));
        $quanhuyen=cohort_add_quanhuyen($cohort->quanhuyen,$khuvuc);
        //     $myfile = fopen("before_cohort_add_quanhuyen.txt", "w") or die("Unable to open file!");
        //      fwrite($myfile, $cohort->quanhuyen);
        //    fclose($myfile);
        
        //      $myfile = fopen("tenphuongxa.txt", "w") or die("Unable to open file!");
        //      fwrite($myfile, $cohort->xaphuong);
        //       fclose($myfile);
        //      $myfile = fopen("quanhuyen.txt", "w") or die("Unable to open file!");
        //     fwrite($myfile, $quanhuyen);
        //       fclose($myfile);
    }
    //   $cohort->quanhuyen = substr($cohort->quanhuyen, 0, strpos($cohort->quanhuyen, "("));
    if($cohort->xaphuong != "Online"){
        $cohort->xaphuong = substr($cohort->xaphuong, 0, strpos($cohort->xaphuong, "("));
        $id_phuongxa = cohort_get_phuongxa($quanhuyen,$cohort->xaphuong);
        //     $myfile = fopen("id_phuongxa.txt", "w") or die("Unable to open file!");
        //    fwrite($myfile, $id_phuongxa);
        //       fclose($myfile);
    }
    //   $myfile = fopen("khoahoc_before_get.txt", "w") or die("Unable to open file!");
    //   fwrite($myfile, $cohort->khoahoc);
    //   fclose($myfile);
    $khoahoc=cohort_get_khoahoc($cohort->khoahoc); // cohort_get_khoahoc ( Cac San Pham Bo Sung )
    //    $myfile = fopen("khoahoc.txt", "w") or die("Unable to open file!");
    //   fwrite($myfile, $khoahoc);
    //     fclose($myfile);
    if($khoahoc==0)
        throw new coding_exception('Khong doc duoc khoa hoc <b>'.$cohort->khoahoc.'</b> kiem tra lai!');
        
        if($khuvuc==0 && $khoahoc==0)
            throw new coding_exception('Khong doc duoc khu vuc <b>'.$cohort->khuvuc.'</b> kiem tra lai!');
            
            if(($mien>0 && $khuvuc>0 && $quanhuyen>0 && $khoahoc>0) || $cohort->online=="Yes")
            {
                if (!isset($cohort->name)) {
                    throw new coding_exception('Missing cohort name in cohort_add_cohort().');
                }
                if (!isset($cohort->idnumber)) {
                    $cohort->idnumber = NULL;
                }
                if (!isset($cohort->description)) {
                    $cohort->description = '';
                }
                if (!isset($cohort->sonha)) {
                    $cohort->sonha = '';
                }
                if (!isset($cohort->descriptionformat)) {
                    $cohort->descriptionformat = FORMAT_HTML;
                }
                if (!isset($cohort->visible)) {
                    $cohort->visible = 1;
                }
                if (empty($cohort->component)) {
                    $cohort->component = '';
                }
                if (!isset($cohort->timecreated)) {
                    $cohort->timecreated = time();
                }
                if (!isset($cohort->timemodified)) {
                    $cohort->timemodified = $cohort->timecreated;
                }
                $cohort->name=$cohort->idnumber;
                $cohort->id = $DB->insert_record('cohort', $cohort);
                
                $event = \core\event\cohort_created::create(array(
                    'context' => context::instance_by_id($cohort->contextid),
                    'objectid' => $cohort->id,
                ));
                $event->add_record_snapshot('cohort', $cohort);
                $event->trigger();
                
                //PHL Cohort
                
                $dateValue=\DateTime::createFromFormat('d/m/Y', $cohort->ngayhoc)->format('Y-m-d');
                $yr = date_parse($dateValue)['year'];
                $mon = date_parse($dateValue)['month'];
                $day = date_parse($dateValue)['day'];
                
                
                $intNgayhoc=strtotime($yr."-".$mon."-".$day);
                
                $dateValue=\DateTime::createFromFormat('d/m/Y', $cohort->ngaythi)->format('Y-m-d');
                $yr = date_parse($dateValue)['year'];
                $mon = date_parse($dateValue)['month'];
                $day = date_parse($dateValue)['day'];
                
                
                $intNgaythi=strtotime($yr."-".$mon."-".$day);
                
                if($cohort->id>0)
                {
                    
                    $phlcohort = (object)array('cohortid' => $cohort->id,
                        'ngayhoc' => $intNgayhoc,'ngaythi' => $intNgaythi, 'khuvuc' => $id_phuongxa, 'khoahoc' => $khoahoc,'thangnam'=>$yr.$mon,'sonha'=>$cohort->sonha,'trainer'=> $cohort->trainer,'aduser'=> $cohort->aduser,'online'=>$cohort->online);
                    
                    //var_dump($phlcohort);
                    cohort_add_phlcohort($phlcohort);
                    
                    
                    $plugin = enrol_get_plugin("cohort");
                    
                    if (!$plugin) {
                        throw new moodle_exception('invaliddata', 'error');
                    }
                    $course = $DB->get_record('course', array('id' => $khoahoc), '*', MUST_EXIST);
                    $fields=array(
                        'name'=>"Upload Cohort ".$cohort->id,
                        'status'=>0,
                        'customint1'=>$cohort->id,
                        'roleid'=>5,
                        'customint2'=>0,
                        'id'=>0,
                        'courseid'=>$course->id,
                        'type'=>"cohort"
                    );
                    $plugin->add_instance($course, $fields);
                    //var_dump($fields);
                    
                    
                }
                return $cohort->id;
            }
            else
                throw new coding_exception('LÃ¡Â»â€”i chÃ†Â°a Ã„â€˜Ã†Â°Ã¡Â»Â£c xÃƒÂ¡c Ã„â€˜Ã¡Â»â€¹nh, vui lÃƒÂ²ng liÃƒÂªn hÃ¡Â»â€¡ bÃ¡Â»â„¢ phÃ¡ÂºÂ­n hÃ¡Â»â€” trÃ¡Â»Â£.');
}
function cohort_get_khoahoc($item) {
    global $DB;
    
    $records = $DB->get_records_sql('select * from {course} where fullname=? or shortname=? or idnumber=?', array('fullname' =>trim($item),'shortname'=>trim($item),'idnumber' =>trim($item)));
    foreach ($records as $record) {
        return $record->id;
    }
    return 0;
}

function cohort_add_khuvuc($item,$mien) {
    global $DB;
    // select *
    // from cohortphl_khuvuc
    // where tenkhuvuc = Q. Bac Tu Liem [excel] and mien = 23
    $records = $DB->get_records_sql('select * from {cohortphl_khuvuc} where tenkhuvuc=? and mien=?', array('tenkhuvuc' =>trim($item),'mien'=>$mien));
    foreach ($records as $record) {
        return $record->id;
    }
    return 0;
    //$newRecord=(object)array('tenkhuvuc'=>$item,'mien'=>$mien);
    //return $DB->insert_record('cohortphl_khuvuc',$newRecord);
}
function cohort_add_quanhuyen($item,$khuvuc) {
    global $DB;
    //    $myfile = fopen("check_this.txt", "w") or die("Unable to open file!");
    //     fwrite($myfile, $item. " and ".$khuvuc);
    //      fclose($myfile);
    $records = $DB->get_records_sql('select * from {cohortphl_quanhuyen} where tenquanhuyen=? and khuvuc=?', array('tenmien' =>$item,'khuvuc'=>$khuvuc));
    foreach ($records as $record) {
        return $record->id;   // tim thay thong tin ten quan huyen, lay id roi tra ra ket qua
    }//  $newRecord=(object)array('tenquanhuyen'=>trim($item),'khuvuc'=>$khuvuc);
    //return 0;
    $newRecord = (object)array('tenquanhuyen'=>trim($item), 'khuvuc' => $khuvuc);
    return $DB->insert_record('cohortphl_quanhuyen',$newRecord); // if $records not found, will insert a new tenquanhuyen
}
function cohort_get_phuongxa($id,$tenphuongxa){
    global $DB;
    $sql = "
    select *
    from mdl232x0_cohortphl_xaphuong
    where quanhuyen = $id
    and tenxaphuong = N'$tenphuongxa'
";
    //    $myfile = fopen("sql.txt", "w") or die("Unable to open file!");
    //     fwrite($myfile, $sql);
    //     fclose($myfile);
    $records = $DB->get_records_sql($sql);
    foreach ($records as $record) {
        return $record->id;   // tim thay thong tin ten quan huyen, lay id roi tra ra ket qua
    }//  $newRecord=(object)array('tenquanhuyen'=>trim($item),'khuvuc'=>$khuvuc);
    //return 0;
    $newRecord = (object)array('quanhuyen'=>$id, 'tenxaphuong' => $tenphuongxa);
    return $DB->insert_record('cohortphl_xaphuong',$newRecord);
}
function cohort_get_mien($item) {
    global $DB;
    //  $myfile = fopen("after_cohort_get_mien.txt", "w") or die("Unable to open file!");
    //    fwrite($myfile, trim($item));
    //    fclose($myfile);
    $records = $DB->get_records_sql('select * from {cohortphl_khuvuc} where tenkhuvuc=?', array('tenkhuvuc' =>trim($item)));
    foreach ($records as $record) {
        return $record->mien;
    }
    return 0;//N/A Mien chua xac dinh
}
function cohort_add_phlcohort($cohort) {
    global $DB;
    
    if(isset($cohort->online) && $cohort->online=="Yes")
        $cohort->online=1;
        else
            $cohort->online=0;
            
            $cohort->id = $DB->insert_record('cohortphl', $cohort);
            
            
            return $cohort->id;
}
function cohort_delete_phlcohort($id) {
    global $DB;
    
    $DB->delete_records('cohortphl', array('id'=>$id));
    
}
function cohort_update_phlcohort_cohort($cohort) {
    global $DB;
    
    $DB->delete_records('cohortphl', array('id'=>$cohort->id));
    cohort_add_phlcohort($cohort);
    
}
/**
 * Update existing cohort.
 * @param  stdClass $cohort
 * @return void
 */
function cohort_update_phlcohort($cohort) {
    global $DB;
    if (property_exists($cohort, 'component') and empty($cohort->component)) {
        // prevent NULLs
        $cohort->component = '';
    }
    
    $cohort->timemodified = time();
    $DB->update_record('cohort', $cohort);
    
    //PHL
    $dateValue =date("Y-m-d",$cohort->ngayhoc);
    $yr = date_parse($dateValue)['year'];
    $mon = date_parse($dateValue)['month'];
    
    $phlcohort = (object)array('id'=>$cohort->phlcohortid,'cohortid' => $cohort->id,
        'ngayhoc' => $cohort->ngayhoc,'ngaythi' => $cohort->ngaythi,'aduser'=>$cohort->aduser,'trainer'=>$cohort->trainer,'khuvuc' => $cohort->khuvuc, 'khoahoc' => $cohort->khoahoc,'thangnam'=>$yr.$mon);
    //var_dump( $phlcohort);
    cohort_update_phlcohort_cohort($phlcohort);
    
    $event = \core\event\cohort_updated::create(array(
        'context' => context::instance_by_id($cohort->contextid),
        'objectid' => $cohort->id,
    ));
    $event->trigger();
    
    
    //$DB->update_record('phlcohort', $phlcohort);
}

function cohort_get_phl_user_search_query($search, $tablealias = '') {
    global $DB;
    $params = array();
    if (empty($search)) {
        // This function should not be called if there is no search string, just in case return dummy query.
        return array('1=1', $params);
    }
    if ($tablealias && substr($tablealias, -1) !== '.') {
        $tablealias .= '.';
    }
    $searchparam = '%' . $DB->sql_like_escape($search) . '%';
    $conditions = array();
    $fields = array('firstname', 'lastname', 'username', 'email', 'phone1');
    $cnt = 0;
    foreach ($fields as $field) {
        
        $conditions[] = $DB->sql_like($tablealias . $field, ':csearch' . $cnt, false);
        $params['csearch' . $cnt] = $searchparam;
        $cnt++;
    }
    $sql = '(' . implode(' OR ', $conditions) . ')';
    //echo $sql;
    //var_dump($params);
    return array($sql, $params);
}
function cohort_get_phl_search_query($search, $tablealias = '') {
    global $DB;
    $params = array();
    if (empty($search)) {
        // This function should not be called if there is no search string, just in case return dummy query.
        return array('1=1', $params);
    }
    if ($tablealias && substr($tablealias, -1) !== '.') {
        $tablealias .= '.';
    }
    //$searchparam = '%' . $DB->sql_like_escape($search) . '%';
    $conditions = array();
    $fields = array('qh.khuvuc','ch.khoahoc','mien');
    $cnt = 0;
    foreach ($fields as $field) {
        
        if(!empty($search[$field]) &&  $search[$field]!='0')
        {
            //echo $field.$search[$field]."XXX".$search[$field];
            $conditions[] =$DB->sql_like($tablealias . $field, ':csearch' . $cnt, false);
            $params['csearch' . $cnt] = $search[$field];
            $cnt++;
            
        }
    }
    if($cnt>0)
    {
        $sql = '(' . implode(' AND ', $conditions) . ')';
        return array($sql, $params);
    }
    else
        return array('1=1', $params);
        
}
function cohort_get_phl_report_search_query($search, $tablealias = '') {
    global $DB;
    $params = array();
    if (empty($search)) {
        // This function should not be called if there is no search string, just in case return dummy query.
        return array('1=1', $params);
    }
    if ($tablealias && substr($tablealias, -1) !== '.') {
        $tablealias .= '.';
    }
    //$searchparam = '%' . $DB->sql_like_escape($search) . '%';
    $conditions = array();
    $fields = array('u.username','u.phone1','qh.khuvuc','ch.id','ch.khoahoc');
    $cnt = 0;
    foreach ($fields as $field) {
        
        if(!empty($search[$field]) &&  $search[$field]!='0')
        {
            //echo $field.$search[$field]."XXX".$search[$field];
            $conditions[] = $DB->sql_like($tablealias . $field, ':csearch' . $cnt, false);
            $params['csearch' . $cnt] = $search[$field];
            $cnt++;
            
        }
    }
    if($cnt>0)
    {
        $sql = '(' . implode(' AND ', $conditions) . ')';
        return array($sql, $params);
    }
    else
        return array('1=1', $params);
        
}
function cohort_student_get_phl_search_query($search, $tablealias = '') {
    global $DB;
    $params = array();
    if (empty($search)) {
        // This function should not be called if there is no search string, just in case return dummy query.
        return array('1=1', $params);
    }
    if ($tablealias && substr($tablealias, -1) !== '.') {
        $tablealias .= '.';
    }
    //$searchparam = '%' . $DB->sql_like_escape($search) . '%';
    $conditions = array();
    $fields = array('qh.khuvuc','ch.khoahoc','c.idnumber','ch.thangnam');
    $cnt = 0;
    foreach ($fields as $field) {
        
        if(!empty($search[$field]) &&  $search[$field]!='0')
        {
            //echo $field.$search[$field]."XXX".$search[$field];
            $conditions[] = $DB->sql_like($tablealias . $field, ':csearch' . $cnt, false);
            $params['csearch' . $cnt] = $search[$field];
            $cnt++;
            
        }
    }
    if($cnt>0)
    {
        $sql = '(' . implode(' AND ', $conditions) . ')';
        return array($sql, $params);
    }
    else
        return array('1=1', $params);
        
}

function cohort_student_get_all_phl_cohorts($page = 0, $perpage = 250, $search = '') {
    global $DB;
    
    $fields = "SELECT c.*,ch.ngayhoc,ch.ngaythi,ch.giothi,qh.khuvuc,ch.thangnam,ch.khoahoc,mi.tenmien,kv.tenkhuvuc,co.fullname,qh.tenquanhuyen,CONCAT('ThÃƒÂ¡ng -',MID(thangnam,5,2),' - NÃ„Æ’m ',MID(thangnam,1,4)) as mieutathangnam";
    $countfields = "SELECT COUNT(*)";
    $sql = " FROM {cohort} c
             JOIN {cohortphl} ch ON c.id = ch.cohortid
             JOIN {course} co ON ch.khoahoc = co.id
             JOIN {cohortphl_quanhuyen} qh ON ch.khuvuc = qh.id
             JOIN {cohortphl_khuvuc} kv ON qh.khuvuc = kv.id
             JOIN {cohortphl_mien} mi ON kv.mien = mi.id ";
    
    $params = array();
    
    $wheresql = ' WHERE c.visible=1';
    
    
    $totalcohorts = $allcohorts = $DB->count_records_sql($countfields . $sql . $wheresql, $params);
    
    if (!empty($search) || false) {
        list($searchcondition, $searchparams) = cohort_student_get_phl_search_query($search, '');
        $wheresql .= ($wheresql ? ' AND ' : ' WHERE ') . $searchcondition;
        $params = array_merge($params, $searchparams);
        $totalcohorts = $DB->count_records_sql($countfields . $sql . $wheresql, $params);
        //var_dump($params);
    }
    
    $order = " ORDER BY ch.thangnam DESC";
    // echo $fields . $sql . $wheresql . $order;
    $cohorts = $DB->get_records_sql($fields . $sql . $wheresql . $order, $params, $page*$perpage, $perpage);
    
    // Preload used contexts, they will be used to check view/manage/assign capabilities and display categories names.
    foreach (array_keys($cohorts) as $key) {
        context_helper::preload_from_record($cohorts[$key]);
    }
    
    return array('totalcohorts' => $totalcohorts, 'cohorts' => $cohorts, 'allcohorts' => $allcohorts);
}
/**
 * Get all the cohorts defined anywhere in system.
 *
 * The function assumes that user capability to view/manage cohorts on system level
 * has already been verified. This function only checks if such capabilities have been
 * revoked in child (categories) contexts.
 *
 * @param int $page number of the current page
 * @param int $perpage items per page
 * @param string $search search string
 * @return array    Array(totalcohorts => int, cohorts => array, allcohorts => int)
 */
function cohort_get_all_phl_cohorts($page = 0, $perpage = 250, $search = '') {
    global $DB;
    
    $fields = "SELECT c.*,ch.ngayhoc,ch.ngaythi,ch.giothi,ch.thangnam,ch.khoahoc,mi.tenmien,kv.tenkhuvuc,co.fullname,qh.tenquanhuyen,qh.khuvuc,ch.sonha,trainer";
    $countfields = "SELECT COUNT(*)";
    $sql = " FROM {cohort} c
             JOIN {cohortphl} ch ON c.id = ch.cohortid
             JOIN {course} co ON ch.khoahoc = co.id
             JOIN {cohortphl_quanhuyen} qh ON ch.khuvuc = qh.id
             JOIN {cohortphl_khuvuc} kv ON qh.khuvuc = kv.id
             JOIN {cohortphl_mien} mi ON kv.mien = mi.id ";
    
    $params = array();
    //var_dump($search);
    if (!empty($search) && $search['ngayhoctu']>0)
    {
        $from=strtotime($search['ngayhoctu']['year'].'-'.$search['ngayhoctu']['month'].'-'.$search['ngayhoctu']['day']);
        $to=strtotime($search['ngayhocden']['year'].'-'.$search['ngayhocden']['month'].'-'.$search['ngayhocden']['day']);
        $wheresql = ' WHERE c.visible=1 and ngayhoc>='.$from.' and ngayhoc<='.$to;
        $wheresql .=" and (c.idnumber like '%".$search['c.idnumber']."%')";
        
    }
    else
        $wheresql = " WHERE c.visible=1";
        
        
        $totalcohorts = $allcohorts = $DB->count_records_sql($countfields . $sql . $wheresql, $params);
        
        if (!empty($search)) {
            list($searchcondition, $searchparams) = cohort_get_phl_search_query($search, '');
            $wheresql .= ($wheresql ? ' AND ' : ' WHERE ') . $searchcondition;
            $params = array_merge($params, $searchparams);
            $totalcohorts = $DB->count_records_sql($countfields . $sql . $wheresql, $params);
            //var_dump($params);
        }
        
        $order = " ORDER BY ch.thangnam DESC";
        // echo $fields . $sql . $wheresql . $order;
        $cohorts = $DB->get_records_sql($fields . $sql . $wheresql . $order, $params, $page*$perpage, $perpage);
        
        // Preload used contexts, they will be used to check view/manage/assign capabilities and display categories names.
        foreach (array_keys($cohorts) as $key) {
            context_helper::preload_from_record($cohorts[$key]);
        }
        
        return array('totalcohorts' => $totalcohorts, 'cohorts' => $cohorts, 'allcohorts' => $allcohorts);
}
function cohort_get_all_phl_cohorts_replica($page = 0, $perpage = 250,$search='', $searchkhoahoc = '', $searchkhuvuc = '') {
    global $DB;
    
    $fields = "SELECT c.*,ch.ngayhoc,ch.ngaythi,ch.giothi,ch.thangnam,ch.khoahoc,mi.tenmien,kv.tenkhuvuc,co.fullname,qh.tenquanhuyen,qh.khuvuc,ch.sonha,trainer";
    $countfields = "SELECT COUNT(*)";
    $sql = " FROM {cohort} c
            JOIN {cohortphl} ch ON c.id = ch.cohortid
            JOIN {cohortphl_xaphuong} xp ON ch.khuvuc = xp.id
             JOIN {course} co ON ch.khoahoc = co.id
             JOIN {cohortphl_quanhuyen} qh ON xp.quanhuyen = qh.id
             JOIN {cohortphl_khuvuc} kv ON qh.khuvuc = kv.id
             JOIN {cohortphl_mien} mi ON kv.mien = mi.id ";
    
    $params = array();
    //var_dump($search);
    if (!empty($search) && $search['ngayhoctu']>0)
    {
        $from=strtotime($search['ngayhoctu']['year'].'-'.$search['ngayhoctu']['month'].'-'.$search['ngayhoctu']['day']);
        $to=strtotime($search['ngayhocden']['year'].'-'.$search['ngayhocden']['month'].'-'.$search['ngayhocden']['day']);
        $wheresql = ' WHERE c.visible=1 and ngayhoc>='.$from.' and ngayhoc<='.$to;
        $wheresql .=" and (c.idnumber like N'%".$search['c.idnumber']."%')";
        if ($searchkhoahoc['ch.khoahoc'] != 0){
            $wheresql .=  " and ch.khoahoc = ".$searchkhoahoc['ch.khoahoc'];
        }
        if ($searchkhuvuc['qh.khuvuc'] != 0){
            $wheresql .= " and qh.khuvuc = ".$searchkhuvuc['qh.khuvuc'];
        }
        
    }
    else
        $wheresql = " WHERE c.visible=1";
        
        
        $totalcohorts = $allcohorts = $DB->count_records_sql($countfields . $sql . $wheresql, $params);
        
        if (!empty($search)) {
            list($searchcondition, $searchparams) = cohort_get_phl_search_query($search, '');
            $wheresql .= ($wheresql ? ' AND ' : ' WHERE ') . $searchcondition;
            $params = array_merge($params, $searchparams);
            $totalcohorts = $DB->count_records_sql($countfields . $sql . $wheresql, $params);
            //var_dump($params);
        }
        
        $order = " ORDER BY ch.thangnam DESC";
        // echo $fields . $sql . $wheresql . $order;
        $cohorts = $DB->get_records_sql($fields . $sql . $wheresql . $order, $params, $page*$perpage, $perpage);
        
        // Preload used contexts, they will be used to check view/manage/assign capabilities and display categories names.
        foreach (array_keys($cohorts) as $key) {
            context_helper::preload_from_record($cohorts[$key]);
        }
        
        return array('totalcohorts' => $totalcohorts, 'cohorts' => $cohorts, 'allcohorts' => $allcohorts);
}
function cohort_get_all_phl_cohorts_by_userid($page = 0, $perpage = 250, $search = '') {
    global $DB;
    global $USER;
    
    $fields = "SELECT c.*,ch.ngayhoc,ch.ngaythi,ch.giothi,qh.khuvuc,ch.thangnam,ch.khoahoc,mi.tenmien,kv.tenkhuvuc,co.fullname,qh.tenquanhuyen,ch.sonha,td.attendeddate";
    $countfields = "SELECT COUNT(*)";
    $sql = " FROM {cohort} c
             JOIN {cohort_members} cm ON c.id=cm.cohortid
             JOIN {cohortphl} ch ON c.id = ch.cohortid
             JOIN {course} co ON ch.khoahoc = co.id
             JOIN {cohortphl_quanhuyen} qh ON ch.khuvuc = qh.id
             JOIN {cohortphl_khuvuc} kv ON qh.khuvuc = kv.id
             LEFT JOIN {cohortphl_thamdu} td ON cm.id = td.cohortmemberid
             JOIN {cohortphl_mien} mi ON kv.mien = mi.id ";
    
    $params = array('userid'=>$USER->id);
    
    $wheresql = ' WHERE c.visible=1 AND co.category <> 18 AND cm.userid=?';
    
    $totalcohorts = $allcohorts = $DB->count_records_sql($countfields . $sql . $wheresql, $params);
    
    $order = " ORDER BY c.idnumber asc";
    //echo $fields . $sql . $wheresql . $order;
    $cohorts = $DB->get_records_sql($fields . $sql . $wheresql . $order, $params, $page*$perpage, $perpage);
    
    // Preload used contexts, they will be used to check view/manage/assign capabilities and display categories names.
    foreach (array_keys($cohorts) as $key) {
        context_helper::preload_from_record($cohorts[$key]);
    }
    
    return array('totalcohorts' => $totalcohorts, 'cohorts' => $cohorts, 'allcohorts' => $allcohorts);
}

function cohort_get_report_users($page = 0, $perpage = 25, $search = '',$listid='') {
    global $DB;
    global $USER;
    
    $fields = "SELECT cm.id,u.id as userid,u.firstname,u.lastname,u.email,u.phone1,u.username,cm.timeadded as ngaydangky,cm.id as cmid,ch.ngayhoc,c.name,c.idnumber,attendeddate";
    $countfields = "SELECT COUNT(*)";
    $sql = " FROM {cohort} c
             JOIN {cohortphl} ch ON c.id=ch.cohortid
             JOIN {cohort_members} cm ON c.id=cm.cohortid
             JOIN {cohortphl_quanhuyen} qh ON ch.khuvuc = qh.id
             JOIN {cohortphl_khuvuc} kv ON qh.khuvuc = kv.id
             LEFT JOIN {cohortphl_thamdu} ck on cm.id=ck.cohortmemberid
             JOIN {user} u ON cm.userid=u.id";
    
    
    $params = array();
    
    
    if (!empty($search) && $search['ngayhoanthanhtu']>0 && $listid=='')
    {
        $from=strtotime($search['ngayhoanthanhtu']['year'].'-'.$search['ngayhoanthanhtu']['month'].'-'.$search['ngayhoanthanhtu']['day']);
        $to=strtotime($search['ngayhoanthanhden']['year'].'-'.$search['ngayhoanthanhden']['month'].'-'.$search['ngayhoanthanhden']['day']);
        $wheresql = ' WHERE c.visible=1 and attendeddate>='.$from.' and attendeddate<='.$to.' AND (firstname like \'%'.$search['fullname'].'%\' OR lastname like \'%'.$search['fullname'].'%\')';
        
    }
    else
    {
        if($listid!='')
            $wheresql = ' WHERE c.visible=1 and ck.attendeddate is not null and u.username in('.$listid.')';
            else
                $wheresql = ' WHERE c.visible=1  and ck.attendeddate is not null';
                
    }
    
    
    $totalcohorts = $allcohorts = $DB->count_records_sql($countfields . $sql . $wheresql, $params);
    
    if (!empty($search)) {
        list($searchcondition, $searchparams) = cohort_get_phl_report_search_query($search, '');
        $wheresql .= ($wheresql ? ' AND ' : ' WHERE ') . $searchcondition;
        $params = array_merge($params, $searchparams);
        //$totalcohorts = $DB->count_records_sql($countfields . $sql . $wheresql, $params);
        //var_dump($params);
    }
    
    $order = " ORDER BY ch.ngayhoc DESC";
    //echo $fields . $sql . $wheresql . $order;
    $cohorts = $DB->get_records_sql($fields . $sql . $wheresql . $order, $params, $page*$perpage, $perpage);
    
    // Preload used contexts, they will be used to check view/manage/assign capabilities and display categories names.
    foreach (array_keys($cohorts) as $key) {
        context_helper::preload_from_record($cohorts[$key]);
    }
    
    return array('totalusers' => $totalcohorts, 'users' => $cohorts, 'allusers' => $allcohorts);
}
function cohort_get_all_users($page = 0, $perpage = 25, $search = '',$cohortid) {
    global $DB;
    global $USER;
    
    $fields = "SELECT u.id,u.firstname,u.lastname,u.email,u.phone1,u.username,cm.timeadded as ngaydangky,cm.id as cmid";
    $countfields = "SELECT COUNT(*)";
    $sql = " FROM {cohort} c
             JOIN {cohort_members} cm ON c.id=cm.cohortid
             JOIN {user} u ON cm.userid=u.id";
    
    
    $params = array('cohortid'=>$cohortid);
    
    $wheresql = ' WHERE c.visible=1 AND cohortid='.$cohortid;
    
    
    
    if (!empty($search)) {
        list($searchcondition, $searchparams) = cohort_get_phl_user_search_query($search, 'u');
        $wheresql .= ($wheresql ? ' AND ' : ' WHERE ') . $searchcondition;
        $params = array_merge($params, $searchparams);
        //$totalcohorts = $DB->count_records_sql($countfields . $sql . $wheresql, $params);
        //var_dump($params);
    }
    
    $totalcohorts = $allcohorts = $DB->count_records_sql($countfields . $sql . $wheresql, $params);
    
    $order = " ORDER BY cm.timeadded DESC";
    //echo $fields . $sql . $wheresql . $order;
    $cohorts = $DB->get_records_sql($fields . $sql . $wheresql . $order, $params, $page*$perpage, $perpage);
    
    // Preload used contexts, they will be used to check view/manage/assign capabilities and display categories names.
    foreach (array_keys($cohorts) as $key) {
        context_helper::preload_from_record($cohorts[$key]);
    }
    
    return array('totalusers' => $totalcohorts, 'users' => $cohorts, 'allusers' => $allcohorts);
}
function cohort_is_member_attended($cmid) {
    global $DB;
    return $DB->record_exists('cohortphl_thamdu', array('cohortmemberid'=>$cmid));
}
function cohort_add_member_attended($cmid) {
    global $DB;
    
    //$dateValue=\DateTime::createFromFormat('d/m/Y', date('Y-m-d');
    //echo date('Y-m-d');
    $intAttendedDate=strtotime(date('Y-m-d'));
    //echo $intAttendedDate;
    $cm=(object)array('cohortmemberid' =>$cmid,'attendeddate'=>$intAttendedDate);
    return $DB->insert_record('cohortphl_thamdu',$cm);
}
function cohort_remove_member_attended($cmid) {
    global $DB;
    $DB->delete_records('cohortphl_thamdu', array('cohortmemberid'=>$cmid));
}
function cohort_get_phl_cohort_detail_replica($cohortid) {
    global $DB;
    
    $fields = "SELECT c.*,ch.id as phlcohortid,ch.ngayhoc,ch.ngaythi,ch.giothi,qh.khuvuc,ch.thangnam,ch.khoahoc,mi.tenmien,kv.tenkhuvuc,co.fullname,qh.tenquanhuyen,qh.id as qhid,xp.tenxaphuong,ch.sonha,ch.trainer,ch.aduser";
    $sql = "  FROM {cohort} c
            JOIN {cohortphl} ch ON c.id = ch.cohortid
            JOIN {cohortphl_xaphuong} xp ON ch.khuvuc = xp.id
             JOIN {course} co ON ch.khoahoc = co.id
             JOIN {cohortphl_quanhuyen} qh ON xp.quanhuyen = qh.id
             JOIN {cohortphl_khuvuc} kv ON qh.khuvuc = kv.id
             JOIN {cohortphl_mien} mi ON kv.mien = mi.id  ";
    
    $params = array();
    $wheresql = ' WHERE c.visible=1 and c.id=?';
    
    
    //echo $fields . $sql . $wheresql . $order;
    $cohorts = $DB->get_records_sql($fields . $sql . $wheresql,array($cohortid));
    foreach (array_keys($cohorts) as $key) {
        context_helper::preload_from_record($cohorts[$key]);
    }
    return $cohorts;
}

function cohort_get_phl_cohort_detail($cohortid) {
    global $DB;
    
    $fields = "SELECT c.*,ch.id as phlcohortid,ch.ngayhoc,ch.ngaythi,ch.giothi,qh.khuvuc,ch.thangnam,ch.khoahoc,mi.tenmien,kv.tenkhuvuc,co.fullname,qh.tenquanhuyen,qh.id as qhid,ch.sonha,ch.trainer,ch.aduser";
    $sql = "  FROM {cohort} c
             JOIN {cohortphl} ch ON c.id = ch.cohortid
             JOIN {cohortphl_quanhuyen} qh ON ch.khuvuc = qh.id
             JOIN {cohortphl_khuvuc} kv ON qh.khuvuc = kv.id
             JOIN {course} co ON ch.khoahoc = co.id
             JOIN {cohortphl_mien} mi ON kv.mien = mi.id  ";
    
    $params = array();
    $wheresql = ' WHERE c.visible=1 and c.id=?';
    
    
    //echo $fields . $sql . $wheresql . $order;
    $cohorts = $DB->get_records_sql($fields . $sql . $wheresql,array($cohortid));
    foreach (array_keys($cohorts) as $key) {
        context_helper::preload_from_record($cohorts[$key]);
    }
    return $cohorts;
}
function cohort_get_phl_cohort_session() {
    global $SESSION;
    
    if(isset($SESSION->selectedcohorts))
        return $SESSION->selectedcohorts;
        
        return array();
        
}
function cohort_set_phl_cohort_session($newcohort) {
    global $SESSION;
    
    if($newcohort==NULL)
    {
        $SESSION->selectedcohorts = array();
        return;
    }
    if(!isset($SESSION->selectedcohorts))
        $SESSION->selectedcohorts = array();
        foreach ($SESSION->selectedcohorts as $cohort) {
            // echo $newcohort->id."XXX".$cohort->id;
            if($newcohort->id==$cohort->id)
                return;
        }
        
        
        
        
        
        array_unshift($SESSION->selectedcohorts,$newcohort);
        
}

function cohort_edit_controls_phl(context $context, moodle_url $currenturl) {
    $tabs = array();
    $currenttab = 'view';
    $viewurl = new moodle_url('/phlcohort/manager.php', array('contextid' => $context->id));
    if (($searchquery = $currenturl->get_param('search'))) {
        $viewurl->param('search', $searchquery);
    }
    if ($context->contextlevel == CONTEXT_SYSTEM) {
        //$tabs[] = new tabobject('view', new moodle_url($viewurl, array('showall' => 0)), get_string('systemcohorts', 'cohort'));
        $tabs[] = new tabobject('viewall', new moodle_url($viewurl, array('showall' => 1)), get_string('allcohorts', 'cohort'));
        if ($currenturl->get_param('showall')) {
            $currenttab = 'viewall';
        }
    } else {
        $tabs[] = new tabobject('view', $viewurl, get_string('cohorts', 'cohort'));
    }
    if (has_capability('moodle/cohort:manage', $context)) {
        $addurl = new moodle_url('/phlcohort/edit.php', array('contextid' => $context->id));
        $tabs[] = new tabobject('addcohort', $addurl, get_string('addcohort', 'cohort'));
        if ($currenturl->get_path() === $addurl->get_path() && !$currenturl->param('id')) {
            $currenttab = 'addcohort';
        }
        
        $uploadurl = new moodle_url('/phlcohort/upload.php', array('contextid' => $context->id));
        $tabs[] = new tabobject('uploadcohorts', $uploadurl, get_string('uploadcohorts', 'cohort'));
        if ($currenturl->get_path() === $uploadurl->get_path()) {
            $currenttab = 'uploadcohorts';
        }
    }
    if (count($tabs) > 1) {
        return new tabtree($tabs, $currenttab);
    }
    return null;
}

