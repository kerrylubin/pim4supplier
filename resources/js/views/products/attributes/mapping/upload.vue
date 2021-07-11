<template>
  <div class="app-container">
    <!-- <upload-excel-component :on-success="handleSuccess" :before-upload="beforeUpload" /> -->
    <el-button class="filter-item" style="margin-left: 10px;" type="primary" icon="el-icon-download" @click="storeMapping()">
      Save
    </el-button>
    <!--
    <el-button href="javascript:void(0);" class="filter-item add_button" style="margin-left: 10px;" type="primary" icon="el-icon-plus">
      Add Inputs
    </el-button>
    -->

    <el-button v-if="isVisible" class="filter-item" style="margin-left: 10px;" type="primary" icon="el-icon-plus" @click="handleCreateAttributes">
      Adds New Attributes
    </el-button>

    <div class="col-12 csv_mapping">

      <el-form :data="tableData" border highlight-current-row>

        <div class="col-12 field_wrapper">

          <el-form-item v-for="(item, index) of tableHeader" :id="'form_' + item.id" :key="index" :name="item.id">

            <el-select v-model="form.admin.attributes[index]">
              <el-option :value="item.name" />
            </el-select>

            <el-select :id="'selected_'+index.toString()" v-model="form.supplier.attributes[index]" :name="item.code" class="csv_picker">
              <el-option v-for="(items, ind) in supplierHeader" :key="ind" :name="items" :prop="ind" :label="items.attribute_label" :value="items.attribute_label" />
            </el-select>

            <!-- <el-button href="javascript:void(0);" class="filter-item remove_button" style="margin-left: 10px;" type="primary" icon="el-icon-minus" /> -->

          </el-form-item>

        </div>
      </el-form>
    </div>

    <el-dialog :title="'Create New Attributes'" :visible.sync="dialogAttributeFormVisible">
      <div v-loading="attributeCreating" class="form-container">
        <el-form ref="attributeForm" :rules="rules" :model="newAttributes" label-position="left" label-width="150px" style="max-width: 500px;">
          <el-form-item :label="$t('Name')" prop="name">
            <el-input v-model="newAttributes.name" />
          </el-form-item>
          <el-form-item :label="$t('Code')" prop="code">
            <el-input v-model="newAttributes.code" />
          </el-form-item>
          <el-form-item :label="$t('Type')" prop="type">
            <el-select v-model="newAttributes.type" class="filter-item" placeholder="type">
              <el-option v-for="item in type" :key="item" :label="item | uppercaseFirst" :value="item" />
            </el-select>
          </el-form-item>
          <el-form-item :label="$t('Required')" prop="required">
            <el-select v-model="newAttributes.required" class="filter-item" placeholder="required">
              <el-option v-for="item in choice" :key="item" :label="item | uppercaseFirst" :value="item" />
            </el-select>
          </el-form-item>
          <el-form-item :label="$t('Unique')" prop="unique">
            <el-select v-model="newAttributes.unique" class="filter-item" placeholder="unique">
              <el-option v-for="item in choice" :key="item" :label="item | uppercaseFirst" :value="item" />
            </el-select>
          </el-form-item>
        </el-form>
        <!-- <template slot-scope="scope"> -->
        <div slot="footer" class="dialog-footer">
          <el-button @click="dialogAttributeFormVisible = false">
            {{ $t('table.cancel') }}
          </el-button>
          <el-button type="primary" @click="createAttribute();">
            {{ $t('table.confirm') }}
          </el-button>
        </div>
        <!-- </template> -->
      </div>
    </el-dialog>

  </div>
</template>

<script>
// import $ from 'jquery';
// import UploadExcelComponent from './index';
import axios from 'axios';
// import UserResource from '@/api/user';
// const userResource = new UserResource();

export default {
  name: 'Upload',
  // components: { UploadExcelComponent },
  data() {
    return {
      page: 'suppliermapping',
      dialogFormVisible: false,
      dialogAttributeFormVisible: false,
      attributeCreating: false,
      userCreating: false,
      isVisible: '',
      newAttributes: {},
      choice: ['yes', 'no'],
      type: ['text', 'dropdown', 'date', 'price', 'image', 'tax'],
      time: ['daily', 'weekly', 'monthly'],
      form: {
        admin: {
          attributes: [],
          edited: [],
          userId: '',
        },
        supplier: {
          attributes: [],
          edited: [],
          userId: '',
        },
      },
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
    createAttribute(){
      // console.log('current user id: ', id);
      this.$refs['attributeForm'].validate((valid) => {
        console.log('valid: ', valid);
        if (valid) {
          var self = this;
          // this.newUser.roles = [this.newUser.role];
          this.attributeCreating = true;
          // console.log('new attr: ', self.newAttributes);

          axios.post(self.$apiAdress + '/api/storeAdminAttributes', self.newAttributes)
            .then(function(response) {
              self.$message({
                type: 'success',
                message: 'Attributes Saved',
                duration: 5 * 1000,
              });

              self.resetNewAttributes();
              self.dialogAttributeFormVisible = false;
              // self.handleFilter();
              self.attributeCreating = false;
              self.$router.go();

              // console.log('storeAttributes: ', response.data);
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
    resetNewAttributes() {
      this.newAttributes = {
        name: '',
        code: '',
        type: '',
        required: '',
        unique: '',
      };
    },
    async getUser() {
      var self = this;
      const data = await self.$store.dispatch('user/getInfo');
      self.user = data;

      if (self.user.roles[0] !== 'admin'){
        self.isVisible = false;
        // return true;
      } else {
        self.isVisible = true;
      }
      console.log('user: ', self.user);
    },
    handleCreateAttributes() {
      this.resetNewAttributes();
      this.dialogAttributeFormVisible = true;
      this.$nextTick(() => {
        this.$refs['attributeForm'].clearValidate();
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
          self.$message({
            type: 'success',
            message: 'Supplier Attributes Successfully Imported',
            duration: 5 * 1000,
          });
          console.log('sup Header: ', self.supplierHeader);
        }).catch(function(error) {
          self.$message({
            type: 'error',
            message: 'Supplier Attributes Are Not Yet Imported',
            duration: 5 * 1000,
          });
          console.log(error);
          self.errorHandler(error.response);
        });
    },
    getSupplierCSVHeaders(supplierId, page){
      var self = this;
      axios.get(self.$apiAdress + '/api/getSupplierCSVHeaders/' + supplierId + '/' + page)
        .then(function(response) {
          // console.log('response.data: ', response.data);
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
      // self.getSupplierCSVHeaders(userId, self.page);
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
