<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use App\Laravue\Models\Role;
use App\Laravue\Models\User;
use App\Http\Controllers\Api\ImportProfileController;
use App\Laravue\JsonResponse;
use Illuminate\Http\Response;

use League\Csv\Exception;
use League\Csv\Reader;
use League\Csv\Statement;

class ImportCSVController extends BaseController
{
    const VIEW = ['supplierprofile', 'suppliermapping'];
    public $VALUE;

    public function getSupplierImportProfileData($id){

        $csv_feed_data = DB::table('import_profile')
        ->where('import_profile.id', '=', $id)
        ->get();
        return json_encode($csv_feed_data);

    }

    public function getSupplierProfileData($supplierId){

      $csv_feed_data = DB::table('import_profile')
      ->where('import_profile.supplier_id', '=', $supplierId)
      ->get();
      return json_encode($csv_feed_data);

    }


    public function getSuppliers(){

        $suppliers = DB::table('suppliers')
        ->select('suppliers.id', 'suppliers.supplier_name')
        ->get();
        return json_encode($suppliers);
    }

    public function getAllProducts(){

        $products = DB::table('products')
        ->get();
        return json_encode($products);
    }

    public function getAllSupplierProducts($id){

        $products = DB::table('products')
        ->where('products.supplier_id', '=', $id)
        ->get();
        return response()->json($products);
    }


    public function getProduct($id){

        $supplier_id = DB::table('products')
        ->select('products.supplier_id')
        ->where('products.id', '=', $id)
        ->get();
        $supplier_name = DB::table('suppliers')
        ->select('suppliers.supplier_name')
        ->where('suppliers.id', '=', json_decode($supplier_id, true) )
        ->pluck('supplier_name');

        $product_attributes_values_id = DB::table('products_attributes')
        ->where('products_attributes.product_id', '=', $id)
        ->pluck('attribute_values_id');
        $attribute_values_name = DB::table('attributes_values')
        ->select('attributes_values.name')
        ->whereIn('attributes_values.id',  json_decode($product_attributes_values_id, true))
        ->pluck('name');

        $mapping_id = DB::table('products_attributes')
        ->where('products_attributes.product_id', '=', $id)
        ->pluck('mapping_id');
        $supplier_attribute_id = DB::table('attribute_mapping')
        ->select('attribute_mapping.supplier_attribute_id')
        ->whereIn('attribute_mapping.id', json_decode($mapping_id, true))
        ->pluck('supplier_attribute_id');
        $supplier_attributes = DB::table('supplier_attributes')
        ->select('supplier_attributes.attribute_label')
        ->whereIn('supplier_attributes.id', json_decode($supplier_attribute_id, true))
        ->pluck('attribute_label');

        // $this->print_x('supplier_attributes: '.$supplier_attributes)."\n";

        $product = array(
            'supplier'      => json_decode($supplier_name, true) ,
            'attributes'    => json_decode($supplier_attributes, true),
            'attributes_values'    => json_decode($attribute_values_name, true),

        );



        return response()->json($product);
    }


