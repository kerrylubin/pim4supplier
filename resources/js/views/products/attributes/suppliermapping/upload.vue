<template>
  <div class="app-container">
    <upload-excel-component :on-success="handleSuccess" :before-upload="beforeUpload" />
    <el-button class="filter-item" style="margin-left: 10px;" type="primary" icon="el-icon-plus" @click="saveCSV()">
      Save
    </el-button>
    <div class="col-12 csv_mapping">

      <el-form>
        <el-form-item :label="user.name">
          <el-time-picker class="csv_picker" style="width: 226px;" type="fixed-time" placeholder="Pick a time" />
        </el-form-item>
      </el-form>

      <el-form :data="tableData" border highlight-current-row>
        <div class="col-12">
          <el-form-item v-for="(item, index) of tableHeader" :key="index" :label="item">
            <!-- <span :id="'selected_'+index.toString()" name="selected">selected: {{ form.selectHeaders }}</span> -->
            <el-select :id="'selected_'+index.toString()" v-model="form.selectHeaders" :name="item" class="csv_picker">
              <el-option v-for="(items, ind) in supplierHeader" :key="ind" :name="items" :prop="items" :label="items" :value="items" />
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
        selectHeaders: '',
        items: '',
        delivery: false,
        type: [],
        resource: '',
        desc: '',
      },

      tableData: [],
      tableHeader: [],
      supplierHeader: [],
      keys: [],
      values: [],
      user: '',
      userId: '',
    };
  },
  mounted: function() {
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
      var userId = localStorage.getItem('user id');

      axios.get(self.$apiAdress + '/api/getAttributes')
        .then(function(response) {
          // self.tableHeader = response.data;
          console.log('tableHeader: ', self.tableHeader);
        }).catch(function(error) {
          console.log(error);
          self.errorHandler(error.response);
        });

      // axios.get(self.$apiAdress + '/api/getUserCSVData')
      //   .then(function(response) {
      //     self.supplierHeader = response.data;
      //     console.log('supplierHeader: ', self.supplierHeader);
      //   }).catch(function(error) {
      //     console.log(error);
      //     self.errorHandler(error.response);
      //   });

      // if (!self.user.roles[0] === 'admin'){

      axios.get(self.$apiAdress + '/api/getSupCSVData/' + userId)
        .then(function(response) {
          self.supplierHeader = response.data;
          console.log('sup Header: ', self.supplierHeader);
        }).catch(function(error) {
          console.log(error);
          self.errorHandler(error.response);
        });
      // }
    },
    async saveCSV(){
      var self = this;
      const data = await self.$store.dispatch('user/getInfo');
      self.user = data;
      var csvHeaderData = null;

      self.user.roles[0] === 'admin' ? csvHeaderData = self.tableHeader : csvHeaderData = self.supplierHeader;

      console.log('csvData: ', csvHeaderData);

      axios.put(self.$apiAdress + '/api/storeUserCSVData/' + csvHeaderData)
        .then(function(response) {
          self.$message({
            type: 'success',
            message: 'CSV Headers Saved',
            duration: 5 * 1000,
          });
          console.log('userCSVData: ', response.data);
        }).catch(function(error) {
          self.$message({
            type: 'error',
            message: error,
            duration: 5 * 1000,
          });
          console.log(error);
          self.errorHandler(error.response);
        });

      for (var i = 0; i < self.tableData.length; i++){
        var keys = Object.keys(self.tableData[i]);
        var values = Object.values(self.tableData[i]);

        console.log('data: ', self.tableData[i]);

        axios.put(self.$apiAdress + '/api/storeTableKeysData/' + keys.toString().replace(/%20/g, ' '))
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

        axios.put(self.$apiAdress + '/api/storeTableValData/' + values.toString().replace(/\//g, '-'))
          .then(function(response) {
            self.$message({
              type: 'success',
              message: 'Table Data is Saved',
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
      }
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

      if (self.user.roles[0] === 'admin'){
        self.tableHeader = header;
        console.log('uploaded Headers: ', header);
        self.tableHeader = self.tableHeader.toString().replace(/[^a-zA-Z ]/g, ' ').split(' ').filter(item => item);
        console.log('uploaded tableHeaders: ', self.tableHeader);
      } else {
        self.supplierHeader = header;

        // self.supplierHeader = Object.assign({}, self.supplierHeader, header);
        // for(var i = 0; i < self.supplierHeader; i++){
        // self.form.selectHeaders = self.supplierHeader;
        // }
        // console.log('uploaded Headers: ', header);

        self.supplierHeader = self.supplierHeader.toString().replace(/[^a-zA-Z ]/g, ' ').split(' ').filter(item => item);
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
