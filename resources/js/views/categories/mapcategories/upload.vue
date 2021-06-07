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
            <span :id="'selected_'+index.toString()" name="selected">selected: {{ form.selectHeaders }}</span>
            <el-select :id="'selected_'+index.toString()" v-model="form.selectHeaders" :name="item" class="csv_picker">
              <el-option v-for="(items, ind) in supplierHeader" :key="ind" :name="items" :prop="items" :label="items" :value="items" />
            </el-select>
          </el-form-item>

          <!-- <el-form-item v-for="(item, index) of tableHeader" :key="index" :label="item">
            <el-select :id="index.toString()" :name="item" v-model="form.select" class="csv_picker">
              <el-option :name="items" v-for="items in supplierHeader" :key="items" :label="items" :prop="items" v-bind:value="items.value" />
            </el-select>
            <span>{{ form.select }}</span>
          </el-form-item> -->

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
      user: '',
    };
  },
  mounted: function() {
    this.getUser();
    this.getCSVData();
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

      axios.get(self.$apiAdress + '/api/getCSVData')
        .then(function(response) {
          self.tableHeader = response.data;
          console.log('tableHeader: ', self.tableHeader);
        }).catch(function(error) {
          console.log(error);
          self.errorHandler(error.response);
        });

      axios.get(self.$apiAdress + '/api/getUserCSVData')
        .then(function(response) {
          self.supplierHeader = response.data;
          console.log('supplierHeader: ', self.supplierHeader);
        }).catch(function(error) {
          console.log(error);
          self.errorHandler(error.response);
        });
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
          console.log('userCSVData: ', response.data);
        }).catch(function(error) {
          console.log(error);
          self.errorHandler(error.response);
        });
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
  /* text-align: end; */
}

.csv_picker{
  margin-bottom: 10px;
}

</style>
