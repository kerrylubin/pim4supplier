<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;


class CsvController extends Controller
{
        /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function getCSVData()
    {
        $csvData = DB::table('csv_mapping')->get();
        return response()->json( $csvData );
    }

}
