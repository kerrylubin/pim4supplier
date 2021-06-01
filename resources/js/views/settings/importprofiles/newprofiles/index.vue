<template>
  <div class="app-container">
    <!-- <el-button class="filter-item" style="margin-left: 10px;" type="primary" icon="el-icon-upload" @click="handleCreate">
      Create New Profile
    </el-button> -->

    <!-- <el-dialog :title="'Import Csv'"> -->
    <div class="form-container">
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
      <div slot="footer" class="dialog-footer">
        <el-button @click="dialogFormVisible = false">
          {{ $t('table.cancel') }}
        </el-button>
        <el-button type="primary" @click="createNewProfile();">
          {{ $t('table.confirm') }}
        </el-button>
      </div>
    </div>
    <!-- </el-dialog> -->

  </div>
</template>

<script>
// import UploadExcelComponent from './index';
import axios from 'axios';
// import UserResource from '@/api/user';
// const userResource = new UserResource();

export default {
  name: 'NewProfiles',
  // components: { UploadExcelComponent },
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
      user: '',
      userId: '',
    };
  },
  mounted: function() {
    this.getUser();
    console.log('params: ', this.$route.params.id);
  },
  methods: {
    createNewProfile(){
      // console.log('current user id: ', id);
      this.$refs['importForm'].validate((valid) => {
        if (valid) {
          var self = this;
          // this.newUser.roles = [this.newUser.role];
          // this.dialogFormVisible = true;
          // this.userCreating = true;
          console.log('new import: ', self.importForm);

          axios.post(self.$apiAdress + '/api/storeImportProfile', self.importForm)
            .then(function(response) {
              self.$message({
                type: 'success',
                message: 'Import Completed',
                duration: 5 * 1000,
              });

              self.resetImportForm();
              // self.dialogFormVisible = false;
              // self.userCreating = false;
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
      // this.dialogFormVisible = true;
      this.$nextTick(() => {
        this.$refs['importForm'].clearValidate();
      });
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
