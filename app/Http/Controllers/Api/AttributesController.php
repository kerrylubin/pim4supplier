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
        if($params['required'] && $params['unique'] == 'yes'){

            $params['required'] = 0;
            $params['unique'] = 0;

            $attr_data = array(
                'code'  => $params['code'],
                'name'  => $params['name'],
                'type'  => $params['type'],
                'required'  => $params['required'],
                'unique'  => $params['unique'],
            );

            DB::table('attributes')->insert([$attr_data]);
        }
        else if($params['required'] && $params['unique'] == 'no'){

            $params['required'] = 1;
            $params['unique'] = 1;

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

    public function getSupAttributeId($label){

        $attr_data = DB::table('supplier_attributes')
        ->select('supplier_attributes.attribute_id')
        ->where('supplier_attributes.attribute_label', '=', $label)
        ->get();
        return json_encode($attr_data);
    }

    public function getAdminAttributeId(){

        // $attr_data = DB::table('supplier_attributes')
        // ->select('supplier_attributes.attribute_id')
        // ->where('supplier_attributes.attribute_label', '=', $label)
        // ->get();

        $attr_data = DB::table('attributes')->pluck('id');

        return json_encode($attr_data);
    }


    public function getSupAttrLabels($id){

        $attr_data = DB::table('attribute_mapping')
        ->select('attribute_mapping.attribute_id')
        ->where('attribute_mapping.supplier_id', '=',$id )
        ->get();
        return json_encode($attr_data);
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
        $supplier_attributes = $params['supplier_attributeVal'];
        $supplier_attributes_id = $params['supplier_attributeId'];

        // echo'params: '.var_dump($attributes);
        // echo'supplier_attributes_id: '.var_dump($supplier_attributes);
        // echo'supplier_attributes_id: '.var_dump($supplier_attributes_id);

        for($i = 0; $i<= count($supplier_attributes); $i++){

            $user_id = DB::table('supplier_attributes')
            ->where('supplier_attributes.supplier_id','=',$currentUser->id)->get();

            if(isset($supplier_attributes[$i]) && $user_id === null){
                $attr_data = array(
                    'supplier_id'  => $currentUser->id,
                    'attribute_id' => $supplier_attributes_id[$i],
                    'attribute_label'  => $supplier_attributes[$i],
                );

                DB::table('supplier_attributes')->insert([$attr_data]);
            }
        }

        //get the id's of the attributes that are chosen to be matched.

        for($i = 0; $i<= count($attributes); $i++){

            if(isset($attributes[$i])){
                $attr_sup_id = explode(" ", $attributes[$i]);
                $attr = explode(" ", $attributes[$i]);
                $attr_id = explode(" ", $attributes[$i]);

                $json = $this->getSupAttributeId($attr[0]);
                $sup_attr_id = json_decode($json, true);

                // echo'sup_attr_id: '.var_dump($sup_attr_id[0]['attribute_id']);

                $attr_mapping_data = array(
                    'supplier_id'  => $currentUser->id,
                    'attribute_id'  => $attr_id[2],
                    'attribute_supplier_id'  => $sup_attr_id[0]['attribute_id'],
                    'attribute_label'  => $attr[0],
                );

                DB::table('attribute_mapping')->insert([$attr_mapping_data]);

            }
        }
    }

    /**
     * Store a newly created resource in storage.
     *
     *  @param  User $user_id
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function storeEditedSupAttributes(Request $request )
    {
        $currentUser = Auth::user();
        $params = $request->all();
        $attributes = $params['attributes'];
        $user_id = $params['userId'];

        //get the id's of the attributes that are chosen to be matched.

        $sup_user_id = DB::table('attribute_mapping')
        ->select('attribute_mapping.supplier_id')
        ->where('attribute_mapping.supplier_id','=',$user_id)->get();

        //get all the ids in a array
        // $json_id = $this->getSupAttrLabels($user_id);
        // echo'json_id: '.var_dump($json_id);
        // $admin_attr_id = json_decode($json_id, true);
        // echo'admin_attr_id: '.var_dump($admin_attr_id);

        $json_id = $this->getAdminAttributeId();
        // echo'json_id: '.var_dump($json_id);
        $admin_attr_id = json_decode($json_id, true);
        echo'admin_attr_id: '.var_dump($admin_attr_id);

        if(count($sup_user_id) >= 0){
            echo'has the sup ID';

            DB::table('attribute_mapping')
            // ->select('attribute_mapping.attribute_supplier_id', 'attribute_mapping.attribute_label')
            ->where('attribute_mapping.supplier_id', '=', $user_id)->delete();

            for($i = 0; $i<= count($attributes); $i++){

                if(isset($attributes[$i])){
                    // $attr_sup_id = explode(" ", $attributes[$i]);
                    // $attr = explode(" ", $attributes[$i]);
                    // $attr_id = explode(" ", $attributes[$i]);

                    // echo'attr_sup_id: '.var_dump($attr_sup_id[1]);
                    // echo'attr_id: '.var_dump($attr_id[2]);

                    $json = $this->getSupAttributeId($attributes[$i]);
                    $sup_attr_id = json_decode($json, true);

                    // if(isset($sup_attr_id[$i])){


                        $attr_mapping_data = array(
                            'supplier_id'  => $user_id,
                            'attribute_id'  => $admin_attr_id[$i],
                            'attribute_supplier_id'  => $sup_attr_id[0]['attribute_id'],
                            'attribute_label'  => $attributes[$i],
                        );

                        DB::table('attribute_mapping')->insert([$attr_mapping_data]);
                    // }


                    // echo'attributes: '.var_dump($attributes[$i]);
                    // echo'sup_attr_id: '.var_dump($sup_attr_id[0]['attribute_id']);

                    // $attr_mapping_data = array(
                    //     // 'attribute_mapping.supplier_id'  => $currentUser->id,
                    //     'attribute_mapping.attribute_supplier_id'  => $sup_attr_id[0]['attribute_id'],
                    //     'attribute_mapping.attribute_label'  => $attributes[$i],
                    // );

                    // DB::table('attribute_mapping')
                    // ->where('attribute_mapping.supplier_id','=',$user_id )
                    // ->update($attr_mapping_data);
                }

            }
        }
        else if(count($sup_user_id) == 0){
            echo'dont have the sup ID';
            // $json_id = $this->getSupAttrLabels($user_id);
            // $admin_attr_id = json_decode($json_id, true);

            $json = $this->getSupAttributeId($attributes[$i]);
            $sup_attr_id = json_decode($json, true);

            // echo'sup_attr_id: '.var_dump($sup_attr_id);

            $attr_mapping_data = array(
                'supplier_id'  => $user_id,
                'attribute_id'  => $admin_attr_id[$i],
                'attribute_supplier_id'  => $sup_attr_id[0]['attribute_id'],
                'attribute_label'  => $attributes[$i],
            );

            DB::table('attribute_mapping')->insert([$attr_mapping_data]);

            // $attr_mapping_data = array(
            //     // 'attribute_mapping.supplier_id'  => $currentUser->id,
            //     'attribute_mapping.attribute_supplier_id'  => $sup_attr_id[0]['attribute_id'],
            //     'attribute_mapping.attribute_label'  => $attributes[$i],
            // );

            // DB::table('attribute_mapping')
            // ->where('attribute_mapping.supplier_id','=',$user_id )
            // ->update($attr_mapping_data);

        }

    }


    // public function getSupAttributes($id)
    // {
    //     $attr_data = DB::table('supplier_attribute')
    //     ->select('supplier_attribute.attribute_label', 'supplier_attribute.id')
    //     ->where('supplier_attribute.supplier_id', '=', $id)
    //     ->get();
    //     return response()->json($attr_data);
    // }

    public function getSupAttributes($id)
    {
        $attr_data = DB::table('attribute_mapping')
        ->select('attribute_mapping.attribute_id', 'attribute_mapping.attribute_supplier_id',
        'attribute_mapping.attribute_label')
        ->where('attribute_mapping.supplier_id', '=', $id)
        ->get();
        return response()->json($attr_data);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  User $user_id
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */

    public function getSupAttributeLabelsbyId(Request $request)
    {
        $params = $request->all();
        $user_id = $params['userId'];

        $attr_mapping_ids = DB::table('attribute_mapping')
        ->select('attribute_mapping.attribute_id', 'attribute_mapping.attribute_supplier_id', 'attribute_mapping.supplier_id')
        ->where('attribute_mapping.supplier_id', '=', $user_id)
        ->get();

        $json_ids = json_encode($attr_mapping_ids);
        $sup_attr_id = json_decode($json_ids, true);

        $sup_attr_labels = DB::table('supplier_attributes')
        ->select('supplier_attributes.attribute_labels')
        ->where('supplier_attributes.supplier_id', '=', $sup_attr_id[0]['attribute_id'])
        ->get();

        $json_labels = json_encode($sup_attr_labels);
        $sup_attr_labels = json_decode($json_labels, true);

        for ($i = 0; $i<= count($sup_attr_labels); $i++){

            $user_id = DB::table('supplier_attributes')
            ->select('supplier_attributes.supplier_id')
            ->whereIn('supplier_attributes.attribute_labels', $sup_attr_labels[$i]['attribute_labels'])->get();

        }

        return json_encode($attr_data);
    }


    /**
     * Store a newly created resource in storage.
     *
     * @param  User $user_id
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function getSupAttributesLabels( Request $request)
    {
        $params = $request->all();
        // echo'supAttrId'.var_dump($params['supAttrId']);
        // echo'userId'.var_dump($params['userId']);

        $supplier_attributes_id = $params['supAttrId'];
        $id = implode(',', $supplier_attributes_id);

        for ($i = 0; $i<= count($supplier_attributes_id); $i++)
        {

            if(isset($supplier_attributes_id)){
                // echo'id: '.var_dump($supplier_attributes_id[$i]);

                $attr_data = DB::table('supplier_attributes')
                ->select('supplier_attributes.attribute_label')
                ->whereIn('supplier_attributes.attribute_id', $supplier_attributes_id)
                // ->where('supplier_attributes.supplier_id', '=', $params['userId'])
                ->get();
                return response()->json($attr_data);
            }

        }

    }



}
