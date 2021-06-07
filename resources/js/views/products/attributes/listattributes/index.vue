<template>
  <div class="app-container">
    <!-- <upload-excel-component :on-success="handleSuccess" :before-upload="beforeUpload" /> -->
    <el-button class="filter-item" style="margin-left: 10px;" type="primary" icon="el-icon-plus" @click="saveCSV()">
      Save
    </el-button>

    <el-button href="javascript:void(0);" class="filter-item add_button" style="margin-left: 10px;" type="primary" icon="el-icon-plus">
      Add Inputs
    </el-button>

    <el-button class="filter-item" style="margin-left: 10px;" type="primary" icon="el-icon-plus" @click="handleCreateAttributes">
      Adds New Attributes
    </el-button>

    <!-- <div class="field_wrapper">
        <div>
          <el-button href="javascript:void(0);" class="filter-item add_button" style="margin-left: 10px;" type="primary" icon="el-icon-plus">
            Add Inputs
          </el-button>
        </div>
      </div> -->

    <div class="col-12 csv_mapping">

      <div v-for="(item, k) of inputs" :key="k" class="form-group">
        <!-- <input type="text" class="form-control" v-model="input.name"> -->
        <select id="cars" v-model="inputs.admin.attributes[k]" name="cars">
          <option value="volvo">Volvo</option>
        </select>
        <select id="cars" v-model="inputs.supplier.attributes[k]" name="cars">
          <option value="volvo">Volvo</option>
        </select>

        <span>
          <i v-show="k || ( !k && inputs.admin.attributes.length > 1)" class="fas fa-minus-circle" @click="remove(k)">Remove</i>
          <i v-show="k == inputs.admin.length-1" class="fas fa-plus-circle" @click="add(k)">Add fields</i>
        </span>
      </div>

      <el-form :data="tableData" border highlight-current-row>

        <div class="col-6 field_wrapper">

          <el-form-item v-for="(item, index) of tableHeader" :id="'form_' + item.id" :key="index" class="attribute_picker">

            <el-select v-model="form.admin.attributes[index]">
              <el-option v-for="(attr, int) in tableHeader" :key="int" :prop="int" :label="attr.name" :value="attr.name" />
            </el-select>

            <el-select v-model="form.supplier.attributes[index]" class="supplier_attribute_picker">
              <el-option v-for="(items, ind) in supplierHeader" :key="ind" :prop="ind" :label="items.attribute_label" :value="items.attribute_label" />
            </el-select>

            <el-button href="javascript:void(0);" class="filter-item remove_button" style="margin-left: 10px;" type="primary" icon="el-icon-minus" />

          </el-form-item>

        </div>

        <!-- <div class="col-6">
          <el-form-item v-for="(item, index) of supplierHeader" :key="index" :name="item.id">

            <el-select :id="'supplier_selected_'+index.toString()" v-model="form.supplier.attributes[index]" :name="item.code" class="supplier_attribute_picker">
              <el-option v-for="(items, ind) in supplierHeader" :key="ind" :name="items" :prop="ind" :label="items.attribute_label" :value="items.attribute_label" />
            </el-select>

          </el-form-item>
        </div>
 -->
      </el-form>

      <el-dialog :title="'Create New Attributes'" :visible.sync="dialogAttributeFormVisible">
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
            <el-button @click="dialogAttributeFormVisible = false">
              {{ $t('table.cancel') }}
            </el-button>
            <el-button type="primary" @click="createAttribute();">
              {{ $t('table.confirm') }}
            </el-button>
          </div>
          <!-- </template> -->
        </div>
      </el-dialog>

    </div>

  </div>
</template>

<script>
import $ from 'jquery';
import axios from 'axios';
// import UserResource from '@/api/user';
// const userResource = new UserResource();

