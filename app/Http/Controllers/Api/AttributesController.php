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
        // $currentUser = Auth::user();
        // if($currentUser->isAdmin()){
            // $attr_data = DB::table('attributes')->pluck('name');
            // return response()->json($attr_data);
        // }
        // else{

            $attr_data = DB::table('attributes')
            ->select('attributes.id', 'attributes.code', 'attributes.name',
             'attributes.type', 'attributes.required', 'attributes.unique')
            ->get();
            return response()->json($attr_data);
        // }
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
    /**
     * Store a newly created resource in storage.
     *
     *  @param  User $user_id
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function storeAttributesMapping(Request $request )
    {
        $currentUser = Auth::user();
        $params = $request->all();
        echo'params: '.var_dump($params);

        // $attr_data = array(
        //     'supplier_id'  => $currentUser->id,
        //     'attribute_id'  => $params['name'],
        //     'attribute_supplier_id'  => $params['id'],
        // );

        // DB::table('attributes')->insert([$attr_data]);
    }

        /**
     * Store a newly created resource in storage.
     *
     *  @param  User $user_id
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function storeSupAttributes(Request $request )
    {
        $currentUser = Auth::user();
        $params = $request->all();
        $attributes = $params['attributes'];
        echo'params: '.var_dump($attributes);

        for($i = 0; $i<= count($attributes); $i++){

            if(isset($attributes[$i])){
                $attr_sup_id = explode(" ", $attributes[$i]);
                $attr = explode(" ", $attributes[$i]);
                $attr_id = explode(" ", $attributes[$i]);

                echo'attr_sup_id: '.var_dump($attr_sup_id[1]);
                echo'attr_id: '.var_dump($attr_id[2]);

                $attr_mapping_data = array(
                    'supplier_id'  => $currentUser->id,
                    'attribute_id'  => $attr_id[2],
                    'attribute_supplier_id'  => $attr_sup_id[1],
                );

                DB::table('attribute_mapping')->insert([$attr_mapping_data]);


                $attr_data = array(
                    'id'           => $attr_sup_id[1],
                    'supplier_id'  => $currentUser->id,
                    'attribute_label'  => $attr[0],
                );

                DB::table('supplier_attribute')->insert([$attr_data]);
            }
        }
    }

    public function getSupAttributes($id)
    {
        $attr_data = DB::table('supplier_attribute')
        ->select('supplier_attribute.attribute_label', 'supplier_attribute.id')
        ->where('supplier_attribute.supplier_id', '=', $id)
        ->get();
        return response()->json($attr_data);
    }

}
