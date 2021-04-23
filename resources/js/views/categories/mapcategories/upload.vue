<template>
  <div class="app-container">
    <upload-excel-component :on-success="handleSuccess" :before-upload="beforeUpload" />
    <el-button class="filter-item" style="margin-left: 10px;" type="primary" icon="el-icon-plus" @click="saveCSV">
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
          <el-form-item v-for="item of tableHeader" :key="item" :prop="item" :label="item">
            <el-select class="csv_picker">
              <el-option v-for="items in supplierHeader" :key="items" :label="items" :value="items" />
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
import UserResource from '@/api/user';

const userResource = new UserResource();

export default {
  name: 'Upload',
  components: { UploadExcelComponent },
  data() {
    return {
      tableData: [],
      tableHeader: [],
      supplierHeader: [],
      user: '',
    };
  },
  mounted: function() {
    this.getUser();
    // this.getCSVData();
    var self = this;
    axios.get(self.$apiAdress + '/api/getCSVData')
      .then(function(response) {
        console.log('CSVData: ', response.data);
        self.tableHeader = response.data;
        console.log('tableHeader: ', self.tableHeader);
      }).catch(function(error) {
        console.log(error);
        self.errorHandler(error.response);
      });
  },
  methods: {
    async getUser() {
      const data = await this.$store.dispatch('user/getInfo');
      this.user = data;
      // this.userData.roles[0] === 'supplier' ? this.roles = this.nonAdminRoles : this.roles;
    },
    async getCSVData(){
      const { data } = await userResource.getCSVHeader();
      console.log('Data: ', data);
      this.tableHeader = data;
    },
    saveCSV(){
      if (this.user.roles[0] === 'admin'){
        console.log('saved in admin csv mapping');
      } else {
        console.log('saved in supllier csv mapping');
      }
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
    handleSuccess({ results, header }) {
      this.tableData = results;
      this.supplierHeader = header;
      // this.tableHeader = header;
      console.log('supplierHeader: ', this.supplierHeader);
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
