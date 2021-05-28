<?php
/**
 * PHPExcel
 *
 * Copyright (c) 2006 - 2015 PHPExcel
 *
 * This library is free software; you can redistribute it and/or
 * modify it under the terms of the GNU Lesser General Public
 * License as published by the Free Software Foundation; either
 * version 2.1 of the License, or (at your option) any later version.
 *
 * This library is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the GNU
 * Lesser General Public License for more details.
 *
 * You should have received a copy of the GNU Lesser General Public
 * License along with this library; if not, write to the Free Software
 * Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA  02110-1301  USA
 *
 * @category   PHPExcel
 * @package    PHPExcel
 * @copyright  Copyright (c) 2006 - 2015 PHPExcel (http://www.codeplex.com/PHPExcel)
 * @license    http://www.gnu.org/licenses/old-licenses/lgpl-2.1.txt	LGPL
 * @version    ##VERSION##, ##DATE##
 */

/** Error reporting */
//error_reporting(E_ALL);
//ini_set('display_errors', TRUE);
//ini_set('display_startup_errors', TRUE);
//date_default_timezone_set('Europe/London');
require('../../../config.php');
require($CFG->dirroot.'/cohort/lib.php');
require($CFG->dirroot.'/phlcohort/lib.php');
require_once($CFG->libdir.'/adminlib.php');


