<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\AgentTempUpdate;
use App\Models\AvtPostcode;
use App\Models\AvtStateIdentifier;
use App\Models\Student\Student;
use App\Models\Student\Party;
use App\Models\FundedStudentCourse;
use App\Models\FundedStudentPaymentDetails;
use App\Models\CommissionDetail;
use App\Models\Audit;
use App\Models\User;
use App\Models\OfferLetterStatus;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    //
    public function __construct(){
        $this->middleware('auth');
    }

    public function index(){
        // $user = User::where('username',$username)->first();
        $user = \Auth::user();
        $students = [];
        $earnings = 0;
        $ctr = 1;
        if($user->id == 1){
            $funded_course = FundedStudentCourse::with('student','course','payment_details','payment_sched','offer_detail','status')->orderBy('id','desc')->get();
            $earnings = FundedStudentPaymentDetails::where('verified', 1)->sum('pre_deduc_comm');
            $total_students = Student::count();
        }else{
            $agent = $user->agent_details;
            $funded_course = $agent->funded_course->load(['student','course','payment_details','payment_sched','offer_detail','status'])->sortByDesc('id');
            $earnings = FundedStudentPaymentDetails::where('user_id', $agent->user_id)->where('verified', 1)->sum('pre_deduc_comm');
            $arr_students = [];
            foreach($funded_course as $fc){
                $agent_com = $fc->payment_details->where('verified', 1)->sum('pre_deduc_comm');
                array_push($arr_students, $fc->student_id);
            }
            $total_students = count(array_unique($arr_students));
        }
        // dd($earnings);
        $funded_course_ = $funded_course->whereIn('status_id', [2,3,4]);
        foreach($funded_course_ as $fcourse){
            $balance = 0;
            $balance = (float)$fcourse->course_fee - $fcourse->payment_details->sum('amount');

            $status_color = 'positive';
            if($fcourse->status_id == 1){
                $status_color = 'warning';
            }elseif($fcourse->status_id == 2 || $fcourse->status_id == 3 || $fcourse->status_id == 4){
                $status_color = 'positive';
            }else{
                $status_color = 'negative';
            }

            $payments = $this->payments($fcourse);
            $course = [];
            if($fcourse->course  == null){
                if($fcourse->course_code == '@@@@'){
                    $course[] = [
                        'item' => $ctr++,
                        'code' => $fcourse->course_code,
                        'name' => 'Unit of Compentency',
                        'course_fee_type' => $fcourse->course_fee_type,
                        'status' => $fcourse->status->description,
                        'status_color' => $status_color,
                        'payment_details' => number_format($fcourse->payment_details->sum('amount'),2),
                        'course_fee' => $fcourse->course_fee,
                        'balance' => $balance > 0 ? number_format($balance,2) : 0.00,
                        'current_payment_sched' => $payments['current_sched'],
                        'payment_plan' => $payments['payment_plan'],
                        
                    ];
                }else{
                    $course[] = [
                        'item' => $ctr++,
                        'code' => $fcourse->course_code,
                        'name' => 'NO COURSE ENROLLED',
                        'course_fee_type' => $fcourse->course_fee_type,
                        'status' => $fcourse->status->description,
                        'status_color' => $status_color,
                        'payment_details' => number_format($fcourse->payment_details->sum('amount'),2),
                        'course_fee' => $fcourse->course_fee,
                        'balance' =>  $balance > 0 ? number_format($balance,2) : 0.00,
                        'current_payment_sched' => $payments['current_sched'],
                        'payment_plan' => $payments['payment_plan'],
                    ];
                }
            }else{
                $course[] = [
                    'item' => $ctr++,
                    'code' => $fcourse->course_code,
                    'name' => $fcourse->course->name,
                    'course_fee_type' => $fcourse->course_fee_type,
                    'status' => $fcourse->status->description,
                    'status_color' => $status_color,
                    'payment_details' => number_format($fcourse->payment_details->sum('amount'),2),
                    'course_fee' => $fcourse->course_fee,
                    'balance' =>  $balance > 0 ? number_format($balance,2) : 0.00,
                    'current_payment_sched' => $payments['current_sched'],
                    'payment_plan' => $payments['payment_plan'],
                ];

            }

            if($balance > 0){
                $students[] = [
                    'student_id' => $fcourse->student->student_id,
                    'name'=> $fcourse->student->party->name,
                    'type'=> $fcourse->student->type->type,
                    'courses' => $course,
                ];
            }
                    
        }
        
        $dashboard  = [
            'total_students'=> $total_students,
            'commission_earnings' => $earnings > 0 ? number_format($earnings,2) : 0.00,
            'studentList'   => $students,
            'activities'    => $this->activities_details(),
            'total_activities'    => count($this->activities_details()),
        ];

       return $dashboard;
    }


    public function activities_details()
    {

        $logout = (isset($_SERVER['HTTPS']) ? "https" : "http") . "://$_SERVER[HTTP_HOST]/user/logout";
        $audits = Audit::with('user.party.person')->select(DB::raw('event, user_id, auditable_id, auditable_type, old_values, new_values, url, MINUTE(created_at) AS created_min, created_at'))->where('url', '!=', $logout)->groupBy('event', 'url', 'user_id', 'created_min')->orderBy('id', 'desc')->limit(20)->get();

        $activity = [];
        foreach ($audits as $k => $v) {
            // dump($v);
            $url = explode('/', $v->url);
            $dname = '';
            $student_details = [];
            
            // Student Module Audits
            if($url[3] == 'student'){
                
                $student_status = '';
                $studentID = '';
                $audit_type = '';

                $old_v = json_decode($v->old_values, true);
                $new_v = json_decode($v->new_values, true);
                if(isset($old_v['status_id'])){
                    if(isset($new_v['status_id'])){
                        $status = OfferLetterStatus::where('id',$new_v['status_id'])->first();
                        $student_status = $status->description;
                    }
                }

                $stud_id = '';
                if($v->auditable_type == 'App\Models\FundedStudentCourse' || $v->auditable_type == 'App\Models\StudentCompletionDetail'){
                    $audit_type = 'Course';
                }elseif($v->auditable_type == 'App\Models\FundedStudentPaymentDetails'){
                    $audit_type = 'Payment';
                }elseif($v->auditable_type == 'App\Models\Student\Student'){
                    $audit_type = 'Student';
                    if(isset($new_v['student_id'])){
                        $stud_idx = Student::where('student_id', $new_v['student_id'])->first();
                        $studentID = $stud_idx->id;
                    }
                }

                $student = Student::with('party.person')->where('id', isset($url[5]) ? $url[5] : $studentID)->first();
                if($student !== null){
                    $student_details = [
                        'student_id' => $student->student_id,
                        'name' => isset($student->party->name) ? $student->party->name : '',
                        'firstname' => $student->party->person->firstname,
                        'lastname' => $student->party->person->lastname,
                        'status' => $student_status,
                        'audit_type' => $audit_type
                    ];
                }
            }
            // dump(count($student_details));
            // Student Module Audits
            // if (isset($v->user->party->person) && $v->user->id != 1) {
                if (isset($v->user->party->person) && $url[3] == 'student'){
                    
                array_push($activity, [
                    'name' => isset($v->user->party->name) ? $v->user->party->name : '',
                    'firstname' => $v->user->party->person->firstname,
                    'lastname' => $v->user->party->person->lastname,
                    'avatar' => '/storage/user/avatars/' . $v->user->profile_image,
                    'url' => $url,
                    'old_values' => $old_v,
                    'new_values' => $new_v,
                    'created_at' => Carbon::parse($v->created_at)->format('Y-m-d H:m:s'),
                    'created_min' =>  Carbon::parse($v->created_at)->diffForHumans(),
                    'event' => $v->event,
                    'dname' => $dname,
                    'student' => $student_details,
                ]);
            }
            // }

        }
        // dd($activity);
        return $activity;
    }

    public function notifications(){

        $logout = (isset($_SERVER['HTTPS']) ? "https" : "http") . "://$_SERVER[HTTP_HOST]/user/logout";
        $audits = Audit::with('user.party.person')->select(DB::raw('event, user_id, auditable_id, auditable_type, old_values, new_values, url, MINUTE(created_at) AS created_min, created_at'))->where('url', '!=', $logout)->groupBy('event', 'url', 'user_id', 'created_min')->orderBy('id', 'desc')->limit(20)->get();

        $notification = [];
        foreach ($audits as $k => $v) {
            
            $url = explode('/', $v->url);
            $dname = '';
            $student_details = [];

            // Student Module Audits
            if($url[3] == 'student'){

                $student_status = '';
                $studentID = '';
                $audit_type = '';
                $collection_status = "";
                

                $old_v = json_decode($v->old_values, true);
                $new_v = json_decode($v->new_values, true);
                if(isset($old_v['verified'])){
                    if(isset($new_v['verified'])){
                        if($new_v['verified'] == 1){
                            $collection_status = 'Approved';
                        }elseif($new_v['verified'] == 0){
                            $collection_status = 'Disapproved';
                        }
                    }
                }
                
                if($v->auditable_type == 'App\Models\FundedStudentPaymentDetails'){
                    $audit_type = 'Payment';
                }
                if(isset($new_v['student_id'])){
                    $stud_idx = Student::where('student_id', $new_v['student_id'])->first();
                    $studentID = $stud_idx->id;
                }
                $student = Student::with('party.person')->where('id',isset($url[5]) ? $url[5] : $studentID)->first();
                if($student !== null){
                    $student_details = [
                        'student_id' => $student->student_id,
                        'name' => isset($student->party->name) ? $student->party->name : '',
                        'firstname' => $student->party->person->firstname,
                        'lastname' => $student->party->person->lastname,
                        'audit_type' => $audit_type,
                        'collection_status' => $collection_status,
                    ];
                }

                // with collection updates only
                if (isset($v->user->party->person) && count($student_details) > 0){
                    if($student_details['collection_status'] !== ''){
                        array_push($notification, [
                            'name' => isset($v->user->party->name) ? $v->user->party->name : '',
                            'firstname' => $v->user->party->person->firstname,
                            'lastname' => $v->user->party->person->lastname,
                            'avatar' => '/storage/user/avatars/' . $v->user->profile_image,
                            'url' => $url,
                            'old_values' => $old_v,
                            'new_values' => $new_v,
                            'created_at' => Carbon::parse($v->created_at)->format('Y-m-d H:m:s'),
                            'created_min' =>  Carbon::parse($v->created_at)->diffForHumans(),
                            'event' => $v->event,
                            'dname' => $dname,
                            'student' => $student_details,
                        ]);
                    }
                }
            }
        }
        // dd($notification);
        return $notification;
    }

    public function top_search($search){
        $studs = [];
        // $user = User::where('username', $username)->first();
        $user = \Auth::user();
        $user_id = $user->id;
        if ($user->hasRole('Agent')) {
            $agent = $user->agent_details; 
            $agent_id = $agent->id;
            $students = Student::with('funded_course','party.person')->has('funded_course')->where('student_id', 'like', '%' . $search . '%')->orWhereHas('party.person', function ($q) use ($search) {
                $q->where('firstname', 'like', '%'.$search.'%'); // student name
            })->orWhereHas('party', function($q) use ($search) {
                 $q->where('name', 'LIKE', '%'. $search . '%'); // student name
            })->whereHas('funded_course', function ($q) use ($agent_id) {
                $q->where('agent_id', $agent_id);
            })->get();
            
            // students with agent's ID in funded course only
            foreach ($students as $stud) {
                if(count($stud->funded_course) > 0){
                    foreach($stud->funded_course as $fc){
                        if($fc->agent_id == $agent_id){
                            $studs[] = [
                                'student_name' => $stud->student_id . ' - ' . $stud->party->name,
                                'name' => $stud->party->name,
                                'student_id' => $stud->student_id,
                                'student_type' => $stud->student_type_id,
                                'student_table_id' => $stud->party->id,
                            ];
                        }
                    }
                }
            }
            $studs = array_map("unserialize", array_unique(array_map("serialize", $studs))); // student has many course
        } else {
            $students = Student::with('funded_course', 'party.person')->has('funded_course')->where('student_id', 'like', '%' . $search . '%')->orWhereHas('party', function($q) use ($search) {
                $q->where('name', 'LIKE', '%'. $search . '%');
            })->get();
            // all students
            foreach ($students as $stud) {
                $studs[] = [
                    'student_name' => $stud->student_id . ' - ' . $stud->party->name,
                    'name' => $stud->party->name,
                    'student_id' => $stud->student_id,
                    'student_type' => $stud->student_type_id,
                    'student_table_id' => $stud->party->id,
                ];
            }
        }

        return $studs;
    }

    public function payments($student_course){
        $funded_course = $student_course;
        $date_now = Carbon::now()->setTimezone('Australia/Melbourne')->format('d/m/Y');
        $current_sched = [];
        $payment_plan = [];
        $pl = [];
        $cp = [];
        $ctr = 1;

        $due_dates_arr = [];
        foreach($funded_course->payment_sched as $key => $psched){

            $balance = 0;
            $balance = (float)$psched->payable_amount - $psched->amount_paid;

            $pd = [];
            foreach($psched->payment_detail as $payment_detail){
                
                $pd[] = [
                    'id' => $payment_detail->id,  
                    'transaction_code' => $payment_detail->transaction_code,  
                    'payment_date' =>  Carbon::parse($payment_detail->payment_date)->format('d/m/Y'),  
                    'amount' => $payment_detail->amount,  
                    'pre_deduc_comm' => $payment_detail->pre_deduc_comm,  
                    'verified' => $payment_detail->verified,  
                    'note' => $payment_detail->note,  
                ];
            }

            $pl[]= [
                'number'             => $ctr++,
                'id'                 => $psched->id,
                'name'               => $psched->id,
                'due_date'           => Carbon::parse($psched->due_date)->format('d/m/Y'),
                'adjusted_date'      => $psched->adjusted_date != '' ? Carbon::parse($psched->adjusted_date)->format('d/m/Y') : null,
                'payable_amount'     => $psched->payable_amount,
                'payment_details'    => $pd,
                'total_paid'         => $psched->amount_paid,
                'balance'            => $balance,
            ];
            
            //get priority payment sched
            $due_date = Carbon::parse($psched->due_date)->format('d/m/Y');
            if($date_now >= $due_date && $psched->amount_paid < $psched->payable_amount){
                array_push($due_dates_arr, $due_date);
                $next_sched = $this->find_closest($due_dates_arr, $date_now);
                if($next_sched == $due_date){
                    $cp = [
                        'number'             => $ctr++,
                        'id'                 => $psched->id,
                        'name'               => $psched->id,
                        'due_date'           => Carbon::parse($psched->due_date)->format('d/m/Y'),
                        'adjusted_date'      => $psched->adjusted_date != '' ? Carbon::parse($psched->adjusted_date)->format('d/m/Y') : null,
                        'payable_amount'     => $psched->payable_amount,
                        'payment_details'    => $pd,
                        'total_paid'         => $psched->amount_paid,
                        'balance'            => $balance,
                    ];  
                } 
            }
        }

        $data = [
            'payment_plan' => $pl,
            'current_sched' => $cp
        ];

        return $data;
    }

    public function find_closest($array, $date){
        //$count = 0;
        foreach($array as $day){
            //$interval[$count] = abs(strtotime($date) - strtotime($day));
            $interval[] = abs(strtotime($date) - strtotime($day));
            //$count++;
        }
    
        asort($interval);
        $closest = key($interval);
        // dump($array[$closest]);
        return $array[$closest];
    }
}
