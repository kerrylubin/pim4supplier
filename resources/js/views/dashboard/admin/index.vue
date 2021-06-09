<template slot-scope="scope">
  <div class="app-container">

    <el-row :gutter="40" class="panel-group">
      <el-col v-for="item of list" :key="item" :xs="12" :sm="12" :lg="6" class="card-panel-col">
        <div class="card-panel">
          <div class="card-panel-icon-wrapper icon-people">
            <svg-icon icon-class="theme" class-name="card-panel-icon" />
          <!-- <pan-thumb :image="user.avatar" :height="'100px'" :width="'100px'" :hoverable="false" /> -->

          </div>
          <div class="card-panel-description">

            <div class="card-panel-text">
              {{ item.unique_code }}
            </div>
            <!-- <div class="card-panel-text">
            {{item.Productname}}
          </div> -->
            <count-to :start-val="0" :end-val="102400" :duration="2600" class="card-panel-num" />
          </div>
        </div>
      </el-col>
    </el-row>

    <pagination v-show="total>0" :total="total" :page.sync="query.page" :limit.sync="query.limit" @pagination="getList()" />

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
      localStorage.setItem('user id', this.userData.id);
      // this.userData.roles[0] === 'supplier' ? this.roles = this.nonAdminRoles : this.roles;
      console.log('userData: ', this.userData);
    },
    // async getList() {
    //   var self = this;
    //   const { limit, page } = this.query;
    //   this.loading = true;
    //   // const { data, meta } = await userResource.list(this.query);

    //   self.list = self.tableData;
    //   self.list.data.forEach((element, index) => { // handles pageination count
    //     element['index'] = (page - 1) * limit + index + 1;
    //   });
    //   self.total = self.list.data.length;
    //   self.loading = false;

    //   // axios.get(self.$apiAdress + '/api/getAttributes')
    //   //   .then(function(response) {
    //   //     self.list = response.data;
    //   //     self.total = self.list.length;
    //   //     self.loading = false;
    //   //     console.log('getAttributes: ', response.data);
    //   //   }).catch(function(error) {
    //   //     self.$message({
    //   //       type: 'error',
    //   //       message: error,
    //   //       duration: 5 * 1000,
    //   //     });
    //   //     console.log(error);
    //   //     self.errorHandler(error.response);
    //   //   });
    // },
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

          self.list.forEach((element, index) => {
            console.log('element: ', element); // handles pageination count
            element['index'] = (page - 1) * limit + index + 1;
          });

          // self.total = self.list.length;
          self.loading = false;
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
.panel-group {
  margin-top: 18px;
  .card-panel-col{
    margin-bottom: 32px;
  }
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
</style>