    public function getSupplierMappedAttributes($currentImportId){

        // $attribute_mapping_id = DB::table('attribute_mapping')
        // ->select('attribute_mapping.supplier_attribute_id')
        // ->get();

        // $supplier_mapped_labels = DB::table('supplier_attributes')
        // ->select('supplier_attributes.attribute_label')
        // ->whereIn('supplier_attributes.id', json_decode($attribute_mapping_id, true) )
        // ->pluck('attribute_label');

        $supplier_attributes_id = DB::table('supplier_attributes')//get id by import id
        ->select('supplier_attributes.id')
        ->where('supplier_attributes.import_profile_id', '=', $currentImportId )
        ->pluck('id');

        // $this->print_x(json_decode($supplier_attributes_id, true));

        // $attribute_mapping_id = DB::table('attribute_mapping')
        // ->select('attribute_mapping.supplier_attribute_id')
        // ->whereIn('attribute_mapping.supplier_attribute_id', '=', json_decode($supplier_attributes_id, true))
        // ->get();

        if( count(json_decode($supplier_attributes_id, true)) == 0){// null return null
            return null;

        }
        else{//else get labals by import id

            $supplier_attributes_id = DB::table('supplier_attributes')
            ->select('supplier_attributes.id')
            ->where('supplier_attributes.import_profile_id', '=', $currentImportId )
            ->pluck('id');

            // $this->print_x($supplier_attributes_id);

            $attribute_mapping_id = DB::table('attribute_mapping')
            ->select('attribute_mapping.supplier_attribute_id')
            ->whereIn('attribute_mapping.supplier_attribute_id', json_decode($supplier_attributes_id, true))
            ->get();


            $supplier_mapped_labels = DB::table('supplier_attributes')
            ->select('supplier_attributes.attribute_label')
            ->whereIn('supplier_attributes.id', json_decode($attribute_mapping_id, true) )
            ->pluck('attribute_label');
            return $supplier_mapped_labels;
        }



    }

    public function checkMapping($supplier_ID){
        $json_supplier_profile_data = $this->getSupplierProfileData($supplier_ID);
        $supplier_profile_data = json_decode($json_supplier_profile_data, true);

        // echo'supplier_profile_data: '.var_dump($supplier_profile_data);
        if( isset($supplier_profile_data[0]['id']) ){

            $currentImportId = $supplier_profile_data[0]['id'];
            $supplier_attributes_id = DB::table('supplier_attributes')
            ->select('supplier_attributes.id')
            ->where('supplier_attributes.import_profile_id', '=', $currentImportId)
            ->get();
            //get the mapping id where supplier_attribute_id exist
            $attr_mapping_ids = DB::table('attribute_mapping')
            ->select('attribute_mapping.supplier_attribute_id')
            ->whereIn('attribute_mapping.supplier_attribute_id', json_decode($supplier_attributes_id, true))
            ->pluck('supplier_attribute_id');

            return response()->json($attr_mapping_ids);
        }



    }

    public function checkImportProfileMapping($currentImportId){
        // $json_supplier_profile_data = $this->getSupplierProfileData($supplier_ID);
        // $supplier_profile_data = json_decode($json_supplier_profile_data, true);

        // // echo'supplier_profile_data: '.var_dump($supplier_profile_data);
        // // if( isset($supplier_profile_data[0]['id']) ){

        //     $currentImportId = $supplier_profile_data[0]['id'];

            $supplier_attributes_id = DB::table('supplier_attributes')
            ->select('supplier_attributes.id')
            ->where('supplier_attributes.import_profile_id', '=', $currentImportId)
            ->get();
            //get the mapping id where supplier_attribute_id exist
            $attr_mapping_ids = DB::table('attribute_mapping')
            ->select('attribute_mapping.supplier_attribute_id')
            ->whereIn('attribute_mapping.supplier_attribute_id', json_decode($supplier_attributes_id, true))
            ->pluck('supplier_attribute_id');
            // $this->print_x($attr_mapping_ids);

            return response()->json($attr_mapping_ids);
        // }



    }


    public function getSupplierCSVHeaders($id, $page){

        $json_supplier_profile_data = $this->getSupplierProfileData($id);
        $supplier_profile_data = json_decode($json_supplier_profile_data, true);

        // echo'supplier_profile_data: '.var_dump($supplier_profile_data);

        $currentImportId = $supplier_profile_data[0]['id'];

        $supplier_attributes = $this->getEntities($currentImportId, $page);

    }


