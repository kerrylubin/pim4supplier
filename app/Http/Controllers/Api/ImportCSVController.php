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

    public function getSupplierImportProfileData($id){

        $csv_feed_data = DB::table('supplier_import_profile')
        ->where('supplier_import_profile.id', '=', $id)
        ->get();
        return json_encode($csv_feed_data);

    }

    public function getSupplierProfileData($supplierId){

        $csv_feed_data = DB::table('supplier_import_profile')
        ->where('supplier_import_profile.supplier_id', '=', $supplierId)
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

    public function getSupplierMappedAttributes(){

        $attribute_mapping_id = DB::table('attribute_mapping')
        ->select('attribute_mapping.supplier_attribute_id')
        ->get();

        $supplier_mapped_labels = DB::table('supplier_attributes')
        ->select('supplier_attributes.attribute_label')
        ->whereIn('supplier_attributes.id', json_decode($attribute_mapping_id, true) )
        ->pluck('attribute_label');

        return $supplier_mapped_labels;
    }

    public function getSupplierCSVHeaders($id, $page){

        $json_supplier_profile_data = $this->getSupplierProfileData($id);
        $supplier_profile_data = json_decode($json_supplier_profile_data, true);

        // echo'supplier_profile_data: '.var_dump($supplier_profile_data);

        $importId = $supplier_profile_data[0]['id'];

        $supplier_attributes = $this->getEntities($importId, $page);

    }


    public function readCSVUrl($importId)
    {
        $json_supplier_profile_data = $this->getSupplierImportProfileData($importId);
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
        DB::table('supplier_attributes')
        ->where('supplier_attributes.profile_id', '=', $supplierId)->delete();

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


    public function getEntities($importId, $page)//get contents in the csv. this can be called with cron
    {
        $json_supplier_profile_data = $this->getSupplierImportProfileData($importId);
        $supplier_profile_data = json_decode($json_supplier_profile_data, true);

        // echo'supplier_profile_data: '.var_dump($supplier_profile_data);

        $importId = $supplier_profile_data[0]['id'];
        $supplier_ID = $supplier_profile_data[0]['supplier_id'];
        $unique_code = $supplier_profile_data[0]['unique_code'];
        $view = ['supplierprofiles','suppliermapping'];

        $csvIterationObject = $this->readCSVUrl($importId);
        $products               = [];
        $supplier_attributes= null;
        $mapped_attributes  = json_decode($this->getSupplierMappedAttributes(), true);
        // echo'M_A: '.var_dump($mapped_attributes);
        foreach ($csvIterationObject as $row) {//loop through csv data. here you can set in database
            // $sku  = $row[$supplier->getSupplierFeedSkuField()];
            $supplier_attributes = array_keys($row);
            // echo'S_A: '.var_dump($supplier_attributes);
            // echo'Row: '.var_dump($test);

            // function to fill products table
            if($mapped_attributes == null ){
                /*
                    Tables to fill when not mapped

                    1.  products table.
                    2.  attribute_values

                */

                $products[] = $row;

                //can be a function
                /*
                    get supplier attributes that is
                    mapped with admin attributes Name so you can use for unique_code
                */
            }
            else if($page == $view[0] ){

                /*
                    Tables to fill when  mapped
                    1.  products table.
                    2.  attribute_values.

                    Tables that are filled when mapped
                    1. product_attributes table.
                    2. attribute_mapping table.

                */

                $mapped_headers = $this->removeHeaders($row, $mapped_attributes);
                $products[] = $mapped_headers;

                $products_data = array(
                    'supplier_id'        =>  intval($supplier_ID),
                    'unique_code'   =>  $mapped_headers['Productnaam'],
                );
                DB::table('products')->insert([$products_data]);

                $attribute_values_data = array(
                    'name'   =>  $mapped_headers['Productnaam'],
                );
                DB::table('attributes_values')->insert([$attribute_values_data]);

            }
            //end of function
        }

        if($page == $view[0]){

            /* can be a function updateProfileCode($products, $supplier_ID)
                $update_supplier_import_profile =  [
                    'unique_code'   =>  $products[0]['sku']
                ];

                //get Import count
                $supplier_profile_data = DB::table('supplier_import_profile')
                ->select('supplier_import_profile.id')
                ->where('supplier_import_profile.supplier_id', '=', $supplier_ID)
                ->get();

                $ids = $this->interateId( $supplier_ID ,json_decode($supplier_profile_data, true) );

                echo'profile_id: '.var_dump($profile_ids);

                DB::table('supplier_import_profile')
                ->whereIn('supplier_import_profile.supplier_id', $ids )
                ->update($update_supplier_import_profile);

                end function
            */
        }

        /*  get the the id you're looking for in the DB.
            if the returned id is the same as the $supplierId update those values.
            else insert those values.
        */

        //can be a function storeSupplierAttributes($supplier_ID, $importId, $supplier_attributes)
        if($page == $view[1] ){

            $profile_id = DB::table('supplier_attributes')
            ->select('supplier_attributes.profile_id')
            ->where('supplier_attributes.profile_id','=',$supplier_ID)->get();

            if(count($profile_id) == 0){

                for($i = 0; $i <= count($supplier_attributes); $i++){

                    if(isset($supplier_attributes[$i])){

                        $supplier_attributes_data = array(
                            'profile_id'        =>  $supplier_ID,
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

        return $products;
    }//end getEntities()


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
