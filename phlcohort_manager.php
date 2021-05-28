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

require('../config.php');
require($CFG->dirroot.'/cohort/lib.php');
require($CFG->dirroot.'/phlcohort/lib.php');
require($CFG->dirroot.'/phlcohort/search_form_manager.php');
require_once($CFG->libdir.'/adminlib.php');

$contextid = optional_param('contextid', 1, PARAM_INT);
$page = optional_param('page', 0, PARAM_INT);
$searchquery  = optional_param('search', '', PARAM_RAW);
$showall = optional_param('showall', true, PARAM_BOOL);

require_login();

if ($contextid) {
    $context = context::instance_by_id($contextid, MUST_EXIST);
} else {
    $context = context_system::instance();
}

if ($context->contextlevel != CONTEXT_COURSECAT and $context->contextlevel != CONTEXT_SYSTEM) {
    print_error('invalidcontext');
}

$category = null;
if ($context->contextlevel == CONTEXT_COURSECAT) {
    $category = $DB->get_record('course_categories', array('id'=>$context->instanceid), '*', MUST_EXIST);
}

$manager = has_capability('moodle/cohort:manage', $context);
$canassign = has_capability('moodle/cohort:assign', $context);
if (!$manager) {
    require_capability('moodle/cohort:view', $context);
}

$strcohorts = get_string('cohorts', 'cohort');

if ($category) {
    $PAGE->set_pagelayout('admin');
    $PAGE->set_context($context);
    $PAGE->set_url('/phlcohort/manager.php', array('contextid'=>$context->id));
    $PAGE->set_title($strcohorts);
    $PAGE->set_heading($COURSE->fullname);
    $showall = false;
} else {
    admin_externalpage_setup('cohorts', '', null, '', array('pagelayout'=>'report'));
}

echo $OUTPUT->header();

$dateValue =date("Y-m-d");
$day = date_parse($dateValue)['day'];
$yr = date_parse($dateValue)['year'];
$mon = date_parse($dateValue)['month'];
$arrayTu = array('year' =>$yr,'month' =>$mon,'day' =>$day);
$mon=$mon+1;
if($mon==13)
{
    $mon=1;
    $yr=$yr+1;
}
$arrayDen = array('year' =>$yr,'month' =>$mon,'day' =>$day);

$searchquery=array(
    'c.idnumber' => optional_param('idnumber','', PARAM_TEXT),
    'ngayhoctu'=>optional_param_array('ngayhoctu', $arrayTu, PARAM_INT),
    'ngayhocden'=>optional_param_array('ngayhocden', $arrayDen, PARAM_INT)
    //'mien' => optional_param('mien',0, PARAM_INT),
//'qh.khuvuc' => optional_param('khuvuc',0, PARAM_INT),
  //  'ch.khoahoc' => optional_param('khoahoc',0, PARAM_INT)
    
);

$searchkhuvuc = array('qh.khuvuc' => optional_param('khuvuc',0, PARAM_INT));
$searchkhoahoc = array('ch.khoahoc' => optional_param('khoahoc',0, PARAM_INT));
if ($showall) {
    $cohorts = cohort_get_all_phl_cohorts_replica($page, 125, $searchquery,$searchkhoahoc,$searchkhuvuc);
} else {
    ;//$cohorts = cohort_get_cohorts($context->id, $page, 125, $searchquery);
}

$count = '';
if ($cohorts['allcohorts'] > 0) {
    if ($searchquery === '') {
        $count = ' ('.$cohorts['allcohorts'].')';
    } else {
        $count = ' ('.$cohorts['totalcohorts'].'/'.$cohorts['allcohorts'].')';
    }
}

echo $OUTPUT->heading(get_string('cohortsin', 'cohort', $context->get_context_name()).$count);

$params = array('page' => $page);
if ($contextid) {
    $params['contextid'] = $contextid;
}
if ($searchquery) {
    ;//$params['search'] = $searchquery;
}
if ($showall) {
    $params['showall'] = true;
}
$baseurl = new moodle_url('/phlcohort/manager.php', $params);

if ($editcontrols = cohort_edit_controls_phl($context, $baseurl)) {
    echo $OUTPUT->render($editcontrols);
}



$khuvuc = $DB->get_records_sql('select kv.id,mi.tenmien,kv.tenkhuvuc from {cohortphl_khuvuc} kv,{cohortphl_mien} mi where kv.mien=mi.id');
//$mien = $DB->get_records_sql('select * from {cohortphl_mien}');
$thangnam = $DB->get_records_sql("select thangnam,'Tháng ' + SUBSTRING(CONVERT(varchar(50),thangnam),5,2) + ' - Năm ' + SUBSTRING(CONVERT(varchar(50),thangnam),1,4) as mieutathangnam from {cohortphl} group by thangnam");
$allcourses = coursecat::get(0)->get_courses(array('recursive' => true));


$editform = new cohort_search_form(null, array('data'=>null,'khuvuc'=>$khuvuc,'courses'=>$allcourses,'thangnam'=>$thangnam));


$from=strtotime($searchquery['ngayhoctu']['year'].'-'.$searchquery['ngayhoctu']['month'].'-'.$searchquery['ngayhoctu']['day']);
$to=strtotime($searchquery['ngayhocden']['year'].'-'.$searchquery['ngayhocden']['month'].'-'.$searchquery['ngayhocden']['day']);

