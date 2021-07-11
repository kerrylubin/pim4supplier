<template slot-scope="scope">
  <div class="app-container">
    <div class="wrapper">

      <!-- <el-table v-loading="loading" :data="list" border fit highlight-current-row style="width: 100%;margin-top:20px;">
        <el-table-column v-for="item of listHeaders" :key="item" :prop="item" :label="item" align="center" heigth="10" />
      </el-table> -->

      <el-table v-loading="loading" :data="list" border fit highlight-current-row style="width: 100%">
        <el-table-column align="center" label="Id" width="80">
          <template slot-scope="scope">
            <span>{{ scope.row.id }}</span>
          </template>
        </el-table-column>

        <el-table-column align="center" min-width="250" label="Supplier Id">
          <template slot-scope="scope">
            <span>{{ scope.row.supplier_id }}</span>
          </template>
        </el-table-column>

        <el-table-column align="center" min-width="250" label="SKU">
          <template slot-scope="scope">
            <span>{{ scope.row.unique_attribute_value }}</span>
          </template>
        </el-table-column>

        <el-table-column align="center" label="Actions" min-width="250" @click="goToUser()">
          <template slot-scope="scope">

            <router-link :to="'/products/productview/' + scope.row.unique_attribute_value +'/'+ scope.row.id">
              <el-button type="primary" size="small" icon="el-icon-goods" @click="viewProduct(scope.row.id)">
                View Product
              </el-button>
            </router-link>

          </template>
        </el-table-column>

      </el-table>

      <pagination v-show="total>0" :total="total" :page.sync="query.page" :limit.sync="query.limit" @pagination="getList()" />
    </div>

  </div>
</template>

<script>
import axios from 'axios';
import Pagination from '@/components/Pagination'; // Secondary package based on el-pagination
import UserResource from '@/api/user';
import Resource from '@/api/resource';
import waves from '@/directive/waves'; // Waves directive
import permission from '@/directive/permission'; // Permission directive
import checkPermission from '@/utils/permission'; // Permission checking

const userResource = new UserResource();
const permissionResource = new Resource('permissions');

