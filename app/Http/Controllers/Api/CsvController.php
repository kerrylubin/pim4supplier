<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;


class CsvController extends BaseController
{
        /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function getCSVData()
    {
        $csvData = DB::table('csv_mapping')->pluck('csv_header');
        // ->first();
        return response()->json( $csvData );
    }

}
