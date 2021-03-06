import Vue from 'vue';
import Router from 'vue-router';

/**
 * Layzloading will create many files and slow on compiling, so best not to use lazyloading on devlopment.
 * The syntax is lazyloading, but we convert to proper require() with babel-plugin-syntax-dynamic-import
 * @see https://doc.laravue.dev/guide/advanced/lazy-loading.html
 */

Vue.use(Router);

/* Layout */
import Layout from '@/layout';

/* Router for modules */
// import elementUiRoutes from './modules/element-ui';
// import componentRoutes from './modules/components';
// import chartsRoutes from './modules/charts';
// import tableRoutes from './modules/table';
import adminRoutes from './modules/admin';
// import nestedRoutes from './modules/nested';
// import errorRoutes from './modules/error';
// import excelRoutes from './modules/excel';
// import permissionRoutes from './modules/permission';
import categoriesRoutes from './modules/categories';

/**
 * Sub-menu only appear when children.length>=1
 * @see https://doc.laravue.dev/guide/essentials/router-and-nav.html
 **/

/**
* hidden: true                   if `hidden:true` will not show in the sidebar(default is false)
* alwaysShow: true               if set true, will always show the root menu, whatever its child routes length
*                                if not set alwaysShow, only more than one route under the children
*                                it will becomes nested mode, otherwise not show the root menu
* redirect: noredirect           if `redirect:noredirect` will no redirect in the breadcrumb
* name:'router-name'             the name is used by <keep-alive> (must set!!!)
* meta : {
    roles: ['admin', 'editor']   Visible for these roles only
    permissions: ['view menu zip', 'manage user'] Visible for these permissions only
    title: 'title'               the name show in sub-menu and breadcrumb (recommend set)
    icon: 'svg-name'             the icon show in the sidebar
    noCache: true                if true, the page will no be cached(default is false)
    breadcrumb: false            if false, the item will hidden in breadcrumb (default is true)
    affix: true                  if true, the tag will affix in the tags-view
  }
**/

export const constantRoutes = [
  {
    path: '/redirect',
    component: Layout,
    hidden: true,
    children: [
      {
        path: '/redirect/:path*',
        component: () => import('@/views/redirect/index'),
      },
    ],
  },
  {
    path: '/login',
    component: () => import('@/views/login/index'),
    hidden: true,
  },
  {
    path: '/auth-redirect',
    component: () => import('@/views/login/AuthRedirect'),
    hidden: true,
  },
  {
    path: '/404',
    redirect: { name: 'Page404' },
    component: () => import('@/views/error-page/404'),
    hidden: true,
  },
  {
    path: '/401',
    component: () => import('@/views/error-page/401'),
    hidden: true,
  },
  {
    path: '',
    component: Layout,
    redirect: 'dashboard',
    children: [
      {
        path: 'dashboard',
        component: () => import('@/views/dashboard/index'),
        name: 'Dashboard',
        meta: { title: 'dashboard', icon: 'dashboard', noCache: false },
      },
    ],
  },
  // {
  //   path: '/documentation',
  //   component: Layout,
  //   redirect: '/documentation/index',
  //   children: [
  //     {
  //       path: 'index',
  //       component: () => import('@/views/documentation/index'),
  //       name: 'Documentation',
  //       meta: { title: 'documentation', icon: 'documentation', noCache: true },
  //     },
  //   ],
  // },
  // {
  //   path: '/categories',
  //   component: Layout,
  //   redirect: '/categories/index',
  //   name: 'Categories',
  //   hidden: false,
  //   meta: {
  //     // roles: ['admin', 'editor'],
  //     title: 'Categories',
  //     icon: 'component',
  //     // permissions: ['view menu nested routes'],
  //   },
  //   children: [
  //     {
  //       path: 'categorylist',
  //       component: () => import('@/views/categories/categorylist/index'), // Parent router-view
  //       name: 'Categorylist',
  //       meta: {
  //         title: 'Category List',
  //         icon: 'list',
  //         permissions: ['view menu category list'],
  //       },
  //     },
  //     {
  //       path: 'createcategories',
  //       component: () => import('@/views/categories/createcategories/index'),
  //       meta: {
  //         title: 'Create Categories',
  //         icon: 'plus',
  //         permissions: ['view menu create categories'],
  //       },
  //     },
  //     {
  //       path: 'mapcategories',
  //       component: () => import('@/views/categories/mapcategories/index'),
  //       meta: {
  //         title: 'Map Categories',
  //         icon: 'sort',
  //         permissions: ['view menu map categories'],
  //       },
  //     },
  //   ],
  // },
  categoriesRoutes,
  {
    path: '/products',
    component: Layout,
    redirect: '/products/menu1',
    name: 'Products',
    meta: {
      title: 'Products',
      icon: 'shopping',
    },
    children: [
      {
        path: 'productlist',
        component: () => import('@/views/products/productlist'), // Parent router-view
        name: 'Productlist',
        meta: {
          title: 'Product List',
          icon: 'list',
        },
      },
      {
        path: 'productview/:sku/:id',
        component: () => import('@/views/products/productview/index'), // Parent router-view
        name: 'Productview',
        hidden: true,
        meta: {
          title: 'Product View',
        },
      },
      {
        path: 'createproducts',
        component: () => import('@/views/products/createproducts'),
        meta: { title: 'Create Products' },
      },
      {
        path: 'attributes',
        component: () => import('@/views/products/attributes/'),
        meta: { title: 'Attributes' },
        children:
        [
          {
            path: 'listattributes',
            component: () => import('@/views/products/attributes/listattributes/index'),
            name: 'list attributes',
            meta: { title: 'List Attributes' },
          },
          {
            path: 'createattributes',
            component: () => import('@/views/products/attributes/createattributes/index'),
            name: 'Create Attributes',
            meta: { title: 'Create Attributes' },
          },
          {
            path: 'mapping',
            component: () => import('@/views/products/attributes/mapping/upload'),
            name: 'Mapping',
            meta: { title: 'Mapping' },

          },
          {
            path: 'suppliermapping/:id',
            component: () => import('@/views/products/attributes/suppliermapping/upload'),
            name: 'Supplier Mapping',
            hidden: true,
            meta: { title: 'Supplier Mapping' },
          },
        ],
      },
    ],
  },
  {
    path: '/settings',
    component: Layout,
    redirect: '/settings/menu1',
    name: 'Settings',
    meta: {
      title: 'Settings',
      icon: 'admin',
      permissions: ['view menu settings'],
    },
    children: [
      {
        path: 'importprofiles',
        component: () => import('@/views/settings/importprofiles/'),
        meta: { title: 'Import Profiles' },
        children:
        [
          {
            path: 'newprofiles',
            component: () => import('@/views/settings/importprofiles/newprofiles/index'),
            name: 'new profiles',
            meta: { title: 'New Profiles' },
          },
          {
            path: 'allprofiles',
            component: () => import('@/views/settings/importprofiles/allprofiles/index'),
            name: 'All Profiles',
            meta: { title: 'All Profiles' },
          },
          {
            path: 'supplierprofiles/:id',
            component: () => import('@/views/settings/importprofiles/supplierprofiles/index'),
            hidden: true,
            name: 'Supplier Profiles',
            meta: { title: 'Supplier Profiles' },
          },
        ],
      },
    ],
  },
  {
    path: '/profile',
    component: Layout,
    redirect: '/profile/edit',
    children: [
      {
        path: 'edit',
        component: () => import('@/views/users/SelfProfile'),
        name: 'SelfProfile',
        meta: { title: 'userProfile', icon: 'user', noCache: true },
      },
    ],
  },
  // {
  //   path: '/guide',
  //   component: Layout,
  //   redirect: '/guide/index',
  //   children: [
  //     {
  //       path: 'index',
  //       component: () => import('@/views/guide/index'),
  //       name: 'Guide',
  //       meta: { title: 'guide', icon: 'guide', noCache: true },
  //     },
  //   ],
  // },

];

