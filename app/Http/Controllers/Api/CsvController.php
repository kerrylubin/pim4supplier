<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use App\Laravue\Models\Role;
use App\Laravue\Models\User;

class CsvController extends BaseController
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function getCSVData()
    {
        // $currentUser = Auth::user();
        // if(!$currentUser->isAdmin()){
        //     $csvData = DB::table('supplier_mapping')->pluck('csv_headers');
        //     return response()->json( $csvData );
        // }
        // else{
        // }

        $csvData = DB::table('csv_mapping')->pluck('csv_header');
        return response()->json( $csvData );
    }

    public function getUserCSVData()
    {
        $currentUser = Auth::user();
        if(!$currentUser->isAdmin()){
            $csvData = DB::table('supplier_mapping')->pluck('csv_header');
            return response()->json( $csvData );
        }
        // $csvData = DB::table('supplier_mapping')->pluck('csv_header');
    }

    public function storeUserCSVData($csv_headers)
    {
        $currentUser = Auth::user();

        $array = explode(',',$csv_headers);

        if(isset($array)){
            // $array = null;

            if(!$currentUser->isAdmin()){
                for($i = 0; $i<= count($array); $i++)
                {
                    $sup_csv_data = array(
                        'user_id'     => $currentUser->id,
                        'csv_header' => $array[$i],
                    );

                    DB::table('supplier_mapping')->insert([$sup_csv_data]);
                }
            }
            else{
                for($i = 0; $i<= count($array); $i++)
                {
                    $admin_csv_data = array(
                        'csv_header' => $array[$i],
                    );

                    DB::table('csv_mapping')->insert([$admin_csv_data]);
                }
            }
        }
    }


}
