<template>
  <div class="app-container">
    <upload-excel-component :on-success="handleSuccess" :before-upload="beforeUpload" />
    <el-button class="filter-item" style="margin-left: 10px;" type="primary" icon="el-icon-plus" @click="saveCSV()">
      Save
    </el-button>
    <div class="col-12 csv_mapping">

      <!-- <el-table :data="tableData" border highlight-current-row style="width: 100%;margin-top:20px;">
        <el-table-column v-for="item of tableHeader" :key="item" :prop="item" :label="item" />
      </el-table> -->

      <el-form>
        <!-- <el-form-item :label="user.name">
          <el-time-picker class="csv_picker" style="width: 226px;" type="fixed-time" placeholder="Pick a time" />
        </el-form-item> -->

        <el-form-item :label="user.name">

          <el-select v-model="form.time" class="csv_picker" placeholder="Please select time">
            <el-option v-for="items in time" :key="items" :label="items" :value="items" />
          </el-select>
        </el-form-item>

      </el-form>

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
      form: {
        time: '',
        attributes: [],
        supplier_attributeId: [],
        supplier_attributeVal: [],
      },
      time: ['hourly', 'daily', 'weekly'],
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
    async getUser() {
      var self = this;
      const data = await self.$store.dispatch('user/getInfo');
      self.user = data;
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
      // var csvHeaderData = null;

      // self.user.roles[0] === 'admin' ? csvHeaderData = self.tableHeader : csvHeaderData = self.supplierHeader;
      // console.log('self.role : ', self.user.roles[0]);

      console.log('mapping: ', self.form);

      if (self.user.roles[0] !== 'admin'){
        self.storeSupAttributes();
        for (var i = 0; i < self.tableData.length; i++){
          // var keys = Object.keys(self.tableData[i]);
          var values = Object.values(self.tableData[i]);

          console.log('data: ', self.tableData[i]);

          // axios.put(self.$apiAdress + '/api/storeTableKeysData/' + keys.toString().replace(/%20/g, ' '))
          //   .then(function(response) {
          //     self.$message({
          //       type: 'success',
          //       message: 'CSV Keys Saved',
          //       duration: 5 * 1000,
          //     });
          //     console.log('storeTableKeysData: ', response.data);
          //   }).catch(function(error) {
          //     self.$message({
          //       type: 'error',
          //       message: error,
          //       duration: 5 * 1000,
          //     });
          //     console.log(error);
          //     self.errorHandler(error.response);
          //   });

          axios.put(self.$apiAdress + '/api/storeTableValData/' + values.toString().replace(/\//g, '-'))
            .then(function(response) {
              self.$message({
                type: 'success',
                message: 'Table Data is Saved',
                duration: 5 * 1000,
              });
              console.log('storeTableValData: ', response.data);
            }).catch(function(error) {
              self.$message({
                type: 'error',
                message: error,
                duration: 5 * 1000,
              });
              console.log(error);
              self.errorHandler(error.response);
            });
        }
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
