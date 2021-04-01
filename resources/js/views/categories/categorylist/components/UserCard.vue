<template>
  <el-card v-if="user.name">
    <div class="user-profile">
      <div class="box-center" />
      <div class="box-social">
        <el-table :data="social" :show-header="false">
          <!-- <el-table-column prop="name" label="Name" /> -->
          <el-table-column label="Count" align="left" width="100">
            <!-- <template slot-scope="scope">
              {{ scope.row.count | toThousandFilter }}
            </template> -->
          </el-table-column>
        </el-table>
      </div>
      <div class="user-follow" />
    </div>
  </el-card>
</template>

<script>

export default {
  props: {
    user: {
      type: Object,
      default: () => {
        return {
          name: '',
          email: '',
          avatar: '',
          roles: [],
        };
      },
    },
  },
  data() {
    return {
      social: [
        {
          'name': 'Followers',
          'count': 1235,
        },
        {
          'name': 'Following',
          'count': 23512,
        },
        {
          'name': 'Friends',
          'count': 7242,
        },
      ],
    };
  },
  mounted: function(){
    console.log('usercard data: ', this.user);
  },
  methods: {
    getRole() {
      var roles = this.user.roles.map(value => this.$options.filters.uppercaseFirst(value));
      return roles.join(' | ');
    },
  },

};
</script>

<style lang="scss" scoped>
.user-profile {
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
</style>
