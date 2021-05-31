<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use App\Laravue\Models\Role;
use App\Laravue\Models\User;
use App\Laravue\JsonResponse;
use Illuminate\Http\Response;

class ImportProfileController extends BaseController
{
    public function getCurrentUserId(){
        $currentUser = Auth::user();
        return $currentUser->id;
    }


    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function storeImportProfile(Request $request )
    {
        $currentUser = Auth::user();
        $params = $request->all();
        // echo'params: '.var_dump($params);

        if(!$currentUser->isAdmin()){

            $import_profile = array(
                'supplier_id'   =>  $currentUser->id,
                'frequency'     =>  $params['frequency'],
                'feed_url'      =>  $params['url'],
                'delimiter'     =>  $params['delimiter'],
            );

            DB::table('supplier_profile')->insert([$import_profile]);

        }
        else{
            return response()->json(new JsonResponse([], 'This is only for suppliers'), Response::HTTP_UNAUTHORIZED);
        }


    }

    public function getImportProfile($id)
    {
        $supplier_profile = DB::table('supplier_profile')
        ->select('supplier_profile.feed_url', 'supplier_profile.delimiter',
        'supplier_profile.frequency')
        ->where('supplier_profile.supplier_id', '=', $this->getCurrentUserId())
        ->get();
        return response()->json($supplier_profile);
    }

}
