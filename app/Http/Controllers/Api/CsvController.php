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

    public function getSupUsers()
    {
        $userQuery = DB::table('supplier_profile_users')
        ->select('supplier_profile_users.user_id','supplier_profile_users.name',
        'supplier_profile_users.email', 'supplier_profile_users.role')
        ->where('supplier_profile_users.id', '=', $currentUser->id)->get();
        return response()->json($userQuery);
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
     * Store a newly created resource in storage.
     *
     * @param  User $user_id
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function storeTableKeysData(Request $request)
    {
        $currentUser = Auth::user();

        $params = $request->all();
        // $csv_keys = array_keys($params);

        echo'params: '.var_dump($params);

        for($i = 0; $i<= count($params); $i++){

            if(isset($params[$i])){

                $csv_data = array(
                    'user_id'     => $currentUser->id,
                    'csv_header'  => $params[$i],
                    // 'csv_content' => $params[$i]
                );

                DB::table('table_headers')->insert([$csv_data]);
            }
        }
    }
    /**
     * Store a newly created resource in storage.
     *
     * @param  User $user_id
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function storeTableValData(Request $request)
    {
        $currentUser = Auth::user();
        // $content_array = explode(',',$table_val);
        // $content_array = explode(',',$table_values);
        // echo'data: '.var_dump($content_array);

        $params = $request->all();
        $csv_values = array_values($params);

        for($i = 0; $i<= count($csv_values); $i++){
            // echo'csv_keys: '.var_dump($csv_keys[$i]);
            // echo'params: '.var_dump($params[$i]);

            if(isset($csv_values[$i])){

                $csv_data = array(
                    'user_id'     => $currentUser->id,
                    // 'csv_header'  => $csv_keys[$i],
                    'csv_content' => $csv_values[$i]
                );

                DB::table('table_data')->insert([$csv_data]);
            }
        }

    }


    public function storeUserCSVData($csv_headers)
    {
        $currentUser = Auth::user();

        $array = explode(',',$csv_headers);

        if(!$currentUser->isAdmin()){

            for($i = 0; $i<= count($array); $i++)
            {
                // $csv_headers = $array[$i];
                // $csv_header_sql = "select * from `supplier_mapping` where `csv_header` = $csv_headers ";

                // $query_csv = DB::table('supplier_mapping')
                // ->select($csv_header_sql);
                // if(!$query_csv){
                    if(isset($array[$i])){

                        $sup_csv_data = array(
                            'user_id'     => $currentUser->id,
                            'csv_header' => $array[$i],
                        );

                        DB::table('supplier_mapping')->insert([$sup_csv_data]);
                    }

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
                    if(isset($array[$i])){

                        $admin_csv_data = array(
                            'csv_header' => $array[$i],
                        );
                        DB::table('csv_mapping')->insert([$admin_csv_data]);
                    }

                // }
            }
        }
    }


}
