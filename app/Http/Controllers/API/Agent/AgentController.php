<?php

namespace App\Http\Controllers\API\Agent;

use App\Http\Controllers\Controller;
use App\Models\Agent;
use App\Models\AgentAttachment;
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
            Agent::where('id',$agent)->update($data);
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
        $data = AgentAttachment::where('hash',$attachment)->get();
        return $data;
    }

    public function saveAttachment(Request $request)
    {
        # code...
    }
}
