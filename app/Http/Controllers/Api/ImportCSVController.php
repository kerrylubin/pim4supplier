<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use App\Laravue\Models\Role;
use App\Laravue\Models\User;
use App\Laravue\JsonResponse;
use Illuminate\Http\Response;


// use FalconMedia\SupplierInventory\Model\SupplierRepository;
// use FireGento\FastSimpleImport\Model\Adapters\NestedArrayAdapterFactory;
// use FireGento\FastSimpleImport\Model\Importer;

use League\Csv\Exception;
use League\Csv\Reader;
use League\Csv\Statement;

// use Magento\Catalog\Api\ProductRepositoryInterface;
// use Magento\Catalog\Model\Product;
// use Magento\CatalogInventory\Model\Stock\StockItemRepository;
// use Magento\Framework\App\Filesystem\DirectoryList;
// use Magento\Framework\App\ResourceConnection;
// use Magento\Framework\App\State;
// use Magento\Framework\Filesystem\Directory\ReadFactory;
// use Magento\ImportExport\Model\Import;
// use Symfony\Component\Console\Command\Command;
// use Symfony\Component\Console\Input\InputArgument;
// use Symfony\Component\Console\Input\InputInterface;
// use Symfony\Component\Console\Output\OutputInterface;
// use Symfony\Component\Filesystem\Filesystem;
// use Symfony\Component\Finder\Finder;

class ImportCSVController extends BaseController
{
    // const NAME_ARGUMENT = 'ID';

    // /**
    //  * @var DirectoryList
    //  */
    // private $directoryList;

    // /**
    //  * @var Importer
    //  */
    // protected $importer;

    // /**
    //  * @var NestedArrayAdapterFactory
    //  */
    // protected $nestedArrayAdapterFactory;

    // /**
    //  * @var ReadFactory
    //  */
    // private $readFactory;

    // /**
    //  * @var StockItemRepository
    //  */
    // private $stockItemRepository;

    // /**
    //  * @var Product
    //  */
    // private $product;

    // /**
    //  * @var string
    //  */
    // protected $behavior;

    // /**
    //  * @var string
    //  */
    // protected $entityCode;

    // /**
    //  * @var boolean|bool[]|null
    //  */
    // private $delimiter;

    // /**
    //  * @var ProductRepositoryInterface
    //  */
    // private $productRepository;

    // /**
    //  * @var State
    //  */
    // private $state;

    // /**
    //  * @var SupplierRepository
    //  */
    // private $supplierRepository;


    // /**
    //  * Import constructor.
    //  *
    //  * @param DirectoryList              $directoryList
    //  * @param ReadFactory                $readFactory
    //  * @param ProductRepositoryInterface $productRepository
    //  * @param State                      $state
    //  * @param SupplierRepository         $supplierRepository
    //  * @param Filesystem                 $filesystem
    //  * @param ResourceConnection         $resourceConnection
    //  * @param StockItemRepository        $stockItemRepository
    //  * @param Product                    $product
    //  * @param Importer                   $importer
    //  * @param NestedArrayAdapterFactory  $nestedArrayAdapterFactory
    //  * @param Finder                     $finder
    //  */
    // public function __construct(
    //     DirectoryList $directoryList,
    //     ReadFactory $readFactory,
    //     ProductRepositoryInterface $productRepository,
    //     State $state,
    //     SupplierRepository $supplierRepository,
    //     ResourceConnection $resourceConnection,
    //     StockItemRepository $stockItemRepository,
    //     Product $product,
    //     Importer $importer,
    //     NestedArrayAdapterFactory $nestedArrayAdapterFactory,
    //     Finder $finder
    // ) {
    //     $this->directoryList       = $directoryList;
    //     $this->readFactory         = $readFactory;
    //     $this->productRepository   = $productRepository;
    //     $this->state               = $state;
    //     $this->supplierRepository  = $supplierRepository;
    //     $this->resourceConnection  = $resourceConnection;
    //     $this->stockItemRepository = $stockItemRepository;
    //     $this->product             = $product;
    //     $this->importer            = $importer;
    //     $this->nestedArrayAdapterFactory = $nestedArrayAdapterFactory;
    //     $this->finder                    = $finder;
    //     parent::__construct();

    // }//end __construct()