    public function readCSVUrl($currentImportId)
    {
        $json_supplier_profile_data = $this->getSupplierImportProfileData($currentImportId);
        $supplier_profile_data = json_decode($json_supplier_profile_data, true);

        // echo'supplier_profile_data: '.var_dump($supplier_profile_data);
        $supplier_id = $supplier_profile_data[0]['supplier_id'];
        $csv_file = $supplier_profile_data[0]['feed_url'];
        $delimiter = $supplier_profile_data[0]['delimiter'];
        $csvContent = file_get_contents($csv_file);

        // $csvFile    = $supplier->getSupplierFeedUrl();
        // $delimiter  = $supplier->getSupplierFeedSeparator();
        // $csvContent = file_get_contents($csvFile);

        try {//try reads content. needs to add *Reader*
            $csv = Reader::createFromString($csvContent);
            $csv->setDelimiter($delimiter);
            $csv->setHeaderOffset(0);
        } catch (Exception $e) {
        }

        $stmt = (new Statement());
        return $stmt->process($csv);

    }//end readCSVUrl()

    public function deleteAttributes($supplierId){
        $json_supplier_profile_data = $this->getSupplierProfileData($supplierId);
        $supplier_profile_data = json_decode($json_supplier_profile_data, true);

        // echo'supplier_profile_data: '.var_dump($supplier_profile_data);

        $currentImportId = $supplier_profile_data[0]['id'];

        DB::table('supplier_attributes')
        ->where('supplier_attributes.import_profile_id', '=', $currentImportId)->delete();

    }

    public function interateId($supplierId, $supplier_attributes){

        $id = [];
        for($i = 0; $i <= count($supplier_attributes); $i++){
            if(isset($supplier_attributes[$i])){

                $id[] = $supplierId;
            }
        }
        return $id;
    }

    public function interateSupplierAttributes($supplier_attributes_data){

        foreach($supplier_attributes_data as $index=>$item) {
            return $index;
        }

    }

    public function removeHeaders($arr, $keys){//this removes unwanted keys from array

        $saved = [];

        foreach ($keys as $key => $value) {

            if (is_int($key) || is_int($value)) {
                $keysKey = $value;
            } else {
                $keysKey = $key;
            }

            if (isset($arr[$keysKey])) {

                $saved[$keysKey] = $arr[$keysKey];
                if (is_array($value)) {

                    $saved[$keysKey] = allow_keys($saved[$keysKey], $keys[$keysKey]);
                }
            }
        }
        return $saved;

    }

    public function getKeys($array,$supplier_ID ){
        $keys = null;
        foreach($array as $key=>$value){

            $products_data = array(
                'supplier_id'        =>  intval($supplier_ID),
                'unique_attribute_value'   =>  $mapped_headers[$keys],
            );
            DB::table('products')->insert([$products_data]);

            //get all mappings where supllierId

            $attribute_values_data = array(
                'name'   =>  $mapped_headers[$keys],
            );

            DB::table('attributes_values')->insert([$attribute_values_data]);


        }
    }

    public function configureImportprofile($unique_attribute_val, $supplierId){
        $update_supplier_import_profile =  [
            'unique_attribute'   =>  $unique_attribute_val
        ];

        //get Import count
        $supplier_profile_data = DB::table('import_profile')
        ->select('import_profile.id')
        ->where('import_profile.supplier_id', '=', $supplierId)
        ->get();

        $ids = $this->interateId( $supplierId ,json_decode($supplier_profile_data, true) );

        // echo'profile_id: '.var_dump($profile_ids);

        DB::table('import_profile')
        ->whereIn('import_profile.supplier_id', $ids )
        ->update($update_supplier_import_profile);
    }

    public function getAttributeValuesId(){
        $attributes_values = DB::table('attributes_values')
        ->select('attributes_values.id')
        ->where('attributes_values.name','=', 'No Data')
        ->pluck('id');

        echo'id: '.print_r(json_decode($attributes_values, true));

            // $this->print_x('id: '.$attributes_values);

        return $attributes_values;
    }

