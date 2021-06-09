<template slot-scope="scope">
  <div class="app-container">
    <div class="wrapper">

      <el-table v-loading="loading" :data="list" border fit highlight-current-row style="width: 100%;margin-top:20px;">
        <el-table-column v-for="item of listHeaders" :key="item" :prop="item" :label="item" align="center" heigth="10" />
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
      tableData: {
        columns: [
          'Id',
          'Image',
          'Name',
          'Quantity',
          'Price',
          'Seller',
        ],
        data: [
          { Id: 1, Quantity: 1, Name: 'Joya Broek', Seller: 'Joya', Price: '123.00$', ImagePath: '/images/a.jpg' },
          { Id: 2, Quantity: 1, Name: 'Venum Broek', Seller: 'Venum', Price: '50.00$', ImagePath: '/images/b.jpg' },
          { Id: 3, Quantity: 3, Name: 'G4F Broek', Seller: 'Gear 4 Fighters', Price: '78.10$', ImagePath: '/images/c.jpg' },
          { Id: 4, Quantity: 1, Name: 'Vso Handshoenen', Seller: 'VSO', Price: '99.99$', ImagePath: '/images/d.jpg' },
          { Id: 5, Quantity: 5, Name: 'Supreme Broek', Seller: 'Supreme', Price: '41.20$', ImagePath: '/images/e.jpg' },
          { Id: 6, Quantity: 6, Name: 'Leo Broek', Seller: 'Leo', Price: '41.20$', ImagePath: '/images/f.jpg' },
        ],
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
    async getList() {
      var self = this;
      const { limit, page } = this.query;
      this.loading = true;

      const data = await this.$store.dispatch('user/getInfo');
      self.userData = data;

      // const { data, meta } = await userResource.list(this.query);
      axios.get(self.$apiAdress + '/api/getAllProducts')
        .then(function(response) {
          self.list = response.data;
          self.listHeaders = Object.keys(self.list[0]);

          self.list.forEach((element, index) => { // handles pageination count
            element['index'] = (page - 1) * limit + index + 1;
          });

          self.total = self.list.length;
          self.loading = false;
          // console.log('Object.keys: ', Object.keys(self.list[0]));
          console.log('list: ', self.list);
        }).catch(function(error) {
          console.log(error);
          self.errorHandler(error.response);
        });

      // axios.get(self.$apiAdress + '/api/getAttributes')
      //   .then(function(response) {
      //     self.list = response.data;
      //     self.total = self.list.length;
      //     self.loading = false;
      //     console.log('getAttributes: ', response.data);
      //   }).catch(function(error) {
      //     self.$message({
      //       type: 'error',
      //       message: error,
      //       duration: 5 * 1000,
      //     });
      //     console.log(error);
      //     self.errorHandler(error.response);
      //   });
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
