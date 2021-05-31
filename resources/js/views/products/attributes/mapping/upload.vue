<template>
  <div class="app-container">
    <upload-excel-component :on-success="handleSuccess" :before-upload="beforeUpload" />
    <el-button class="filter-item" style="margin-left: 10px;" type="primary" icon="el-icon-download" @click="saveCSV()">
      Save
    </el-button>
    <el-button class="filter-item" style="margin-left: 10px;" type="primary" icon="el-icon-upload" @click="handleCreate">
      Import CSV
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
        <div class="col-12">
          <el-form-item v-for="(item, index) of tableHeader" :key="index" :name="item.id" :label="item.name">
            <el-select :id="'selected_'+index.toString()" v-model="form.attributes[item.id]" :name="item.code" class="csv_picker">
              <el-option v-for="(items, ind) in supplierHeader" :key="ind" :name="items" :prop="ind" :label="items" :value="items +' '+ ind+' '+ item.id" />
            </el-select>
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
import UploadExcelComponent from './index';
import axios from 'axios';
// import UserResource from '@/api/user';
// const userResource = new UserResource();

export default {
  name: 'Upload',
  components: { UploadExcelComponent },
  data() {
    return {
      dialogFormVisible: false,
      userCreating: false,
      form: {
        attributes: [],
        supplier_attributeId: [],
        supplier_attributeVal: [],
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
    this.getCSVData();
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
    async getCSVData(){
      var self = this;
      const data = await self.$store.dispatch('user/getInfo');
      self.user = data;

      console.log('user data: ', self.user);

      axios.get(self.$apiAdress + '/api/getAttributes')
        .then(function(response) {
          self.tableHeader = response.data;
          console.log('tableHeaders: ', self.tableHeader);
        }).catch(function(error) {
          console.log(error);
          self.errorHandler(error.response);
        });
    },
    storeSupAttributes(){
      var self = this;
      axios.post(self.$apiAdress + '/api/storeSupAttributes', self.form)
        .then(function(response) {
          self.$message({
            type: 'success',
            message: 'Attributes Saved',
            duration: 5 * 1000,
          });
          // self.$router.go();
          console.log('storeSupAttributes: ', response.data);
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
    async saveCSV(){
      var self = this;
      const data = await self.$store.dispatch('user/getInfo');
      self.user = data;
      var csvHeaderData = null;

      self.user.roles[0] === 'admin' ? csvHeaderData = self.tableHeader : csvHeaderData = self.supplierHeader;
      // console.log('self.role : ', self.user.roles[0]);

      if (self.user.roles[0] !== 'admin'){
        self.storeSupAttributes();

        axios.post(self.$apiAdress + '/api/storeTableKeysData/', csvHeaderData)
          .then(function(response) {
            self.$message({
              type: 'success',
              message: 'CSV Keys Saved',
              duration: 5 * 1000,
            });
            console.log('storeTableKeysData: ', response.data);
          }).catch(function(error) {
            self.$message({
              type: 'error',
              message: error,
              duration: 5 * 1000,
            });
            console.log(error);
            self.errorHandler(error.response);
          });

        // for (var i = 0; i < self.tableData.length; i++){
        //   var keys = Object.keys(self.tableData[i]);
        // var values = Object.values(self.tableData[i]);
        // console.log('values: ', values);
        // console.log('supplierHeader: ', self.supplierHeader);

        // axios.post(self.$apiAdress + '/api/storeTableValData/', self.tableData[i])
        //   .then(function(response) {
        //     self.$message({
        //       type: 'success',
        //       message: 'Table Data is Saved',
        //       duration: 5 * 1000,
        //     });
        //     console.log('storeTableValData: ', response.data);
        //   }).catch(function(error) {
        //     self.$message({
        //       type: 'error',
        //       message: error,
        //       duration: 5 * 1000,
        //     });
        //     console.log(error);
        //     self.errorHandler(error.response);
        //   });
        // }
      }
      // we dont use this no more we use the attributes
      // axios.put(self.$apiAdress + '/api/storeUserCSVData/' + csvHeaderData)
      //   .then(function(response) {
      //     self.$message({
      //       type: 'success',
      //       message: 'CSV Headers Saved',
      //       duration: 5 * 1000,
      //     });
      //     console.log('userCSVData: ', response.data);
      //   })
      //   .catch(function(error) {
      //     self.$message({
      //       type: 'error',
      //       message: error,
      //       duration: 5 * 1000,
      //     });
      //     console.log(error);
      //     self.errorHandler(error.response);
      //   });
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
  text-align: end;
}

.csv_picker{
  margin-bottom: 10px;
}

</style>
