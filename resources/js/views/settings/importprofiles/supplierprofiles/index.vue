<template>
  <div class="app-container">
    <div class="filter-container">
      <el-button type="primary" size="small" icon="el-icon-plus" @click="handleCreateProfile();">
        Create New Profile
      </el-button>
    </div>

    <el-table v-loading="loading" :data="list" border fit highlight-current-row style="width: 100%">
      <el-table-column align="center" label="Id" width="80">
        <template slot-scope="scope">
          <span>{{ scope.row.id }}</span>
        </template>
      </el-table-column>

      <el-table-column align="center" label="Supplier Id">
        <template slot-scope="scope">
          <span>{{ scope.row.supplier_id }}</span>
        </template>
      </el-table-column>

      <el-table-column align="center" label="Url">
        <template slot-scope="scope">
          <span>{{ scope.row.feed_url }}</span>
        </template>
      </el-table-column>

      <el-table-column align="center" label="Delimiter" width="120">
        <template slot-scope="scope">
          <span>{{ scope.row.delimeter }}</span>
        </template>
      </el-table-column>

      <el-table-column align="center" label="Frequency" width="120">
        <template slot-scope="scope">
          <span>{{ scope.row.frequency }}</span>
        </template>
      </el-table-column>

      <el-table-column align="center" label="Unique Code" width="120">
        <template slot-scope="scope">
          <span>{{ scope.row.unique_code }}</span>
        </template>
      </el-table-column>

      <el-table-column align="center" label="Actions" width="350" @click="goToUser()">
        <template slot-scope="scope">
          <el-button type="primary" size="small" icon="el-icon-download" @click="handleImport(scope.row.id);">
            Import Products
          </el-button>
        </template>
      </el-table-column>

    </el-table>

    <pagination v-show="total>0" :total="total" :page.sync="listQuery.page" :limit.sync="listQuery.limit" @pagination="getList" />

    <el-dialog :title="'Create New Profle'" :visible.sync="dialogFormVisible">
      <div v-loading="profileCreating" class="form-container">
        <el-form ref="profileForm" :rules="rules" :model="profileForm" label-position="left" label-width="150px" style="max-width: 500px;">
          <el-form-item label="CSV Feed URL" prop="url" placeholder="Add your URL">
            <el-input v-model="profileForm.url" />
          </el-form-item>
          <el-form-item label="Frequency" prop="frequency">
            <el-select v-model="profileForm.frequency" placeholder="Please select frequency">
              <el-option v-for="items in frequency" :key="items" :label="items" :value="items" />
            </el-select>
          </el-form-item>
          <el-form-item label="Delimiter" prop="delimiter">
            <el-input v-model="profileForm.delimiter" />
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
import axios from 'axios';
// import { fetchPv, createArticle, updateArticle } from '@/api/article';
import waves from '@/directive/waves'; // Waves directive
// import { parseTime } from '@/utils';
import Pagination from '@/components/Pagination'; // Secondary package based on el-pagination

// arr to obj ,such as { CN : "China", US : "USA" }

