<template>
  <div class="app-container">

    <div class="filter-container">
      <el-button class="filter-item" style="margin-left: 10px;" type="primary" icon="el-icon-plus" @click="handleCreateUser">
        Create New Supplier
      </el-button>
    </div>

    <el-table v-loading="loading" :data="list" border fit highlight-current-row style="width: 100%">
      <el-table-column align="center" label="Id" width="80">
        <template slot-scope="scope">
          <span>{{ scope.row.id }}</span>
        </template>
      </el-table-column>

      <el-table-column align="center" min-width="250" label="Supplier Name">
        <template slot-scope="scope">
          <span>{{ scope.row.supplier_name }}</span>
        </template>
      </el-table-column>

      <!-- <el-table-column align="center" label="Url">
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
      </el-table-column> -->

      <el-table-column align="center" label="Actions" min-width="250" @click="goToUser()">
        <template slot-scope="scope">

          <el-button type="primary" size="small" icon="el-icon-plus" @click="handleCreateProfile(scope.row.id);">
            Create New Profile
          </el-button>

          <router-link :to="'/products/attributes/suppliermapping/'+scope.row.id">
            <el-button type="info" size="small" icon="el-icon-edit" @click="setSupplierId(scope.row.id)">
              Mapping
            </el-button>
          </router-link>

          <router-link :to="'/settings/importprofiles/supplierprofiles/'+scope.row.id">
            <el-button type="warning" size="small" icon="el-icon-edit" @click="setSupplierId(scope.row.id)">
              Show Import Profiles
            </el-button>
          </router-link>
        </template>
      </el-table-column>

    </el-table>

    <pagination v-show="total>0" :total="total" :page.sync="listQuery.page" :limit.sync="listQuery.limit" @pagination="getList" />

    <el-dialog :title="'Create New Profle'" :visible.sync="dialogProfileFormVisible">
      <div v-loading="formCreating" class="form-container">
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
          <el-button @click="dialogProfileFormVisible = false">
            {{ $t('table.cancel') }}
          </el-button>
          <el-button type="primary" @click="createNewProfile();">
            {{ $t('table.confirm') }}
          </el-button>
        </div>
        <!-- </template> -->
      </div>
    </el-dialog>

    <el-dialog :title="'Create New Supplier'" :visible.sync="dialogUserFormVisible">
      <div v-loading="formCreating" class="form-container">
        <el-form ref="userForm" :rules="rules" :model="newUser" label-position="left" label-width="150px" style="max-width: 500px;">
          <el-form-item :label="$t('user.role')" prop="role">
            <el-select v-model="newUser.role" class="filter-item" placeholder="Please select role">
              <el-option v-for="item in roles" :key="item" :label="item | uppercaseFirst" :value="item" />
            </el-select>
          </el-form-item>
          <el-form-item label="Name" prop="name" placeholder="Add your Name and Brand">
            <el-input v-model="newUser.name" />
          </el-form-item>
          <el-form-item :label="$t('user.email')" prop="email">
            <el-input v-model="newUser.email" />
          </el-form-item>
          <el-form-item :label="$t('user.password')" prop="password">
            <el-input v-model="newUser.password" show-password />
          </el-form-item>
          <el-form-item :label="$t('user.confirmPassword')" prop="confirmPassword">
            <el-input v-model="newUser.confirmPassword" show-password />
          </el-form-item>
        </el-form>
        <!-- <template slot-scope="scope"> -->
        <div slot="footer" class="dialog-footer">
          <el-button @click="dialogUserFormVisible = false">
            {{ $t('table.cancel') }}
          </el-button>
          <el-button type="primary" @click="createSupplier();">
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
import UserResource from '@/api/user';
// import Resource from '@/api/resource';
// import waves from '@/directive/waves'; // Waves directive
// import { parseTime } from '@/utils';
import Pagination from '@/components/Pagination'; // Secondary package based on el-pagination

const userResource = new UserResource();
// const permissionResource = new Resource('permissions');