    public function configureProductAttributeValues($headers, $supplier_ID){
        $keys = array_keys($headers);
        // $values = array_values($headers);
        // $this->VALUE[] = array_values($headers);
        // $attribute_Val[] = $values;
        // echo'products: '.var_dump($mapped_headers[$i])."\n";
        // $this->print_x($headers);
        $products_data = array(
            'supplier_id'   =>  intval($supplier_ID),
            'unique_attribute_value'   =>  $headers['sku'],
        );
        DB::table('products')->insert([$products_data]);
        // store products unique_attribute
        for($j = 0; $j <= count($keys); $j++){//store attributes_values
            if(isset($keys[$j])){
                // $headers[$keys[$j]] === "" ? $headers[$keys[$j]] = "No Data ".$j : $headers[$keys[$j]] = $headers[$keys[$j]];

                $attribute_values_data = array(
                    'name'   =>  $headers[$keys[$j]],
                );
                DB::table('attributes_values')->insert([$attribute_values_data]);



                // $attributes_values = DB::table('attributes_values')
                // ->select('attributes_values.id')
                // ->where('attributes_values.name','=', $headers[$keys[$j]])
                // ->pluck('id');

                // $valuesID['values_id'] = json_decode($attributes_values, true);
                // $headers += $valuesID;
            }
        }

    }


    public function configureMappings($headers, $index, $mapping_id){
        $keys = array_keys($headers);
        $attributes_values = DB::table('attributes_values')
        ->select('attributes_values.id')
        ->pluck('id');

        $valuesID = json_decode($attributes_values, true);
        // $valuesID['values_id'] = json_decode($attributes_values, true);
        $chunks = array_chunk($valuesID, count($keys));
        // $this->print_x('values_id: '.$valuesID);

        if(isset($chunks[$index])){
            $value_id['values_id'] = $chunks[$index];
            $headers += $value_id;
        }

        $products_data_id = DB::table('products')
        ->select('products.id')
        ->where('products.unique_attribute_value', '=', $headers['sku'])
        ->get();

        $productID  = json_decode($products_data_id, true);
        // $this->print_x($productID);
        if(isset($productID[0])){
            $headers += $productID[0];
        }

        $headers += $mapping_id;


        for($j = 0; $j <= count($keys); $j++){//store attributes_values
            if(isset($keys[$j]) ){
                // $headers[$keys[$j]] === "" ? $headers[$keys[$j]] = "No Data ".$j : $headers[$keys[$j]] = $headers[$keys[$j]];
                if(isset($headers['id'],$headers['mapping_id'][$j],$headers['values_id'][$j])){

                    $products_data = array(
                        'product_id'            =>  $headers['id'],
                        'mapping_id'            =>  $headers['mapping_id'][$j],
                        'attribute_values_id'   =>  $headers['values_id'][$j],
                    );
                    DB::table('products_attributes')->insert([$products_data]);

                    // $product_data = array(
                    //     'product_id'    =>  $headers['id'],
                    //     'mapping_id'    =>  $headers['mapping_id'][$j],
                    //     'value'         =>  $headers[$keys[$j]],
                    // );
                    // DB::table('product_attributes')->insert([$product_data]);//change to product_attributes
                    // //insert mapped attributes
                }
            }

        }
    }


