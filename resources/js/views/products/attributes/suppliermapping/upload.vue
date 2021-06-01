<template>
  <div class="app-container">
    <!-- <upload-excel-component :on-success="handleSuccess" :before-upload="beforeUpload" /> -->
    <el-button class="filter-item" style="margin-left: 10px;" type="primary" icon="el-icon-plus" @click="saveCSV()">
      Save
    </el-button>

    <div class="col-12 csv_mapping">

      <el-form>
        <el-form-item :label="user.name">
          <el-select v-model="form.time" class="csv_picker" placeholder="Please select time">
            <el-option v-for="items in time" :key="items" :label="items" :value="items" />
          </el-select>
        </el-form-item>
      </el-form>

      <el-form :data="tableData" border highlight-current-row>

        <div class="col-12">
          <el-form-item v-for="(item, index) of tableHeader" :key="index" :name="item.id" :label="item.name">

            <el-select :id="'selected_'+index.toString()" v-model="form.attributes[item.id]" :name="item.code" class="csv_picker">
              <el-option v-for="(items, ind) in supplierHeader" :key="ind" :name="items" :prop="ind" :label="items.attribute_label" :value="items.attribute_label" />
            </el-select>

            <div :class="'field_wrapper_'+index.toString()">
              <div>
                <el-button href="javascript:void(0);" class="filter-item add_button" style="float:left;" type="primary" icon="el-icon-plus" />
              </div>
            </div>

          </el-form-item>
        </div>
      </el-form>
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
        time: '',
        attributes: [],
        edited: [],
        userId: '',
      },
      map: {
        userId: '',
        supAttrId: [],
      },
      time: ['hourly', 'daily', 'weekly'],
      tableData: [],
      tableHeader: [],
      supplierHeader: [],
      labels: [],
      keys: [],
      values: [],
      user: '',
      userId: '',
    };
  },
  mounted: function() {
    this.getUser();
    this.getCSVData();
    this.createInputs();
    console.log('params: ', this.$route.params.id);
  },
  methods: {
    async getUser() {
      var self = this;
      const data = await self.$store.dispatch('user/getInfo');
      self.user = data;
    },
    createInputs() {
      var self = this;
      self.$nextTick(() => {
        var maxField = 9; // Input fields increment limitation
        var addButton = $('.add_button'); // Add button selector
        var wrapper = $('.field_wrapper_'); // Input field wrapper
        console.log('wrapper: ', $('.field_wrapper_') + 0);
        var items = ['Canada', 'Denmark', 'Finland', 'Germany', 'Mexico'];

        var x = 0; // Initial field counter is 1

        // Once add button is clicked

        // self.tableHeader.forEach((item, index) => {

        //   });

        for (var i = 0; i <= self.tableHeader; i++){
          console.log('item: ', self.tableHeader);
          // console.log('index: ', index );
        }
        console.log('pressed!!', self.tableHeader);

        $(addButton).click(function(){
          // Check maximum number of input fields
          if (x < maxField){
            x++; // Increment field counter
            var delBtn = '<button href="javascript:void(0);" type="primary" style="width:50px;" class="remove_button"> - </button>';
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
          $(self).parent('div').remove(); // Remove field html
          x--; // Decrement field counter
        });
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
    getCSVData(){
      var self = this;
      // const data = await self.$store.dispatch('user/getInfo');
      // self.user = data;

      console.log('user data: ', self.user);
      var userId = localStorage.getItem('user id');
      self.form.userId = localStorage.getItem('user id');
      self.map.userId = localStorage.getItem('user id');

      axios.get(self.$apiAdress + '/api/getAttributes')
        .then(function(response) {
          self.tableHeader = response.data;
          console.log('tableHeaders: ', self.tableHeader);
        }).catch(function(error) {
          console.log(error);
          self.errorHandler(error.response);
        });

      // axios.get(self.$apiAdress + '/api/getUserCSVData')
      //   .then(function(response) {
      //     self.supplierHeader = response.data;
      //     console.log('supplierHeader: ', self.supplierHeader);
      //   }).catch(function(error) {
      //     console.log(error);
      //     self.errorHandler(error.response);
      //   });

      // if (!self.user.roles[0] === 'admin'){

      // axios.get(self.$apiAdress + '/api/getSupCSVData/' + userId)
      //   .then(function(response) {
      //     self.supplierHeader = response.data;
      //     console.log('sup Header: ', self.supplierHeader);
      //   }).catch(function(error) {
      //     console.log(error);
      //     self.errorHandler(error.response);
      //   });

      axios.get(self.$apiAdress + '/api/getEntities/' + userId)
        .then(function(response) {
          // console.log('response.data: ', response.data);
        }).catch(function(error) {
          console.log(error);
          self.errorHandler(error.response);
        });

      axios.get(self.$apiAdress + '/api/getSupAttributes/' + userId)
        .then(function(response) {
          self.supplierHeader = response.data;
          for (var i = 0; i < self.supplierHeader.length; i++){
            self.map.supAttrId.push(self.supplierHeader[i].attribute_supplier_id);
            var attrId = self.supplierHeader[i].attribute_id;
            // var attrSupId = self.supplierHeader[i].attribute_supplier_id;
            var attributeLabel = self.supplierHeader[i].attribute_label;
            self.form.attributes[attrId];// sets json key to the attribute Id
            self.form.attributes[attrId] = attributeLabel;// this sets the value
            // self.form.edited[attrId] = attributeLabel;
            // self.form.edited[attrId];
            // self.form.edited[attrId] = attributeLabel + ' ' + attrSupId + ' ' + attrId;
            // self.form.edited[attrId].attributeLabel[attrSupId];
          }
          // self.getSupAttributesLabels();
          console.log('form: ', self.form);
          console.log('map: ', self.map);
          console.log('form edited: ', self.form.edited);
          console.log('form attributes: ', self.form.attributes);

          // console.log('attributeLabel: ', attributeLabel);
          console.log('sup Header: ', self.supplierHeader);
        }).catch(function(error) {
          console.log(error);
          self.errorHandler(error.response);
        });

      // }
    },
    storeSupAttributes(){
      var self = this;
      axios.post(self.$apiAdress + '/api/storeEditedSupAttributes', self.form)
        .then(function(response) {
          self.$message({
            type: 'success',
            message: 'Attributes Saved',
            duration: 5 * 1000,
          });
          // self.$router.go();
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

      // self.user.roles[0] === 'admin' ? csvHeaderData = self.tableHeader : csvHeaderData = self.supplierHeader;

      // console.log('form: ', self.form.attributes);
      // console.log('form edited: ', self.form.edited);
      // console.log('sup headers: ', self.supplierHeader);

      self.storeSupAttributes();

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

      for (var i = 0; i < self.tableData.length; i++){
        // var keys = Object.keys(self.tableData[i]);
        // var values = Object.values(self.tableData[i]);

        console.log('tableData: ', self.tableData[i]);
        console.log('tableHeader: ', self.tableHeader[i]);

        // axios.put(self.$apiAdress + '/api/storeTableKeysData/' + keys.toString().replace(/%20/g, ' '))
        //   .then(function(response) {
        //     self.$message({
        //       type: 'success',
        //       message: 'CSV Keys Saved',
        //       duration: 5 * 1000,
        //     });
        //     console.log('storeTableKeysData: ', response.data);
        //   }).catch(function(error) {
        //     self.$message({
        //       type: 'error',
        //       message: error,
        //       duration: 5 * 1000,
        //     });
        //     console.log(error);
        //     self.errorHandler(error.response);
        //   });

        // axios.put(self.$apiAdress + '/api/storeTableValData/' + values.toString().replace(/\//g, '-'))
        //   .then(function(response) {
        //     self.$message({
        //       type: 'success',
        //       message: 'Table Data is Saved',
        //       duration: 5 * 1000,
        //     });
        //     console.log('storeTableKeysData: ', response.data);
        //   }).catch(function(error) {
        //     self.$message({
        //       type: 'error',
        //       message: error,
        //       duration: 5 * 1000,
        //     });
        //     console.log(error);
        //     self.errorHandler(error.response);
        //   });
      }
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
  text-align: end;
}

.csv_picker{
  margin-bottom: 10px;
}

</style>
