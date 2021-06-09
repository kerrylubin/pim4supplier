<template slot-scope="scope">
  <div class="app-container">
    <el-alert :closable="false" title="Create Attributes" type="success">
      <router-view />
    </el-alert>
    <div class="filter-container">
      <!-- <el-input v-model="query.keyword" :placeholder="$t('table.keyword')" style="width: 200px;" class="filter-item" @keyup.enter.native="handleFilter" />
      <el-button v-waves class="filter-item" type="primary" icon="el-icon-search" @click="handleFilter">
        {{ $t('table.search') }}
      </el-button> -->
      <el-button class="filter-item" style="margin-left: 10px;" type="primary" icon="el-icon-plus" @click="handleCreateAttributes">
        Adds New Attributes
      </el-button>
      <el-button v-waves :loading="downloading" class="filter-item" type="primary" icon="el-icon-download" @click="handleDownload">
        {{ $t('table.export') }}
      </el-button>
    </div>

    <el-table v-loading="loading" :data="list" border fit highlight-current-row style="width: 100%">
      <el-table-column align="center" label="Id" width="80">
        <template slot-scope="scope">
          <span>{{ scope.row.id }}</span>
        </template>
      </el-table-column>

      <el-table-column align="center" label="Name">
        <template slot-scope="scope">
          <span>{{ scope.row.name }}</span>
        </template>
      </el-table-column>

      <el-table-column align="center" label="Code">
        <template slot-scope="scope">
          <span>{{ scope.row.code }}</span>
        </template>
      </el-table-column>

      <el-table-column align="center" label="Type" width="120">
        <template slot-scope="scope">
          <span>{{ scope.row.type }}</span>
        </template>
      </el-table-column>

    </el-table>

    <pagination v-show="total>0" :total="total" :page.sync="query.page" :limit.sync="query.limit" @pagination="getList" />

    <el-dialog :title="'Create New Attributes'" :visible.sync="dialogFormVisible">
      <div v-loading="attributeCreating" class="form-container">
        <el-form ref="attributeForm" :rules="rules" :model="newAttributes" label-position="left" label-width="150px" style="max-width: 500px;">
          <el-form-item :label="$t('Name')" prop="name">
            <el-input v-model="newAttributes.name" />
          </el-form-item>
          <el-form-item :label="$t('Code')" prop="code">
            <el-input v-model="newAttributes.code" />
          </el-form-item>
          <el-form-item :label="$t('Type')" prop="type">
            <el-select v-model="newAttributes.type" class="filter-item" placeholder="type">
              <el-option v-for="item in type" :key="item" :label="item | uppercaseFirst" :value="item" />
            </el-select>
          </el-form-item>
          <el-form-item :label="$t('Required')" prop="required">
            <el-select v-model="newAttributes.required" class="filter-item" placeholder="required">
              <el-option v-for="item in choice" :key="item" :label="item | uppercaseFirst" :value="item" />
            </el-select>
          </el-form-item>
          <el-form-item :label="$t('Unique')" prop="unique">
            <el-select v-model="newAttributes.unique" class="filter-item" placeholder="unique">
              <el-option v-for="item in choice" :key="item" :label="item | uppercaseFirst" :value="item" />
            </el-select>
          </el-form-item>
        </el-form>
        <!-- <template slot-scope="scope"> -->
        <div slot="footer" class="dialog-footer">
          <el-button @click="dialogFormVisible = false">
            {{ $t('table.cancel') }}
          </el-button>
          <el-button type="primary" @click="createAttribute(currentUserId);">
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
import Pagination from '@/components/Pagination'; // Secondary package based on el-pagination
// import UserResource from '@/api/user';
// import Resource from '@/api/resource';
import waves from '@/directive/waves'; // Waves directive
import permission from '@/directive/permission'; // Permission directive
import checkPermission from '@/utils/permission'; // Permission checking

// const userResource = new UserResource();
// const permissionResource = new Resource('permissions');

