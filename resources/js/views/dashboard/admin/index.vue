<template>
  <div class="dashboard-editor-container" style="padding: 40px;">

    <div class="grid-wrapper center" style="width:100%">

      <div class="grid-row">
        <div v-for="(item, index) of gridData.columns" :key="index" class="grid-header">{{ item }}</div>
      </div>

      <div v-for="(item, ind) of gridData.data" :key="ind" class="grid-row">
        <div class="list-row-item">
          <div>{{ item.Id }}</div>
        </div>

        <div class="list-row-item">
          <div><img :src="item.ImagePath" class="list-image"></div>
        </div>

        <div class="list-row-item">
          <div>{{ item.Name }}</div>
        </div>

        <div class="list-row-item">
          <div>{{ item.Quantity }}</div>
        </div>

        <div class="list-row-item">
          <div>{{ item.Price }}</div>
        </div>

        <div class="list-row-item">
          <div>{{ item.Seller }}</div>
        </div>

      </div>
    </div>
    <pagination v-show="total>0" :total="total" :page.sync="query.page" :limit.sync="query.limit" @pagination="getList()" />
  </div>
</template>

<script>
import Pagination from '@/components/Pagination';

var cartData = {
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
};

export default {
  name: 'DashboardAdmin',
  components: { Pagination },
  data() {
    return {
      list: null,
      total: 0,
      loading: true,
      downloading: false,
      userCreating: false,
      query: {
        page: 1,
        limit: 15,
        keyword: '',
        role: '',
      },
      gridData: cartData,
      buttonSwitchViewText: 'Switch to ListView',
      buttonSwitchDataText: 'Switch to books data',
      isGridView: true,
      isBookData: false,
    };
  },
  mounted: function(){
    console.log('data:', this.gridData.data.length);
  },
  methods: {
    async getList() {
      const { limit, page } = this.query;
      this.loading = true;
      // const { data, meta } = await userResource.list(this.query);
      // this.list = data;
      // console.log('currentUserId: ', this.currentUserId);
      // console.log('currentUser: ', this.currentUser);
      // console.log('list: ', this.list);

      this.gridData.forEach((element, index) => {
        element['index'] = (page - 1) * limit + index + 1;
      });

      this.total = this.gridData.data.length;
      this.loading = false;
    },
  },
};
</script>

<style rel="stylesheet/scss" lang="scss" scoped>
// .dashboard-editor-container {
//   padding: 32px;
//   background-color: rgb(240, 242, 245);

.grid-wrapper {
    display: table;
    border: 4px solid #304156;
    border-radius: 6px;
    transition: all ease 0.5s;
}

.grid-header {
	font-weight: bold;
	background: #304156;
	color: white;
	border-bottom: 4px solid #f6f6f6;
}

.grid-row {
	display: table-row;
}

.grid-row > div {
	display: table-cell;
	padding: 10px 20px;
}

.grid-wrapper > div:nth-child(even) {
	background: #f6f6f6;
	transition: all ease 0.4s;
}

.grid-wrapper > div:nth-child(odd) {
	background: #fafafa;
	transition: all ease 0.4s;
}

.grid-wrapper > div:hover {
	background: #a9d6ff;
	transition: all ease 0.4s;
}
// }
</style>
