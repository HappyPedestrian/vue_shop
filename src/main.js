import Vue from 'vue'
import App from './App.vue'
import router from './router'
import './plugins/element.js'
import './assets/css/global.css'
import './assets/font/iconfont.css'
import axios from 'axios'
import VueTable from 'vue-table-with-tree-grid'

// 导入富文本编辑器
import VueQuillEditor from 'vue-quill-editor'
// 导入富文本编辑器的样式
import 'quill/dist/quill.core.css' // import styles
import 'quill/dist/quill.snow.css' // for snow theme
import 'quill/dist/quill.bubble.css' // for bubble theme

axios.defaults.headers.post['Content-Type'] = 'application/x-www-form-urlencoded'

// axios.interceptors.request.use(config => {
//   config.headers.Authorization = window.sessionStorage.getItem('token')
//   return config
// })

axios.interceptors.request.use(function (config) {
  // Do something before request is sent
  let token = window.sessionStorage.getItem('token')
      config.headers['Authorization'] = token // 将token放到请求头发送给服务器
      return config
}, function (error) {
  console.log(Promise.reject(error))
})

Vue.prototype.$http = axios

// 将富文本编辑器注册为全局组件
Vue.use(VueQuillEditor)

Vue.component('tree-table', VueTable)

Vue.filter('dateFormat', function(originValue) {
  let date
  if (originValue instanceof Date) {
    date = originValue
  } else {
    date = new Date(originValue * 1000)
  }

  const y = date.getFullYear()
  const M = (date.getMonth() + 1 + '').padStart(2, '0')
  const d = (date.getDate() + '').padStart(2, '0')

  const hh = (date.getHours() + '').padStart(2, '0')
  const mm = (date.getMinutes() + '').padStart(2, '0')
  const ss = (date.getSeconds() + '').padStart(2, '0')

  return `${y}-${M}-${d} ${hh}:${mm}:${ss}`
})

Vue.config.productionTip = false

new Vue({
  router,
  render: h => h(App)
}).$mount('#app')