export default {
  name: 'Upload',
  // components: { UploadExcelComponent },
  data() {
    return {
      form: {
        admin: {
          time: '',
          attributes: [],
          userId: '',
        },
        supplier: {
          time: '',
          attributes: [],
          userId: '',
        },
      },
      map: {
        userId: '',
        supAttrId: [],
      },
      inputs: {
        admin: {
          time: '',
          attributes: [],
          userId: '',
        },
        supplier: {
          time: '',
          attributes: [],
          userId: '',
        },
      },
      newAttributes: {},
      dialogAttributeFormVisible: false,
      attributeCreating: false,
      choice: ['yes', 'no'],
      type: ['text', 'dropdown', 'date', 'price', 'image', 'tax'],
      time: ['hourly', 'daily', 'weekly'],
      tableData: [],
      tableHeader: [],
      supplierHeader: [],
      labels: [],
      keys: [],
      values: [],
      user: '',
      userId: '',
      rules: {
        code: [{ required: true, message: 'Code is required', trigger: 'blur' }],
        name: [{ required: true, message: 'Attribute name is required', trigger: 'blur' }],
        type: [{ required: true, message: 'Type is required', trigger: 'blur' }],
        required: [{ required: true, message: 'required is required', trigger: 'blur' }],
        unique: [{ required: true, message: 'Unique is required', trigger: 'blur' }],
      },
    };
  },
  mounted: function() {
    this.getUser();
    this.getCSVData();
    this.createInputs();
    console.log('params: ', this.$route.params.id);
  },
  methods: {
    add() {
      // var x = this.form.admin.attributes.length;

      var ObjAdmin = this.tableHeader;
      var ObjSupplier = this.supplierHeader;

      this.inputs.admin.attributes.push(ObjAdmin);
      this.inputs.supplier.attributes.push(ObjSupplier);

      // this.form.admin.attributes.push(Obj);
      // this.form.supplier.attributes.push(Obj);
      console.log(this.tableHeader[0]);
    },
    remove(index) {
      this.inputs.splice(index, 1);
    },
    async getUser() {
      var self = this;
      const data = await self.$store.dispatch('user/getInfo');
      self.user = data;
    },
    createInputs() {
      var self = this;
      self.$nextTick(() => {
        var addButton = $('.add_button'); // Add button selector
        var wrapper = $('.field_wrapper'); // Input field wrapper

        // Once add button is clicked
        var x = 0; // Initial field counter is 1

        $(addButton).click(function(){
          var maxInputs = (self.supplierHeader.length - self.tableHeader.length);
          // var y = (wrapper.children('div').length - 1);
          console.log('length: ', wrapper.children('div').length);
          console.log('wrapper: ', $('#form_' + x));
          // Check maximum number of input fields
          if (x < maxInputs){
            // var dropDown = wrapper.children('div').show()[y];
            var dropDown = $('#form_' + x);
            console.log('dropDown: ', dropDown);
            x++;
            // y++; // Increment field counter
            // var delBtn = '<button href="javascript:void(0);" type="primary" style="width:50px;" class="remove_button el-icon-remove-outline"> - </button>';
            // var dropDown = '<div><select class="admin_input" style="margin-bottom:22px" id="sel_' + x + '" name="added_inputs" ></select>' + delBtn + '</div>'; // New input field html
            // dropDown.attr('name', 'form_' + y);

            $(wrapper).append(dropDown.clone().prop('id', 'form_' + x)); // Add field html

            // $.each(self.supplierHeader, function(index, value) {
            //   // APPEND OR INSERT DATA TO SELECT ELEMENT.
            //   // console.log('sel: ', index, value);
            //   $('#sel_' + x).append('<option value="' + index + ' ">' + value.attribute_label + '</option>');
            // });
            // console.log('x: ', x);
          }
        });

        // Once remove button is clicked
        $(wrapper).on('click', '.remove_button', function(e){
          e.preventDefault();
          $(this).parent('div').remove(); // Remove field html
          x--; // Decrement field counter
        });

        // $(wrapper).on('click', '.remove_button', function(e){
        //   e.preventDefault();
        //   $(this).parent('div').remove(); // Remove field html
        //   x--; // Decrement field counter
        // });
      });
    },
    getSupAttributesLabels(){
      var self = this;
      axios.post(self.$apiAdress + '/api/getSupAttributesLabels', self.map)
        .then(function(response) {
          self.labels = response.data;
          console.log('sup labels: ', self.labels);
        }).catch(function(error) {
          console.log(error);
          self.errorHandler(error.response);
        });
    },
    getAdminAtrributes(){
      var self = this;
      axios.get(self.$apiAdress + '/api/getAttributes')
        .then(function(response) {
          self.tableHeader = response.data;
          // this sets the dropdown value
          // for (var i = 0; i < self.tableHeader.length; i++){
          //   // self.map.supAttrId.push(self.tableHeader[i].attribute_supplier_id);
          //   // var attrId = self.tableHeader[i].id;
          //   // var attrSupId = self.supplierHeader[i].attribute_supplier_id;
          //   var attributeLabel = self.tableHeader[i].name;
          //   self.form.admin.attributes[i];// sets json key to the attribute Id
          //   self.form.admin.attributes[i] = attributeLabel;// this sets the value
          // }
          // console.log('self.form.admin: ', self.form.admin);
          // // console.log('formAdmin attrId: ', attrId);
          // console.log('self.form.admin attributes: ', self.form.admin.attributes);
          // console.log('tableHeaders: ', self.tableHeader);
        }).catch(function(error) {
          console.log(error);
          self.errorHandler(error.response);
        });
    },
    getSupplierMapping(supplierId){
      var self = this;
      axios.get(self.$apiAdress + '/api/getSupplierMapping/' + supplierId)
        .then(function(response) {
          var supplierHeader = response.data;
          for (var i = 0; i < supplierHeader.length; i++){
            // var attrId = self.supplierHeader[i].id;
            // var attrSupId = self.supplierHeader[i].attribute_supplier_id;
            var attributeLabel = supplierHeader[i].attribute_label;
            self.form.supplier.attributes[i];// sets json key to the attribute Id
            self.form.supplier.attributes[i] = attributeLabel;// this sets the value
          }
          // self.getSupAttributesLabels();
          console.log('form: ', self.form);
          // console.log('attrId: ', attrId);
          console.log('form edited: ', self.form.edited);
          console.log('form attributes: ', self.form.attributes);

          console.log('attributeLabel: ', attributeLabel);
          console.log('supplierHeader: ', response.data);
        }).catch(function(error) {
          console.log(error);
          self.errorHandler(error.response);
        });
    },
    getSupplierAttributes(supplierId){
      var self = this;
      axios.get(self.$apiAdress + '/api/getSupAttributes/' + supplierId)
        .then(function(response) {
          self.supplierHeader = response.data;
          console.log('sup Header: ', self.supplierHeader);
        }).catch(function(error) {
          console.log(error);
          self.errorHandler(error.response);
        });
    },
    getEntities(supplierId){
      var self = this;
      axios.get(self.$apiAdress + '/api/getEntities/' + supplierId)
        .then(function(response) {
          // console.log('response.data: ', response.data);
        }).catch(function(error) {
          console.log(error);
          self.errorHandler(error.response);
        });
    },
    getCSVData(){
      var self = this;
      // const data = await self.$store.dispatch('user/getInfo');
      // self.user = data;
      console.log('user data: ', self.user);
      var userId = localStorage.getItem('user id');
      self.form.supplier.userId = localStorage.getItem('user id');
      // self.map.userId = localStorage.getItem('user id');

      // axios.get(self.$apiAdress + '/api/getAttributes')
      //   .then(function(response) {
      //     self.tableHeader = response.data;
      //     // this sets the dropdown value
      //     for (var i = 0; i < self.tableHeader.length; i++){
      //       // self.map.supAttrId.push(self.tableHeader[i].attribute_supplier_id);
      //       // var attrId = self.tableHeader[i].id;
      //       // var attrSupId = self.supplierHeader[i].attribute_supplier_id;
      //       var attributeLabel = self.tableHeader[i].name;
      //       self.form.admin.attributes[i];// sets json key to the attribute Id
      //       self.form.admin.attributes[i] = attributeLabel;// this sets the value
      //     }
      //     console.log('self.form.admin: ', self.form.admin);
      //     // console.log('formAdmin attrId: ', attrId);
      //     console.log('self.form.admin attributes: ', self.form.admin.attributes);
      //     console.log('tableHeaders: ', self.tableHeader);
      //   }).catch(function(error) {
      //     console.log(error);
      //     self.errorHandler(error.response);
      //   });

      self.getAdminAtrributes();
      self.getSupplierMapping(userId);
      self.getEntities(userId);
      self.getSupplierAttributes(userId);

      // axios.get(self.$apiAdress + '/api/getSupplierMapping/' + userId)
      //   .then(function(response) {
      //     var supplierHeader = response.data;
      //     for (var i = 0; i < supplierHeader.length; i++){
      //       // var attrId = self.supplierHeader[i].id;
      //       // var attrSupId = self.supplierHeader[i].attribute_supplier_id;
      //       var attributeLabel = supplierHeader[i].attribute_label;
      //       self.form.supplier.attributes[i];// sets json key to the attribute Id
      //       self.form.supplier.attributes[i] = attributeLabel;// this sets the value
      //     }
      //     // self.getSupAttributesLabels();
      //     console.log('form: ', self.form);
      //     // console.log('attrId: ', attrId);
      //     console.log('form edited: ', self.form.edited);
      //     console.log('form attributes: ', self.form.attributes);

      //     console.log('attributeLabel: ', attributeLabel);
      //     console.log('supplierHeader: ', response.data);
      //   }).catch(function(error) {
      //     console.log(error);
      //     self.errorHandler(error.response);
      //   });

      // // if (!self.user.roles[0] === 'admin'){

      // axios.get(self.$apiAdress + '/api/getEntities/' + userId)
      //   .then(function(response) {
      //     // console.log('response.data: ', response.data);
      //   }).catch(function(error) {
      //     console.log(error);
      //     self.errorHandler(error.response);
      //   });

      // axios.get(self.$apiAdress + '/api/getSupAttributes/' + userId)
      //   .then(function(response) {
      //     self.supplierHeader = response.data;
      //     console.log('sup Header: ', self.supplierHeader);
      //   }).catch(function(error) {
      //     console.log(error);
      //     self.errorHandler(error.response);
      //   });
    },
    storeSupAttributes(form){
      var self = this;
      axios.post(self.$apiAdress + '/api/storeEditedSupAttributes', form)
        .then(function(response) {
          self.$message({
            type: 'success',
            message: 'Mapping Saved',
            duration: 5 * 1000,
          });
          // self.$router.go(); //reloads page
          console.log('storeSupAttributes: ', response.data);
        }).catch(function(error) {
          self.$message({
            type: 'error',
            message: error,
            duration: 5 * 1000,
          });
          console.log(error);
          // self.$router.go();

          self.errorHandler(error.response);
        });
    },
    async saveCSV(){
      var self = this;
      const data = await self.$store.dispatch('user/getInfo');
      self.user = data;
      // var csvHeaderData = null;
      console.log('form admin: ', self.form.admin);
      console.log('form supplier: ', self.form.supplier);

      // var addedInputs = $('.admin_input');

      // for(var i = 1; i <= addedInputs.length; i++){
      //   self.form.admin.attributes.push($('#sel_' + i).find(':selected').text());
      // }
      console.log('self.form: ', self.form);
      // console.log('name: ',name);

      // self.user.roles[0] === 'admin' ? csvHeaderData = self.tableHeader : csvHeaderData = self.supplierHeader;

      // console.log('form: ', self.form.attributes);
      // console.log('form edited: ', self.form.edited);
      // console.log('sup headers: ', self.supplierHeader);

      self.storeSupAttributes(self.form);

      // axios.put(self.$apiAdress + '/api/storeUserCSVData/' + csvHeaderData)
      //   .then(function(response) {
      //     self.$message({
      //       type: 'success',
      //       message: 'CSV Headers Saved',
      //       duration: 5 * 1000,
      //     });
      //     console.log('userCSVData: ', response.data);
      //   }).catch(function(error) {
      //     self.$message({
      //       type: 'error',
      //       message: error,
      //       duration: 5 * 1000,
      //     });
      //     console.log(error);
      //     self.errorHandler(error.response);
      //   });

      // for (var i = 0; i < self.tableData.length; i++){
      //   // var keys = Object.keys(self.tableData[i]);
      //   // var values = Object.values(self.tableData[i]);

      //   console.log('tableData: ', self.tableData[i]);
      //   console.log('tableHeader: ', self.tableHeader[i]);

      //   // axios.put(self.$apiAdress + '/api/storeTableKeysData/' + keys.toString().replace(/%20/g, ' '))
      //   //   .then(function(response) {
      //   //     self.$message({
      //   //       type: 'success',
      //   //       message: 'CSV Keys Saved',
      //   //       duration: 5 * 1000,
      //   //     });
      //   //     console.log('storeTableKeysData: ', response.data);
      //   //   }).catch(function(error) {
      //   //     self.$message({
      //   //       type: 'error',
      //   //       message: error,
      //   //       duration: 5 * 1000,
      //   //     });
      //   //     console.log(error);
      //   //     self.errorHandler(error.response);
      //   //   });

      //   // axios.put(self.$apiAdress + '/api/storeTableValData/' + values.toString().replace(/\//g, '-'))
      //   //   .then(function(response) {
      //   //     self.$message({
      //   //       type: 'success',
      //   //       message: 'Table Data is Saved',
      //   //       duration: 5 * 1000,
      //   //     });
      //   //     console.log('storeTableKeysData: ', response.data);
      //   //   }).catch(function(error) {
      //   //     self.$message({
      //   //       type: 'error',
      //   //       message: error,
      //   //       duration: 5 * 1000,
      //   //     });
      //   //     console.log(error);
      //   //     self.errorHandler(error.response);
      //   //   });
      // }
    },
    createAttribute(){
      // console.log('current user id: ', id);
      this.$refs['attributeForm'].validate((valid) => {
        console.log('valid: ', valid);
        if (valid) {
          var self = this;
          // this.newUser.roles = [this.newUser.role];
          this.attributeCreating = true;
          // console.log('new attr: ', self.newAttributes);

          axios.post(self.$apiAdress + '/api/storeAdminAttributes', self.newAttributes)
            .then(function(response) {
              self.$message({
                type: 'success',
                message: 'Attributes Saved',
                duration: 5 * 1000,
              });

              self.resetNewAttributes();
              self.dialogAttributeFormVisible = false;
              // self.handleFilter();
              self.attributeCreating = false;
              // self.$router.go();

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
    handleCreateAttributes() {
      this.resetNewAttributes();
      this.dialogAttributeFormVisible = true;
      this.$nextTick(() => {
        this.$refs['attributeForm'].clearValidate();
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
    async handleSuccess({ results, header }) {
      var self = this;
      const data = await self.$store.dispatch('user/getInfo');
      self.user = data;

      self.tableData = results;

      if (self.user.roles[0] === 'admin'){
        self.tableHeader = header;
        console.log('uploaded Headers: ', header);
        self.tableHeader = self.tableHeader.toString().replace(/[^a-zA-Z ]/g, ' ').split(' ').filter(item => item);
        console.log('uploaded tableHeaders: ', self.tableHeader);
      } else {
        self.supplierHeader = header;

        // self.supplierHeader = Object.assign({}, self.supplierHeader, header);
        // for(var i = 0; i < self.supplierHeader; i++){
        // self.form.attributes = self.supplierHeader;
        // }
        // console.log('uploaded Headers: ', header);

        self.supplierHeader = self.supplierHeader.toString().replace(/[^a-zA-Z ]/g, ' ').split(' ').filter(item => item);
        console.log('supplierHeader: ', self.supplierHeader);
      }
    },
  },
};
</script>

<style>
.csv_mapping{
  margin-top: 10px;
  padding: 15px;
  /* text-align: end; */
}

.attribute_picker{
  margin-bottom: 10px;
}

/* .admin_attribute_picker{
  margin-bottom: 10px;
}
.supplier_attribute_picker{
  margin-bottom: 10px;
} */

</style>
