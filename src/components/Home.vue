<template>
  <el-container class="home_container">
    <el-header>
      <div class="header_left">
        <img src="../assets/blackbear.jpg" alt />
        <span>电商管理系统</span>
      </div>
      <el-button @click="logout" type="info">退出</el-button>
    </el-header>
    <el-container>
      <el-aside :width="isCollapse ? '64px' : '200px'">
        <div class="toggle_button" @click="toggleCollapse">|||</div>
        <el-menu background-color="#333744" text-color="#fff" active-text-color="#409EFF" unique-opened
        :collapse="isCollapse" :collapse-transition="false" router :default-active="activePath">
          <el-submenu :index='item.order' v-for="(item, index) in menulist" :key="item.id">
            <template slot="title">
              <i :class="iconObj[index]"></i>
              <span>{{ item.authname }}</span>
            </template>
            <el-menu-item :index="'/' +childitem.path" v-for="childitem in item.children" :key="childitem.id" @click="saveNavActive('/' +childitem.path)">
              <template  slot="title" >
                <i class="el-icon-menu"></i>
                <span>{{ childitem.authname }}</span>
              </template>
            </el-menu-item>
          </el-submenu>
        </el-menu>
      </el-aside>
      <el-main>
        <router-view></router-view>
      </el-main>
    </el-container>
  </el-container>
</template>

<script>
export default {
  data () {
    return {
      menulist: [],
      iconObj: [
        'iconfont icon-lunkuodasan-',
        'iconfont icon-tijikongjian',
        'iconfont icon-shangpingouwudai2',
        'iconfont icon-danju-tianchong',
        'iconfont icon-baobiao'
      ],
      isCollapse: false,
      activePath: ''
    }
  },
  created () {
    this.getMenus()
    this.activePath = window.sessionStorage.getItem('activePath')
  },
  methods: {
    logout () {
      window.sessionStorage.clear()
      this.$router.push('/login')
    },
    getMenus () {
      console.log(window.sessionStorage.getItem('token'))
      this.$http.get('http://localhost/vue/vue_shop/getMenus.php', {
         params: {
            Authorization: window.sessionStorage.getItem('token')
         }
      })
      .then(response => {
        const { data: res } = response
        console.log(res)
        if (res.meta.status !== 200) {
          return this.$message.error(res.meta.message)
        }
        res.data.sort((item1, item2) => {
          return parseInt(item1.order) - parseInt(item2.order)
        })
        this.menulist = res.data
      })
    },
    toggleCollapse () {
      this.isCollapse = !this.isCollapse
    },
    saveNavActive (activePath) {
      window.sessionStorage.setItem('activePath', activePath)
      this.activePath = activePath
    }
  }
}
</script>

<style lang="css" scope="this api replaced by slot-scope in 2.5.0+">
.home_container {
  height: 100%;
}
.el-header {
  background-color: #373d41;
  display: flex;
  justify-content: space-between;
  align-items: center;
  color: #fff;
  font-size: 25px;
  padding-left: 0;
}
.el-header img {
  height: 60px;
  width: 80px;
}
.header_left {
  display: flex;
  align-items: center;

}
.el-aside {
  background-color: #333744;
}
.el-aside .el-menu{
  border-right: 0;
}
.el-main {
  background-color: #eaedf1;
}
.iconfont{
  margin-right: 20px;
}
.toggle_button{
  background-color: #4a5064;
  line-height: 24px;
  color: #fff;
  font-size: 10px;
  text-align: center;
  letter-spacing: 0.2em;
  cursor: pointer;
}
</style>
