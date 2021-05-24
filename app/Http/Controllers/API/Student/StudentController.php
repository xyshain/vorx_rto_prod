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
                    'prior_educational_achievement_ids',
                    'prior_educational_achievement_flag',
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
        $funded_detail->disability_ids = $funded_detail->disabilityvalue;
        $funded_detail->prior_educational_achievement_ids = $funded_detail->prioreducationvalue;
        $funded_detail->language_id = $funded_detail->languageapi;
        $funded_detail->indigenous_status_id = $funded_detail->indigenous;
        $funded_detail->country_id = $funded_detail->country;
        $funded_detail->highest_school_level_completed_id = $funded_detail->highestschool;
        $funded_detail->labour_force_status_id = $funded_detail->labour;

        if($funded_detail->disability_flag == null){
            if($funded_detail->disability_ids == null){
                $funded_detail->disability_flag = false;
            }else{
                $funded_detail->disability_flag = true;
            }
            $funded_detail->disability_flag = false;
        }else{
            if($funded_detail->disability_flag == 'N'){
                $funded_detail->disability_flag = false;
            }else{
                $funded_detail->disability_flag = true;
            }
        }
        if($funded_detail->at_school_flag == null){
            $funded_detail->at_school_flag = false;
        }else{
            if($funded_detail->at_school_flag == 'N'){
                $funded_detail->at_school_flag = false;
            }else{
                $funded_detail->at_school_flag = true;
            }
        }
        if($funded_detail->prior_educational_achievement_flag == null){
            $funded_detail->prior_educational_achievement_flag = false;
        }else{
            if($funded_detail->prior_educational_achievement_flag == 'N'){
                $funded_detail->prior_educational_achievement_flag = false;
            }else{
                $funded_detail->prior_educational_achievement_flag = true;
            }
        }
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
        $contact_detail->addr_suburb = $contact_detail->suburb;
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
        $visa->visa_type = $visa->visavalue;
    
      

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
        ];
        return $data;
    }

    public function course(Student $student){
        
        $data = $student->load('funded_course.status','funded_course.offer_detail');
        $course = [];
        foreach($data->funded_course as $funded_course){
            $d = [
                'code' => $funded_course->course->code,
                'name' => $funded_course->course->name,
                'status' => $funded_course->status != null ?  $funded_course->status->description : '',
                'start_date' => Carbon::parse($funded_course->start_date)->format('d/m/Y'), 
                'end_date' => Carbon::parse($funded_course->end_date)->format('d/m/Y'), 
            ];
            array_push($course,$d);
           
        }
        return $course;
    }

}
