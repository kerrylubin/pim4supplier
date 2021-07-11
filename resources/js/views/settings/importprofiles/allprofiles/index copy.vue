<template>
  <div class="app-container">

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

      <el-table-column align="center" label="Actions" width="350" @click="goToUser()">
        <template slot-scope="scope">
          <el-button type="primary" size="small" icon="el-icon-plus" @click="handleCreate(scope.row.supplier_id);">
            Create New Profile
          </el-button>
          <router-link :to="'/products/attributes/suppliermapping/'+scope.row.supplier_id">
            <el-button type="info" size="small" icon="el-icon-edit" @click="setSupplierId(scope.row.supplier_id)">
              Mapping
            </el-button>
          </router-link>
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
import { fetchPv, createArticle, updateArticle } from '@/api/article';
import waves from '@/directive/waves'; // Waves directive
import { parseTime } from '@/utils';
import Pagination from '@/components/Pagination'; // Secondary package based on el-pagination

const calendarTypeOptions = [
  { key: 'CN', display_name: 'China' },
  { key: 'US', display_name: 'USA' },
  { key: 'JA', display_name: 'Japan' },
  { key: 'VI', display_name: 'Vietnam' },
];

// arr to obj ,such as { CN : "China", US : "USA" }
const calendarTypeKeyValue = calendarTypeOptions.reduce((acc, cur) => {
  acc[cur.key] = cur.display_name;
  return acc;
}, {});

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
    typeFilter(type) {
      return calendarTypeKeyValue[type];
    },
  },
  data() {
    return {
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
      importanceOptions: [1, 2, 3],
      calendarTypeOptions,
      sortOptions: [{ label: 'ID Ascending', key: '+id' }, { label: 'ID Descending', key: '-id' }],
      statusOptions: ['published', 'draft', 'deleted'],
      showReviewer: false,
      temp: {
        id: undefined,
        importance: 1,
        remark: '',
        timestamp: new Date(),
        title: '',
        type: '',
        status: 'published',
      },
      dialogFormVisible: false,
      dialogStatus: '',
      textMap: {
        update: 'Edit',
        create: 'Create',
      },
      dialogPvVisible: false,
      pvData: [],
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
    createNewProfile(){
      // console.log('current user id: ', id);
      this.$refs['profileForm'].validate((valid) => {
        if (valid) {
          var self = this;
          // this.newUser.roles = [this.newUser.role];
          this.dialogFormVisible = true;
          this.profileCreating = true;
          console.log('Profile: ', self.profileForm);

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
              self.$router.go(); // refresh
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
    setSupplierId(id){
      localStorage.setItem('user id', id);
    },
    async getList() {
      var self = this;
      const { limit, page } = this.listQuery;
      this.loading = true;
      // const { data, meta } = await userResource.list(this.query);

      // self.list = self.atrList;
      axios.get(self.$apiAdress + '/api/getImportProfiles')
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
      this.listQuery.page = 1;
      this.getList();
    },
    handleModifyStatus(row, status) {
      this.$message({
        message: 'Successful operation',
        type: 'success',
      });
      row.status = status;
    },
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
      this.handleFilter();
    },
    resetTemp() {
      this.temp = {
        id: undefined,
        importance: 1,
        remark: '',
        timestamp: new Date(),
        title: '',
        status: 'published',
        type: '',
      };
    },
    handleCreate(id) {
      this.resetTemp();
      // this.dialogStatus = 'create';
      this.profileForm.supplier_id = id;
      this.dialogFormVisible = true;
      this.$nextTick(() => {
        this.$refs['profileForm'].clearValidate();
      });
    },
    createData() {
      this.$refs['dataForm'].validate((valid) => {
        if (valid) {
          this.temp.id = parseInt(Math.random() * 100) + 1024; // mock a id
          this.temp.author = 'laravue';
          createArticle(this.temp).then(() => {
            this.list.unshift(this.temp);
            this.dialogFormVisible = false;
            this.$notify({
              title: 'Success',
              message: 'Created successfully',
              type: 'success',
              duration: 2000,
            });
          });
        }
      });
    },
    handleUpdate(row) {
      this.temp = Object.assign({}, row); // copy obj
      this.temp.timestamp = new Date(this.temp.timestamp);
      this.dialogStatus = 'update';
      this.dialogFormVisible = true;
      this.$nextTick(() => {
        this.$refs['dataForm'].clearValidate();
      });
    },
    updateData() {
      this.$refs['dataForm'].validate((valid) => {
        if (valid) {
          const tempData = Object.assign({}, this.temp);
          tempData.timestamp = +new Date(tempData.timestamp); // change Thu Nov 30 2017 16:41:05 GMT+0800 (CST) to 1512031311464
          updateArticle(tempData).then(() => {
            for (const v of this.list) {
              if (v.id === this.temp.id) {
                const index = this.list.indexOf(v);
                this.list.splice(index, 1, this.temp);
                break;
              }
            }
            this.dialogFormVisible = false;
            this.$notify({
              title: 'Success',
              message: 'Updated successfully',
              type: 'success',
              duration: 2000,
            });
          });
        }
      });
    },
    handleDelete(row) {
      this.$notify({
        title: 'Success',
        message: 'Deleted successfully',
        type: 'success',
        duration: 2000,
      });
      const index = this.list.indexOf(row);
      this.list.splice(index, 1);
    },
    handleFetchPv(pv) {
      fetchPv(pv).then(response => {
        this.pvData = response.data.pvData;
        this.dialogPvVisible = true;
      });
    },
    handleDownload() {
      this.downloadLoading = true;
      import('@/vendor/Export2Excel').then(excel => {
        const tHeader = ['timestamp', 'title', 'type', 'importance', 'status'];
        const filterVal = ['timestamp', 'title', 'type', 'importance', 'status'];
        const data = this.formatJson(filterVal, this.list);
        excel.export_json_to_excel({
          header: tHeader,
          data,
          filename: 'table-list',
        });
        this.downloadLoading = false;
      });
    },
    formatJson(filterVal, jsonData) {
      return jsonData.map(v => filterVal.map(j => {
        if (j === 'timestamp') {
          return parseTime(v[j]);
        } else {
          return v[j];
        }
      }));
    },
  },
};
</script>
