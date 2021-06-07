<template slot-scope="scope">
  <div class="app-container">

    <el-alert :closable="false" title="Create Attributes" type="success">
      <router-view />
    </el-alert>

    <div class="filter-container">

      <div class="field_wrapper">
        <div>
          <el-button href="javascript:void(0);" class="filter-item add_button" style="margin-left: 10px;" type="primary" icon="el-icon-plus">
            Add Inputs
          </el-button>
          <!-- <input type="text" name="field_name[]" value=""/> -->
          <!-- <a href="javascript:void(0);" class="add_button" title="Add field"><img src="add-icon.png"/></a> -->
        </div>
      </div>

    </div>
  </div>
</template>

<script>
// import axios from 'axios';
import $ from 'jquery';
// import UserResource from '@/api/user';
// import Resource from '@/api/resource';
// import waves from '@/directive/waves'; // Waves directive
// import permission from '@/directive/permission'; // Permission directive
// import checkPermission from '@/utils/permission'; // Permission checking

// const userResource = new UserResource();
// const permissionResource = new Resource('permissions');

export default {
  name: 'CreateAttributes',
  // directives: { waves, permission },
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
      form: {
        select: [],
      },
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
      // permissionProps: {
      //   children: 'children',
      //   label: 'name',
      //   disabled: 'disabled',
      // },
      // permissions: [],
      // menuPermissions: [],
      // otherPermissions: [],
    };
  },
  created() {
    // this.resetNewUser();
    this.getList();
  },
  mounted: function(){
    // this.getUser();
    this.createInputs();
  },
  methods: {
    // checkPermission,
    createInputs() {
      this.$nextTick(() => {
        var maxField = 9; // Input fields increment limitation
        var addButton = $('.add_button'); // Add button selector
        var wrapper = $('.field_wrapper'); // Input field wrapper
        var items = ['Canada', 'Denmark', 'Finland', 'Germany', 'Mexico'];

        var x = 0; // Initial field counter is 1

        // Once add button is clicked

        $(addButton).click(function(){
          // Check maximum number of input fields
          if (x < maxField){
            x++; // Increment field counter
            var delBtn = '<button href="javascript:void(0);" type="primary" style="width:50px;" class="remove_button el-icon-remove-outline"> - </button>';
            var dropDown = '<div><select id="sel_' + x + '" name="field_name[]" ></select>' + delBtn + '</div>'; // New input field html
            $(wrapper).append(dropDown); // Add field html

            $.each(items, function(index, value) {
              // APPEND OR INSERT DATA TO SELECT ELEMENT.
              console.log('sel: ', index, value);
              $('#sel_' + x).append('<option value="' + index + ' ">' + value + '</option>');
            });
            console.log('x: ', x);
          }
        });
        // Once remove button is clicked
        $(wrapper).on('click', '.remove_button', function(e){
          e.preventDefault();
          $(this).parent('div').remove(); // Remove field html
          x--; // Decrement field counter
        });
      });
    },
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
    // async getList() {
    //   var self = this;
    //   const { limit, page } = this.query;
    //   this.loading = true;
    //   // const { data, meta } = await userResource.list(this.query);

    //   // self.list = self.atrList;
    //   axios.get(self.$apiAdress + '/api/getAttributes')
    //     .then(function(response) {
    //       self.list = response.data;
    //       self.list.forEach((element, index) => {
    //         element['index'] = (page - 1) * limit + index + 1;
    //       });
    //       self.total = self.list.length;
    //       self.loading = false;
    //       console.log('getAttributes: ', response.data);
    //     }).catch(function(error) {
    //       self.$message({
    //         type: 'error',
    //         message: error,
    //         duration: 5 * 1000,
    //       });
    //       console.log(error);
    //       self.errorHandler(error.response);
    //     });
    // },
    // handleFilter() {
    //   this.query.page = 1;
    //   this.getList();
    // },
    // handleCreate() {
    //   this.resetNewUser();
    //   this.dialogFormVisible = true;
    //   this.$nextTick(() => {
    //     this.$refs['userForm'].clearValidate();
    //   });
    // },
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
    // createAttribute(){
    //   // console.log('current user id: ', id);
    //   this.$refs['userForm'].validate((valid) => {
    //     if (valid) {
    //       var self = this;
    //       // this.newUser.roles = [this.newUser.role];
    //       this.attributeCreating = true;
    //       console.log('new attr: ', self.newAttributes);

    //       axios.post(self.$apiAdress + '/api/storeAdminAttributes', self.newAttributes)
    //         .then(function(response) {
    //           self.$message({
    //             type: 'success',
    //             message: 'Attributes Saved',
    //             duration: 5 * 1000,
    //           });

    //           self.resetNewUser();
    //           self.dialogFormVisible = false;
    //           self.handleFilter();
    //           self.attributeCreating = false;
    //           // self.$router.go()

    //           console.log('storeAttributes: ', response.data);
    //         }).catch(function(error) {
    //           self.$message({
    //             type: 'error',
    //             message: error,
    //             duration: 5 * 1000,
    //           });
    //           console.log(error);
    //           self.errorHandler(error.response);
    //         });
    //     } else {
    //       self.$message({
    //         type: 'error',
    //         message: 'error submit!!',
    //         duration: 5 * 1000,
    //       });
    //       console.log('error submit!!');
    //       return false;
    //     }
    //   });
    // },
    // resetNewUser() {
    //   this.newAttributes = {
    //     name: '',
    //     code: '',
    //     type: '',
    //     required: '',
    //     unique: '',
    //   };
    // },
    // handleDownload() {
    //   this.downloading = true;
    //   import('@/vendor/Export2Excel').then(excel => {
    //     const tHeader = ['id', 'code', 'name', 'type'];
    //     const filterVal = ['id', 'code', 'name', 'type'];
    //     const data = this.formatJson(filterVal, this.list);
    //     excel.export_json_to_excel({
    //       header: tHeader,
    //       data,
    //       filename: 'attributes',
    //     });
    //     this.downloading = false;
    //   });
    // },
    // formatJson(filterVal, jsonData) {
    //   return jsonData.map(v => filterVal.map(j => v[j]));
    // },
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
