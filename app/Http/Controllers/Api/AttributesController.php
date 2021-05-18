<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use App\Laravue\Models\Role;
use App\Laravue\Models\User;

class AttributesController extends BaseController
{
    public function getAttributes()
    {
        $attr_data = DB::table('attributes')
        ->select('attributes.id', 'attributes.code', 'attributes.name',
         'attributes.type', 'attributes.required', 'attributes.unique')
        ->get();
        return response()->json($attr_data);
    }
    /**
     * Store a newly created resource in storage.
     *
     *  @param  User $user_id
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function storeAttributes(Request $request )
    {
        // echo'data: '.var_dump($request->all());
        $params = $request->all();

        $attr_data = array(
            'code'  => $params['code'],
            'name'  => $params['name'],
            'type'  => $params['type'],
            'required'  => $params['required'],
            'unique'  => $params['unique'],
        );

        DB::table('attributes')->insert([$attr_data]);
    }

}