    // /**
    //  * {@inheritdoc}
    //  */
    // protected function execute(InputInterface $input, OutputInterface $output)
    // {
    //     $this->updateSupplierStock($input->getArgument(self::NAME_ARGUMENT));
    // }//end execute()


    // /**
    //  * {@inheritdoc}
    //  */
    // protected function configure()
    // {
    //     $this->setName('falconmedia:supplierstock:import');
    //     $this->setDescription('Import supplier stock per SKU from csv');
    //     $this->setBehavior(Import::BEHAVIOR_ADD_UPDATE);
    //     $this->setDefinition(
    //         [
    //             new InputArgument(
    //                 self::NAME_ARGUMENT,
    //                 InputArgument::REQUIRED,
    //                 'Supplier ID'
    //             ),
    //         ]
    //     );
    //     $this->setEntityCode('catalog_product');
    //     parent::configure();

    // }//end configure()

    public function getSupplierProfileData($id){

        $csv_feed_data = DB::table('supplier_profile')
        ->select('supplier_profile.feed_url', 'supplier_profile.delimiter',
        'supplier_profile.frequency')
        ->where('supplier_profile.supplier_id', '=', $id)
        ->get();
        return json_encode($csv_feed_data);
    }

    public function getSuppliers(){

        $suppliers = DB::table('suppliers')
        ->select('suppliers.id', 'suppliers.supplier_name')
        ->get();
        return json_encode($suppliers);
    }


    public function readCSVUrl($supplierId)
    {
        $json_supplier_profile_data = $this->getSupplierProfileData($supplierId);
        $supplier_profile_data = json_decode($json_supplier_profile_data, true);

        // echo'supplier_profile_data: '.var_dump($supplier_profile_data);

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

    public function interateSupplierId($supplierId, $supplier_attributes){

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



    public function getEntities($supplierId)//get contents in the csv. this can be called with cron
    {
        $csvIterationObject = $this->readCSVUrl($supplierId);
        $data               = [];
        $supplier_attributes = null;
        foreach ($csvIterationObject as $row) {//loop through csv data. here you can set in database
            // $sku           = $row[$supplier->getSupplierFeedSkuField()];
            $supplier_attributes = array_keys($row);
            $data[] = $row;




            // if ($this->product->getIdBySku($sku)) {
                // $sku           = $row[$supplier->getSupplierFeedSkuField()];
                // $supplierStock = $row[$supplier->getSupplierFeedStockField()];
                // $supplierName  = $supplier->getSupplierName();

                // $data[]        = [
                //     'sku'            => $sku,
                //     'supplier_stock' => $supplierStock,
                //     'supplier'       => $supplierName,
                // ];
            // }
        }

        /*  get the the id you're looking for in the DB.
            if the returned id is the same as the $supplierId update those values.
            else insert those values.
        */

        $supplier_id = DB::table('supplier_attributes')
        ->select('supplier_attributes.profile_id')
        ->where('supplier_attributes.profile_id','=',$supplierId)->get();

        if(count($supplier_id) == 0){

            for($i = 0; $i <= count($supplier_attributes); $i++){

                if(isset($supplier_attributes[$i])){

                    $supplier_attributes_data = array(
                        'profile_id'        =>  intval($supplierId),
                        'attribute_label'   =>  $supplier_attributes[$i],
                    );
                    DB::table('supplier_attributes')->insert([$supplier_attributes_data]);
                }
            }
        }
        else {

            echo'already filled!!';
            // $this->deleteAttributes($supplierId);
            $id = [];
            $supplier_attributes_data = [];

            for($i = 0; $i <= count($supplier_attributes); $i++){

                if(isset($supplier_attributes[$i])){

                    $id[] = $supplierId;

                    // $supplier_attributes_data[] = [
                    //     'attribute_label'   =>  $supplier_attributes[$i],
                    // ];

                    // array_push($supplier_attributes_data, [
                    //     'attribute_label'   =>  $supplier_attributes[$i]
                    // ]);

                    // $profile_ids = $this->interateSupplierId($supplierId ,$supplier_attributes);

                    // DB::table('supplier_attributes')
                    // ->whereIn('supplier_attributes.profile_id', $profile_ids )
                    // ->update($supplier_attributes_data[$i]);


                    // DB::table('supplier_attributes')->insert([$supplier_attributes_data]);
                }
            }

            // $index = $this->interateSupplierAttributes($supplier_attributes_data);
        }

        // return $index;
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