export default {
  name: 'CreateAttributes',
  components: { Pagination },
  directives: { waves, permission },
  data() {
    return {
      list: null,
      listHeaders: null,
      total: 0,
      loading: true,
      downloading: false,
      attributeCreating: false,
      query: {
        page: 1,
        limit: 15,
        keyword: '',
        role: '',
      },
      currentUserId: 0,
      userData: null,
      currentUser: {
        name: '',
        permissions: [],
        rolePermissions: [],
      },
    };
  },
  computed: {
  },
  created() {
    this.resetNewUser();
    this.getList();
  },
  mounted: function(){
    this.getUser();
  },
  methods: {
    checkPermission,
    async getPermissions() {
      const { data } = await permissionResource.list({});
      const { all, menu, other } = this.classifyPermissions(data);
      this.permissions = all;
      this.menuPermissions = menu;
      this.otherPermissions = other;
    },
    async getUser() {
      const data = await this.$store.dispatch('user/getInfo');
      this.userData = data;
      this.userData.roles[0] === 'supplier' ? this.roles = this.nonAdminRoles : this.roles;
      console.log('userData: ', this.userData);
    },
    viewProduct(id){
      localStorage.setItem('product', id);
    },
    async getList() {
      var self = this;
      const { limit, page } = this.query;
      this.loading = true;

      const data = await this.$store.dispatch('user/getInfo');
      self.userData = data;

      // const { data, meta } = await userResource.list(this.query);
      if (self.userData.roles[0] === 'admin'){
        axios.get(self.$apiAdress + '/api/getAllProducts')
          .then(function(response) {
            self.list = response.data;

            self.list.forEach((element, index) => {
              element['index'] = (page - 1) * limit + index + 1;
            });

            self.loading = false;
            self.total = self.list.length;
            console.log('list: ', self.list);
          }).catch(function(error) {
            console.log(error);
            self.errorHandler(error.response);
          });

        console.log('admin products');
      } else {
        axios.get(self.$apiAdress + '/api/getAllSupplierProducts/' + self.userData.id)
          .then(function(response) {
            self.list = response.data;

            self.list.forEach((element, index) => {
              element['index'] = (page - 1) * limit + index + 1;
            });

            self.loading = false;
            self.total = self.list.length;
            console.log('list: ', self.list);
          }).catch(function(error) {
            console.log(error);
            self.errorHandler(error.response);
          });

        console.log('supplier products');
      }
    },
    handleFilter() {
      this.query.page = 1;
      this.getList();
    },
    handleCreate() {
      this.resetNewUser();
      this.dialogFormVisible = true;
      this.$nextTick(() => {
        this.$refs['userForm'].clearValidate();
      });
    },
    handleDelete(id, name) {
      this.$confirm('This will permanently delete user ' + name + '. Continue?', 'Warning', {
        confirmButtonText: 'OK',
        cancelButtonText: 'Cancel',
        type: 'warning',
      }).then(() => {
        userResource.destroy(id).then(response => {
          this.$message({
            type: 'success',
            message: 'Delete completed',
          });
          this.handleFilter();
        }).catch(error => {
          console.log(error);
        });
      }).catch(() => {
        this.$message({
          type: 'info',
          message: 'Delete canceled',
        });
      });
    },
    async handleEditPermissions(id) {
      this.currentUserId = id;
      this.dialogPermissionLoading = true;
      this.dialogPermissionVisible = true;
      const found = this.list.find(user => user.id === id);
      console.log('handlePermission: ', id);
      const { data } = await userResource.permissions(id);
      this.currentUser = {
        id: found.id,
        name: found.name,
        permissions: data,
      };
      this.dialogPermissionLoading = false;
      this.$nextTick(() => {
        this.$refs.menuPermissions.setCheckedKeys(this.permissionKeys(this.userMenuPermissions));
        this.$refs.otherPermissions.setCheckedKeys(this.permissionKeys(this.userOtherPermissions));
      });
    },
    createAttribute(){
      // console.log('current user id: ', id);
      this.$refs['userForm'].validate((valid) => {
        if (valid) {
          var self = this;
          // this.newUser.roles = [this.newUser.role];
          this.attributeCreating = true;
          console.log('new attr: ', self.newAttributes);

          axios.post(self.$apiAdress + '/api/storeAdminAttributes', self.newAttributes)
            .then(function(response) {
              self.$message({
                type: 'success',
                message: 'Attributes Saved',
                duration: 5 * 1000,
              });

              self.resetNewUser();
              self.dialogFormVisible = false;
              self.handleFilter();
              self.attributeCreating = false;
              // self.$router.go()

              console.log('storeAttributes: ', response.data);
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
    resetNewUser() {
      this.newAttributes = {
        name: '',
        code: '',
        type: '',
        required: '',
        unique: '',
      };
    },
    handleDownload() {
      this.downloading = true;
      import('@/vendor/Export2Excel').then(excel => {
        const tHeader = ['id', 'code', 'name', 'type'];
        const filterVal = ['id', 'code', 'name', 'type'];
        const data = this.formatJson(filterVal, this.list);
        excel.export_json_to_excel({
          header: tHeader,
          data,
          filename: 'attributes',
        });
        this.downloading = false;
      });
    },
    formatJson(filterVal, jsonData) {
      return jsonData.map(v => filterVal.map(j => v[j]));
    },
    permissionKeys(permissions) {
      return permissions.map(permssion => permssion.id);
    },
    classifyPermissions(permissions) {
      const all = []; const menu = []; const other = [];
      permissions.forEach(permission => {
        const permissionName = permission.name;
        all.push(permission);
        if (permissionName.startsWith('view menu')) {
          menu.push(this.normalizeMenuPermission(permission));
        } else {
          other.push(this.normalizePermission(permission));
        }
      });
      return { all, menu, other };
    },

    normalizeMenuPermission(permission) {
      return { id: permission.id, name: this.$options.filters.uppercaseFirst(permission.name.substring(10)), disabled: permission.disabled || false };
    },

    normalizePermission(permission) {
      const disabled = permission.disabled || permission.name === 'manage permission';
      return { id: permission.id, name: this.$options.filters.uppercaseFirst(permission.name), disabled: disabled };
    },

    confirmPermission() {
      const checkedMenu = this.$refs.menuPermissions.getCheckedKeys();
      const checkedOther = this.$refs.otherPermissions.getCheckedKeys();
      const checkedPermissions = checkedMenu.concat(checkedOther);
      this.dialogPermissionLoading = true;

      userResource.updatePermission(this.currentUserId, { permissions: checkedPermissions }).then(response => {
        this.$message({
          message: 'Permissions has been updated successfully',
          type: 'success',
          duration: 5 * 1000,
        });
        this.dialogPermissionLoading = false;
        this.dialogPermissionVisible = false;
      });
    },
  },
};
</script>

<style lang="scss" scoped>

.filter-container {
  padding-bottom: 10px;
  float: right;
}
.edit-input {
  padding-right: 100px;
}
.cancel-btn {
  position: absolute;
  right: 15px;
  top: 10px;
}
.dialog-footer {
  text-align: left;
  padding-top: 0;
  margin-left: 150px;
}
.app-container {
  flex: 1;
  justify-content: space-between;
  font-size: 14px;
  padding-right: 8px;

  .block {
    float: right;
    min-width: 250px;
  }
  .clear-left {
    clear: left;
  }

}
</style>
