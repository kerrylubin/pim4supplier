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

        /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function storeSupplierImportProfile(Request $request )
    {
        $currentUser = Auth::user();
        $params = $request->all();

        // if($currentUser->isAdmin()){

            $supplier_profile_data = DB::table('import_profile')
            ->where('import_profile.supplier_id', '=', $params['supplier_id'])
            ->get();

            echo'supplier_profile_data: '.var_dump($supplier_profile_data);


            $unique_attribute = json_decode($supplier_profile_data, true);
            // echo'unique_attributes: '.var_dump($unique_attribute);

            if(count($unique_attribute) == 0){
                $import_profile = array(
                    'supplier_id'   =>  $params['supplier_id'],
                    'frequency'     =>  $params['frequency'],
                    'feed_url'      =>  $params['url'],
                    'delimiter'     =>  $params['delimiter'],
                    'unique_attribute'   =>  'Artikelnummer',
                );
                DB::table('import_profile')->insert([$import_profile]);
            }
            else{
                if(isset($unique_attribute[0]['unique_attribute'])){

                    $import_profile = array(
                        'supplier_id'   =>  $params['supplier_id'],
                        'frequency'     =>  $params['frequency'],
                        'feed_url'      =>  $params['url'],
                        'delimiter'     =>  $params['delimiter'],
                        'unique_attribute'   =>  $unique_attribute[0]['unique_attribute'],
                    );
                    DB::table('import_profile')->insert([$import_profile]);
                }

            }
        // }
        // else{
        //     return response()->json(new JsonResponse([], 'This is only for suppliers'), Response::HTTP_UNAUTHORIZED);
        // }


    }

    public function getImportProfiles()
    {
        $supplier_profile = DB::table('supplier_profile')
        ->select('supplier_profile.feed_url', 'supplier_profile.delimiter',
        'supplier_profile.frequency','supplier_profile.supplier_id','supplier_profile.id')
        ->get();
        return response()->json($supplier_profile);
    }

    public function getSupplierImportProfile($id)
    {
        $supplier_profile = DB::table('import_profile')
        ->where('import_profile.supplier_id', '=', $id)
        ->get();
        return response()->json($supplier_profile);
    }

    public function deleteSupplierProfile($id){

        $delete = DB::table('supplier_profile')
        ->where('supplier_profile.id', '=', $id)->delete();
        return response()->json($delete);
    }


}