    public function getEntities($currentImportId, $page)//get contents in the csv. this can be called with cron
    {
        // echo'wat!!:'.var_dump($page);
        ini_set('max_execution_time', 600);// temporary set timeout time to 10min

        $json_supplier_profile_data = $this->getSupplierImportProfileData($currentImportId);
        $supplier_profile_data = json_decode($json_supplier_profile_data, true);

        // echo'supplier_profile_data: '.var_dump($supplier_profile_data);

        $currentImportId           = $supplier_profile_data[0]['id'];
        $supplier_ID        = $supplier_profile_data[0]['supplier_id'];
        // $import_profiles = json_decode($this->getSupplierProfileData($supplier_ID), true);

        $query = DB::table('import_profile')
        ->select('import_profile.id')
        ->where('import_profile.supplier_id', '=', $supplier_ID)
        ->pluck('id');

        $import_ids = json_decode($query, true);

        // $this->print_x(json_decode($import_ids, true));
        $unique_attribute   = $supplier_profile_data[0]['unique_attribute'];
        $view = ['supplierprofiles','suppliermapping'];

        $csvIterationObject = $this->readCSVUrl($currentImportId);
        $products           = [];
        $supplier_attributes= null;
        $mapped_headers     = [];
        $sku                = [];
        $mapped_attributes  = json_decode($this->getSupplierMappedAttributes($currentImportId), true);
        // echo'M_A: '.var_dump($mapped_attributes);
        foreach ($csvIterationObject as $row) {//loop through csv data. here you can set in database
            // $sku  = $row[$supplier->getSupplierFeedSkuField()];
            // echo'S_A: '.var_dump($supplier_attributes);
            // echo'Row: '.var_dump($test);
            $supplier_attributes = array_keys($row);

            // function to fill products table
            if(!$mapped_attributes == null ){
                // echo'null';
                /*
                    Tables to fill when  mapped
                    1.  products table.
                    2.  attribute_values.

                    Tables that are filled when mapped
                    1. product_attributes table.

                */
                // $supplier_attributes = array_keys($row);

                $mapped_headers[] = $this->removeHeaders($row, $mapped_attributes);
                // $keys = $this->getKeys($mapped_headers);
                // $mapped = $this->removeHeaders($row, $mapped_attributes);
                // $products[] = $mapped_headers;
                $sku[]        = $row['sku'];
                // $keys = array_keys($mapped);

            }
            else{
                echo 'No Mappings';
                break;//stops foreach
            }
            //end of function
        }

        if($page == $view[0]){
            /*  can be a function updateProfileCode($products, $supplier_ID)
                end function
            */
            // $mapped_keys = [];
            // echo'mapping'."\n";

            // $json_supplier_profile_data = $this->getSupplierProfileData($supplier_ID);
            // $supplier_profile_data = json_decode($json_supplier_profile_data, true);

            $valuesID = [];
            // echo'supplier_profile_data: '.var_dump($supplier_profile_data);

            // $currentImportId = $supplier_profile_data[0]['id'];
            // $unique_attribute = $supplier_profile_data[0]['unique_attribute'];

            for($i = 0; $i <= count($mapped_headers); $i++){

                if(isset($mapped_headers[$i])){
                    // echo'mapped headers: '."\n";
                    //$sku[] = $mapped_headers[$i];
                    // && in_array($currentImportId, $import_ids)
                    // if(!in_array($unique_attribute, $sku))
                    if($unique_attribute === 'Artikelnummer'){//first time takin feed = !
                        // echo'unique_attribute: '."\n";
                        $this->configureImportprofile($mapped_headers[$i]['sku'], $supplier_ID);
                        $this->configureProductAttributeValues($mapped_headers[$i], $supplier_ID);
                    }
                    else if(in_array($unique_attribute, $sku)){
                        return 'Same';
                        // $this->configureImportprofile($mapped_headers[$i]['sku'], $supplier_ID);
                        // $this->configureProductAttributeValues($mapped_headers[$i], $supplier_ID);
                    }
                    else if($currentImportId > $import_ids[0]){
                        return 'New Feed';
                        // $this->configureImportprofile($mapped_headers[$i]['sku'], $supplier_ID);
                        // $this->configureProductAttributeValues($mapped_headers[$i], $supplier_ID);
                    }
                    // else if(in_array($unique_attribute, $sku) && in_array($currentImportId, $import_ids)){
                    //     return 'Update';
                    // }
                    else {
                        return 'Update';
                    }
                }
            }

            // $this->print_x($valuesID);

            $supplier_attributes_id = DB::table('supplier_attributes')
            ->select('supplier_attributes.id')
            ->where('supplier_attributes.import_profile_id', '=', $currentImportId)
            ->get();
            //get the mapping id where supplier_attribute_id exist
            $attr_mapping_ids = DB::table('attribute_mapping')
            ->select('attribute_mapping.id')
            ->whereIn('attribute_mapping.supplier_attribute_id', json_decode($supplier_attributes_id, true))
            ->pluck('id');

            // $this->print_x($attr_mapping_ids)."\n";
            $attribute_mapping_id['mapping_id'] = json_decode($attr_mapping_ids, true);

            for($i = 0; $i < count($mapped_headers); $i++){
                if(isset($mapped_headers[$i])){
                    if(!in_array($unique_attribute, $sku) ){//first time taking feed = !

                        $this->configureMappings($mapped_headers[$i], $i, $attribute_mapping_id);
                    }
                    // else if (in_array($unique_attribute, $sku))){//if there is no
                    //  return 'Same';
                    //     $this->configureMappings($mapped_headers[$i], $i, $attribute_mapping_id);
                    // }
                    // else if(in_array($unique_attribute, $sku) && in_array($currentImportId, $import_ids) && !$mapped_attributes == null){
                    //     return 'Update';
                    // }
                    else{
                        //update
                    }
                }
            }
            // $this->print_x($mapped_headers);
        }
        //can be a function storeSupplierAttributes($supplier_ID, $currentImportId, $supplier_attributes)
        // $this->print_x('watt: '.$page);
        if($page == $view[1]  ){


            $profile_id = DB::table('supplier_attributes')
            ->select('supplier_attributes.import_profile_id')
            ->where('supplier_attributes.import_profile_id','=',$currentImportId)->get();

            if(count($profile_id) == 0){
                for($i = 0; $i <= count($supplier_attributes); $i++){

                    if(isset($supplier_attributes[$i])){

                        $supplier_attributes_data = array(
                            'import_profile_id'        =>  $currentImportId,
                            'attribute_label'   =>  $supplier_attributes[$i],
                        );
                        DB::table('supplier_attributes')->insert([$supplier_attributes_data]);
                    }
                }
            }
            else {

                // echo'already filled!!';
                // $this->deleteAttributes($supplierId);
                $id = [];
                $supplier_attributes_data = [];

                for($i = 0; $i <= count($supplier_attributes); $i++){

                    if(isset($supplier_attributes[$i])){

                        $id[] = $supplier_ID;

                        // $supplier_attributes_data[] = [
                        //     'attribute_label'   =>  $supplier_attributes[$i],
                        // ];

                        // array_push($supplier_attributes_data, [
                        //     'attribute_label'   =>  $supplier_attributes[$i]
                        // ]);

                        // $profile_ids = $this->interateId($supplierId ,$supplier_attributes);

                        // DB::table('supplier_attributes')
                        // ->whereIn('supplier_attributes.profile_id', $profile_ids )
                        // ->update($supplier_attributes_data[$i]);


                        // DB::table('supplier_attributes')->insert([$supplier_attributes_data]);
                    }
                }

                // $index = $this->interateSupplierAttributes($supplier_attributes_data);
            }
        }
        //end of function
        // return $mapped_headers;
    }//end getEntities()

