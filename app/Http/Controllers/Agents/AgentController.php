<?php

namespace App\Http\Controllers\Agents;

use App\Http\Controllers\Controller;
use App\Models\Agent;
use App\Models\AgentAttachment;
use App\Models\AgentCommissionSetting;
use App\Models\AgentCommissionSettingMain;
use App\Models\CommissionStatus;
use App\Models\Notification;
use App\Models\Collection;
use App\Models\AgentDetail;
use App\Models\AgentApplication;
use App\Http\Controllers\Send\EmailSendingController;
use App\Models\EmailAutomation;
use App\Models\Course;
use App\Models\FundedStudentPaymentDetails;
use App\Models\Student\Student;
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
        $agent_collections = Collection::with('agent','student.party','funded_student_course.course','attachment')->where('agent_id',$id)->orderBy('id','desc')->get();

        return $agent_collections;
        
        // $agent_collections = FundedStudentPaymentDetails::with('agent','attachment','student.party','funded_student_course.course','payment_schedule_template')->where('agent_id',$id)
        // ->orderBy('id','desc')->get();

        // $tcodes = [];
        // $dup_codes = [];
        // $note_att = [];
        // for($i = 0; $i < count($agent_collections); $i++){
        //     if(isset($agent_collections[$i]->transaction_code)){
        //         if(in_array($agent_collections[$i]->transaction_code,$tcodes)){
        //             // unset($agent_collections[$i]);
        //             array_push($dup_codes,$agent_collections[$i]->transaction_code);
        //         }else{
        //             $payment_details = FundedStudentPaymentDetails::where('transaction_code',$agent_collections[$i]->transaction_code)->get();
        //             $amount = 0;
        //             $attachment = null;
        //             $note = null;
        //             $remarks = null;
        //             foreach($payment_details as $pd){
        //                 $amount += $pd->amount;
        //                 // dump($pd->attachment);
        //                 if(isset($pd->attachment)){
        //                     $attachment = $pd->attachment;
        //                 }
        //                 if(isset($pd->note)){
        //                     $note = $pd->note;
        //                 }
        //                 if(isset($pd->remarks)){
        //                     $remarks = $pd->remarks;
        //                 }
        //             }
                    
        //             $note_att[$i] = ['tcode'=>$agent_collections[$i]->transaction_code,'note'=>$note,'attachment'=>$attachment,'amount'=>$amount,'remarks'=>$remarks];
        //             // $agent_collections[$i]->amount = $amount;
        //             // if(!in_array($agent_collections[$i]->transaction_code,$tcodes)){
        //                 array_push($tcodes,$agent_collections[$i]->transaction_code);
        //             // }
        //         }
        //     }
        // }
        
        // foreach($agent_collections as $ac){
        //     foreach($note_att as $na){  
        //         if($ac->transaction_code == $na['tcode']){
        //             $ac->attachments = $na['attachment'];
        //             $ac->note = $na['note'];
        //             $ac->amount = $na['amount'];
        //             $ac->remarks = $na['remarks'];
        //             // return response()->json(['ac'=>$ac,'ac_attachment'=>$ac->attachment,'na'=>$na['attachment']]);
        //         }
        //     }
        // }

        // for($i = 0; $i < count($agent_collections); $i++){
        //     if(isset($agent_collections[$i]->transaction_code)){
        //         if(in_array($agent_collections[$i]->transaction_code,$dup_codes)){
        //             if(($key = array_search($agent_collections[$i]->transaction_code,$dup_codes))!==false){
        //                 unset($dup_codes[$key]);
        //             }
        //             unset($agent_collections[$i]);
        //         }
        //     }
        // }
        // return $agent_collections;
    }

    public function declineCollection($request){ //funded student payment detail id
        $id         = $request['id'];
        $trxn_code  = $request['transaction_code'];
        $remarks    = $request['remarks'];
        
        try{
            DB::beginTransaction();
            // $student_funded_payment_detail = FundedStudentPaymentDetails::where('id',$id)->first();

            // $student_funded_payment_detail->verified = 2;
            // $student_funded_payment_detail->remarks = $request['remarks'];
            // $student_funded_payment_detail->update();
            
            $collection = Collection::find($id);
            $collection->verified = 2;
            $collection->remakrs = $remarks;
            $collection->update();

            $org = TrainingOrganisation::first();
            $send = new EmailSendingController;

            if(isset($request['agent'])){
                $name = $request['agent']['agent_name'];
                if(isset($request['agent']['email'])){
                    $emailsTo[] = $request['agent']['email'];
                }else{   
                    return response()->json(['status'=>'error','message'=>'Agent email not found']);
                }
            }else{
                return response()->json(['status'=>'error','message'=>'Agent not found']);
            }

            $content = '<b>Dear ' . $name . ',</b><br><br>Your collection has been declined.<br>Trxn no: '.$trxn_code;

            $s = $send->send_automate('Collection Declined', $content, ['Vorx' => $org->email_address], $emailsTo);
            // $s['status']='success';
            if($s['status']=='success'){
                DB::commit();
                $this->notifyAgent($collection);
                return response()->json(['status'=>'success']);
            }else{
                DB::rollback();
                return response()->json(['status'=>'error','message'=>'Email error']);
            }
        }catch(Exception $e){
            DB::rollback();
            return response()->json(['status'=>'error','message'=>$e->getMessage()]);
        }
    }

    public function studentPayments(Request $request){
        $id = $request->id;

        $funded_student_payments = FundedStudentPaymentDetails::where('student_course_id',$id)->where('verified',1)->get();
        $funded_payment_sched_template = PaymentScheduleTemplate::where('funded_student_course_id',$id)->get();
        
        $sched_with_payment = [];
        
        $unverified_amount = $request->amount_paid;
        $prededuct_com = $request->pre_deduc_com;
        
        foreach($funded_payment_sched_template as $fd){
            $fd->approved_amount_paid = $fd->approved_amount_paid;
            
            $fd->balance = $fd->payable_amount - $fd->approved_amount_paid;
            
            if($fd->balance > 0){
                if($unverified_amount > $fd->balance){
                    $fd->unverified_amount = $fd->balance;
                    $unverified_amount = $unverified_amount - $fd->balance;
                }else if($unverified_amount <= $fd->balance && $unverified_amount > 0){
                    $fd->unverified_amount = $unverified_amount;
                    $unverified_amount = $unverified_amount - $unverified_amount;
                }else{
                    $fd->unverified_amount = 0;
                }
            }
            $fd->pre_deduct_com = $prededuct_com;
            if($prededuct_com > $fd->commission){
                $fd->pre_deduct_com = $fd->commission;
                $prededuct_com = $prededuct_com - $fd->commission;
            }else{
                $fd->pre_deduct_com = $prededuct_com;
                $prededuct_com = 0;
            }
            // if($prededuct_com > 0){
            //     if($prededuct_com > $fd->commission){
            //         $fd->prededuccom = $fd->comission;
            //         $prededuct_com = $prededuct_com - $fd->commission;
            //     }else if($prededuct_com < $fd->commission){
            //         $fd->prededuccom = $prededuct_com;
            //         $prededuct_com = 0;
            //     }
            // }else{
            //     $fd->prededuccom = 0;
            // }
        }

        // foreach($funded_payment_sched_template as $ff){
        //     if(isset($ff->unverified_amount) && $ff->unverified_amount!=0){
        //         array_push($sched_with_payment,$ff);
        //     }
        // }
        for($i = 0; $i < count($funded_payment_sched_template); $i++){
            // dump($i);
            if(isset($funded_payment_sched_template[$i]->unverified_amount) && $funded_payment_sched_template[$i]->unverified_amount != 0){
                $sched_with_payment[$i] = $funded_payment_sched_template[$i];
            }else if(isset($funded_payment_sched_template[$i]->pre_deduct_com) && $funded_payment_sched_template[$i]->pre_deduct_com > 0){
                $sched_with_payment[$i] = $funded_payment_sched_template[$i];
            }
        }
        //  return $sched_with_payment;        
        $ret = [
            'funded_student_payments'=>$funded_student_payments,
            'funded_payment_sched_template'=>$sched_with_payment
        ];
        return $ret;
    }

    public function collectionAction(Request $request){
        // return $request->all();
        if($request->action == 'accept'){
            return $this->acceptCollection($request->all());
        }else{
            return $this->declineCollection($request->student_payment);
        }
    }
    
    public function collectionEmail($payment_schedule,$student_payment){
        // return $payment_schedule;
        $total_amount = $student_payment['amount']+$student_payment['pre_deduc_comm'];
        // return $total_amount;
        $course = $student_payment['funded_student_course']['course']['code'].' - '.$student_payment['funded_student_course']['course']['name'];
        $content = '<b>Dear '.$student_payment['student']['party']['name'].',</b><br><br>Your collection has been verified and accepted.
        <p>Transaction Code: '.$student_payment['transaction_code'].'</p>
        <p>Course: '.$course.'</p>
        <p>Recieved Amount: '.number_format($student_payment['amount'],2).'</p>
        <p>Pre-deducted Commission: '.number_format($student_payment['pre_deduc_comm'],2).'</p>
        <p>Total Amount: '.number_format($total_amount,2).'</p>
        <p>Notes: '.$student_payment['note'].'</p>
        <p>Remarks: '.$student_payment['remakrs'].'</p>
        <br>';
        // return $content;
        $tbody = '';
            
        foreach($payment_schedule as $key=>$ps){
            $mont = $key+1;
            $tr = '
                <tr>
                    <td style="text-align:left; border: 1px solid #ddd;padding: 8px;">
                        '.$mont.'
                    </td>
                    <td style="text-align:left; border: 1px solid #ddd;padding: 8px;">
                        '.number_format($ps['balance'],2).'
                    </td>
                    <td style="text-align:left; border: 1px solid #ddd;padding: 8px;">
                        '.Carbon::parse($ps['due_date'])->format('d/m/Y').'
                    </td>
                    <td style="text-align:left; border: 1px solid #ddd;padding: 8px;">
                        '.number_format($ps['approved_amount_paid'],2).'
                    </td>
                    <td style="text-align:left; border: 1px solid #ddd;padding: 8px;">
                        '.number_format($ps['unverified_amount'],2).'
                    </td>
                    <td style="text-align:left; border: 1px solid #ddd;padding: 8px;">
                        '.number_format($ps['commission'],2).'
                    </td>
                    <td style="text-align:left; border: 1px solid #ddd;padding: 8px;">
                        '.number_format($ps['pre_deduct_com'],2).'
                    </td>
                </tr>
            ';
            $tbody .= $tr;
        }
        // return $tbody;
        // return 'aw';
        $table = '<table style="border-collapse: collapse;
        width: 100%;">
                    <thead>
                        <tr>
                            <th style="text-align:left; border: 1px solid #ddd;
                            padding: 8px;">Month #</th>

                            <th style="text-align:left; border: 1px solid #ddd;
                            padding: 8px;">Amount Due</th>

                            <th style="text-align:left; border: 1px solid #ddd;
                            padding: 8px;">Due Date</th>

                            <th style="text-align:left; border: 1px solid #ddd;
                            padding: 8px;">Amount Paid</th>

                            <th style="text-align:left; border: 1px solid #ddd;
                            padding: 8px;">Allocated Amount</th>

                            <th style="text-align:left; border: 1px solid #ddd;
                            padding: 8px;">Commission</th>

                            <th style="text-align:left; border: 1px solid #ddd;
                            padding: 8px;">Pre-deducted Commission</th>
                        </tr>
                    </thead>
                    <tbody>
                        '.$tbody.'
                    </tbody>
                </table>';
        $content .= $table;

        return $content;
    }

    public function acceptCollection($request){
        
        $payment_schedule   = $request['payment_schedule'];
        $student_payment    = $request['student_payment'];
        $trxn_code          = $request['student_payment']['transaction_code'];
        $prededuct_com      = $request['student_payment']['pre_deduc_comm'];
        $unverified_amount  = $request['student_payment']['amount'];
        $remarks            = $request['remarks'];
        $collection_id      = $request['student_payment']['id'];
        $user_id            = \Auth::user()->id;
        
        $collection = Collection::find($collection_id);
        // return $payment_schedule;
        try{
            DB::beginTransaction();
            if($payment_schedule!=null){
                
                foreach($payment_schedule as $ps){
                    // if(isset($ps['unverified_amount'])){
                        // if($ps['unverified_amount']!=0){
                        if($unverified_amount != 0){
                            $amount = $unverified_amount > $ps['balance'] ? $ps['balance'] : $unverified_amount;
                            // if($student_payment['payment_schedule_template_id'] == $ps['id']){
                            //     $payment_details = FundedStudentPaymentDetails::where('id',$student_payment['id'])->first();
                            //     $payment_details->amount = $ps['unverified_amount'];
                            //     $payment_details->verified = 1;
                            //     // $payment_details->remarks = $remarks;
                            //     $payment_details->collection_id = $student_payment['id'];
                            //     $payment_details->update();
                            //     $prededuct_com = 0;
                            // }else{
                                $new_payment = new FundedStudentPaymentDetails;
                                $new_payment->student_id = $student_payment['student_id'];
                                $new_payment->agent_id = $student_payment['agent_id'];
                                $new_payment->student_course_id = $student_payment['student_course_id'];
                                $new_payment->offer_letter_course_detail_id = $ps['offer_letter_course_detail_id'];
                                $new_payment->transaction_code = $student_payment['transaction_code'];
                                $new_payment->payment_schedule_template_id = $ps['id'];
                                $new_payment->payment_date = $student_payment['payment_date'];
                                $new_payment->amount = $amount;
                                $new_payment->verified = 1;
                                $new_payment->collection_id = $collection_id;
                                // $new_payment->remarks = $remarks;
                                $new_payment->user_id = $user_id;
                                $new_payment->pre_deduc_comm = $ps['pre_deduct_com'];
                                $new_payment->save();
        
                                $prededuct_com = 0;
                                $unverified_amount = $unverified_amount - $amount;
                            // }
                        }
                    // }                    
                }
            }else{
                return 'la pa nahuman';
                $payment_details = FundedStudentPaymentDetails::where('id',$student_payment['id'])->first();
                $payment_details->verified = 1;
                $payment_details->collection_id = $student_payment['id'];
                
                $payment_details->update();
            }
            
            $collection->verified = 1;
            $collection->remakrs = $remarks;
            $collection->update();
            
            $org = TrainingOrganisation::first();
            $send = new EmailSendingController;
            // return $student_payment;
            if(isset($student_payment['agent'])){
                $name = $student_payment['agent']['agent_name'];
                if(isset($student_payment['agent']['email'])){
                    $emailsTo[] = $student_payment['agent']['email'];
                }else{   
                    return response()->json(['status'=>'error','message'=>'Agent email not found']);
                }
            }else{
                return response()->json(['status'=>'error','message'=>'Agent not found']);
            }

            $content = $this->collectionEmail($request['payment_schedule'],$request['student_payment']);
            $s = $send->send_automate('Collection Verified', $content, ['Vorx' => $org->email_address], $emailsTo);
            // $s['status']='success';
            if($s['status']=='success'){
                DB::commit();
                $student_payment['verified'] = 1;
                $this->notifyAgent($student_payment);
                return response()->json(['status'=>'success']);
            }else{
                DB::rollback();
                return response()->json(['status'=>'error','message'=>'Email error']);
            }
            
            
        }catch(Exception $e){
            DB::rollback();
            return response()->json(['status'=>'error','message'=>$e->getMessage()]);
        }

    }

    public function notifyAgent($collection){
        
        $student = Student::with(['party.person'])->where('student_id', $collection['student_id'])->first();
        $funded_course = $collection['funded_student_course'];
        $course = '';
        if($funded_course['course_code'] == '@@@@'){
            $course = 'Unit of Compentency';
        }else{
            $course = $funded_course['course_code'].' - '. $funded_course['course']['name'];
        }

        $collection_status = "";
        if($collection['verified'] == 1){
            $collection_status = 'Verified';
        }elseif($collection['verified'] == 0){
            $collection_status = 'Pending';
        }elseif($collection['verified'] == 2){
            $collection_status = 'Declined';
        } 
        // return $collection_status;
        try {
            DB::beginTransaction();
            
            $notify = new Notification;
            $notify->fill([
                'type' => 'payment_collection',
                'table_name' => 'funded_student_payment_details',
                'table_id' => $collection['id'],
                'reference_id' => $collection['agent_id'] !== null ? $collection['agent_id'] : $student['student_id'], 
                'date_recorded' => Carbon::now()->setTimezone('Australia/Melbourne')->format('Y-m-d H:i:s'),
                'message' => 'Payment collection has been '. $collection_status .' under '. $course . ' course.',
                'is_seen' => 0,
                'action' => 'updated',
                'link' => $student['student_type_id'] == 1 ? '/student/' . $student['id'] : '/student/domestic/' . $student['id']
            ]);
            $notify->user()->associate(\Auth::user());
            $notify->save();

            DB::commit();
        } catch (\Throwable $th) {
            DB::rollback();

            throw $th;
        }
    }

    public function getTransaction($id,$trxn_code){
        if($trxn_code!='null'){
            $student_funded_payment_detail = FundedStudentPaymentDetails::with('agent','payment_schedule_template')->where('transaction_code',$trxn_code)->get();
            $payment_sched_template = PaymentScheduleTemplate::where('funded_student_course_id',$student_funded_payment_detail[0]->student_course_id)->get();
            
            foreach($payment_sched_template as $fd){
                $fd->approved_amount_paid = $fd->approved_amount_paid;
                
                $fd->balance = $fd->payable_amount - $fd->approved_amount_paid;
            }

            $res = json_encode(['student_funded_payment_detail'=>$student_funded_payment_detail,'payment_sched_template'=>$payment_sched_template]);
        }else{
            $student_funded_payment_detail = FundedStudentPaymentDetails::with('agent','payment_schedule_template')->where('id',$id)->get();
            $res = json_encode(['student_funded_payment_detail'=>$student_funded_payment_detail,'payment_sched_template'=>null]);     
        }

        return $res;
    }
}
