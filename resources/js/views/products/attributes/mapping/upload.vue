<template>
  <div class="app-container">
    <!-- <upload-excel-component :on-success="handleSuccess" :before-upload="beforeUpload" /> -->
    <el-button class="filter-item" style="margin-left: 10px;" type="primary" icon="el-icon-download" @click="storeMapping()">
      Save
    </el-button>

    <el-button href="javascript:void(0);" class="filter-item add_button" style="margin-left: 10px;" type="primary" icon="el-icon-plus">
      Add Inputs
    </el-button>

    <div class="col-12 csv_mapping">

      <!-- <el-form>

        <el-form-item :label="user.name">

          <el-select v-model="form.time" class="csv_picker" placeholder="Please select time">
            <el-option v-for="items in time" :key="items" :label="items" :value="items" />
          </el-select>
        </el-form-item>

      </el-form> -->

      <el-form :data="tableData" border highlight-current-row>

        <div class="col-12 field_wrapper">

          <el-form-item v-for="(item, index) of tableHeader" :id="'form_' + item.id" :key="index" :name="item.id">

            <el-select v-model="form.admin.attributes[index]">
              <el-option :value="item.name" />
            </el-select>

            <el-select :id="'selected_'+index.toString()" v-model="form.supplier.attributes[index]" :name="item.code" class="csv_picker">
              <el-option v-for="(items, ind) in supplierHeader" :key="ind" :name="items" :prop="ind" :label="items.attribute_label" :value="items.attribute_label" />
            </el-select>

            <el-button href="javascript:void(0);" class="filter-item remove_button" style="margin-left: 10px;" type="primary" icon="el-icon-minus" />

          </el-form-item>

        </div>
      </el-form>
    </div>

    <el-dialog :title="'Import Csv'" :visible.sync="dialogFormVisible">
      <div v-loading="userCreating" class="form-container">
        <el-form ref="importForm" :rules="rules" :model="importForm" label-position="left" label-width="150px" style="max-width: 500px;">
          <el-form-item label="CSV Feed URL" prop="url" placeholder="Add your URL">
            <el-input v-model="importForm.url" />
          </el-form-item>
          <el-form-item label="Frequency" prop="frequency">
            <el-select v-model="importForm.frequency" placeholder="Please select frequency">
              <el-option v-for="items in time" :key="items" :label="items" :value="items" />
            </el-select>
          </el-form-item>
          <el-form-item label="Delimiter" prop="delimiter">
            <el-input v-model="importForm.delimiter" />
          </el-form-item>
        </el-form>
        <!-- <template slot-scope="scope"> -->
        <div slot="footer" class="dialog-footer">
          <el-button @click="dialogFormVisible = false">
            {{ $t('table.cancel') }}
          </el-button>
          <el-button type="primary" @click="createNewProfile();">
            {{ $t('table.confirm') }}
          </el-button>
        </div>
        <!-- </template> -->
      </div>
    </el-dialog>

  </div>
</template>

<script>
import $ from 'jquery';
// import UploadExcelComponent from './index';
import axios from 'axios';
// import UserResource from '@/api/user';
// const userResource = new UserResource();

