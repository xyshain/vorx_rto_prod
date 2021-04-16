<?php

namespace App\Http\Controllers\Reports;

use App\Http\Controllers\CertificateIssuance\CertificateIssuanceController;
use App\Http\Controllers\Controller;
use App\Http\Controllers\Send\EmailSendingController;
use App\Models\AgentDetail;
use Illuminate\Http\Request;
use Carbon\Carbon;

// VORX RTO MODELS
use App\Models\Student\Student;
use App\Models\Student\Party;
use App\Models\Student\Person;
use App\Models\FundedStudentCourse;
use App\Models\FundedStudentCourseDetail;
use App\Models\FundedStudentDetails;
use App\Models\FundedStudentContactDetails;
use App\Models\StateIdentifier;
use App\Models\StudentCompletion;
use App\Models\StudentCompletionDetail;
use App\Models\AvtSub;
use App\Models\TrainingDeliveryLoc AS TDL;
use App\Models\TrainingOrganisation AS TO;
use App\Models\Course as C;
use App\Models\CourseAvtDetail as CAD;
use App\Models\AvtUnitEducationField;
use App\Models\Course;
use App\Models\OfferLetterStatus;
use App\Models\StudentCertificateIssuance;
use App\Models\TrainingOrganisation;
use App\Models\Unit;
use App\Models\StudentClass;
use App\Models\User;
use App\Models\Attendance;

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use File;
use PhpOffice\PhpSpreadsheet\RichText\RichText;

class ReportsController extends Controller
{
    //
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index()
    {
        $courses = [];

        if(\Auth::user()->hasRole('Demo')){
            $courses =  Course::where('user_id', \Auth::user()->id)->get()->pluck('name','code');
        }else{
            $courses =  Course::all()->pluck('name','code');
        }
        $courses['*'] = 'All Courses';
        
        $agents = AgentDetail::all()->pluck('agent_name', 'id');
        $agents[0] = 'No Agent'; 
        $agents['^'] = 'All Agents'; 
        $agents['*'] = 'All Agents and No Agent'; 

        $statuses = OfferLetterStatus::all()->pluck('description', 'id');
        $statuses['*'] = 'All Status';
        
        \JavaScript::put([
            'student_status' => $statuses,
            'courses' => $courses,
            'agents' => $agents,
        ]);

        return view('reports.lists');
    }

    public function list_generator(Request $request)
    {
        // dd($request->all());

        if(count($request->enrolments) == 0){
            return ['status' => 0];
        }

        $enrolments = $request->enrolments;


        $headers = array_keys($enrolments[0]);

        $path = storage_path('app/excel');
        File::makeDirectory($path, $mode = 0777, true, true);

        // dd($path);

        $spreadsheet = new Spreadsheet();
        $spreadsheet->getDefaultStyle()->getFont()->setSize(11);
        $sheet = $spreadsheet->getActiveSheet();

        

        $richTitle = new RichText();
        $title = 'Enrolment List ( '. Carbon::createFromFormat('Y-m', $request->from)->format('M Y') . ' - '. Carbon::createFromFormat('Y-m', $request->to)->format('M Y'). ' )'; 
        $titleBolds = $richTitle->createTextRun($title);
        $titleBolds->getFont()->setBold(true)->setSize(26);
        // dump($richTitle);
        $sheet->setCellValue('A1', $richTitle);

        // headers
        $letter = 'A';
        foreach ($headers as $item){
            // $itm = $richText->createTextRun($item)->getFont()->setBold(true);
            $spreadsheet->getActiveSheet()->getColumnDimension($letter)->setAutoSize(true);
            $richText = new RichText();
            $bolds = $richText->createTextRun($item);
            $bolds->getFont()->setBold(true)->setSize(12);
            // dump($richText);
            $sheet->setCellValue($letter.'3', $richText);
            $letter++;
        }

        // Merge for Title
        $sheet->mergeCells('A1:'.$letter.'1');

        // body
        $number = 4;
        // for ($number = 1 ; count($enrolments) > $number ; $number++){
        foreach ($enrolments as $item){
            $letter = 'A';
            foreach ($item as $v){
                $var = $letter.''.$number;
                $sheet->setCellValue($var, $v);
                $letter++;
            }
            $number++;
        }
        // }
        
        
        $writer = new Xlsx($spreadsheet);
        
        $f = $path.'/Enrolment List.xlsx';

        $writer->save($f);

        // dd('exported');

        return [
            'status' => 1,
            'file' => 'Enrolment List.xlsx',
            'rename' => 'Enrolment List '. Carbon::createFromFormat('Y-m', $request->from)->format('M Y') . ' - '. Carbon::createFromFormat('Y-m', $request->to)->format('M Y'). '.xlsx'
        ];
        
        
        // header("Content-type: application/xls");
        // header('Content-Disposition: attachment; filename=Student_List.xls');
        // header("Pragma: no-cache"); 
        // header("Expires: 0");

        // dd('test');

        // $output = fopen("php://output", "w");
        // $header = array_keys($stud[0]);
        // fputcsv($output, $header);

        // foreach ($enrolments as $value) {

            

            // fputcsv($output, [
            //     'Serial No.' => $hosp['org_details']['serial_no'],
            //     'Hospital' => $hosp['party']['name'],
            //     'Category' => $hosp['org_details']['category_type'],
            //     'Director' => $reps,
            //     'Address' => $address,
            //     'Email' => $hosp['org_details']['email'],
            //     'Telephone' => $hosp['org_details']['tel_no'],
            //     'Fax' => $hosp['org_details']['fax']
            // ]);

            // fputcsv($output, $value);
            

        // }
        // fclose($output);


        // dd($enrolments);
        //
        // return view('reports.lists');
    }