export default {
  name: 'AllProfiles',
  components: { Pagination },
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
    var validateConfirmPassword = (rule, value, callback) => {
      if (value !== this.newUser.password) {
        callback(new Error('Password is mismatched!'));
      } else {
        callback();
      }
    };
    return {
      tableKey: 0,
      loading: true,
      list: null,
      listHeaders: null,
      listQuery: {
        page: 1,
        limit: 20,
        importance: undefined,
        title: undefined,
        type: undefined,
        sort: '+id',
      },
      dialogUserFormVisible: false,
      dialogProfileFormVisible: false,
      formCreating: false,
      frequency: ['daily', 'weekly', 'monthly'],
      total: 0,
      profileForm: {
        supplier_id: '',
        url: '',
        delimiter: '',
        frequency: '',
      },
      newUser: {},
      roles: ['supplier'],
      sortOptions: [{ label: 'ID Ascending', key: '+id' }, { label: 'ID Descending', key: '-id' }],
      showReviewer: false,
      dialogFormVisible: false,
      dialogStatus: '',
      rules: {
        role: [{ required: true, message: 'Role is required', trigger: 'change' }],
        name: [{ required: true, message: 'Name is required', trigger: 'blur' }],
        email: [
          { required: true, message: 'Email is required', trigger: 'blur' },
          { type: 'email', message: 'Please input correct email address', trigger: ['blur', 'change'] },
        ],
        password: [{ required: true, message: 'Password is required', trigger: 'blur' }],
        confirmPassword: [{ validator: validateConfirmPassword, trigger: 'blur' }],
      },
      downloadLoading: false,
    };
  },
  created() {
    this.getList();
  },
  methods: {
    async getList() {
      var self = this;
      const { limit, page } = self.listQuery;
      self.loading = true;
      // const { data, meta } = await userResource.list(this.query);

      // self.list = self.atrList;
      axios.get(self.$apiAdress + '/api/getSuppliers')
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
    //   var self = this;
    //   self.listQuery.page = 1;
    //   self.getList();
    // },
    // sortChange(data) {
    //   const { prop, order } = data;
    //   if (prop === 'id') {
    //     this.sortByID(order);
    //   }
    // },
    // sortByID(order) {
    //   if (order === 'ascending') {
    //     this.listQuery.sort = '+id';
    //   } else {
    //     this.listQuery.sort = '-id';
    //   }
    //   this.handleFilter();
    // },
    setSupplierId(id){
      localStorage.setItem('user id', id);
    },
    createNewProfile(){
      // console.log('current user id: ', id);
      this.$refs['profileForm'].validate((valid) => {
        if (valid) {
          var self = this;
          // this.newUser.roles = [this.newUser.role];
          this.dialogProfileFormVisible = true;
          this.formCreating = true;
          console.log('Profile: ', self.profileForm);

          axios.post(self.$apiAdress + '/api/storeSupplierImportProfile', self.profileForm)
            .then(function(response) {
              self.$message({
                type: 'success',
                message: 'Profile Created',
                duration: 5 * 1000,
              });

              self.resetprofileForm();
              self.dialogProfileFormVisible = false;
              self.formCreating = false;
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
    createSupplier(){
      // console.log('current user id: ', id);
      this.$refs['userForm'].validate((valid) => {
        if (valid) {
          this.newUser.roles = [this.newUser.role];
          this.formCreating = true;
          // console.log('new user: ', this.newUser);
          userResource
            .store(this.newUser, this.userData)
            .then(response => {
              this.$message({
                message: 'New user ' + this.newUser.name + '(' + this.newUser.email + ') has been created successfully.',
                type: 'success',
                duration: 5 * 1000,
              });
              this.resetNewUser();
              this.dialogUserFormVisible = false;
              this.handleFilter();
            })
            .catch(error => {
              console.log(error);
            })
            .finally(() => {
              this.formCreating = false;
            });
        } else {
          console.log('error submit!!');
          return false;
        }
      });
    },
    resetNewUser() {
      this.newUser = {
        name: '',
        email: '',
        brand: '',
        password: '',
        confirmPassword: '',
        role: 'supplier',
      };
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
      var self = this;
      self.resetprofileForm();
      self.profileForm.supplier_id = localStorage.getItem('user id');
      self.dialogProfileFormVisible = true;
      self.$nextTick(() => {
        self.$refs['profileForm'].clearValidate();
      });
    },
    handleCreateUser() {
      this.resetTemp();
      // this.dialogStatus = 'create';
      // this.profileForm.supplier_id = id;
      this.dialogUserFormVisible = true;
      this.$nextTick(() => {
        this.$refs['userForm'].clearValidate();
      });
    },
  },
};
</script>
