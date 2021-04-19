<template>
  <div class="app-container">
    <upload-excel-component :on-success="handleSuccess" :before-upload="beforeUpload" />
    <!-- <el-table :data="tableData" border highlight-current-row style="width: 100%;margin-top:20px;">
      <el-table-column v-for="item of tableHeader" :key="item" :prop="item" :label="item" />
    </el-table> -->
    <!-- <div class="row" style="margin-top:50px;"> -->
    <div style="margin-top:50px;">
      <el-form>
        <el-form-item :label="user.name">
          <el-col :span="18">
            <el-time-picker type="fixed-time" placeholder="Pick a time" />
          </el-col>
        </el-form-item>
      </el-form>

      <el-form :data="tableData" border highlight-current-row style="width: 100%;">
        <div class="col-12">
          <el-form-item v-for="item of tableHeader" :key="item" :prop="item" :label="item">

            <el-col :span="18">
              <el-select style="margin-bottom:10px;">
                <el-option v-for="items in tableHeader" :key="items" :label="items" :value="items" />
              </el-select>
            </el-col>
          </el-form-item>
        </div>
      </el-form>
    </div>
  </div>
  <!-- </div> -->
</template>

<script>
import UploadExcelComponent from './index';

export default {
  name: 'Upload',
  components: { UploadExcelComponent },
  data() {
    return {
      tableData: [],
      tableHeader: [],
      user: '',
    };
  },
  mounted: function() {
    this.getUser();
  },
  methods: {
    async getUser() {
      const data = await this.$store.dispatch('user/getInfo');
      this.user = data;
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
      console.log('table Header: ', this.tableHeader);
    },
  },
};
</script>