    public function download($type,$file,$rename)
    {

        $path = storage_path('app/'.$type);

        $f = $path.'/'.$file;

        // dd($f);

        $filetype=filetype($f);
        $filename=basename($f);
        header ("Content-Type: ".$filetype);
        header ("Content-Length: ".filesize($f));
        header ("Content-Disposition: attachment; filename=".$rename);

        readfile($f);
    }
    

    public function student_list(Request $request)
    {
        
        if(!isset($request->automate) && \Auth::user()->hasRole('Demo')){
            // $students = Student::where('user_id', \Auth::user()->id)->where('student_type_id', $request->student_type)->get();
            $students = Student::where('user_id', \Auth::user()->id)->where('student_type_id', $request->student_type)->get();
        }else{
            // $students = Student::where('student_type_id', $request->student_type)->get();
            $students = Student::where('student_type_id', $request->student_type == '*' ? '!=' : '=', $request->student_type)->get();
        }

        // dump($students->toArray());
        $enrolments = [];
        
        if(!isset($request->from)){
            return $enrolments;
        }
        // dump($request->from.'-01');
        // dd(date($request->to.'-t'));
        // dd($request->all());

        // agent filter
        $agent = $request->get_agent == '0' ? null : $request->get_agent;

        // if($request->student_type == 2){
            foreach($students as $stud){
                foreach($stud->completion as $com){
                    // dump($request->get_course);
                    if($request->get_course == '*'){
                        // dump('dro');
                        // dump($request->get_agent);
                        // dd($agent);
                        if($agent == '*'){
                            $funded_course = FundedStudentCourse::with('course_details', 'status')->where('student_id', $stud->student_id)->where('course_code', $com->course_code)->where('status_id', $request->get_status == '*' ? '!=' : '=', $request->get_status)->whereBetween('start_date', [$request->from.'-01', date($request->to.'-t')])->first();
                        }else{
                            $funded_course = FundedStudentCourse::with('course_details', 'status')->where('student_id', $stud->student_id)->where('course_code', $com->course_code)->where('status_id', $request->get_status == '*' ? '!=' : '=', $request->get_status)->where('agent_id', $agent == '^' ? '!=' : '=', $agent)->whereBetween('start_date', [$request->from.'-01', date($request->to.'-t')])->first();
                        }
                    }else{
                        // dump('dri');
                        if($com->course_code == $request->get_course){
                            if($agent == '*'){
                                $funded_course = FundedStudentCourse::with('course_details', 'status')->where('student_id', $stud->student_id)->where('course_code', $request->get_course)->where('status_id', $request->get_status == '*' ? '!=' : '=', $request->get_status)->whereBetween('start_date', [$request->from.'-01', date($request->to.'-t')])->first();
                            }else{
                                $funded_course = FundedStudentCourse::with('course_details', 'status')->where('student_id', $stud->student_id)->where('course_code', $request->get_course)->where('status_id', $request->get_status == '*' ? '!=' : '=', $request->get_status)->where('agent_id', $agent == '*' ? '!=' : '=', $agent)->whereBetween('start_date', [$request->from.'-01', date($request->to.'-t')])->first();
                            }
                        }else{
                            $funded_course = null;
                        }
                    }

                    
                    // $funded_course = FundedStudentCourse::with('course_details', 'status')->where('student_id', $stud->student_id)->where('course_code', $com->course_code)->first();
                    
                    if($funded_course){
                        if($com->course_code != '1111'){
                            $completion_date = !in_array($com->completion_date, ['', null]) ? Carbon::createFromFormat('Y-m-d', $com->completion_date)->format('d/m/Y') : null;
                        }else{
                            $completion_date = !in_array($com->details[0]->completion_date, ['', null]) ? Carbon::createFromFormat('Y-m-d', $com->details[0]->completion_date)->format('d/m/Y') : null;
                        }
        
                        // dd();
        
                        $address = !in_array($stud->contact_detail->addr_building_property_name, ['', null]) ? $stud->contact_detail->addr_building_property_name.' ' : '';
                        $address .= !in_array($stud->contact_detail->addr_flat_unit_detail, ['', null]) ? $stud->contact_detail->addr_flat_unit_detail.' ' : '';
                        $address .= !in_array($stud->contact_detail->addr_street_num, ['', null]) ? $stud->contact_detail->addr_street_num.' ' : '';
                        $address .= !in_array($stud->contact_detail->addr_street_name, ['', null]) ? $stud->contact_detail->addr_street_name.' ' : '';
                        $address .= !in_array($stud->contact_detail->addr_suburb, ['', null]) ? $stud->contact_detail->addr_suburb.' ' : '';
                        $address .= isset($stud->contact_detail->state->state_key) && !in_array($stud->contact_detail->state->state_key, ['', null]) ? $stud->contact_detail->state->state_key.' ' : '';
                        $address .= !in_array($stud->contact_detail->postcode, ['', null]) ? $stud->contact_detail->postcode : '';
                        
                        $course_fee_type = '';
                        switch ($funded_course->course_fee_type) {
                            case 'FF':
                                $course_fee_type = 'Full Fee';
                                break;
                            case 'C':
                                $course_fee_type = 'Concessional';
                                break;
                            case 'NC':
                                $course_fee_type = 'Non-Concessional';
                                break;
                            default:
                                $course_fee_type = '';
                                break;
                        }

                        if($com->course_code == '1111'){
                            $course_code = $com->details[0]->course_unit_code;
                            $course_name = $com->details[0]->unit->description;
                        }elseif($com->course_code == '@@@@'){
                            if(isset($com->details[1])){
                                $course_code = '';
                                $course_name = 'Unit of Competency';
                                $course_name .= !in_array($funded_course->uc_description, ['', null]) ? ' - '.$funded_course->uc_description : '';
                            }else{
                                $course_code = $com->details[0]->course_unit_code;
                                $course_name = $com->details[0]->unit->description;
                            }
                        }else{
                            $course_code = $com->course_code;
                            $course_name = $com->course->name;
                        }

                        // get total payments
                        if($stud->student_type_id == 1 && isset($funded_course->payment_details[0])){
                            $payments = $funded_course->payment_details;
                        }elseif($stud->student_type_id != 1 && isset($funded_course->offer_detail->payments[0])){
                            $payments = $funded_course->offer_detail->payments;
                        }else{
                            $payments = null;
                        }
                        // $payments = $stud->student_type_id == 1 ? $funded_course->payment_details : $funded_course->offer_detail->payments;
                        $total_payments = 0;
                        if($payments != null){
                            foreach($payments as $pay){
                                $total_payments = $total_payments + $pay->amount;
                            }
                        }
    
                        $enrolments[] = [
                            'Student ID' => $stud->student_id,
                            'Reported?' => $stud->report_avetmiss == 1 ? 'Yes' : 'No',
                            'Student Type' => $stud->type->type,
                            'Given Name' => ucwords(strtolower($stud->party->person->firstname)),
                            'Last Name' => ucwords(strtolower($stud->party->person->lastname)),
                            'Date of Birth' => isset($stud->party->person->date_of_birth) && !in_array($stud->party->person->date_of_birth, ['', null]) ? Carbon::createFromFormat('Y-m-d', $stud->party->person->date_of_birth)->format('d/m/Y') : null,
                            'USI' => $stud->funded_detail->unique_student_id,
                            'AISS Date' => isset($funded_course->aiss_date) && !in_array($funded_course->aiss_date, ['', null]) ? Carbon::createFromFormat('Y-m-d', $funded_course->aiss_date)->format('d/m/Y')  : null, 
                            'Course Code' => $course_code,
                            'Course Name' => $course_name,
                            'Start Date' => isset($funded_course->start_date) && !in_array($funded_course->start_date, ['', null]) ? Carbon::createFromFormat('Y-m-d', $funded_course->start_date)->format('d/m/Y') : null,
                            'End Date' => isset($funded_course->end_date) && !in_array($funded_course->end_date, ['', null]) ? Carbon::createFromFormat('Y-m-d', $funded_course->end_date)->format('d/m/Y')  : null, 
                            // 'course_status' => $com->course_code != '1111' ? null : null,
                            'Course Fee Type' => $course_fee_type,
                            'Course Fee' => $funded_course->course_fee,
                            'Total Payments' => $total_payments,
                            'Remaining Balance Fee' => $funded_course->course_fee - $total_payments,
                            'State Funding Type' => isset($funded_course->detail->fund_state->description) ? $funded_course->detail->fund_state->description : '',
                            'Completion Date' => $completion_date,
                            'Mobile Number' => $stud->contact_detail->phone_mobile,
                            'Email' => $stud->contact_detail->email,
                            'Address' => $address,
                            'Postal Delivery Box' => $stud->contact_detail->addr_postal_delivery_box,
                            'Training Delivery Location' => isset($com->details[0]->training_delivery_location->train_org_dlvr_loc_name) ? $com->details[0]->training_delivery_location->train_org_dlvr_loc_name : '',
                            'Highest School Level Completed' => isset($stud->funded_detail->highest_school_level->description) ? $stud->funded_detail->highest_school_level->description : null,
                            'Language' => isset($stud->funded_detail->language->description) ? $stud->funded_detail->language->description : null,
                            'Labor Force' => isset($stud->funded_detail->labor_force->status) ? $stud->funded_detail->labor_force->status : null,
                            'Disability' => $stud->funded_detail->disability_value,
                            'Prior Education' => $stud->funded_detail->prior_education_value,
                            // 'person' => $stud->party->person,
                            // 'contact_detail' => $stud->contact_detail,
                            // 'funded_course' => $funded_course,
                            // 'funded_course_detail' => $funded_course->course_details,
                            // 'completion' => $com,
                            // 'completion_detail' => $com->details
                        ];
                    }
                    
                }
            }
        // }else{
            // dd($enrolments);
            // foreach($students as $stud){
            //     foreach($stud->completion as $com){
                    
            //     }
            // }
            // $enrolments = [];
        // }

        // dd('end');
        return $enrolments;
    }

