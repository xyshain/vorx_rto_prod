<?php

namespace App\Http\Controllers\API\Agent;

use App\Http\Controllers\Controller;
use App\Models\AgentAttachment;
use App\Models\AgentDetail;
use App\Models\AvtClientIndigenousStatus;
use App\Models\AvtCountryIdentifier;
use App\Models\AvtDisabilityTypes;
use App\Models\AvtHighestSchlLvlCompleted;
use App\Models\AvtLabourForceStatus;
use App\Models\AvtLangIdentifier;
use App\Models\AvtPriorEducationAchievement;
use App\Models\AvtStateIdentifier;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class AgentController extends Controller
{
    public function profile()
    {
        try {
            $user = Auth::user();
            return $user->agent_details;
            //code...
        } catch (\Throwable $th) {
            throw $th;
        }
        
    }
    public function update(Request $request, $agent)
    {
        try {
            DB::beginTransaction();
            $data = $request->all();
            AgentDetail::where('id',$agent)->update($data);
            DB::commit();
            return response(['status'=>'success','message'=> 'Updated Successfully'],200);
        } catch (\Throwable $th) {
            DB::rollback();
            throw $th;
        }
    }

    public function fetchAllAttachment()
    {
        if( Auth::user()->agent_details != null){
            return Auth::user()->agent_details->attachments;
        }else{
            return response(['status'=>'error','message'=>'not found'],404);
        }
    }

    public function fetchAttachment($attachment)
    {
        $data = AgentAttachment::where('hash_name',$attachment)->first();
        return $data;
    }

    public function saveAttachment(Request $request)
    {
        # code...
    }

    public function defaults(){
        $defaults = [];
        
        $country =  AvtCountryIdentifier::orderBy('full_name', 'asc')->select('full_name as label','identifier as value')->get();
        $disabilities = AvtDisabilityTypes::orderBy('description','asc')->select('description as label','value')->get();
        $achievements = AvtPriorEducationAchievement::orderBy('description','asc')->select('description as label','value')->get();
        $language = AvtLangIdentifier::orderBy('description','asc')->select('description as label','value')->get();
        $location = AvtStateIdentifier::orderBy('state_name','asc')->select('state_name as label','state_key as value')->get();
        $indigenous = AvtClientIndigenousStatus::orderBy('description','asc')->select('description as label','value')->get();
        $labour_force_status = AvtLabourForceStatus::orderBy('status','asc')->select('status as label','value')->get();
        $schl_lvl_completed = AvtHighestSchlLvlCompleted::orderBy('description','asc')->select('description as label','value')->get();
        $defaults['country'] = $country;
        $defaults['disabilities'] = $disabilities;
        $defaults['achievements'] = $achievements;
        $defaults['language'] = $language;
        $defaults['location'] = $location;
        $defaults['indigenous'] = $indigenous;
        $defaults['labour_force_status'] = $labour_force_status;
        $defaults['schl_lvl_completed'] = $schl_lvl_completed;
        
        return $defaults;
    }
}