export default {
  name: 'Upload',
  // components: { UploadExcelComponent },
  data() {
    return {
      dialogFormVisible: false,
      userCreating: false,
      form: {
        admin: {
          time: '',
          attributes: [],
          edited: [],
          userId: '',
        },
        supplier: {
          time: '',
          attributes: [],
          edited: [],
          userId: '',
        },
      },
      time: ['daily', 'weekly', 'monthly'],
      importForm: {
        url: '',
        delimiter: '',
        frequency: '',
      },
      rules: {
        url: [{ required: true, message: 'URL is required!', trigger: ['blur', 'change'] }],
        delimiter: [{ required: true, message: 'Delimiter is required!', trigger: ['blur', 'change'] }],
        frequency: [{ required: true, message: 'Frequency is required!', trigger: ['blur', 'change'] }],
      },
      tableData: [],
      tableHeader: [],
      supplierHeader: [],
      user: '',
      userId: '',
    };
  },
  mounted: function() {
    // this.storeFile();
    this.getUser();
    this.getSupplierAttributeData();
    this.createInputs();
    console.log('params: ', this.$route.params.id);
  },
  methods: {
    createNewProfile(){
      // console.log('current user id: ', id);
      this.$refs['importForm'].validate((valid) => {
        if (valid) {
          var self = this;
          // this.newUser.roles = [this.newUser.role];
          this.dialogFormVisible = true;
          this.userCreating = true;
          console.log('new import: ', self.importForm);

          axios.post(self.$apiAdress + '/api/storeImportProfile', self.importForm)
            .then(function(response) {
              self.$message({
                type: 'success',
                message: 'Import Completed',
                duration: 5 * 1000,
              });

              self.resetImportForm();
              self.dialogFormVisible = false;
              self.userCreating = false;
              // console.log('import: ', response.data);
            }).catch(function(error) {
              self.$message({
                type: 'error',
                message: error,
                duration: 5 * 1000,
              });
              console.log(error);
              self.errorHandler(error.response);
            });
        } else {
          self.$message({
            type: 'error',
            message: 'error submit!!',
            duration: 5 * 1000,
          });
          console.log('error submit!!');
          return false;
        }
      });
    },
    resetImportForm() {
      this.importForm = {
        url: '',
        delimiter: '',
        frequency: '',
      };
    },
    async getUser() {
      var self = this;
      const data = await self.$store.dispatch('user/getInfo');
      self.user = data;
      console.log('user: ', self.user);
    },
    createInputs() {
      var self = this;
      self.$nextTick(() => {
        var addButton = $('.add_button'); // Add button selector
        var wrapper = $('.field_wrapper'); // Input field wrapper

        // Once add button is clicked
        var x = 0; // Initial field counter is 1

        $(addButton).click(function(){
          var maxInputs = (self.supplierHeader.length - self.tableHeader.length);
          // var y = (wrapper.children('div').length - 1);
          // console.log('length: ', wrapper.children('div').length);
          console.log('wrapper: ', wrapper.children('div').length);
          // Check maximum number of input fields
          if (x < maxInputs){
            // var dropDown = wrapper.children('div').show()[y];
            var dropDown = $('#form_' + x);
            // console.log('dropDown: ', dropDown);
            x++;

            // y++; // Increment field counter
            // var delBtn = '<button href="javascript:void(0);" type="primary" style="width:50px;" class="remove_button el-icon-remove-outline"> - </button>';
            // var dropDown = '<div><select class="admin_input" style="margin-bottom:22px" id="sel_' + x + '" name="added_inputs" ></select>' + delBtn + '</div>'; // New input field html
            // dropDown.attr('name', 'form_' + y);

            $(wrapper).append(dropDown.clone().prop('id', 'form_' + x)); // Add field html
          }
        });

        // Once remove button is clicked
        $(wrapper).on('click', '.remove_button', function(e){
          e.preventDefault();
          $(this).parent('div').remove(); // Remove field html
          x--; // Decrement field counter
        });

        // $(wrapper).on('click', '.remove_button', function(e){
        //   e.preventDefault();
        //   $(this).parent('div').remove(); // Remove field html
        //   x--; // Decrement field counter
        // });
      });
    },
    getAdminAtrributes(){
      var self = this;
      axios.get(self.$apiAdress + '/api/getAttributes')
        .then(function(response) {
          self.tableHeader = response.data;
          // this sets the dropdown value
          for (var i = 0; i < self.tableHeader.length; i++){
            // self.map.supAttrId.push(self.tableHeader[i].attribute_supplier_id);
            // var attrId = self.tableHeader[i].id;
            // var attrSupId = self.supplierHeader[i].attribute_supplier_id;
            var attributeLabel = self.tableHeader[i].name;
            self.form.admin.attributes[i];// sets json key to the attribute Id
            self.form.admin.attributes[i] = attributeLabel;// this sets the value
          }
          // console.log('self.form.admin: ', self.form.admin);
          // // console.log('formAdmin attrId: ', attrId);
          // console.log('self.form.admin attributes: ', self.form.admin.attributes);
          // console.log('tableHeaders: ', self.tableHeader);
        }).catch(function(error) {
          console.log(error);
          self.errorHandler(error.response);
        });
    },
    getSupplierMapping(supplierId){
      var self = this;
      axios.get(self.$apiAdress + '/api/getSupplierMapping/' + supplierId)
        .then(function(response) {
          var supplierHeader = response.data;
          for (var i = 0; i < supplierHeader.length; i++){
            // var attrId = self.supplierHeader[i].id;
            // var attrSupId = self.supplierHeader[i].attribute_supplier_id;
            var attributeLabel = supplierHeader[i].attribute_label;
            self.form.supplier.attributes[i];// sets json key to the attribute Id
            self.form.supplier.attributes[i] = attributeLabel;// this sets the value
          }
          // self.getSupAttributesLabels();
          console.log('form: ', self.form);
          // console.log('attrId: ', attrId);
          console.log('form edited: ', self.form.edited);
          console.log('form attributes: ', self.form.attributes);

          console.log('attributeLabel: ', attributeLabel);
          console.log('supplierHeader: ', response.data);
        }).catch(function(error) {
          console.log(error);
          self.errorHandler(error.response);
        });
    },
    getSupplierAttributes(supplierId){
      var self = this;
      axios.get(self.$apiAdress + '/api/getSupAttributes/' + supplierId)
        .then(function(response) {
          self.supplierHeader = response.data;
          console.log('sup Header: ', self.supplierHeader);
        }).catch(function(error) {
          console.log(error);
          self.errorHandler(error.response);
        });
    },
    async getSupplierAttributeData(){
      var self = this;
      const data = await self.$store.dispatch('user/getInfo');
      self.user = data;

      console.log('user data: ', self.user);

      self.getAdminAtrributes();
      self.getSupplierMapping(self.user.id);
      // self.getEntities(userId);
      self.getSupplierAttributes(self.user.id);

      // axios.get(self.$apiAdress + '/api/getAttributes')
      //   .then(function(response) {
      //     self.tableHeader = response.data;
      //     console.log('tableHeaders: ', self.tableHeader);
      //   }).catch(function(error) {
      //     console.log(error);
      //     self.errorHandler(error.response);
      //   });
    },
    storeSupplierMappings(form){
      var self = this;
      axios.post(self.$apiAdress + '/api/storeSupplierMappings', form)
        .then(function(response) {
          self.$message({
            type: 'success',
            message: 'Mapping Saved',
            duration: 5 * 1000,
          });
          // self.$router.go();
          console.log('storeSupplierMappings: ', response.data);
        }).catch(function(error) {
          self.$message({
            type: 'error',
            message: error,
            duration: 5 * 1000,
          });
          console.log(error);
          // self.$router.go();
          self.errorHandler(error.response);
        });
    },
    async storeMapping(){
      var self = this;
      const data = await self.$store.dispatch('user/getInfo');
      self.user = data;
      // var csvHeaderData = null;

      // self.user.roles[0] === 'admin' ? csvHeaderData = self.tableHeader : csvHeaderData = self.supplierHeader;
      // console.log('self.role : ', self.user.roles[0]);

      if (self.user.roles[0] !== 'admin'){
        self.storeSupplierMappings(self.form);
      }
      // else got to supplier mapping
    },
    setValue(){
      console.log('CLICK!!');
    },
    beforeUpload(file) {
      const isLt1M = file.size / 1024 / 1024 < 1;

      if (isLt1M) {
        return true;
      }

      this.$message({
        message: 'Please do not upload files larger than 1m in size.',
        type: 'warning',
      });
      return false;
    },
    handleCreate() {
      this.resetImportForm();
      this.dialogFormVisible = true;
      this.$nextTick(() => {
        this.$refs['importForm'].clearValidate();
      });
    },
    async handleSuccess({ results, header }) {
      var self = this;
      const data = await self.$store.dispatch('user/getInfo');
      self.user = data;

      self.tableData = results;

      // self.tableData = self.tableData.toString().filter(item => item);
      // console.log('Table Data Array: ', self.tableData);

      if (self.user.roles[0] === 'admin'){
        self.tableHeader = header;
        console.log('uploaded Headers: ', header);
        self.tableHeader = self.tableHeader.toString().replace(/[^a-zA-Z ]/g, ' ').split(' ').filter(item => item);
        console.log('uploaded tableHeaders: ', self.tableHeader);
      } else {
        self.supplierHeader = header;

        // self.supplierHeader = Object.assign({}, self.supplierHeader, header);
        // for(var i = 0; i < self.supplierHeader; i++){

        // self.form.attributes = self.supplierHeader;
        // }

        console.log('Key Headers: ', Object.keys(self.supplierHeader));

        self.supplierHeader = self.supplierHeader.toString().replace(/[^a-zA-Z ]/g, ' ').split(' ').filter(item => item);
        self.form.supplier_attributeId = Object.keys(self.supplierHeader);
        self.form.supplier_attributeVal = Object.values(self.supplierHeader);
        console.log('supplierHeader: ', self.supplierHeader);
      }
    },
  },
};
</script>

<style>
.csv_mapping{
  margin-top: 10px;
  padding: 15px;
  /* text-align: end; */
}

.csv_picker{
  margin-bottom: 10px;
}

</style>