    public function send_course_progress(Request $request)
    {
        // dd($request->course['id']);
        $org = TrainingOrganisation::first();
        
        if(!$org || in_array($org->email_address, ['', null])){
            return ['status' => 'error', 'message' => 'Organisation\'s Email Address must not be blank'];
        }

        if($request->type == 'Progress Report'){
            $certController = new CertificateIssuanceController;
            $prg = $certController->progress_report($request->student_id, $request->course['id'], 1);
            if($prg){
                $send = new EmailSendingController;

                $s = $send->send_automate('Progress Report', 'This is your Progress Report', [$org->training_organisation_name => $org->email_address], $request->email, [$prg['path']]);

                if($s['status'] == 'success'){
                    return ['status' => 'success'];
                }else{
                    return ['status' => 'error', 'message' => 'Failed sending report'];
                }

            }else {
                return ['status' => 'error', 'message' => 'No report found.'];
            }

        }
    }

    public function attendance(){
        $classes = StudentClass::orderBy('desc','asc')->get();
        // dd($classes);
        \JavaScript::put([
            'classes'=>$classes
        ]);
        return view('reports.attendance-list');
    }

    public function generate_attendance(Request $request){
        $class_id = $request->class_id;
        $from = $request->from;
        $to = $request->to;

        if($from==null && $to == null){
            try{
                $attendances = Attendance::with('student.party','attendance_details')->where('class_id',$class_id)->get();
                // return $attendances;
                $students = [];
                $pref_hours = 0;
                $actu_hours = 0;
                foreach($attendances as $att){
                    if(isset($att->student)&&$att->student!=null){
                        // dd($att,'naay unod ble');
                        $_user = User::where('username',$att->student_id)->first();
                        if(isset($_user)){
                            $att->user = $_user;
                        }else{
                            $att->user = null;
                        }
                        if(isset($att->attendance_details)){
                            foreach($att->attendance_details as $ad){
                                $pref_hours += $ad->preferred_hours;
                                $actu_hours += $ad->actual_hours;
                            }
                            $att->pref_hours = $pref_hours;
                            $att->actual_hours = $actu_hours;
                            if($pref_hours!=0){
                                $att->percent_actual_hours = $actu_hours / $pref_hours * 100;
                            }
                            // $att->percent_actual_hours = $pref_hours / $actu_hours *100;
                        }
                        array_push($students,$att);
                    }
                }
                // dd();
                // dd($attendances);
                // return $attendances;
                return response()->json(['status'=>'success','attendances'=>$students]);
            }catch(Exception $e){
                return response()->json(['status'=>'error','message'=>$e->getMessage()]);
            }
        }
    }
    // public function class_list(){
    //     $classes = 
    // }

}