export default {
  name: 'AllProfiles',
  components: { Pagination },
  directives: { waves },
  filters: {
    statusFilter(status) {
      const statusMap = {
        published: 'success',
        draft: 'info',
        deleted: 'danger',
      };
      return statusMap[status];
    },
  },
  data() {
    return {
      page: 'supplierprofile',
      tableKey: 0,
      loading: true,
      list: null,
      listHeaders: null,
      profileCreating: false,
      frequency: ['daily', 'weekly', 'monthly'],
      total: 0,
      listQuery: {
        page: 1,
        limit: 20,
        importance: undefined,
        title: undefined,
        type: undefined,
        sort: '+id',
      },
      profileForm: {
        supplier_id: '',
        url: '',
        delimiter: '',
        frequency: '',
      },
      sortOptions: [{ label: 'ID Ascending', key: '+id' }, { label: 'ID Descending', key: '-id' }],
      statusOptions: ['published', 'draft', 'deleted'],
      dialogFormVisible: false,
      dialogStatus: '',
      rules: {
        type: [{ required: true, message: 'type is required', trigger: 'change' }],
        timestamp: [{ type: 'date', required: true, message: 'timestamp is required', trigger: 'change' }],
        title: [{ required: true, message: 'title is required', trigger: 'blur' }],
      },
      downloadLoading: false,
    };
  },
  created() {
    this.getList();
  },
  methods: {
    setSupplierId(id){
      localStorage.setItem('user id', id);
    },
    async getList() {
      var self = this;
      const { limit, page } = self.listQuery;
      self.loading = true;
      var userId = localStorage.getItem('user id');
      // const { data, meta } = await userResource.list(this.query);

      axios.get(self.$apiAdress + '/api/getSupplierImportProfile/' + userId)
        .then(function(response) {
          self.list = response.data;

          self.list.forEach((element, index) => {
            element['index'] = (page - 1) * limit + index + 1;
          });
          self.total = self.list.length;
          self.loading = false;
          // console.log('getAttributes: ', response.data);
        }).catch(function(error) {
          self.$message({
            type: 'error',
            message: error,
            duration: 5 * 1000,
          });
          console.log(error);
          self.errorHandler(error.response);
        });
    },
    // handleFilter() {
    //   this.listQuery.page = 1;
    //   this.getList();
    // },
    sortChange(data) {
      const { prop, order } = data;
      if (prop === 'id') {
        this.sortByID(order);
      }
    },
    sortByID(order) {
      if (order === 'ascending') {
        this.listQuery.sort = '+id';
      } else {
        this.listQuery.sort = '-id';
      }
      // this.handleFilter();
    },
    createNewProfile(){
      // console.log('current user id: ', id);
      this.$refs['profileForm'].validate((valid) => {
        if (valid) {
          var self = this;
          // this.newUser.roles = [this.newUser.role];
          // self.dialogFormVisible = true;
          self.profileCreating = true;
          console.log('Profile: ', self.profileForm);
          self.profileForm.supplier_id = localStorage.getItem('user id');

          axios.post(self.$apiAdress + '/api/storeSupplierImportProfile', self.profileForm)
            .then(function(response) {
              self.$message({
                type: 'success',
                message: 'Profile Created',
                duration: 5 * 1000,
              });

              self.resetprofileForm();
              self.dialogFormVisible = false;
              self.profileCreating = false;
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
    resetprofileForm() {
      this.profileForm = {
        supplier_id: '',
        url: '',
        delimiter: '',
        frequency: '',
      };
    },
    handleCreateProfile(){
      this.resetprofileForm();
      // this.dialogStatus = 'create';
      this.dialogFormVisible = true;
      this.$nextTick(() => {
        this.$refs['profileForm'].clearValidate();
      });
    },
    handleImport(id){
      var self = this;

      this.loading = true;

      var products = [];

      axios.get(self.$apiAdress + '/api/getEntities/' + id + '/' + self.page)
        .then(function(response) {
          products = response.data;
          // self.listHeaders = Object.keys(self.list[0]);
          // self.list.forEach((element, index) => { // handles pageination count
          //   element['index'] = (page - 1) * limit + index + 1;
          // });
          // self.total = self.list.length;
          self.loading = false;
          console.log('products: ', products);
          self.$message({
            type: 'success',
            message: 'Products Imported',
            duration: 5 * 1000,
          });
        }).catch(function(error) {
          console.log(error);
          self.errorHandler(error.response);
        });

      // self.$confirm('This will permanently delete this profile. Continue?', 'Warning', {
      //   confirmButtonText: 'OK',
      //   cancelButtonText: 'Cancel',
      //   type: 'warning',
      // }).then(() => {

      //   axios.get('/api/deleteSupplierProfile/' + id)
      //     .then(function(response) {
      //       self.$message({
      //         type: 'success',
      //         message: 'Profile Deleted',
      //         duration: 5 * 1000,
      //       });
      //       self.$router.go();
      //     }).catch(function(error) {
      //       self.$message({
      //         type: 'error',
      //         message: error,
      //         duration: 5 * 1000,
      //       });

      //       console.log(error);
      //       self.errorHandler(error.response);
      //     });
      // }).catch(() => {
      //   self.$message({
      //     type: 'info',
      //     message: 'Delete canceled',
      //   });
      // });
    },
  },
};
</script>
