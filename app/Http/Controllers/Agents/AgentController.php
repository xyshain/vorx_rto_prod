<?php

namespace App\Http\Controllers\Agents;

use App\Http\Controllers\Controller;
use App\Models\Agent;
use App\Models\AgentAttachment;
use App\Models\AgentCommissionSetting;
use App\Models\AgentCommissionSettingMain;
use App\Models\CommissionStatus;
use App\Models\AgentDetail;
use App\Models\AgentApplication;
use App\Http\Controllers\Send\EmailSendingController;
use App\Models\EmailAutomation;
use App\Models\Course;
use App\Models\FundedStudentPaymentDetails;
use App\Models\PaymentScheduleTemplate;
use App\Models\Student\Party;
use App\Models\TrainingOrganisation;
use App\Models\User;
use Illuminate\Http\Request;
use DB;
use File;
use Storage;
use Carbon\Carbon;
use Illuminate\Support\Facades\Hash;

class AgentController extends Controller
{

    public function __construct()
    {
        $this->middleware('auth');
    }
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        // dd();
        
        $app_settings = TrainingOrganisation::first();
        // dd($app_settings->add_on('agent'));
        if($app_settings->add_on('agent') == 0){
            // dd('test');
            return 'access deneid...';
        }

        return view('agents.index');
    }

    public function lists()
    {
        // $agents = Agent::with(['detail', 'party'])->orderBy('id', 'desc')->paginate(10);
        $agents = AgentDetail::with(['user'])->orderBy('id', 'desc')->get();
        // dd($agents[190]);
        return $agents;
    }

    public function list_search($data){
        $agents = Agent::with(['detail', 'party'])->whereHas('detail', function($q) use($data){
            $q->where('company_name', 'like', '%'.$data.'%');
        })->orWhereHas('party', function($q) use($data) {
            $q->where('name', 'like', '%' . $data . '%');
        })->orderBy('id', 'desc')->paginate(10);

        return json_encode($agents);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        // dd($request->all());

        $validator = \Validator::make($request->all(), [
            'agent.company_name' => 'required',
            'agent.agent_name' => 'required',
            'agent.email' => 'required|email',
            'agent.phone' => 'required',
        ]);

        // validator nice names
        $validator->setAttributeNames([
            'agent.company_name'    => 'Company Name',
            'agent.agent_name'      => 'Agent Name',
            'agent.email'           => 'Email',
            'agent.phone'           => 'Phone',
        ]);
        $permitted_chars = '0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $agent_code = $this->generate_string($permitted_chars, 10);
        try {
            // start db transaction
            DB::beginTransaction();

            $user = new User;
            $party = new Party;
            $agent_detail = new AgentDetail;

            $party->name = $request->agent['company_name'];
            $party->party_type_id = 2;
            $party->save();
            
            $user->username = $request->agent['email'];
            $user->password = Hash::make('agent123!@#');
            $user->is_active = 0;
            $user->profile_image = 'default.png';
            // $user->user()->associate(\Auth::user());
            $user->party()->associate($party);
            $user->save();
            $user->assignRole('Agent');

            $agent_detail->company_name = $request->agent['company_name'];
            $agent_detail->agent_name = $request->agent['agent_name'];
            $agent_detail->phone = $request->agent['phone'];
            $agent_detail->email = $request->agent['email'];
            $agent_detail->agent_code = $agent_code;
            $agent_detail->is_active = 0;
            $agent_detail->user()->associate($user);
            $agent_detail->user_reference()->associate(\Auth::user());
            $agent_detail->save();
            
            DB::commit();

            // dd($agent);

            return ['status' => 'success'];

        } catch (\Exception $e) {
            // rollback db transactions
            DB::rollBack();

            // return to previous page with errors
            return json_encode(['message' => $e->getMessage(), 'status' => 'error']);
        }


    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Agent  $agent
     * @return \Illuminate\Http\Response
     */
    public function show($agent)
    {
        //
        $courses = Course::all();
        $comm_statuses = CommissionStatus::all();
        
        $agent = AgentDetail::with(['user.party'])->where('id', $agent)->first();

        // dd($agent);

        \JavaScript::put([
            'agent' => $agent,
            'courses' => $courses,
            'comm_statuses' => $comm_statuses
        ]);
        // dd($agent);
        return view('agents.show');
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\Agent  $agent
     * @return \Illuminate\Http\Response
     */
    public function edit(Agent $agent)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Agent  $agent
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Agent $agent)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Agent  $agent
     * @return \Illuminate\Http\Response
     */
    public function destroy($agent)
    {
        //
        try {
            // start db transaction
            DB::beginTransaction();

            $agent = AgentDetail::with('user.party')->where('id', $agent)->first();

            if(isset($agent->commission_settings[0])){
                foreach ($agent->commission_settings as $key => $value) {
                    $value->delete();
                }
            }
            
            if($agent->user->party){
                $agent->user->party->delete();
            }
            
            if($agent->user){
                $agent->user->delete();
            }
            
            
            $agent->delete();

            DB::commit();

            // dd($agent);

            return ['status' => 'success'];
        } catch (\Exception $e) {
            // rollback db transactions
            DB::rollBack();

            // return to previous page with errors
            return json_encode(['message' => $e->getMessage(), 'status' => 'error']);
        }
        
    }

    public function agent_info($agent_id)
    {
        // $agent = Agent::with(['detail', 'party'])->where('id', $agent_id)->first();
        $agent = AgentDetail::with(['user.party'])->where('id', $agent_id)->first();

        // dd($agent);
        return $agent;
    }

    public function agent_info_update(Request $request, $agent_id)
    {
        // dd($request->all());
        try {
            // start db transaction
            DB::beginTransaction();
        // dd($request->agent['id']);
            // $agent = Agent::with(['detail', 'party'])->where('id', $agent_id)->first();
            $agent = AgentDetail::with(['user.party'])->where('id', $agent_id)->first();
            if(isset($request->agent['detail']['is_active']) && $request->agent['detail']['is_active'] == true){
                $is_active = 1;
            }else{
                $is_active = 0;
            }

            // agent user updates
            if($agent->user) {
                $agent->user->is_active = $is_active;
                $agent->user->update();
            }

            $agent->fill(
                $request->agent['detail']
            );
            $agent->is_active = $is_active;
            $agent->update();
            // $agent->update([
            //     'is_active' => isset($request->agent['detail']['is_active']) ? $request->agent['detail']['is_active'] : 0
            // ]);

            $agent->user->party->update(
                [
                    'name' => $request->agent['detail']['company_name']
                ]
            );

            DB::commit();

            return ['status'=>'updated'];

        } catch (\Exception $e) {
            // rollback db transactions
            DB::rollBack();

            // return to previous page with errors
            return ['message' => $e->getMessage(), 'status' => 'error'];
        }
    }

    public function fetch_agent_commission ($agent_id)
    {
        $commissions = AgentCommissionSettingMain::where('agent_id',$agent_id)->orderBy('id','desc')->get();

        $coms = [];

        foreach ($commissions as $key => $value) {
            $coms[] = [
                'id' => $value->id,
                'commission_type_id' => $value->commission_type,
                'commission_value' => $value->commision_value,
                'real_commission_value' => number_format($value->commision_value). $value->commission_type,
                'course_code' => $value->course->code,
                'gst_type' =>  $value->gst_status == 1 ? 'Including' : 'Excluding',
                'remarks' => $value->remarks,
                // 'registered_gst' => $value->gst_status,
                'registered_gst' => strtoupper($value->gst_type),
                'registered_gst_sign' => $value->gst_status,
                'registered_gst_date' => $value->registered_date != '0000-00-00' ? Carbon::parse($value->registered_date)->format('d/m/Y') : null,
                // 'starting_student_count' => $value->starting_student_count,
                // 'starting_commission_value' => $value->starting_commission_value
            ];
        } 
        // old code
        /* $commissions = AgentCommissionSetting::where('agent_id', $agent_id)->orderBy('id', 'desc')->get();

        $coms = [];

        foreach ($commissions as $key => $value) {
            $coms[] = [
                'id' => $value->id,
                'commission_type_id' => $value->commission_type_id,
                'commission_value' => $value->commission_value,
                'real_commission_value' => $value->real_commission_value. $value->commission_type_id,
                'course_code' => $value->course_code,
                'gst_type' => $value->gst_type,
                'remarks' => $value->remarks,
                'registered_gst' => $value->registered_gst,
                'registered_gst_sign' => $value->registered_gst == 1 ? 'Registered' : 'Not Registered',
                // 'registered_gst_sign' => $value->registered_gst == 1 ? '<i class="fas fa-check" style="color: green;"></i>' : '<i class="fas fa-times" style="color: rgb(231, 74, 59);"></i>',
                'registered_gst_date' => $value->registered_gst_date,
                'starting_student_count' => $value->starting_student_count,
                'starting_commission_value' => $value->starting_commission_value
            ];
        } */
        // dd($coms);
        return $coms;
    }

    public function edit_agent_commission($commId)
    {
        $comm = AgentCommissionSettingMain::where('id', $commId)->first();

        

        // $comm->commission_value = $comm->real_commission_value;
        
        return $comm;
    }

    public function agent_commission_save(Request $request)
    {   
        
        $validator = \Validator::make($request->all(), [
            'acs.course_id' => 'required',
            'acs.commision_value' => 'required',
            'acs.gst_type' => 'required',
        ]);

        // validator nice names
        $validator->setAttributeNames([
            'acs.course_id'        => 'Course',
            // 'acs.all_courses'        => 'All Courses',
            'acs.gst_type'           => 'GST Type',
            'acs.commision_value'   => 'Commission Value',
        ]);
        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }
        try {
            // start db transaction
            DB::beginTransaction();
           
                
                $data = AgentCommissionSettingMain::updateOrCreate(
                    [
                        'id' => isset($request['acs']['id']) ? $request['acs']['id'] : 0,
                        'agent_id' => $request['agentID']
                    ],
                    [
                    'commission_type' => '%',
                        'course_id' => $request['acs']['course_id'],
                        'commision_value' => $request['acs']['commision_value'],
                        'gst_type' => $request['acs']['gst_type'],
                        'remarks' => isset($request['acs']['remarks']) ? $request['acs']['remarks'] : '',
                        'gst_status' => isset($request['acs']['gst_status']) && $request['acs']['gst_status'] == 1 ? 1 : 0,
                        'registered_date' => isset($request['acs']['registered_date']) && $request['acs']['registered_date'] != null ? Carbon::parse($request['acs']['registered_date'])->format('Y-m-d') : null,
                        'commission_cutoff' => 1,
                        'user_id' => \Auth::user()->id,
                    ]
                );
            // commit db transactions
            DB::commit();

            return ['status' => 'success'];

        } catch (\Exception $e) {
            // rollback db transactions
            DB::rollBack();

            // return to previous page with errors
            return ['status' => 'error', 'msg' => $e->getMessage()];
        }
    }

    public function delete_agent_commission($commId)
    {
        
        // dd($commId);
        $acs = AgentCommissionSettingMain::find($commId);
        try {
            // start db transaction
            DB::beginTransaction();

            $acs->delete();

            // commit db transactions
            DB::commit();
            return ['status' => 'success'];
        } catch (\Exception $e) {
            // rollback db transactions
            DB::rollBack();

            return ['status' => 'error', 'msg' => $e->getMessage()];
        }
    }

    public function generate_agreement($id){

        $detail = AgentDetail::with('agent_application','commission_settings')->where('id', $id)->first();
        $signed = false;
        $app_form = null;
        if($detail->agent_application){
            $app_form = $detail->agent_application->application_form;
            $app_form = $app_form['inputs'];
            if($detail->agent_application->agent_agreement_sig_agreement == 1){
                $signed = true;
            }
        }
        $org = TrainingOrganisation::first();
        $dated    = Carbon::now()->setTimezone('Australia/Melbourne')->format('Y-m-d');

        $path = storage_path('app/public/agent/new/attachments/'.$detail->id);

        if (!file_exists($path)) {
            mkdir($path, 0777, true);
        }

        if(config('app.name') == 'CEA'){
            $pdf_template = 'custom.cea.education-agent-agreement-pdf';
        }elseif (config('app.name') == 'Phoenix') {
            $pdf_template = 'custom.pca.education-agent-agreement-pdf';
        }else{
            // vorx theme and content
            // $pdf_template = 'agent-application.education-agent-agreement-pdf';
            // abort(403, 'File not found.');
            return response()->json(['status' => 'error', 'message' => 'Please provide template for Education Agent Agreement.']);
        }

        \PDF::loadView($pdf_template, compact('detail','org','dated','app_form','signed'))->setPaper(array(0, 0, 612, 870), 'portrait')->save($path.'/education-agent-agreement.pdf')->stream($detail->agent_name .' - Education Agent Agreement.pdf');

                // save to agent attachments
                $agentAttachment = AgentAttachment::where('agent_id', $detail->id)->where('_input', 'agent-agreement')->first();
                if(!$agentAttachment) {
                    $agentAttachment = new AgentAttachment([
                        'name'      => 'education-agent-agreement.pdf',
                        'hash_name' => 'education-agent-agreement',
                        'size'      => null,
                        'mime_type' => 'application/pdf',
                        'ext'       => 'pdf',
                        '_input'       => 'agent-agreement',
                    ]);
                    // associated user
                    $agentAttachment->user()->associate(\Auth::user());
                    $agentAttachment->agent()->associate($detail->id);
                    $agentAttachment->save();
                    $agentAttachment->path_id = $detail->id;
                    $agentAttachment->update();
                }
        // return \PDF::loadView($pdf_template, compact('detail','org','dated','app_form'))->setPaper(array(0, 0, 612, 870), 'portrait')->stream('agent-agreement');
        return ['status' => 'success','path' => $path . '/education-agent-agreement.pdf', 'name' => $detail->agent_name.' - Education Agent Agreement.pdf'];
    }

    public function agentAgreementView($id){

        $detail = AgentDetail::with('agent_application','commission_settings')->where('id', $id)->first();
        if($detail){
            $signed = false;
            $app_form = null;
            if($detail->agent_application){
                $app_form = $detail->agent_application->application_form;
                $app_form = $app_form['inputs'];
                if($detail->agent_application->agent_agreement_sig_agreement == 1){
                    $signed = true;
                }
            }
            
            $org = TrainingOrganisation::first();
            $dated    = Carbon::now()->setTimezone('Australia/Melbourne')->format('Y-m-d');

            if(config('app.name') == 'CEA'){
                $pdf_template = 'custom.cea.education-agent-agreement-pdf';
            }elseif (config('app.name') == 'Phoenix') {
                $pdf_template = 'custom.pca.education-agent-agreement-pdf';
            }else{
                // vorx theme and content
                // $pdf_template = 'agent-application.education-agent-agreement-pdf';
                abort(403, 'File not found.'); 
            }

            return \PDF::loadView($pdf_template, compact('detail','org','dated','app_form','signed'))->setPaper(array(0, 0, 612, 870), 'portrait')->stream($detail->agent_name. ' - Education Agent Agreement.pdf');
        }else{
            abort(403, 'File not found.'); 
        }
    }
    // application_email
    
    public function application_email($id){

        $detail = AgentDetail::with('agent_application','commission_settings')->where('id', $id)->first();
        $emailsTo = [];
        if (isset($detail->email) && !in_array($detail->email, ['', null])) {
            $emailsTo[] = $detail->email;
        }
        if (isset($detail->email_2) && !in_array($detail->email_2, ['', null])) {
            $emailsTo[] = $detail->email_2;
        }
        $admin_emails = EmailAutomation::where('addOn', 'agent_form')->pluck('email');

        $paths = [];
        // Attachments along with the agent agreement
        $nc = storage_path('app/public/agent/national-codes');
        if(file_exists($nc)){
            // raw file contents
            $paths[] = [
                'path' => $nc.'/Standard 1 of National code 2018.pdf', 
                'name' => 'Standard 1 of National code 2018.pdf'
            ];
            $paths[] = [
                'path' => $nc.'/Standard 2 of National code 2018.pdf', 
                'name' => 'Standard 2 of National code 2018.pdf'
            ];
            $paths[] = [
                'path' => $nc.'/Standard 3 of National code 2018.pdf', 
                'name' => 'Standard 3 of National code 2018.pdf'
            ];
            $paths[] = [
                'path' => $nc.'/Standard 4 of National code 2018.pdf', 
                'name' => 'Standard 4 of National code 2018.pdf'
            ];
       }

        
        $aa = AgentAttachment::where('agent_id', $detail->id)->where('_input','agent-agreement')->first();
        if($aa){
            $paths[] = [
                'path' => storage_path('app/public/agent/new/attachments/'.$detail->id.'/'.$aa->hash_name.'.'.$aa->ext), 
                'name' => $aa->name
            ];
        }else{
            $agent_agreement = $this->generate_agreement($detail->id);
            if(isset($agent_agreement['path'])){
                $paths[] = $agent_agreement;
            }
        }

        $org = TrainingOrganisation::first();
        $send = new EmailSendingController;
        if($detail->agent_application){
            $reviewLink = url('agent-agreement/review/' . $detail->agent_application->process_id);
            $content = 'Dear <b>'.$detail->agent_name.'</b>, <br><br>Greetings of the day. <br><br>We are pleased to inform you that we have received all the documents and reference checks in support of your application to become our representative to recruit students. Your agent/representative application has been approved. I am sending you a <a href="' . $reviewLink . '">link</a> of agent agreement in this email, please sign and send back to us and save a copy for your records. <br><br>Also, I have attached the fact sheets relevant to you as our representative, please go through them. We have to remain compliant to National code 2018 and Standards for Registered Training Organisations 2015 (SRTOs 2015).<br><br>Link for SRTOs 2015: <a href="https://www.asqa.gov.au/standards/marketing-recruitment">https://www.asqa.gov.au/standards/marketing-recruitment</a><br>Link for National code 2018: <a href="https://internationaleducation.gov.au/regulatory-information/Pages/National-Code-2018-Factsheets-.aspx">https://internationaleducation.gov.au/regulatory-information/Pages/National-Code-2018-Factsheets-.aspx</a><br><br>Please feel free to contact us if you have any questions in this regard.<br><br>Regards,<br><br><b>Admin Team</b><br>'.$org->training_organisation_name.'<br>RTO NO '.$org->training_organisation_id.' | CRICOS CODE '.$org->cricos_code.'';
                
        }else{
            // return response()->json(['status' => 'error', 'message' => 'No Agent Application.']);
            // removed reviewlink
            $content = 'Dear <b>'.$detail->agent_name.'</b>, <br><br>Greetings of the day. <br><br>We are pleased to inform you that we have received all the documents and reference checks in support of your application to become our representative to recruit students. Your agent/representative application has been approved. <br><br>Also, I have attached the fact sheets relevant to you as our representative, please go through them. We have to remain compliant to National code 2018 and Standards for Registered Training Organisations 2015 (SRTOs 2015).<br><br>Link for SRTOs 2015: <a href="https://www.asqa.gov.au/standards/marketing-recruitment">https://www.asqa.gov.au/standards/marketing-recruitment</a><br>Link for National code 2018: <a href="https://internationaleducation.gov.au/regulatory-information/Pages/National-Code-2018-Factsheets-.aspx">https://internationaleducation.gov.au/regulatory-information/Pages/National-Code-2018-Factsheets-.aspx</a><br><br>Please feel free to contact us if you have any questions in this regard.<br><br>Regards,<br><br><b>Admin Team</b><br>'.$org->training_organisation_name.'<br>RTO NO '.$org->training_organisation_id.' | CRICOS CODE '.$org->cricos_code.'';
        }
        
        if($org->email_address == null){
            return response()->json(['status' => 'error', 'message' => 'Organisation email address is missing.']);
        }
        $s = $send->send_automate('Representative/Education Agent Application Form', $content, [$org->training_organisation_name => $org->email_address], $emailsTo, $paths, $admin_emails);

        if ($s['status'] == 'success') {
            return response()->json(['status' => 'success', 'message' => 'Verified Successfully']);
        } else {
            return response()->json(['status' => 'error', 'message' => 'Something went wrong.']);
        }
        
    }


    public function generate_string($input = '0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ', $strength = 16)
    {
        $input_length = strlen($input);
        $random_string = '';

        for ($i = 0; $i < $strength; $i++) {
            $random_character = $input[mt_rand(0, $input_length - 1)];
            $random_string .= $random_character;
        }

        $validate = AgentDetail::where('agent_code', $random_string)->first();

        if ($validate) {
            $permitted_chars = '0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ';
            $this->generate_string($permitted_chars, 10);
        } else {
            return $random_string;
        }
    }

    public function agentCollection($id){
        $agent_collections = FundedStudentPaymentDetails::with('attachment','student.party','funded_student_course','payment_schedule_template')->where('agent_id',$id)
        ->orderBy('id','desc')->get();

        return $agent_collections;
    }

    public function declineCollection($id){ //funded student payment detail id
        $student_funded_payment_detail = FundedStudentPaymentDetails::where('id',$id)->first();

        $student_funded_payment_detail->verified = 2;
        $student_funded_payment_detail->update();
    }

    public function studentPayments($id,$amount){
        $funded_student_payments = FundedStudentPaymentDetails::where('student_course_id',$id)->where('verified',1)->get();
        $funded_payment_sched_template = PaymentScheduleTemplate::where('funded_student_course_id',$id)->get();
        $ret = [
            'funded_student_payments'=>$funded_student_payments,
            'funded_payment_sched_template'=>$funded_payment_sched_template
        ];
        
        $unverified_amount = $amount;
        foreach($funded_payment_sched_template as $fd){
            $fd->approved_amount_paid = $fd->approved_amount_paid;
            
            $fd->balance = $fd->payable_amount - $fd->approved_amount_paid;

            // if($fd->balance>0){
            //     $fd->unverified_amount = $unverified_amount >= $fd->payable_amount ? $fd->balance :  $unverified_amount;
            //     $unverified_amount = $unverified_amount - $fd->payable_amount;
            // }
                // breaking sa unverified amount 
            if($fd->balance > 0){
                if($unverified_amount > $fd->balance){
                    $fd->unverified_amount = $fd->balance;
                    $unverified_amount = $unverified_amount - $fd->balance;
                }else if($unverified_amount < $fd->balance && $unverified_amount > 0){
                    $fd->unverified_amount = $unverified_amount;
                    $unverified_amount = $unverified_amount - $unverified_amount;
                }else{
                    $fd->unverified_amount = 0;
                }
                // dump($unverified_amount);
            }
        }

        return $ret;
    }

    public function acceptCollection(Request $request){
        // return $request->all();
        $agent_collection = $request->amount;
        $trxn_code = $request->transaction_code;
        
        // dd($request->id,$agent_collection,$trxn_code);
        $student_funded_payment_detail = FundedStudentPaymentDetails::where('id',$request->id)->first();

        $payment_schedule = PaymentScheduleTemplate::where('funded_student_course_id',$request->student_course_id)->get();
        
        $unfinished_payments = [];

        // foreach($payment_schedule as $ps){
        //     $payable = 0;
        //     $ps->approved_amount_paid = $ps->approved_amount_paid;
        //     if($ps->approved_amount_paid < $ps->payable_amount){
        //         if($ps->id == $student_funded_payment_detail->payment_schedule_template_id){
        //             $payable = $agent_collection > $ps->payable_amount ? $ps->payable_amount  - $ps->approved_amount : $agent_collection - $ps->approved_amount ;
        //             $agent_collection = $agent_collection - $payable;
        //             dump($payable,$agent_collection);
        //         }else{
        //             $payable = $agent_collection > $ps->payable_amount ? $ps->payable_amount  - $ps->approved_amount : $agent_collection - $ps->approved_amount ;
        //             $agent_collection = $agent_collection - $payable;
        //             dump($payable,$agent_collection);
        //         }
        //     }
        $first = true;
        foreach($payment_schedule as $ps){
            $payable = 0;
            if($ps->approved_amount_paid < $ps->payable_amount){
                if($first == true){
                    $student_funded_payment_detail->
                }
                $payable = ;
                $student_funded_payment_detail->amount = $ps->
            }
        }
        


        return response()->json([$unfinished_payments,$student_funded_payment_detail]);

    }
}