    public function print_x($arr) {
        echo "<pre>";
        print_r($arr);
        echo "</pre>";
    }

    protected function updateSupplierStock($supplierId)
    {
        $supplier      = $this->supplierRepository->get($supplierId);
        $supplierName  = $supplier->getSupplierName();
        $importerModel = $this->importer;
        $productsArray = $this->getEntities($supplierId, $supplier);
        $importerModel->setBehavior($this->getBehavior());
        $importerModel->setEntityCode($this->getEntityCode());
        $adapterFactory = $this->nestedArrayAdapterFactory;
        $importerModel->setImportAdapterFactory($adapterFactory);

        try {
            if ($productsArray) {
                $importerModel->processImport($productsArray);
            }
        } catch (\Exception $e) {
            print($e->getMessage());
        }

    }//end updateSupplierStock()


    public function getBehavior()
    {
        return $this->behavior;

    }//end getBehavior()


    /**
     * @param string $behavior
     */
    public function setBehavior($behavior)
    {
        $this->behavior = $behavior;

    }//end setBehavior()


    /**
     * @return string
     */
    public function getEntityCode()
    {
        return $this->entityCode;

    }//end getEntityCode()


    /**
     * @param string $entityCode
     */
    public function setEntityCode($entityCode)
    {
        $this->entityCode = $entityCode;

    }//end setEntityCode()
}
