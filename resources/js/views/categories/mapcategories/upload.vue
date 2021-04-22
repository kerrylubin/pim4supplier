<template>
  <div class="app-container">
    <upload-excel-component :on-success="handleSuccess" :before-upload="beforeUpload" />
    <!-- <el-table :data="tableData" border highlight-current-row style="width: 100%;margin-top:20px;">
      <el-table-column v-for="item of tableHeader" :key="item" :prop="item" :label="item" />
    </el-table> -->
    <!-- <div class="row" style="margin-top:50px;"> -->
    <el-button class="filter-item" style="margin-left: 10px;" type="primary" icon="el-icon-plus">
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
              <el-option v-for="items in tableHeader" :key="items" :label="items" :value="items" />
            </el-select>
          </el-form-item>
        </div>
      </el-form>
    </div>
  </div>
  <!-- </div> -->
</template>

<script>
import UploadExcelComponent from './index';
// import axios from 'axios';
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
    this.getCSVData();

    // axios.get(this.$apiAdress + '/api/getCSVData')
    //   .then(function(response) {
    //     console.log('CSVData: ', response);
    //   }).catch(function(error) {
    //     console.log(error);
    //     self.errorHandler(error.response);
    //   });
  },
  methods: {
    async getUser() {
      const data = await this.$store.dispatch('user/getInfo');
      this.user = data;
    },
    async getCSVData(){
      const { data } = await userResource.getCSVHeader();
      this.tableHeader = data;
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
      this.tableHeader = header;
      this.supplierHeader = header;
      console.log('table Header: ', this.tableHeader);
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