export const asyncRoutes = [
  // permissionRoutes,
  // componentRoutes,
  // chartsRoutes,
  // nestedRoutes,
  // tableRoutes,
  // excelRoutes,
  // elementUiRoutes,
  adminRoutes,
  // {
  //   path: '/theme',
  //   component: Layout,
  //   redirect: 'noredirect',
  //   children: [
  //     {
  //       path: 'index',
  //       component: () => import('@/views/theme/index'),
  //       name: 'Theme',
  //       meta: { title: 'theme', icon: 'theme' },
  //     },
  //   ],
  // },
  // {
  //   path: '/clipboard',
  //   component: Layout,
  //   redirect: 'noredirect',
  //   meta: { permissions: ['view menu clipboard'] },
  //   children: [
  //     {
  //       path: 'index',
  //       component: () => import('@/views/clipboard/index'),
  //       name: 'ClipboardDemo',
  //       meta: { title: 'clipboardDemo', icon: 'clipboard', roles: ['supplier', 'editor', 'user'] },
  //     },
  //   ],
  // },
  // errorRoutes,
  //
  // {
  //   path: '/zip',
  //   component: Layout,
  //   redirect: '/zip/download',
  //   alwaysShow: true,
  //   meta: { title: 'zip', icon: 'zip', permissions: ['view menu zip'] },
  //   children: [
  //     {
  //       path: 'download',
  //       component: () => import('@/views/zip'),
  //       name: 'ExportZip',
  //       meta: { title: 'exportZip' },
  //     },
  //   ],
  // },
  // {
  //   path: '/pdf',
  //   component: Layout,
  //   redirect: '/pdf/index',
  //   meta: { title: 'pdf', icon: 'pdf', permissions: ['view menu pdf'] },
  //   children: [
  //     {
  //       path: 'index',
  //       component: () => import('@/views/pdf'),
  //       name: 'Pdf',
  //       meta: { title: 'pdf' },
  //     },
  //   ],
  // },
  // {
  //   path: '/pdf/download',
  //   component: () => import('@/views/pdf/Download'),
  //   hidden: true,
  // },
  // {
  //   path: '/i18n',
  //   component: Layout,
  //   meta: { permissions: ['view menu i18n'] },
  //   children: [
  //     {
  //       path: 'index',
  //       component: () => import('@/views/i18n'),
  //       name: 'I18n',
  //       meta: { title: 'i18n', icon: 'international' },
  //     },
  //   ],
  // },
  // {
  //   path: '/external-link',
  //   component: Layout,
  //   children: [
  //     {
  //       path: 'https://github.com/tuandm/laravue',
  //       meta: { title: 'externalLink', icon: 'link' },
  //     },
  //   ],
  // },
  // { path: '*', redirect: '/404', hidden: true },
];

const createRouter = () => new Router({
  // mode: 'history', // require service support
  scrollBehavior: () => ({ y: 0 }),
  base: process.env.MIX_LARAVUE_PATH,
  routes: constantRoutes,
});

const router = createRouter();

// Detail see: https://github.com/vuejs/vue-router/issues/1234#issuecomment-357941465
export function resetRouter() {
  const newRouter = createRouter();
  router.matcher = newRouter.matcher; // reset router
}

export default router;
