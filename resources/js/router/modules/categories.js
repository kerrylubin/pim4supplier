import Layout from '@/layout';

const categoriesRoutes = {
  path: '/categories',
  component: Layout,
  redirect: '/categories/index',
  name: 'Categories',
  hidden: false,
  meta: {
    title: 'Categories',
    icon: 'component',
  },
  children: [
    {
      path: 'categorylist',
      component: () => import('@/views/categories/categorylist/index'), // Parent router-view
      name: 'Categorylist',
      meta: {
        title: 'Category List',
        icon: 'list',
        permissions: ['view menu category list'],
        noCache: true,
      },
    },
    {
      path: 'createcategories',
      component: () => import('@/views/categories/createcategories/index'),
      meta: {
        title: 'Create Categories',
        icon: 'plus',
        permissions: ['view menu create categories'],
        noCache: true,
      },
    },
    {
      path: 'mapcategories',
      component: () => import('@/views/categories/mapcategories/index'),
      meta: {
        title: 'Map Categories',
        icon: 'sort',
        permissions: ['view menu map categories'],
        noCache: true,
      },
    },
  ],
};

export default categoriesRoutes;
