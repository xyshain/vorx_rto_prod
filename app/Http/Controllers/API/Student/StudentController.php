<?php

namespace App\Http\Controllers\API\Student;

use App\Http\Controllers\Controller;
use App\Models\Student\Student;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class StudentController extends Controller
{
    //
    public function index($username){
        // $user = User::where('username',$username)->first();
        $user = Auth::user();
        $students = [];
        if($user->id == 1){
            $rstudent = Student::orderBy('id','desc')->get();
            foreach($rstudent as $student){
                $funded_course = $student->funded_course->load('course');
                $course = [];
                foreach($funded_course as $fcourse){
                    $status_color = 'positive';
                    if($fcourse->status_id == 1){
                        $status_color = 'warning';
                    }elseif($fcourse->status_id == 2 || $fcourse->status_id == 3 || $fcourse->status_id == 4){
                        $status_color = 'positive';
                    }elseif($fcourse->status_id == 6){
                        $status_color = 'info';
                    }else{
                        $status_color = 'negative';
                    }
                    if($fcourse->course  == null){
                        $course[] = [
                            'code' => $fcourse->course_code,
                            'name' => 'NO COURSE NAME',
                            'status' => $fcourse->status->description,
                            'status_color' => $status_color
                        ];
                    }else{
                        $course[] = [
                            'code' => $fcourse->course_code,
                            'name' => $fcourse->course->name,
                            'status' => $fcourse->status->description,
                            'status_color' => $status_color
                        ];

                    }
                }
                $students[] = [
                    'student_id' => $student->student_id,
                    'name'=> $student->party->name,
                    'type'=> $student->type->type,
                    'courses' => $course
                ];
            } 
        }else{
            $agent = $user->agent_details;
            $offer_letter = $agent->offer_letters->load('student');
            
            foreach($offer_letter as $key=>$offer){
                $funded_course = $offer->student->funded_course->load('course');
                $course = [];
                foreach($funded_course as $fcourse){
                    $status_color = 'positive';
                    if($fcourse->status_id == 1){
                        $status_color = 'warning';
                    }elseif($fcourse->status_id == 2 || $fcourse->status_id == 3 || $fcourse->status_id == 4){
                        $status_color = 'positive';
                    }else{
                        $status_color = 'negative';
                    }
                    if($fcourse->course  == null){
                        $course[] = [
                            'code' => $fcourse->course_code,
                            'name' => 'NO COURSE NAME',
                            'status' => $fcourse->status->description,
                            'status_color' => $status_color
                        ];
                    }else{
                        $course[] = [
                            'code' => $fcourse->course_code,
                            'name' => $fcourse->course->name,
                            'status' => $fcourse->status->description,
                            'status_color' => $status_color
                        ];

                    }
                }
                $students[] = [
                    'student_id' => $offer->student->student_id,
                    'name'=> $offer->student->party->name,
                    'type'=> $offer->student->type->type,
                    'courses' => $course
                ];
            }
        }
        
        return $students;
    }

    public function show(Student $student){
        // $data = [];
        $person = $student->party->person;
        $funded_detail = $student->funded_detail()->select([
                    'unique_student_id',
                    'highest_school_level_completed_id',
                    'indigenous_status_id',
                    'location',
                    'language_id',
                    'labour_force_status_id',
                    'country_id',
                    'disability_flag',
                    'disability_ids',
                    'disability_ids',
                    'prior_educational_achievement_ids',
                    'at_school_flag',
                    'institute',
                    'statistical_area_level_1_id',
                    'statistical_area_level_2_id',
                    'aiss_check_date',
                    'year_completed',
                    'verified',
                    'verified_by',
                    'verified_date',
                ])->first();
        $contact_detail = $student->contact_detail()->select([
                    'addr_suburb',
                    'addr_postal_delivery_box',
                    'addr_street_name',
                    'addr_street_num',
                    'addr_flat_unit_detail',
                    'addr_building_property_name',
                    'postcode',
                    'state_id',
                    'phone_home',
                    'phone_work',
                    'phone_mobile',
                    'email',
                    'email_at',
                    "emer_name",
                    "emer_address",
                    "emer_telephone",
                    "emer_relationship"
                ])->first();
        $visa = $student->visa_details()->select([
                'nationality',
                'passport_number',
                'issue_date',
                'expiry_date',
                'visa_type',
                'subclass',
                'expiry_date_visa_type',
                'applied_for_au_residency',
                'study_rights'
            ])->first();

        $course = $student->funded_course;

        $data = [
            'firstname'     => $person->firstname,
            'middlename'    => $person->middlename,
            'lastname'      => $person->lastname,
            'dob'           => Carbon::parse($person->date_of_birth)->format('d/m/Y'),
            'gender'        => $person->gender,
            'prefix'        => $person->prefix,
            'funded_detail' => $funded_detail,
            'contact'       => $contact_detail,
            'visa'          => $visa,
            'course'        => $course
        ];
        return $data;
    }

}