if (PHP_SAPI == 'cli')
    die('This example should only be run from a Web Browser');
    
    /** Include PHPExcel */
    require_once dirname(__FILE__) . '/Classes/PHPExcel.php';
    
    $cohortid=optional_param('id',0, PARAM_INT);
    $context=optional_param('context','', PARAM_RAW);
    $trainer=optional_param('trainer',null, PARAM_NOTAGS);
    require_login();
    if($context=='members' && $cohortid>0)
    {
        $listUserID='';
        if($SESSION->bulk_users_phl!=null)
        {
            $listUserID=implode(',',$SESSION->bulk_users_phl);
        }
        $sql = "SELECT u.id,CONCAT(u.firstname,' ',u.lastname) as uname,u.email,u.phone1,u.username,cm.timeadded,c.idnumber,FORMAT(dateadd(SECOND, ch.ngayhoc, '1/1/1970'),'dd/MM/yyyy') as ngayhoc,co.id as idkhoahoc,co.fullname as khoahoc,cohortmemberid,CONCAT(' ','') as sign,
CONCAT(' ','') as code,CONCAT('$trainer','') as trainer,tenkhuvuc,tenquanhuyen, xp.tenxaphuong as tenxaphuong, m.tenmien,ch.ngaythi as ngaythi
             FROM {cohort} c
             JOIN {cohort_members} cm ON c.id=cm.cohortid
             JOIN {cohortphl} ch ON c.id=ch.cohortid
             JOIN {course} co ON ch.khoahoc = co.id
             JOIN {user} u ON cm.userid=u.id
            JOIN {cohortphl_xaphuong} xp ON ch.khuvuc = xp.id
             JOIN {cohortphl_quanhuyen} qh ON xp.quanhuyen = qh.id
             JOIN {cohortphl_khuvuc} kv ON qh.khuvuc = kv.id
             JOIN {cohortphl_thamdu} ck on cm.id=ck.cohortmemberid
            JOIN {cohortphl_mien} m on kv.mien = m.id
";
        if($cohortid>0)
        {
            if($listUserID!='')
                $sql.=" WHERE c.visible=1 AND ch.cohortid=? AND u.id in (".$listUserID.")";
                else
                    $sql.=" WHERE c.visible=1 AND ch.cohortid=?";
        }
        else
        {
            if($listUserID!='')
                $sql.=" WHERE c.visible=1 AND u.id in (".$listUserID.")";
                else
                    $sql.=" WHERE c.visible=1";
        }
        $users=$DB->get_records_sql($sql,array('cohortid' =>$cohortid));
        foreach($users as $user){
            $condition = $user->khoahoc;
            $second_condition = $user->idkhoahoc;
            
        }
        
        $cohort_items = $DB->get_record('cohortphl', array('cohortid' =>$cohortid), '*', MUST_EXIST);
        $course = $DB->get_record('course', array('id'=>$second_condition), '*', MUST_EXIST);
        
        $template = $DB->get_record('phl_templates', array('id'=>$course->templateid), '*', MUST_EXIST);
        $objPHPExcel = PHPExcel_IOFactory::load($CFG->dirroot.'/phlcohort/csv/'.$template->template_name);
        $count_mem = $DB->count_records_sql("select count(id) from {cohort_members}
                                            where cohortid = $cohortid", array('cohortid'=>$cohortid));
        $update_cohortphl->id = $cohort_items->id;
        $update_cohortphl->cohortid = $cohort_items->cohortid;
        $update_cohortphl->trainer = $trainer;
        $DB->update_record('cohortphl',$update_cohortphl);
        $i=10;
        //   $template = $DB->get_record('phl_templates',array('id'=>$course->templateid), '*', MUST_EXIST);
        if($second_condition==47){ // PSS exclusive
            foreach ($users as $user) {
                $last_row = $i;
                $objPHPExcel->setActiveSheetIndex(0)
                ->setCellValue('C5',''.$user->khoahoc)
                ->setCellValue('H4',''.$trainer)
                ->setCellValue('H6',''.$user->ngaythi)
                ->setCellValue('C4',''.$user->tenmien.' - TP.'.$user->tenkhuvuc.' - '.$user->tenquanhuyen.' - '.$user->tenxaphuong)
                ->setCellValue('C6',''.$user->ngayhoc)
                ->setCellValue('A'.$i,'  '.$i-9)
                ->setCellValue('B'.$i,$user->uname)
                ->setCellValue('C'.$i,'')
                ->setCellValue('D'.$i,'')
                ->setCellValue('F'.$i,''.$user->username)
                ->setCellValue('G'.$i,'');
                $i++;
            }
            $objPHPExcel->getActiveSheet()->duplicateStyle($objPHPExcel->getActiveSheet()->getStyle('B10'),'A10:Q'.$i);
            // HÃƒÂ ng 1
            //        $last_row = $last_row + 3;
            //         $objPHPExcel->setActiveSheetIndex(0)
            //         ->setCellValue('B'.$last_row,'S? lu?ng d? di?u ki?n');
            //        $objPHPExcel->setActiveSheetIndex(0)
            //         ->setCellValue('J'.$last_row,'Ðang Ký');
            //        $objPHPExcel->setActiveSheetIndex(0)
            //        ->setCellValue('L'.$last_row,'Ðang Ký');
            //         $objPHPExcel->setActiveSheetIndex(0)
            //         ->setCellValue('N'.$last_row,'Ðang Ký');
            // HÃƒÂ ng 2
            //         $last_row = $last_row + 1;
            //         $objPHPExcel->setActiveSheetIndex(0)
            //        ->setCellValue('B'.$last_row,'S? lu?ng d? thi th?c t?');
            //         $objPHPExcel->setActiveSheetIndex(0)
            //         ->setCellValue('J'.$last_row,'Th?c t?');
            //          $objPHPExcel->setActiveSheetIndex(0)
            //         ->setCellValue('L'.$last_row,'Th?c t?');
            //          $objPHPExcel->setActiveSheetIndex(0)
            //          ->setCellValue('N'.$last_row,'Th?c t?');
            // HÃƒÂ ng 3
            //        $last_row = $last_row + 1;
            //        $objPHPExcel->setActiveSheetIndex(0)
            //         ->setCellValue('B'.$last_row,'T? l? d? thi');
            //         $objPHPExcel->setActiveSheetIndex(0)
            //         ->setCellValue('J'.$last_row,'T? l?');
            //        $objPHPExcel->setActiveSheetIndex(0)
            //        ->setCellValue('L'.$last_row,'T? l?');
            //          $objPHPExcel->setActiveSheetIndex(0)
            //         ->setCellValue('N'.$last_row,'T? l?');
            //        $objPHPExcel->getActiveSheet()->getStyle('B'.$last_row.':N'.$last_row)->getFont()->setBold(true);
            // HÃƒÂ ng 4
            //     $last_row = $last_row + 1;
            //     $objPHPExcel->setActiveSheetIndex(0)
            //        ->setCellValue('B'.$last_row,'S? lu?ng d? thi d?t');
            // HÃƒÂ ng 5
            //       $last_row = $last_row + 1;
            //      $objPHPExcel->setActiveSheetIndex(0)
            //        ->setCellValue('B'.$last_row,'T? l? thi d?t');
            //        $objPHPExcel->getActiveSheet()->getStyle('B'.$last_row)->getFont()->setBold(true);
            // HÃƒÂ ng 4
            //       $last_row = $last_row + 2;
            //       $objPHPExcel->setActiveSheetIndex(0)
            //       ->setCellValue('B'.$last_row,'Ch? ký chuyên viên hu?n luy?n');
            //       $objPHPExcel->getActiveSheet()->getStyle('B'.$last_row)->getFont()->setBold(true);
            // Set active sheet index to the first sheet, so Excel opens this as the first sheet
            $objPHPExcel->setActiveSheetIndex(0);
            header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
            //        header('Content-Disposition: attachment;filename="PSS.xlsx"');
            header('Content-Disposition: attachment;filename="'.$user->idnumber.'".xlsx"');
            header('Cache-Control: max-age=0');
            header('Cache-Control: max-age=1');
            header ('Expires: Mon, 26 Jul 1997 05:00:00 GMT'); // Date in the past
            header ('Last-Modified: '.gmdate('D, d M Y H:i:s').' GMT'); // always modified
            header ('Cache-Control: cache, must-revalidate'); // HTTP/1.1
            header ('Pragma: public'); // HTTP/1.0
        }
        else{
            foreach ($users as $user) {
                $objPHPExcel->setActiveSheetIndex(0)
                ->setCellValue('C4',''.$user->idnumber)
                ->setCellValue('C5',''.$user->ngayhoc)
                ->setCellValue('C4',''.$user->tenmien.' - TP.'.$user->tenkhuvuc.' - '.$user->tenquanhuyen.' - '.$user->tenxaphuong)
                ->setCellValue('C7',''.$user->trainer)
                ->setCellValue('A'.$i,$i-9)
                ->setCellValue('B'.$i,$user->uname)
                ->setCellValue('C'.$i,$user->username)
                ->setCellValue('D'.$i,'')
                ->setCellValue('F'.$i,'')
                ->setCellValue('G'.$i,'')
                ->setCellValue('H'.$i,'')
                ->setCellValue('I'.$i,'')
                ->setCellValue('J'.$i,'')
                ->setCellValue('K'.$i,'')
                ->setCellValue('L'.$i,'');
                $i++;
            }
            $objPHPExcel->getActiveSheet()->duplicateStyle($objPHPExcel->getActiveSheet()->getStyle('B10'),'A10:L'.$i); $objPHPExcel->setActiveSheetIndex(0);
            header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
            //         header('Content-Disposition: attachment;filename="default.xlsx"');
            header('Content-Disposition: attachment;filename="'.$user->idnumber.'".xlsx"');
            header('Cache-Control: max-age=0');
            header('Cache-Control: max-age=1');
            header ('Expires: Mon, 26 Jul 1997 05:00:00 GMT'); // Date in the past
            header ('Last-Modified: '.gmdate('D, d M Y H:i:s').' GMT'); // always modified
            header ('Cache-Control: cache, must-revalidate'); // HTTP/1.1
            header ('Pragma: public'); // HTTP/1.0
        }
        $objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007');
        $objWriter->save('php://output');
        exit;
        redirect(new moodle_url("/phlcohort/user.php?id=$cohortid"));
    }
