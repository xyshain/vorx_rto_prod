<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\AgentTempUpdate;
use App\Models\AvtPostcode;
use App\Models\AvtStateIdentifier;
use App\Models\Student\Student;
use App\Models\FundedStudentCourse;
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
    // public function __construct(){
    //     $this->middleware('auth');
    // }

    public function index(){
        // $user = User::where('username',$username)->first();
        $user = \Auth::user();
        // dd($user);
        $students = [];
        $earnings = 0;
        if($user->id == 1){
            $fc = FundedStudentCourse::with('student','course')->orderBy('id','desc')->get();
            $earnings = CommissionDetail::all()->sum('commission_payable');
            $total_student = $fc->count();
            $funded_course = $fc->whereIn('status_id', [2,3]);
        }else{
            $agent = $user->agent_details;
            $commissions = $agent->commissions->load('commission_details');
            foreach($commissions as $com){
                $earnings = $com->commission_details->sum('commission_payable');
            }
            $total_student = $agent->funded_course->count();
            $funded_course = $agent->funded_course->whereIn('status_id', [2,3])->load('student','course');    
        }
        // dd($earnings);
        foreach($funded_course as $fcourse){

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

            $course = [];
            if($fcourse->course  == null){
                if($fcourse->course_code == '@@@@'){
                    $course[] = [
                        'code' => '',
                        'name' => 'Unit of Compentency',
                        'status' => $fcourse->status->description,
                        'status_color' => $status_color,
                        'payment_details' => number_format($fcourse->payment_details->sum('amount'),2),
                        'course_fee' => $fcourse->course_fee,
                        'balance' => $balance > 0 ? number_format($balance,2) : 0.00
                        
                    ];
                }else{
                    $course[] = [
                        'code' => $fcourse->course_code,
                        'name' => 'NO COURSE NAME',
                        'status' => $fcourse->status->description,
                        'status_color' => $status_color,
                        'payment_details' => number_format($fcourse->payment_details->sum('amount'),2),
                        'course_fee' => $fcourse->course_fee,
                        'balance' =>  $balance > 0 ? number_format($balance,2) : 0.00
                    ];
                }
            }else{
                $course[] = [
                    'code' => $fcourse->course_code,
                    'name' => $fcourse->course->name,
                    'status' => $fcourse->status->description,
                    'status_color' => $status_color,
                    'payment_details' => number_format($fcourse->payment_details->sum('amount'),2),
                    'course_fee' => $fcourse->course_fee,
                    'balance' =>  $balance > 0 ? number_format($balance,2) : 0.00
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
            'total_students'=> $total_student,
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
}
