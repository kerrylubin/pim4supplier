<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use App\Laravue\Models\Role;
use App\Laravue\Models\User;

class ImportProfile extends BaseController
{
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

        $import_profile = array(
            'supplier_id'   =>  $currentUser->id,
            'frequency'     =>  $params['frequency'],
            'feed_url'      =>  $params['url'],
            'delimiter'     =>  $params['delimiter'],
        );

        DB::table('supplier_profile')->insert([$import_profile]);

    }
}