echo $editform->display();


// Output pagination bar.
echo $OUTPUT->paging_bar($cohorts['totalcohorts'], $page, 125, $baseurl);

$data = array();
$editcolumnisempty = true;
foreach($cohorts['cohorts'] as $cohort) {
    $line = array();
    $cohortcontext = context::instance_by_id($cohort->contextid);
    $line[] ="<span style='white-space: nowrap;'>".format_text($cohort->idnumber,$cohort->descriptionformat)."</span>";
    //$line[] = format_text($cohort->name,$cohort->descriptionformat);
    $line[] ="<span style='white-space: nowrap;'>".format_text($cohort->fullname,$cohort->descriptionformat)."</span>";
    $line[] = date("d/m/Y",$cohort->ngayhoc);
    $line[] = format_text($cohort->sonha." - ".$cohort->tenquanhuyen." - ".$cohort->tenkhuvuc,$cohort->descriptionformat);
    
    $color="";
    if(isset($cohort->trainer))
        $color="grey";
        
        $count_members=$DB->count_records('cohort_members', array('cohortid'=>$cohort->id));
        $urlparams=array('id' =>$cohort->id);
        $line[] = html_writer::link(new moodle_url('user.php', $urlparams),
            $count_members,
            array('title' => "Danh Sách Đăng Ký",'style'=>'font-weight:bold;color:'. $color.';'));
        
        
        /*
         if (empty($cohort->component)) {
         $line[] = get_string('nocomponent', 'cohort');
         } else {
         $line[] = get_string('pluginname', $cohort->component);
         }
         */
        
        $buttons = array();
        if (empty($cohort->component)) {
            $cohortmanager = has_capability('moodle/cohort:manage', $cohortcontext);
            $cohortcanassign = has_capability('moodle/cohort:assign', $cohortcontext);
            
            $urlparams = array('id' => $cohort->id, 'returnurl' => $baseurl->out_as_local_url());
            $showhideurl = new moodle_url('/phlcohort/edit.php', $urlparams + array('sesskey' => sesskey()));
            if ($cohortmanager) {
                /*
                 if ($cohort->visible) {
                 $showhideurl->param('hide', 1);
                 $visibleimg = $OUTPUT->pix_icon('t/hide', get_string('hide'));
                 $buttons[] = html_writer::link($showhideurl, $visibleimg, array('title' => get_string('hide')));
                 } else {
                 $showhideurl->param('show', 1);
                 $visibleimg = $OUTPUT->pix_icon('t/show', get_string('show'));
                 $buttons[] = html_writer::link($showhideurl, $visibleimg, array('title' => get_string('show')));
                 }
                 */
                if($count_members==0)
                {
                    $buttons[] = html_writer::link(new moodle_url('/phlcohort/edit.php', $urlparams + array('delete' => 1)),
                        $OUTPUT->pix_icon('t/delete', get_string('delete')),
                        array('title' => get_string('delete')));
                }
                $buttons[] = html_writer::link(new moodle_url('/phlcohort/edit.php', $urlparams),
                    $OUTPUT->pix_icon('t/edit', get_string('edit')),
                    array('title' => get_string('edit')));
                $editcolumnisempty = false;
            }
            if ($cohortcanassign) {
                $buttons[] = html_writer::link(new moodle_url('/phlcohort/assign.php', $urlparams),
                    $OUTPUT->pix_icon('i/users', get_string('assign', 'core_cohort')),
                    array('title' => get_string('assign', 'core_cohort')));
                //download button
                /*
                 $urlparams = array('cohortid' => $cohort->id, 'course' => 24);
                 $buttons[] = html_writer::link(new moodle_url('/report/completion/index.php', $urlparams),
                 $OUTPUT->pix_icon('i/user', "Kết quả học"),
                 array('title' => "Kết quả học"));*/
                $editcolumnisempty = false;
            }
        }
        $line[] = implode(' ', $buttons);
        
        $data[] = $row = new html_table_row($line);
        if (!$cohort->visible) {
            $row->attributes['class'] = 'dimmed_text';
        }
}


$table = new html_table();
$table->head  = array("Mã Lớp","Khóa Học", "Ngày Học","Địa Điểm","<span style='white-space: nowrap;'>Đã Đăng Ký</span>");
//$table->colclasses = array('leftalign name', 'leftalign id', 'leftalign description', 'leftalign size','centeralign source');
/*if ($showall) {
 array_unshift($table->head, get_string('category'));
 array_unshift($table->colclasses, 'leftalign category');
 }*/
if (!$editcolumnisempty) {
    $table->head[] = get_string('edit');
    $table->colclasses[] = 'centeralign action';
} else {
    // Remove last column from $data.
    foreach ($data as $row) {
        array_pop($row->cells);
    }
}
$table->id = 'cohorts';
$table->attributes['class'] = 'admintable generaltable';
$table->data  = $data;
echo html_writer::table($table);
echo $OUTPUT->paging_bar($cohorts['totalcohorts'], $page, 25, $baseurl);
echo $OUTPUT->footer();