export default {
  name: 'CreateAttributes',
  components: { Pagination },
  directives: { waves, permission },
  data() {
    return {
      list: null,
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
      choice: ['yes', 'no'],
      type: ['text', 'dropdown', 'date', 'price', 'image', 'tax'],
      newAttributes: {},
      dialogFormVisible: false,
      dialogPermissionVisible: false,
      dialogPermissionLoading: false,
      currentUserId: 0,
      userData: null,
      currentUser: {
        name: '',
        permissions: [],
        rolePermissions: [],
      },
      rules: {
        code: [{ required: true, message: 'Code is required', trigger: 'blur' }],
        name: [{ required: true, message: 'Attribute name is required', trigger: 'blur' }],
        type: [{ required: true, message: 'Type is required', trigger: 'blur' }],
        required: [{ required: true, message: 'required is required', trigger: 'blur' }],
        unique: [{ required: true, message: 'Unique is required', trigger: 'blur' }],
      },
      permissionProps: {
        children: 'children',
        label: 'name',
        disabled: 'disabled',
      },
      permissions: [],
      menuPermissions: [],
      otherPermissions: [],
    };
  },
  computed: {
    normalizedMenuPermissions() {
      let tmp = [];
      this.currentUser.permissions.role.forEach(permission => {
        tmp.push({
          id: permission.id,
          name: permission.name,
          disabled: true,
        });
      });
      const rolePermissions = {
        id: -1, // Just a faked ID
        name: 'Inherited from role',
        disabled: true,
        children: this.classifyPermissions(tmp).menu,
      };

      tmp = this.menuPermissions.filter(permission => !this.currentUser.permissions.role.find(p => p.id === permission.id));
      const userPermissions = {
        id: 0, // Faked ID
        name: 'Extra menus',
        children: tmp,
        disabled: tmp.length === 0,
      };

      return [rolePermissions, userPermissions];
    },
    normalizedOtherPermissions() {
      let tmp = [];
      this.currentUser.permissions.role.forEach(permission => {
        tmp.push({
          id: permission.id,
          name: permission.name,
          disabled: true,
        });
      });
      const rolePermissions = {
        id: -1,
        name: 'Inherited from role',
        disabled: true,
        children: this.classifyPermissions(tmp).other,
      };

      tmp = this.otherPermissions.filter(permission => !this.currentUser.permissions.role.find(p => p.id === permission.id));
      const userPermissions = {
        id: 0,
        name: 'Extra permissions',
        children: tmp,
        disabled: tmp.length === 0,
      };

      return [rolePermissions, userPermissions];
    },
    userMenuPermissions() {
      return this.classifyPermissions(this.userPermissions).menu;
    },
    userOtherPermissions() {
      return this.classifyPermissions(this.userPermissions).other;
    },
    userPermissions() {
      return this.currentUser.permissions.role.concat(this.currentUser.permissions.user);
    },
  },
  created() {
    this.resetNewAttributes();
    this.getList();
    // if (checkPermission(['manage permission'])) {
    //   this.getPermissions();
    // }
  },
  mounted: function(){
    // this.getUser();
  },
  methods: {
    checkPermission,
    // async getPermissions() {
    //   const { data } = await permissionResource.list({});
    //   const { all, menu, other } = this.classifyPermissions(data);
    //   this.permissions = all;
    //   this.menuPermissions = menu;
    //   this.otherPermissions = other;
    // },
    // async getUser() {
    //   const data = await this.$store.dispatch('user/getInfo');
    //   this.userData = data;
    //   this.userData.roles[0] === 'supplier' ? this.roles = this.nonAdminRoles : this.roles;
    //   console.log('userData: ', this.userData);
    // },
    async getList() {
      var self = this;
      const { limit, page } = this.query;
      self.loading = true;
      // const { data, meta } = await userResource.list(this.query);

      // self.list = self.atrList;
      axios.get(self.$apiAdress + '/api/getAttributes')
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
    handleFilter() {
      this.query.page = 1;
      this.getList();
    },
    handleCreateAttributes() {
      this.resetNewAttributes();
      this.dialogFormVisible = true;
      this.$nextTick(() => {
        this.$refs['attributeForm'].clearValidate();
      });
    },
    // handleDelete(id, name) {
    //   this.$confirm('This will permanently delete user ' + name + '. Continue?', 'Warning', {
    //     confirmButtonText: 'OK',
    //     cancelButtonText: 'Cancel',
    //     type: 'warning',
    //   }).then(() => {
    //     userResource.destroy(id).then(response => {
    //       this.$message({
    //         type: 'success',
    //         message: 'Delete completed',
    //       });
    //       this.handleFilter();
    //     }).catch(error => {
    //       console.log(error);
    //     });
    //   }).catch(() => {
    //     this.$message({
    //       type: 'info',
    //       message: 'Delete canceled',
    //     });
    //   });
    // },
    // async handleEditPermissions(id) {
    //   this.currentUserId = id;
    //   this.dialogPermissionLoading = true;
    //   this.dialogPermissionVisible = true;
    //   const found = this.list.find(user => user.id === id);
    //   console.log('handlePermission: ', id);
    //   const { data } = await userResource.permissions(id);
    //   this.currentUser = {
    //     id: found.id,
    //     name: found.name,
    //     permissions: data,
    //   };
    //   this.dialogPermissionLoading = false;
    //   this.$nextTick(() => {
    //     this.$refs.menuPermissions.setCheckedKeys(this.permissionKeys(this.userMenuPermissions));
    //     this.$refs.otherPermissions.setCheckedKeys(this.permissionKeys(this.userOtherPermissions));
    //   });
    // },
    createAttribute(){
      // console.log('current user id: ', id);
      this.$refs['attributeForm'].validate((valid) => {
        console.log('valid: ', valid);
        if (valid) {
          var self = this;
          // this.newUser.roles = [this.newUser.role];
          self.attributeCreating = true;
          // console.log('new attr: ', self.newAttributes);

          axios.post(self.$apiAdress + '/api/storeAdminAttributes', self.newAttributes)
            .then(function(response) {
              self.$message({
                type: 'success',
                message: 'Attributes Saved',
                duration: 5 * 1000,
              });

              self.resetNewAttributes();
              self.attributeCreating = false;
              self.dialogFormVisible = false;
              // self.handleFilter();
              self.$router.go();

              // console.log('storeAttributes: ', response.data);
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
    resetNewAttributes() {
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
    // permissionKeys(permissions) {
    //   return permissions.map(permssion => permssion.id);
    // },
    // classifyPermissions(permissions) {
    //   const all = []; const menu = []; const other = [];
    //   permissions.forEach(permission => {
    //     const permissionName = permission.name;
    //     all.push(permission);
    //     if (permissionName.startsWith('view menu')) {
    //       menu.push(this.normalizeMenuPermission(permission));
    //     } else {
    //       other.push(this.normalizePermission(permission));
    //     }
    //   });
    //   return { all, menu, other };
    // },

    // normalizeMenuPermission(permission) {
    //   return { id: permission.id, name: this.$options.filters.uppercaseFirst(permission.name.substring(10)), disabled: permission.disabled || false };
    // },

    // normalizePermission(permission) {
    //   const disabled = permission.disabled || permission.name === 'manage permission';
    //   return { id: permission.id, name: this.$options.filters.uppercaseFirst(permission.name), disabled: disabled };
    // },

    // confirmPermission() {
    //   const checkedMenu = this.$refs.menuPermissions.getCheckedKeys();
    //   const checkedOther = this.$refs.otherPermissions.getCheckedKeys();
    //   const checkedPermissions = checkedMenu.concat(checkedOther);
    //   this.dialogPermissionLoading = true;

    //   userResource.updatePermission(this.currentUserId, { permissions: checkedPermissions }).then(response => {
    //     this.$message({
    //       message: 'Permissions has been updated successfully',
    //       type: 'success',
    //       duration: 5 * 1000,
    //     });
    //     this.dialogPermissionLoading = false;
    //     this.dialogPermissionVisible = false;
    //   });
    // },
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
