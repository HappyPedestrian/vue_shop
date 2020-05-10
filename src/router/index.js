import Vue from 'vue'
import VueRouter from 'vue-router'
import Login from '../components/Login.vue'
import Home from '../components/Home.vue'
import Welcome from '../components/Welcome.vue'
import Users from '../components/user/Users.vue'
import Rights from '../components/power/Rights.vue'
import Roles from '../components/power/Roles.vue'
import Cate from '../components/goods/Cate.vue'
import Params from '../components/goods/Params.vue'
import GoodsList from '../components/goods/List.vue'
import Add from '../components/goods/Add.vue'
import Order from '../components/order/Order.vue'
import OrderNumberStatistics from '../components/statistics/OrderStatistics.vue'
import OrderAdd from '../components/order/OrderAdd.vue'
import OrderTimeStatistics from '../components/statistics/OrderTimeStatistics.vue'
import GoodsAnalyze from '../components/statistics/GoodsAnalyze.vue'
import GeographyAnalyze from '../components/statistics/GeographyAnalyze.vue'
Vue.use(VueRouter)

const routes = [
  { path: '/', redirect: '/login' },
  { path: '/login', component: Login },
  { path: '/home',
    component: Home,
    redirect: '/welcome',
    children: [ { path: '/welcome', component: Welcome },
                { path: '/users', component: Users },
                { path: '/rights', component: Rights },
                { path: '/roles', component: Roles },
                { path: '/goodsCategory', component: Cate },
                { path: '/categoryParam', component: Params },
                { path: '/GoodsList', component: GoodsList },
                { path: '/goods/add', component: Add },
                { path: '/orderList', component: Order },
                { path: '/orderNumberStatistics', component: OrderNumberStatistics },
                { path: '/orders/add', component: OrderAdd },
                { path: '/orderTimeStatistics', component: OrderTimeStatistics },
                { path: '/goodsAnalyze', component: GoodsAnalyze },
                { path: '/geographyAnalyze', component: GeographyAnalyze } ]
  }
]

const router = new VueRouter({
  routes
})

router.beforeEach((to, from, next) => {
  if (to.path === '/login') return next()
  let tokenStr = window.sessionStorage.getItem('token')
  if (!tokenStr) return next('/login')
  return next()
})

export default router
