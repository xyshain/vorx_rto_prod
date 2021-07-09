<?php

namespace App\Http\Controllers\Agent;

use App\Http\Controllers\Controller;
use App\Models\AgentCommissionCutoff;
use App\Models\AgentDetail;
use App\Models\CommissionCutoff;
use App\Models\CommissionDetail;
use App\Models\TrainingOrganisation;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class CommissionController extends Controller
{
    //
    public function index(){
        $agents = AgentDetail::with('commission_settings.sub_settings.student', 'commission_settings.cutoff_period', 'commission_settings.sub_settings.student_course', 'commission_settings.sub_settings.cutoff_period')->has('commission_settings')->get();

        $list = [];

        foreach ($agents as $key => $agent) {
            $student_count = 0;
            $name = $agent->user->party->name;
            $list[$key]['Company'] = $name;
            $list[$key]['agent_name'] = $agent->agent_name;
            $list[$key]['agent_user'] = $agent->id;
         
            // dd($agent->commission_settings);
            foreach($agent->commission_settings as $com_key => $commission_setting){
                $student_count += $commission_setting->sub_settings->groupBy('student_id')->count();
                // dd($student_count);
                $cutop = explode(',', $commission_setting->cutoff_period->cutoff);
                // $cutoff = 15;
                $cutoffs = [$date_holder = date('Y-m-') . $cutop[0],$date_holder = date('Y-m-') . $cutop[1]];
                // $cutoffs = [$date_holder = date('Y-m-') . $cutop[0],$date_holder = date('Y-m-') . '10'];
                $now = Carbon::now()->format('d');
                // $now = 16;
                if ($now <= $cutop[0]) {
                    $serial = 'A' . $agent->id . '-' . date('ym') . $cutop[0];
                    $date_holder = date('Y-m-') . $cutop[0];
                    $cutoff = $cutop[0];
                    
                } else {
                    $serial = 'A' . $agent->id . '-' . date('ym') . $cutop[1];
                    $date_holder = date('Y-m-') . $cutop[1];
                    $cutoff = $cutop[1];
                }
                foreach($commission_setting->sub_settings as $subkey => $subsetting){
                    $student = $subsetting->student;
                    $funded_course = $subsetting->student_course;
                    // $funded_course = $student->funded_course()->where('course_code', $subsetting->course->code)->first();
                    $offer_details = $funded_course->offer_detail;
                    // $payment_details = $funded_course->payment_details()->get(['id as payment_id', 'payment_date', 'amount', 'pre_deduc_comm', 'comm_release_status', 'note'])->toArray();
                    $payment_details = $funded_course->payment_details()->where('verified',1)->doesntHave('commission')->get(['id as payment_id','payment_schedule_template_id as payment_sched_id','payment_date','amount','pre_deduc_comm','comm_release_status','note','verified'])->toArray();
                    $template_i = !$funded_course->payment_sched->isEmpty() ? $funded_course->payment_sched->first()->id : null;
                    $non_tuition = $offer_details->offer_letter->fees->application_fee + $offer_details->offer_letter->fees->materials_fee;
                    $nontuition = $non_tuition;
                    $com_holder = 0;
                    $payment_details = $this->payment_details($payment_details,$nontuition,$subsetting,$template_i);
                    $_payment_details = collect($payment_details);
                    $total = $_payment_details->sum('computed_commission');
                    $total_pre_deducted = $_payment_details->sum(function($payments){
                        if($payments['verified'] == 1){
                            return $payments['pre_deduc_comm'];
                        }
                    });
                    // 
                    // $now = Carbon::now()->format('d');
                    $studentDetails = [
                        'id' => $subsetting->id,
                        'student_id' => $student->student_id,
                        'name' => $student->party->name,
                        'dob' => $student->party->person->date_of_birth,
                        'code' => $funded_course->course->code.' - '.$funded_course->course->name,
                        'student_course_id' => $funded_course->id,
                        'course_start' => $funded_course->start_date,
                        'course_end' => $funded_course->end_date,
                        'course_end' => $funded_course->end_date,
                        'tuition' => $offer_details->offer_letter->fees->course_tuition_fee - $offer_details->offer_letter->fees->discounted_amount,
                        'non-tuition' => $non_tuition,
                        'status'    => $funded_course->status->description,
                        'commission_limit' => $subsetting->commission_limit,
                        'total_payable_commission' => round($_payment_details->sum('computed_commission')),
                        'actual_total_payable_commission' => round($_payment_details->where('comm_release_status','<>','2')->sum('actual_commission')),
                        'student_total_paid' => round($_payment_details->sum('amount')),
                        'total_pre_deducted' => $total_pre_deducted,
                        'gst_status' => $subsetting->gst_status,
                        'gst_type' => $subsetting->gst_type,
                        'payments' => $_payment_details,
                        
                    ];
                    $list[$key]['students'][] = $studentDetails;
                }
                $list[$key]['serial'] = $serial;
                $list[$key]['student_count'] = $student_count;
            }
            
        }
        $this->store_commission_details($list);
        dd($list);
    }

    public function store_cutoffs($data){
        try {
            DB::beginTransaction();
            $cutoff = explode('-',$data['serial']);
            $agent_comm = AgentCommissionCutoff::updateOrCreate(
                [
                    'agent_id' => $data['agent_user'],
                    'serial_no' => $data['serial']
                ],[
                    'cutoff' => Carbon::createFromFormat('ymd',$cutoff[1])->format('y-m-d')
                ]);
            // dump($agent_comm);
            DB::commit();
            return ['serial' => $data['serial'],'cutoff'=> $cutoff[1] ];
        } catch (\Throwable $th) {
            DB::rollback();
            throw $th;
        }
    }

    public function updateCutoff($serial,$overpayment){
        // dd($serial);
        $data = CommissionDetail::where('serial_no',$serial)->get();
        $summary = [
            'total_actual_amount_received' => $data->sum('actual_amount'),
            'total_computed_commission' => $data->sum('computed_commission') -$data->sum('pre_deducted_comission'),
            'due_comission' => $data->sum('commission_payable'),
            'total_pre_deducted_comission' => $data->sum('pre_deducted_comission'),
            'total_over_payment' => $overpayment
        ];
        try {
            DB::beginTransaction();
            AgentCommissionCutoff::updateOrCreate(
                [
                    'serial_no' => $serial
                ],
                $summary
            );
            DB::commit();
            dump($summary);
        } catch (\Throwable $th) {
            DB::rollback();
            throw $th;
        }
        // dump($summary);
    }

    public function store_commission_details($datas){
         try {
            DB::beginTransaction();
             foreach($datas as $data){
                 if(isset($data['students'])){
                    $agent_cutoff = $this->store_cutoffs($data);
                    $overpayment = 0;
                    foreach ($data['students'] as $student) {
                        $accumulated = $student['payments']->last();
                        $overpayment += $accumulated['accumulated'];
                        // dd(array_key_last($student['payments']));
                        foreach ($student['payments'] as $payment) {

                            $com_details = CommissionDetail::updateOrCreate(
                                [
                                    'serial_no' => $agent_cutoff['serial'],
                                    'payment_id' => $payment['payment_id'],

                                ],
                                [
                                    'agent_commission_settings_sub_id' => $student['id'],
                                    'cutoff'                    => Carbon::createFromFormat('ymd', $agent_cutoff['cutoff'])->format("Y-m-d"),
                                    'payment_date'              => $payment['payment_date'],
                                    'pre_deducted_comission'    => $payment['pre_deduc_comm'] == null ? 0.00 : $payment['pre_deduc_comm'],
                                    'computed_commission'       => $payment['computed_commission'],
                                    'commission_payable'        => $payment['actual_commission'],
                                    'comm_release_status'       => $payment['comm_release_status'],
                                    'amount_received'           => $payment['amount'],
                                    'actual_amount'             => $payment['actual_amount'],
                                    'accumulated'               => $payment['accumulated'],
                                    'payment_sched_id'          => $payment['payment_sched_id'],
                                ]
                            );
                            DB::commit();
                            // dump($com_details);
                        }
                    }
                    $this->updateCutoff($agent_cutoff['serial'], $overpayment);
                 }
             }
            // dd(Carbon::createFromFormat('ymd', $data)->format("Y-m-d"));
                
            } catch (\Throwable $th) {
                DB::rollback();
                throw $th;
            }
    }

    public function commission_not_registered($settings, $payment_details, $details, $com_holder,$total_deduct,$checker){
        if ($settings->commission_type == '%') {
            if ($settings->gst_status == 0) {
                $payment_details['computed_commission'] =  round($payment_details['actual_amount'] * ($settings->commision_value / 100), 2);
                if ($details['verified'] == 1) {
                    if($total_deduct > 0){
                        $total_deduct += $total_deduct - $payment_details['computed_commission'];
                        $payment_details['actual_commission'] = $total_deduct > 0 ? 0 : $payment_details['computed_commission'];
                        $payment_details['accumulated'] = abs($total_deduct);
                    }else{
                        $total_deduct = 0;
                    }


                    // $com_holder += $details['pre_deduc_comm'] - $payment_details['computed_commission'];
                    // $payment_details['actual_commission'] =  $details['pre_deduc_comm'] > $payment_details['computed_commission'] ? 0 : abs($payment_details['computed_commission'] - $details['pre_deduc_comm']);
                    // $com_holder = $total_deduct;
                    // $payment_details['actual_commission'] = $com_holder > 0 ? abs($payment_details['computed_commission']) :$payment_details['computed_commission'];
                    // if ($payment_details['computed_commission'] - $details['pre_deduc_comm'] > 0) {
                    //     $com_holder = 0;
                    // }
                    // $payment_details['accumulated'] = abs($com_holder);
                } else {
                    $payment_details['actual_commission'] =  $details['pre_deduc_comm'] > $payment_details['computed_commission'] ? 0 : abs($payment_details['computed_commission'] - $details['pre_deduc_comm']);
                    $payment_details['accumulated'] = abs($com_holder);
                }
                if ($details['pre_deduc_comm'] == null) {
                    // dd($com_holder);
                    $com_holder = $payment_details['computed_commission'] - $com_holder;
                    if ($total_deduct < 0) {
                        $payment_details['actual_commission'] = $payment_details['computed_commission'];
                        $payment_details['accumulated']  = abs($total_deduct);
                    } else {
                        $payment_details['actual_commission'] = $total_deduct;
                        $payment_details['accumulated']  = 0;
                        $total_deduct = 0;
                    }
                }
            } else {
                $payment_details['computed_commission'] =  round($payment_details['actual_amount'] * ($settings->commision_value / 100) - ($payment_details['actual_amount'] * ($settings->commision_value / 100) * .10), 2);
                if ($details['verified'] == 1) {

                    if($total_deduct > 0){
                        $total_deduct += $total_deduct - $payment_details['computed_commission'];
                        $payment_details['actual_commission'] = $total_deduct > 0 ? 0 : $payment_details['computed_commission'];
                        $payment_details['accumulated'] = abs($total_deduct);
                    }else{
                        $total_deduct = 0;
                    }

                    // $com_holder += $details['pre_deduc_comm'] - $payment_details['computed_commission'];
                    // $payment_details['actual_commission'] =  $details['pre_deduc_comm'] > $payment_details['computed_commission'] ? 0 : abs($payment_details['computed_commission'] - $details['pre_deduc_comm']);
                    // if ($payment_details['computed_commission'] - $details['pre_deduc_comm'] > 0) {
                    //     $com_holder = 0;
                    // }
                    // dd($com_holder);
                    $payment_details['accumulated'] = abs($total_deduct);
                } else {
                    $payment_details['actual_commission'] =  $details['pre_deduc_comm'] > $payment_details['computed_commission'] ? 0 : abs($payment_details['computed_commission'] - $details['pre_deduc_comm']);
                    $payment_details['accumulated'] = $total_deduct;
                }
                if ($details['pre_deduc_comm'] == null) {
                    // dump($payment_details['computed_commission'] .'-'. $com_holder);
                    //    dump($payment_details['computed_commission'] .'-'. $com_holder);
                        // $com_holder = $payment_details['computed_commission'] - $total_deduct; 
                        // dump($com_holder);
                        if ($total_deduct < 0) {
                            $payment_details['actual_commission'] = 0;
                            $payment_details['accumulated']  = abs($total_deduct);
                            $total_deduct = abs($total_deduct);
                        } else {
                            $payment_details['actual_commission'] = $total_deduct;
                            $payment_details['accumulated']  = 0;
                            $total_deduct = 0;
                        }

                }
            }
        } else {
            $payment_details['computed_commission'] = $settings->commision_value;
        }
        return ['p' => $payment_details, 'c' => $total_deduct,'cc' => $checker];
    }

    public function commission_registered($settings,$payment_details,$details, $com_holder,$total_deduct,$checker){
        // dump($details);
        // dd($settings);
        // dd($settings->cutoff_period);
        // dump($checker);
        if ($settings->commission_type == '%') {
            if ($settings->gst_status == 1) {
                $payment_details['computed_commission'] =  round($payment_details['amount'] * ($settings->commision_value / 100), 2);
                    if($payment_details['pre_deduc_comm'] != 0){
                        
                        if($total_deduct > 0){
                            dump($payment_details);
                            // dd($payment_details['computed_commission']);
                            $total_deduct = $total_deduct - $payment_details['computed_commission'];
                          
                            if($total_deduct > 0){
                                $payment_details['actual_commission'] = 0;
                                $payment_details['accumulated'] = abs($total_deduct);
                            }else{
                                $payment_details['actual_commission'] =  abs($total_deduct);
                                 $payment_details['accumulated'] = abs($total_deduct);
                            }
                            
                        }else{
                            $total_deduct = 0;
                            $payment_details['actual_commission'] = $payment_details['computed_commission'];
                            $payment_details['accumulated'] = abs($total_deduct);
                            $checker = 0;
                        }
                        $checker = 1;
                        // dump($checker);
                    }else{
                        if($checker){
                            $total_deduct = $total_deduct - $payment_details['computed_commission'];
                            if($total_deduct > 0){
                                $payment_details['actual_commission'] = 0;
                                $payment_details['accumulated'] = abs($total_deduct);
                            }else{
                                $checker = false;
                                $payment_details['actual_commission'] =  abs($total_deduct);
                                 $payment_details['accumulated'] = abs($total_deduct);
                            }

                        }else{
                            dump($total_deduct);
                            $payment_details['actual_commission'] = $payment_details['computed_commission'];
                            $payment_details['accumulated'] = abs($total_deduct);
                        }
                        
                    }
                   
                  
                    // $com_holder += $details['pre_deduc_comm'] - $payment_details['computed_commission'];
                    // $payment_details['actual_commission'] =  $details['pre_deduc_comm'] > $payment_details['computed_commission'] ? 0 : abs($payment_details['computed_commission'] - $details['pre_deduc_comm']);
                    // if ($payment_details['computed_commission'] - $details['pre_deduc_comm'] > 0) {
                    //     $com_holder = 0;
                    // }
                    // $payment_details['accumulated'] = abs($com_holder);
                
                // if ($details['pre_deduc_comm'] == null) {
                //     dd('nisulods');
                //     $total_deduct = $payment_details['computed_commission'] - $total_deduct;
                //     if ($com_holder < 0) {
                //         $payment_details['actual_commission'] = $payment_details['computed_commission'];
                //         $payment_details['accumulated']  = abs($total_deduct);
                //     } else {
                //         $payment_details['actual_commission'] = $total_deduct;
                //         $payment_details['accumulated']  = 0;
                //         $total_deduct = 0;
                //     }
                // }
            }else{
                dd('sulod');
                $payment_details['computed_commission'] =  round($payment_details['actual_amount'] * ($settings->commision_value / 100) + ($payment_details['actual_amount']* ($settings->commision_value / 100) * .10), 2);
                if ($details['verified'] == 1) {
                    if($payment_details['pre_deduc_comm'] != 0){
                        if($total_deduct > 0){
                            $total_deduct = $total_deduct - $payment_details['computed_commission'];
                            
                            if($total_deduct > 0){
                                $payment_details['actual_commission'] = 0;
                                $payment_details['accumulated'] = abs($total_deduct);
                            }else{
                                $payment_details['actual_commission'] =  abs($total_deduct);
                                 $payment_details['accumulated'] = abs($total_deduct);
                            }
                            
                        }else{
                            $total_deduct = 0;
                            $payment_details['actual_commission'] = $payment_details['computed_commission'];
                            $payment_details['accumulated'] = abs($total_deduct);
                        }
                    }else{
                        $payment_details['actual_commission'] = $payment_details['computed_commission'];
                        $payment_details['accumulated'] = abs($total_deduct);
                    }
                   
                  
                    // $com_holder += $details['pre_deduc_comm'] - $payment_details['computed_commission'];
                    // $payment_details['actual_commission'] =  $details['pre_deduc_comm'] > $payment_details['computed_commission'] ? 0 : abs($payment_details['computed_commission'] - $details['pre_deduc_comm']);
                    // if ($payment_details['computed_commission'] - $details['pre_deduc_comm'] > 0) {
                    //     $com_holder = 0;
                    // }
                    // $payment_details['accumulated'] = abs($com_holder);
                } 
            }
        }else{
            $payment_details['computed_commission'] = $settings->commision_value;
        }

        return ['p' => $payment_details, 'c'=> $total_deduct ,'cc' => $checker];
    }

    public function payment_details($payment_details,$non_tuition,$settings,$payment_sched_id){
        $nontuition = $non_tuition;
        $com_holder = 0;
        $pdees = collect($payment_details);
        $total_deduct = $pdees->sum('pre_deduc_comm');
        $checker = 0;
        foreach ($pdees as $pkey => $details) {
                if($details['payment_sched_id'] == $payment_sched_id){
                    $payment_details[$pkey]['actual_amount'] = $details['amount'];
                    $payment_details[$pkey]['computed_commission'] = 0;
                    $payment_details[$pkey]['actual_commission'] = 0;
                    $payment_details[$pkey]['accumulated'] = 0;
                }else{
                    if ($payment_details[0]['amount'] < $non_tuition) {
                        // dd($details['amount'] );
                        if ($nontuition <= 0) {
                            $payment_details[$pkey]['actual_amount'] = $details['amount'] + $details['pre_deduc_comm'];
                            if ($settings->gst_type == 'registered') {
                                $pdata = $this->commission_registered($settings, $payment_details[$pkey], $details, $com_holder,$total_deduct,$checker);
                            } else {
                                $pdata = $this->commission_not_registered($settings, $payment_details[$pkey], $details, $com_holder,$total_deduct,$checker);
                            }
                            $payment_details[$pkey] = $pdata['p'];
                            $total_deduct = $pdata['c'];
                            $checker = $pdata['cc'];
                        } else {
                            if ($details['payment_sched_id'] == $payment_sched_id) {
                                $nontuition = $nontuition - $details['amount'];
                                
                                if ($nontuition < 0) {
                                    $payment_details[$pkey]['actual_amount'] = abs($nontuition);
                                    if ($settings->gst_type == 'registered') {
                                        $pdata = $this->commission_registered($settings, $payment_details[$pkey], $details, $com_holder,$total_deduct,$checker);
                                    } else {
                                        $pdata = $this->commission_not_registered($settings, $payment_details[$pkey], $details, $com_holder,$total_deduct,$checker);
                                    }
                                    $payment_details[$pkey] = $pdata['p'];
                                    $total_deduct = $pdata['c'];
                                    $checker = $pdata['cc'];
                                } else {
                                    $payment_details[$pkey]['actual_amount'] = $details['amount'];
                                    $payment_details[$pkey]['computed_commission'] = 0;
                                    $payment_details[$pkey]['actual_commission'] = 0;
                                    $payment_details[$pkey]['accumulated'] = 0;
                                }
                            }else{
                                $payment_details[$pkey]['actual_amount'] = $details['amount'] - $details['pre_deduc_comm'];
                                if ($settings->gst_type == 'registered') {
                                    $pdata = $this->commission_registered($settings, $payment_details[$pkey], $details, $com_holder,$total_deduct,$checker);
                                } else {
                                    $pdata = $this->commission_not_registered($settings, $payment_details[$pkey], $details, $com_holder,$total_deduct,$checker);
                                }
                                $payment_details[$pkey] = $pdata['p'];
                                $total_deduct = $pdata['c'];
                                $checker = $pdata['cc'];
                            }
                            
    
                            
    
                            // exit();
                        }
                    } else {
                        // initial > non_tuition
                      
                        $payment_details[$pkey]['actual_amount'] = $details['amount'] - $details['pre_deduc_comm'];
                        if ($settings->gst_type == 'registered') {
                            $pdata = $this->commission_registered($settings, $payment_details[$pkey], $details, $com_holder,$total_deduct);
                        } else {
    
                            $pdata = $this->commission_not_registered($settings, $payment_details[$pkey], $details, $com_holder,$total_deduct);
                        }
                        $payment_details[$pkey] = $pdata['p'];
                        $total_deduct = $pdata['c'];
                        $checker = $pdata['cc'];
                    }
                }
                
                
            
        }
        return $payment_details;
    }
    
    public function view_commison_per_agent($serial,AgentDetail $agent){
        $cutoff = AgentCommissionCutoff::with('commission_details.commission_sub.student_course')->where('serial_no',$serial)->where('agent_id',$agent->id)->first();
        $com_setting = TrainingOrganisation::first();
        if ($com_setting->logo_img != null) {
            $logo = 'storage/config/images/' . $com_setting->logo_img;
        } else {
            $logo = 'images/logo/vorx_logo.png';
        }
        $logo_url = url('/' . $logo);
        $tuition = 0;
        $students = [];
        foreach($agent->funded_course as $course){
            if($course->commission != null){
                
                $commission_schedule = $course->commission->commission_details()->where('serial_no',$serial)->get();
                if(!$commission_schedule->isEmpty()){
                    if($course->offer_detail != null){
                        $fee = $course->offer_detail->offer_letter->fees;
                        $tuition = number_format($fee->course_tuition_fee - $fee->discounted_amount,2);
                        $non_tuition = number_format($fee->materials_fee + $fee->application_fee, 2);
                    }else{
                        $tuition = $course->course_fee;
                    }
                    $payments = [];
                    $deducted_com = 0;
                    foreach($course->payment_sched as $psched){
                        if($psched->commission1 != null){
                            // dump($psched->commission1);
                            $commission_payable = 0;
                            $amount_receive = $psched->commission1->sum(function($commmission) use( $serial ){
                                if($commmission['serial_no'] == $serial){
                                    return $commmission['amount_received'];
                                }
                            });
                            $commission_payable = $psched->commission1->sum( function($commmission) use( $serial ){
                                if($commmission['serial_no'] == $serial){
                                    return $commmission['commission_payable'];
                                }
                            });
                            $commission_deducted1 = $psched->commission1->sum(function($commmission) use( $serial ){
                                if($commmission['serial_no'] == $serial){
                                    return $commmission['pre_deducted_comission'];
                                }
                            });
                            $computed_commission = $psched->commission1->sum(function($commmission) use( $serial ){
                                if($commmission['serial_no'] == $serial){
                                    return $commmission['computed_commission'];
                                }
                            });
                            $deducted_com = $commission_deducted1;
                            if($commission_payable == 0){
                                $deducted_com = $deducted_com-$computed_commission;
                            }else{
                                $deducted_com = $deducted_com-$commission_payable;
                            }
                            
                            if($deducted_com > 0){
                                $commission_payable = 0;
                            }else{
                                if($commission_payable == 0){
                                    $deducted_com1 = $deducted_com-$computed_commission;
                                }else{
                                    $deducted_com1 = $deducted_com-$commission_payable;
                                }
                                if($deducted_com - $deducted_com1 > 0){
                                    $commission_payable = $deducted_com;
                                }else{
                                    $commission_payable = $computed_commission;
                                }
                                
                            }
                          


                           
                            foreach($psched->commission1 as $cdetail){
                                if($cdetail['serial_no'] == $serial){
                                    $payments[$cdetail->payment_sched_id] = [
                                                'due_date' => $psched->due_date->format('d/m/Y'),
                                                'due_amount' => $psched->payable_amount,
                                                'date_paid' => $psched->last_payment_date,
                                                'amount_paid' =>$amount_receive,
                                                'commission' => number_format($commission_payable,2),
                                                'deducted' => $psched->prededucted_com,
                                            ];
                                }
                            }

                            

                            // if($psched->commission->serial_no == $serial){

                            //     // $commission_payable = $psched->commission1->sum( function($commmission) use( $serial ){
                            //     //     if($commmission['serial_no'] == $serial){
                            //     //         return $commmission['commission_payable'];
                            //     //     }
                            //     // });
                            //     // $commission_deducted1 = $psched->commission1->sum(function($commmission) use( $serial ){
                            //     //     if($commmission['serial_no'] == $serial){
                            //     //         return $commmission['pre_deducted_comission'];
                            //     //     }
                            //     // });
                            //     // $computed_commission = $psched->commission1->sum(function($commmission) use( $serial ){
                            //     //     if($commmission['serial_no'] == $serial){
                            //     //         return $commmission['computed_commission'];
                            //     //     }
                            //     // });
                            //     // $amount_receive = $psched->commission1->sum(function($commmission) use( $serial ){
                            //     //     if($commmission['serial_no'] == $serial){
                            //     //         return $commmission['amount_received'];
                            //     //     }
                            //     // });
                               
                            //     $deducted_com = $deducted_com - $commission_payable;
                            //     // $commission_deducted = $commission_deducted1 - $commission_payable ;
                            //     // dump($deducted_com);
                            //     if($deducted_com > 0){
                            //         $commission_payable = 0;
                            //     }else{
                            //         $deducted_com = 0;
                            //         $commission_payable = $commission_payable;
                            //     }
                                
                            //     $payments[] = [
                            //         'due_date' => $psched->due_date->format('d/m/Y'),
                            //         'due_amount' => $psched->payable_amount,
                            //         'date_paid' => $psched->last_payment_date,
                            //         'amount_paid' => $amount_receive,
                            //         'commission' => number_format($commission_payable,2),
                            //         'deducted' => $psched->prededucted_com,
                            //     ];
                            // }else{
                            //     $payments[] = [
                            //         'due_date' => $psched->due_date->format('d/m/Y'),
                            //         'due_amount' => $psched->payable_amount,
                            //         'date_paid' => $psched->last_payment_date,
                            //         'amount_paid' => $psched->approved_amount_paid,
                            //         'commission' => '0.00',
                            //         'deducted' => '0.00',
                            //     ];
                            // }
                            
                        }else{
                            $payments[] = [
                                'due_date' => $psched->due_date->format('d/m/Y'),
                                'due_amount' => $psched->payable_amount,
                                'date_paid' => $psched->last_payment_date,
                                'amount_paid' => $psched->approved_amount_paid,
                                'commission' => '0.00',
                                'deducted' => '0.00',
                            ];
                        }
                        
                    }
                    $payment_plan = collect($payments);
    
                    $student[] = [
                        'student_id'            => $course->student_id,
                        'name'                  => $course->student->party->name,
                        'dob'                   => $course->student->party->person->date_of_birth,
                        'course'                => $course->course_code.' - '.$course->course->name,
                        'tuition'               => $tuition,
                        'non_tuition'           => $non_tuition,
                        'course_start'          => Carbon::parse($course->start_date)->format('d/m/Y'),
                        'course_end'            => Carbon::parse($course->end_date)->format('d/m/Y'),
                        'commission_limit'      => $course->commission->commission_limit,
                        'status'                => $course->status->description,
                        'payments'              => $payment_plan,
                        'total_deducted'        => $payment_plan->sum('deducted'),
                        'total_commission'      => $payment_plan->sum('commission'),
    
                    ];
                }
                
            }
        }

        $settings = [
            'cutoff' => $cutoff,
            'com_setting' => $com_setting,
            'logo'  => $logo,
            'students' => $student
        ];
        return $settings;
    }

    public function agent_commission_generate(AgentDetail $agent){
        $agent->load('commission_settings.sub_settings.student', 'commission_settings.cutoff_period', 'commission_settings.sub_settings', 'commission_settings.sub_settings.cutoff_period');
        $list = [];
            $student_count = 0;
            $name = $agent->user->party->name;
            $list['Company'] = $name;
            $list['agent_name'] = $agent->agent_name;
            $list['agent_user'] = $agent->id;

            foreach ($agent->commission_settings as $com_key => $commission_setting) {
                $student_count += $commission_setting->sub_settings->groupBy('student_id')->count();
                // dd($student_count);
                $cutop = explode(',', $commission_setting->cutoff_period->cutoff);
                $cutoff = 15;
                $cutoffs = [$date_holder = date('Y-m-') . $cutop[0], $date_holder = date('Y-m-') . $cutop[1]];
                $now = Carbon::now()->format('d');
                if ($now <= $cutop[0]) {
                    $serial = 'A' . $agent->id . '-' . date('ym') . $cutop[0];
                    $date_holder = date('Y-m-') . $cutop[0];
                    $cutoff = $cutop[0];
                } else {
                    $serial = 'A' . $agent->id . '-' . date('ym') . $cutop[1];
                    $date_holder = date('Y-m-') . $cutop[1];
                    $cutoff = $cutop[1];
                }
                foreach ($commission_setting->sub_settings as $subkey => $subsetting) {
                    $student = $subsetting->student;
                    // $funded_course = $student->funded_course()->where('course_code', $subsetting->course->code)->first();
                    $funded_course = $subsetting->student_course;
                    $offer_details = $funded_course->offer_detail;
                    $payment_details = $funded_course->payment_details()->where('verified',1)->doesntHave('commission')->get(['id as payment_id', 'payment_date', 'amount', 'pre_deduc_comm', 'comm_release_status', 'note','verified'])->toArray();
                    $template_i = !$funded_course->payment_sched->isEmpty() ? $funded_course->payment_sched->first()->id : null;
                    $non_tuition = $offer_details->offer_letter->fees->application_fee + $offer_details->offer_letter->fees->materials_fee;
                    $nontuition = $non_tuition;
                    $com_holder = 0;
                    $payment_details = $this->payment_details($payment_details, $nontuition, $subsetting,$template_i);
                    $_payment_details = collect($payment_details);
                    $total = $_payment_details->sum('computed_commission');
                    $total_pre_deducted = $_payment_details->sum(function ($payments) {
                        if ($payments['comm_release_status'] == 1) {
                            return $payments['pre_deduc_comm'];
                        }
                    });
                    // 
                    // $now = Carbon::now()->format('d');
                    $studentDetails = [
                        'id' => $subsetting->id,
                        'student_id' => $student->student_id,
                        'name' => $student->party->name,
                        'dob' => $student->party->person->date_of_birth,
                        'code' => $funded_course->course->code . ' - ' . $funded_course->course->name,
                        'course_start' => $funded_course->start_date,
                        'course_end' => $funded_course->end_date,
                        'course_end' => $funded_course->end_date,
                        'tuition' => $offer_details->offer_letter->fees->course_tuition_fee - $offer_details->offer_letter->fees->discounted_amount,
                        'non-tuition' => $non_tuition,
                        'status'    => $funded_course->status->description,
                        'commission_limit' => $subsetting->commission_limit,
                        'total_payable_commission' => round($_payment_details->sum('computed_commission')),
                        'actual_total_payable_commission' => round($_payment_details->where('comm_release_status', '<>', '2')->sum('actual_commission')),
                        'student_total_paid' => round($_payment_details->sum('amount')),
                        'total_pre_deducted' => $total_pre_deducted,
                        'gst_status' => $subsetting->gst_status,
                        'gst_type' => $subsetting->gst_type,
                        'payments' => $_payment_details,

                    ];
                    $list['students'][] = $studentDetails;
                }
                $list['serial'] = $serial;
                $list['student_count'] = $student_count;
            }
            if(isset($list['serial'])){
                $settings = $this->view_commison_per_agent($list['serial'],$list['agent_user']);
                dd($settings);
                return \PDF::loadView('commissions.agent_commission_cutoff',['setting' =>$settings])->stream('hehe');
        
            }else{
                return ['status'=>'error','message'=>'no commission setting assigned to this agent'];
            }
    }

    public function view_commison_per_agent_back($serial,AgentDetail $agent){
        $cutoff = AgentCommissionCutoff::with('commission_details.commission_sub.student_course')->where('serial_no',$serial)->where('agent_id',$agent->id)->first();
        $com_setting = TrainingOrganisation::first();
        if ($com_setting->logo_img != null) {
            $logo = 'storage/config/images/' . $com_setting->logo_img;
        } else {
            $logo = 'images/logo/vorx_logo.png';
        }
        $logo_url = url('/' . $logo);
        $student = [];
        // dump($cutoff);
        // dump($cutoff->commission_details);
        foreach ($cutoff->commission_details as $key => $commission_detail) {
            // dd($commission_detail->commission_sub->student_course);
            $funded = $commission_detail->commission_sub->student_course;
            $tuition = 0;
            $non_tuition = 0;
            if($funded->offer_detail != null){
                $fee = $funded->offer_detail->offer_letter->fees;
                $tuition = number_format($fee->course_tuition_fee - $fee->discounted_amount,2);
                $non_tuition = number_format($fee->materials_fee + $fee->application_fee, 2);
            }else{
                $tuition = $funded->course_fee;
            }
            $_payments = $commission_detail->commission_sub->student_course->payment_sched->load('payment_detail.commission');
            $payments = [];
            // dd($_payment_details);
            foreach($_payments as $payment){
                
                if($payment->commission != null){
                    if($payment->commission->serial_no == $serial){ 
                        if($payment->payment_detail->count()> 0){
                            $commission_payable = 0;
                            $commission_deducted = 0;
                            foreach($payment->payment_detail as $detail){
                                if($detail->commission->serial_no == $serial){
                                    
                                    $commission_payable+=$detail->commission->commission_payable;
                                }
                            }
                        }
                        $payments[] = [
                            'due_date' => $payment->due_date->format('d/m/Y'),
                            'due_amount' => $payment->payable_amount,
                            'date_paid' => !$payment->payment_detail->isEmpty() ?  Carbon::parse($payment->payment_detail[0]->payment_date)->format('d/m/Y') : null,
                            'amount_paid' => $payment->approved_amount_paid,
                            'commission' => $payment->approved_commission,
                            'deducted' => $payment->prededucted_com,
                            'actual_commission' => $commission_payable,
                        ];
                    }else{
                        $payments[] = [
                            'due_date' => $payment->due_date->format('d/m/Y'),
                            'due_amount' => $payment->payable_amount,
                            'date_paid' =>  !$payment->payment_detail->isEmpty() ? Carbon::parse($payment->payment_detail[0]->payment_date)->format('d/m/Y') : null,
                            'amount_paid' => $payment->approved_amount_paid,
                            'commission' => 0,
                            'deducted' => 0,
                            'actual_commission' => 0,
                        ];
                    }
                    
                }else{
                    $payments[] = [
                        'due_date' => $payment->due_date->format('d/m/Y'),
                        'due_amount' => $payment->payable_amount,
                        'date_paid' =>  null,
                        'amount_paid' => $payment->approved_amount_paid,
                        'commission' => 0,
                        'deducted' => 0,
                        'actual_commission' => 0,
                    ];
                }
                
            }
            $payments = collect($payments);
            $student[$commission_detail->agent_commission_settings_sub_id] = [
                'student_id'    => $commission_detail->commission_sub->student_id,
                'name'          => $commission_detail->commission_sub->student->party->name,
                'dob'          => $commission_detail->commission_sub->student->party->person->date_of_birth,
                'course'        => $commission_detail->commission_sub->student_course->course_code. ' - ' .$commission_detail->commission_sub->student_course->course->name,
                'payments'      => $payments,
                'tuition'       =>  $tuition,
                'non_tuition'   => $non_tuition,
                'course_start'   => $funded->start_date,
                'course_end'   => $funded->end_date,
                'commission_limit'   => $commission_detail->commission_sub->commission_limit,
                'total_deducted'   => $payments->sum('deducted'),
                'total_commission'   => $payments->sum('actual_commission'),
                'status'   => $funded->status->description
            ];
            // dump($commission_detail->commission_sub->student->party->name);
            // dump($commission_detail->commission_sub->course->name);
        }
        $settings = [
            'cutoff' => $cutoff,
            'com_setting' => $com_setting,
            'logo'  => $logo,
            'students' => $student
        ];
        return $settings;
    }
}
