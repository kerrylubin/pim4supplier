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
            // $csvData = DB::table('supplier_mapping')->select('supplier_mapping.csv_header')->where('supplier_mapping.user_id', '=', $id)->get();
            $csvData = DB::table('supplier_mapping')->pluck('csv_header');

            return response()->json($csvData);
        }
    }

    public function getSupCSVData($id)
    {
        $csvData = DB::table('supplier_mapping')
        ->select('supplier_mapping.csv_header')
        ->where('supplier_mapping.user_id', '=', $id)
        ->pluck('csv_header');
        return response()->json($csvData);
    }
        /**
     * @param array table_keys
     * @param array table_values
     */
    public function storeTableKeysData($table_keys )
    {
        $currentUser = Auth::user();
        $header_array = explode(',',$table_keys);
        // $content_array = explode(',',$table_values);

        // echo'data: '.var_dump($header_array);
        // echo'data: '.$content_array;

        for($i = 0; $i<= count($header_array); $i++){

            $csv_data = array(
                'user_id'     => $currentUser->id,
                'csv_header'  => $header_array[$i],
                // 'csv_content' => $$content_array[$i]
            );

            DB::table('table_data')->insert([$csv_data]);
        }

    }

    public function storeTableValData($table_val )
    {
        $currentUser = Auth::user();
        $content_array = explode(',',$table_val);
        // $content_array = explode(',',$table_values);

        // echo'data: '.var_dump($content_array);
        // echo'data: '.$content_array;

        for($i = 0; $i<= count($content_array); $i++){

            $csv_data = array(
                'user_id'     => $currentUser->id,
                // 'csv_header'  => $header_array[$i],
                'csv_content' => $content_array[$i]
            );

            DB::table('table_data')->insert([$csv_data]);
        }

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
                    // $csv_headers = $array[$i];
                    // $csv_header_sql = "select * from `supplier_mapping` where `csv_header` = $csv_headers ";

                    // $query_csv = DB::table('supplier_mapping')
                    // ->select($csv_header_sql);
                    // if(!$query_csv){

                        $sup_csv_data = array(
                            'user_id'     => $currentUser->id,
                            'csv_header' => $array[$i],
                        );

                        DB::table('supplier_mapping')->insert([$sup_csv_data]);
                    // }

                }
            }
            else{
                for($i = 0; $i<= count($array); $i++)
                {
                    // $csv_headers = $array[$i];

                    // $csv_header_sql = "select `csv_header` from `csv_mapping`";

                    // $query_csv = DB::table('csv_mapping')
                    // ->select('csv_mapping.csv_header')->where('csv_mapping.csv_header', '=', $csv_headers);

                    // $query_csv = DB::select($csv_header_sql);
                    // echo'query: '.var_dump($query_csv);

                    // if($query_csv){
                    //     echo$array[$i];

                        $admin_csv_data = array(
                            'csv_header' => $array[$i],
                        );

                        DB::table('csv_mapping')->insert([$admin_csv_data]);
                    // }
                }
            }
        }
    }


}
