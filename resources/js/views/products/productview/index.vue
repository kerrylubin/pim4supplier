<template slot-scope="scope">
  <div class="app-container">
    <div class="wrapper">

      <el-card>
        <div class="product-view">
          <div class="card-panel box-center">
            <svg-icon icon-class="theme" class-name="card-panel-icon" />
          </div>
          <div class="box-center">
            <div v-for="name of productData.supplier" :key="name" class="user-name text-center">
              {{ name }}
            </div>
          </div>
          <div class="box-social text-center">

            <div class="attributes">
              <div class="attributes-labels">
                <span v-for="attributes of productData.attributes" :key="attributes" class="label">
                  {{ attributes }}
                </span>
              </div>
              <div class="attributes-values">
                <span v-for="attributes_values of productData.attributes_values" :key="attributes_values" class="value">
                  {{ attributes_values }}
                </span>
              </div>
            </div>

          </div>
        </div>

      </el-card>

    </div>

  </div>
</template>

<script>
import axios from 'axios';
import UserResource from '@/api/user';
import Resource from '@/api/resource';
import waves from '@/directive/waves'; // Waves directive
import permission from '@/directive/permission'; // Permission directive
import checkPermission from '@/utils/permission'; // Permission checking

const userResource = new UserResource();
const permissionResource = new Resource('permissions');

export default {
  name: 'Productview',
  directives: { waves, permission },
  data() {
    return {
      list: null,
      listHeaders: null,
      productData: [],
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
      this.loading = true;
      var productId = localStorage.getItem('product');

      const data = await this.$store.dispatch('user/getInfo');
      self.userData = data;

      // const { data, meta } = await userResource.list(this.query);
      axios.get(self.$apiAdress + '/api/getProduct/' + productId)
        .then(function(response) {
          self.productData = response.data;

          console.log('productData: ', self.productData);
          self.loading = false;
        }).catch(function(error) {
          console.log(error);
          self.errorHandler(error.response);
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
.product-view {
    .card-panel {
    height: 108px;
    cursor: pointer;
    font-size: 12px;
    position: relative;
    overflow: hidden;
    color: #666;
    background: #fff;
    box-shadow: 4px 4px 40px rgba(0, 0, 0, .05);
    border-color: rgba(0, 0, 0, .05);
    &:hover {
      .card-panel-icon-wrapper {
        color: #fff;
      }
      .icon-people {
         background: #40c9c6;
      }
      .icon-message {
        background: #36a3f7;
      }
      .icon-money {
        background: #f4516c;
      }
      .icon-shopping {
        background: #34bfa3
      }
    }
    .icon-people {
      color: #40c9c6;
    }
    .icon-message {
      color: #36a3f7;
    }
    .icon-money {
      color: #f4516c;
    }
    .icon-shopping {
      color: #34bfa3
    }
    .card-panel-icon-wrapper {
      float: left;
      margin: 14px 0 0 14px;
      padding: 16px;
      transition: all 0.38s ease-out;
      border-radius: 6px;
    }
    .card-panel-icon {
      float: left;
      font-size: 48px;
    }
    .card-panel-description {
      float: right;
      font-weight: bold;
      margin: 26px;
      margin-left: 0px;
      .card-panel-text {
        line-height: 18px;
        color: rgba(0, 0, 0, 0.45);
        font-size: 16px;
        margin-bottom: 12px;
      }
      .card-panel-num {
        font-size: 20px;
      }
    }
  }

  .user-name {
    font-weight: bold;
  }
  .box-center {
    padding-top: 10px;
  }
  .user-role {
    padding-top: 10px;
    font-weight: 400;
    font-size: 14px;
  }
  .box-social {
    padding-top: 30px;
    .el-table {
      border-top: 1px solid #dfe6ec;
    }
  }
  .user-follow {
    padding-top: 20px;
  }
}

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
.attributes {
  display: flex;
  flex-direction: row;
  flex-wrap: wrap;
  width: 100%;
  max-width: 100%;

  &-labels,
  &-values {
    width: 50%;
  }
  span {
    display:flex;
    flex-direction: row;
    flex-wrap: wrap;
    width: 100%;
    height: 30px;
    padding: 5px 10px;
    border-bottom: 1px solid;
  }

}
</style>
