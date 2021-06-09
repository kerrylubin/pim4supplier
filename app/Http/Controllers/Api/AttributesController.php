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

            $attr_data = DB::table('admin_attributes')
            ->select('admin_attributes.id', 'admin_attributes.code', 'admin_attributes.name',
             'admin_attributes.type', 'admin_attributes.required', 'admin_attributes.unique')
            ->get();
            return response()->json($attr_data);
        // }
    }
    /**
     * Store a newly created resource in storage.
     *
     * @param  User $user_id
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function storeAdminAttributes(Request $request )
    {
        // echo'data: '.var_dump($request->all());
        $params = $request->all();

        $bool_required = ($params['required'] == 'yes' ? 1 : 0);
        $bool_unique = ($params['unique'] == 'yes' ? 1 : 0);
        // echo'id: '.var_dump($id);

        $attr_data = array(
            // 'id'    => $id,
            'code'  => $params['code'],
            'name'  => $params['name'],
            'type'  => $params['type'],
            'required'  => $bool_required,
            'unique'  => $bool_unique,
        );

        DB::table('admin_attributes')->insert([$attr_data]);

        // $data = DB::table('admin_attributes')
        // ->select('admin_attributes.id')
        // ->get();

        // $json = json_encode($data);
        // $attr_id = json_decode($json, true);
        // $id = ( count($attr_id));

        // $attr_mapping_data = array(
        //     // 'supplier_id'  => $currentUser->id,
        //     'attribute_id'  => $attr_id,
        //     // 'attribute_supplier_id'  => $sup_attr_id[0]['attribute_id'],
        //     // 'attribute_label'  => $attr[0],
        // );

        // DB::table('attribute_mapping')->insert([$attr_mapping_data]);
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

    public function getSupAttributeId($labels){

        $attr_data = DB::table('supplier_attributes')
        ->select('supplier_attributes.id')
        ->whereIn('supplier_attributes.attribute_label', $labels)
        ->get();
        return $attr_data;

    }

    public function getAdminAttributeId($labels){

        $attr_data = DB::table('admin_attributes')
        ->select('admin_attributes.id')
        ->whereIn('admin_attributes.name', $labels)
        ->get();
        return $attr_data;
    }


    public function getSupAttrLabels($id){

        $attr_data = DB::table('attribute_mapping')
        ->select('attribute_mapping.attribute_id')
        ->where('attribute_mapping.supplier_id', '=',$id )
        ->get();
        return $attr_data;

    }

    public function getSupplierAttributeLabel($supplierId){
        $attr_data = DB::table('supplier_attributes')
        ->select('supplier_attributes.attribute_label')
        ->where('supplier_attributes.profile_id', '=', $supplierId)
        ->get();

        $json = json_encode($attr_data);

        $labels = json_decode($json,true);

        $sup_array = [];

        for($i = 0; $i<= count($labels); $i++){
            if(isset($labels[$i])){
                // echo'sup_array'.var_dump($labels[$i]["attribute_label"])."\n";
                $sup_array[]  = $labels[$i]["attribute_label"];
            }
        }
        array_unshift($sup_array,"");
        unset($sup_array[0]);

        return $sup_array;
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
        $time = $params['time'];
        $supplier_attributes = $params['supplier_attributeVal'];
        $supplier_attributes_id = $params['supplier_attributeId'];

        // echo'params: '.var_dump($attributes);
        // echo'supplier_attributes_id: '.var_dump($supplier_attributes);
        // echo'supplier_attributes_id: '.var_dump($supplier_attributes_id);

        //check the sup_attr_label that are in the DB.
        //compare to see if there the same if not store them
        $user_id = DB::table('supplier_attributes')
        ->select('supplier_attributes.profile_id')
        ->where('supplier_attributes.profile_id','=',$currentUser->id)->get();

        $mapping_user_id = DB::table('attribute_mapping')
        ->select('attribute_mapping.id')
        ->where('attribute_mapping.id','=',$currentUser->id)->get();

        // echo'user_id: '.count($user_id);

        // if(count($user_id) <= 0){

            for($i = 0; $i<= count($supplier_attributes); $i++){

                if(count($user_id) <= 0){
                    // echo'low: ';
                    if(isset($supplier_attributes[$i]) ){

                        $attr_data = array(
                            'profile_id'  => $currentUser->id,
                            // 'attribute_id' => $supplier_attributes_id[$i],
                            'attribute_label'  => $supplier_attributes[$i],
                        );

                        DB::table('supplier_attributes')->insert([$attr_data]);
                    }
                }
            }

            //get the id's of the attributes that are chosen to be matched.
            //cant store attribute mapping twice because of duplicate entry on
            //primary key.
            if(count($mapping_user_id) <=0){

                for($i = 0; $i<= count($attributes); $i++){
                    // if(count($mapping_user_id <=0)){

                    if(isset($attributes[$i])){
                        $attr_sup_id = explode(" ", $attributes[$i]);
                        $attr = explode(" ", $attributes[$i]);
                        $attr_id = explode(" ", $attributes[$i]);

                        $json = $this->getSupAttributeId($attr[0]);
                        $sup_attr_id = json_decode($json, true);

                        // echo'sup_attr_id: '.var_dump($sup_attr_id);

                        $attr_mapping_data = array(
                            'id'  => $currentUser->id,
                            'supplier_attribute_id'  => $sup_attr_id[0]['attribute_id'],
                            'admin_attribute_id'  => $attr_id[2],
                            // 'attribute_label'  => $attr[0],
                        );

                        DB::table('attribute_mapping')->insert([$attr_mapping_data]);
                    }
                    // }
                    //
                }
            }
            // else if(count($mapping_user_id) >=0){
            //     DB::table('attribute_mapping')
            //     // ->select('attribute_mapping.attribute_supplier_id', 'attribute_mapping.attribute_label')
            //     ->where('attribute_mapping.supplier_id', '=', $currentUser->id)->delete();

            //     for($i = 0; $i<= count($attributes); $i++){

            //         if(isset($attributes[$i])){
            //             $attr_sup_id = explode(" ", $attributes[$i]);
            //             $attr = explode(" ", $attributes[$i]);
            //             $attr_id = explode(" ", $attributes[$i]);

            //             $json = $this->getSupAttributeId($attr[0]);
            //             $sup_attr_id = json_decode($json, true);

            //             // echo'sup_attr_id: '.var_dump($sup_attr_id);

            //             $attr_mapping_data = array(
            //                 'supplier_id'  => $currentUser->id,
            //                 'attribute_id'  => $attr_id[2],
            //                 'attribute_supplier_id'  => $sup_attr_id[0]['attribute_id'],
            //                 'attribute_label'  => $attr[0],
            //             );

            //             DB::table('attribute_mapping')->insert([$attr_mapping_data]);
            //         }
            //     }
            // }


    }

    /**
     * Store a newly created resource in storage.
     *
     *  @param  User $user_id
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function storeSupplierMappings(Request $request )
    {
        $currentUser = Auth::user();
        $params = $request->all();

        $sup_attributes = $params['supplier']['attributes'];
        $admin_attributes = $params['admin']['attributes'];
        echo'supplier: '.var_dump($params['supplier']['attributes']);

        /*
            store the added inputs in admin attributes
        */
        $user_id = $params['supplier']['userId'];

        //get the id's of the attributes that are chosen to be matched.
        $sup_user_id = DB::table('supplier_attributes')
        ->select('supplier_attributes.profile_id')
        ->where('supplier_attributes.profile_id','=', $user_id)->get();

        $supplier_attributes_id = DB::table('supplier_attributes')
        ->select('supplier_attributes.id')
        ->where('supplier_attributes.profile_id', '=', $user_id)
        ->get();

        $attr_mapping_ids = DB::table('attribute_mapping')
        ->select('attribute_mapping.supplier_attribute_id')
        ->whereIn('attribute_mapping.supplier_attribute_id', json_decode($supplier_attributes_id, true))
        ->get();

        // echo'attr_mapping_ids: '.$attr_mapping_ids;

        //get all the ids in a array

        $json_id = $this->getAdminAttributeId($admin_attributes);
        // echo'json_id: '.var_dump($json_id);
        $admin_attr_id = json_decode($json_id, true);
        // echo'admin_attr_id: '.var_dump($admin_attr_id);

        $json_attr = $this->getSupAttributeId($sup_attributes);
        $sup_attr_id = json_decode($json_attr, true);

        /* get all sup_attr_label by userId
        those label you make an id for each of them.
        the labels that have been picked are then given those id
        */

        $labels  = $this->getSupplierAttributeLabel($user_id);
        // $labels = json_decode($json_sup_attr, true);
        // echo'sup_attributes'.var_dump($sup_attributes)."\n";

        if(count($attr_mapping_ids) == 0){ // change to $sup_user_id
            // echo'sup_attr_id: '.var_dump($sup_attr_id);

            // DB::table('attribute_mapping')
            // ->where('attribute_mapping.id', '=', $user_id)->delete();

            for($i = 0; $i<= count($sup_attr_id); $i++){

                // if(isset($sup_attributes[$i])){
                    //     $supplier_attribute_id = array_search($sup_attributes[$i], $labels);
                    //     echo'labels id: '.array_search($sup_attributes[$i], $labels)."\n";
                    // }
                    // if(isset($sup_attr_id[$i])){

                        //     // $attr_sup_id = explode(" ", $attributes[$i]);
                        //     // $attr = explode(" ", $attributes[$i]);
                        //     // $attr_id = explode(" ", $attributes[$i]);

                        //     // echo'attr_sup_id: '.var_dump($attr_sup_id[1]);
                        //     // echo'attr_id: '.var_dump($attr_id[2]);

                        if(isset($sup_attr_id[$i])){

                            // echo'sup_attr_id: '.$i.var_dump($admin_attr_id[$i]);

                            $attr_mapping_data = array(
                                'supplier_attribute_id'     => $sup_attr_id[$i]['id'],
                                'admin_attribute_id'        => $admin_attr_id[$i]['id'],
                            );

                            DB::table('attribute_mapping')->insert([$attr_mapping_data]);
                        }

                    // echo'attributes: '.var_dump($attributes[$i]);
                    // echo'sup_attr_id: '.var_dump($sup_attr_id[0]['attribute_id']);

                    // $attr_mapping_data = array(
                    //     // 'attribute_mapping.supplier_id'  => $currentUser->id,
                    //     'attribute_supplier_id'  => $sup_attr_id[0]['attribute_id'],
                    //     'attribute_label'  => $attributes[$i],
                    // );

                    // DB::table('attribute_mapping')
                    // ->whereIn('attribute_mapping.supplier_id',$admin_attributes_id )
                    // ->update($attr_mapping_data);
                // }
            }
        }
        else if(count($attr_mapping_ids) >= 0){//this is for mapping changed from sup_user_id

            // $json = $this->getSupAttributeId($attributes[$i]);
            // $sup_attr_id = json_decode($json, true);

            // echo'sup_attr_id: '.var_dump($sup_attr_id);

            for($i = 0; $i<= count($sup_attr_id); $i++){

                if(isset($sup_attributes[$i])){

                    // $attr_mapping_data = array(
                    //     // 'id'  => $user_id,
                    //     'admin_attribute_id'  => $admin_attr_id[$i],
                    //     'supplier_attribute_id'  => $sup_attr_id[0]['attribute_id'],
                    //     // 'attribute_label'  => $attributes[$i],
                    // );

                    // DB::table('attribute_mapping')->insert([$attr_mapping_data]);

                    $attr_mapping_data = array(
                        // 'attribute_mapping.supplier_id'  => $currentUser->id,
                        'supplier_attribute_id'  => $sup_attr_id[$i]['id'],
                    );

                    DB::table('attribute_mapping')
                    ->whereIn('attribute_mapping.admin_attribute_id', $admin_attr_id)
                    ->update($attr_mapping_data);

                }
            }

        }

    }

    public function getSupAttributes($id)
    {
        $attr_data = DB::table('supplier_attributes')
        ->select('supplier_attributes.attribute_label', 'supplier_attributes.id')
        ->where('supplier_attributes.profile_id', '=', $id)
        ->get();
        return response()->json($attr_data);
    }

    public function getSupplierMapping($id){

        $attr_mapping = DB::table('attribute_mapping')
        ->select('attribute_mapping.supplier_attribute_id')
        ->get();

        $supplier_attributes_label = DB::table('supplier_attributes')
        ->select('supplier_attributes.attribute_label','supplier_attributes.id')
        ->whereIn('supplier_attributes.id', json_decode($attr_mapping, true))
        ->get();

        return $supplier_attributes_label;

        // $json = $this->getSupAttributeId($attr[0]);
        // $sup_attr_id = json_decode($json, true);



    }

    // public function getSupAttributes($id)
    // {
    //     $attr_data = DB::table('attribute_mapping')
    //     ->select('attribute_mapping.attribute_id', 'attribute_mapping.attribute_supplier_id',
    //     'attribute_mapping.attribute_label')
    //     ->where('attribute_mapping.supplier_id', '=', $id)
    //     ->get();
    //     return response()->json($attr_data);
    // }

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
