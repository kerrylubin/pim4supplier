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
        $currentUser = Auth::user();

        if(!$currentUser->isAdmin()){
            $csvData = DB::table('supplier_mapping')->pluck('csv_headers');
            return response()->json( $csvData );
        }
        else{
            $csvData = DB::table('csv_mapping')->pluck('csv_header');
            return response()->json( $csvData );
        }
    }

    public function getUserCSVData()
    {
        $csvData = DB::table('supplier_mapping')->pluck('csv_header');
    }

    public function storeUserCSVData($csv_headers)
    {
        $currentUser = Auth::user();

        echo'csv_headers: '.array($csv_headers);

        if(!$currentUser->isAdmin()){
            for($i = 0; $i<= count($csv_headers); $i++)
            {
                $sup_csv_data = array(
                    'user_id'     => $currentUser->id,
                    'csv_headers' => $csv_headers[$i],
                );

                echo'csv sup data: '.$sup_csv_data;

                DB::table('supplier_mapping')->insert([$sup_csv_data]);

                // DB::table('supplier_mapping')->insert('insert into supplier_mapping
                // (
                //     user_id, csv_headers
                // )
                // values (?,?)',
                // [
                //     $sup_csv_data
                // ]);
            }
        }
        else{
            for($i = 0; $i<= count($csv_headers); $i++)
            {
                $admin_csv_data = array(
                    'csv_header' => $csv_headers[$i],
                );

                DB::table('csv_mapping')->insert([$admin_csv_data]);
            }
        }
    }


}
