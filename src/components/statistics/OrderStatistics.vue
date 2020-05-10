<template>
  <div>
    <!-- 面包屑导航区域 -->
    <el-breadcrumb separator-class="el-icon-arrow-right">
      <el-breadcrumb-item :to="{ path: '/home' }">首页</el-breadcrumb-item>
      <el-breadcrumb-item>数据统计</el-breadcrumb-item>
      <el-breadcrumb-item>商品销量统计</el-breadcrumb-item>
    </el-breadcrumb>

    <!-- 卡片区域 -->
    <el-card>
      <!-- 警告区 -->
      <el-alert
        title="注意，只允许选择第二级以下分类！"
        type="warning"
        :closable="false"
        show-icon
      ></el-alert>

      <!-- 选择商品分类区域 -->
      <el-row class="cat_opt">
        <el-col>
          <span>选择商品分类</span>
          <!-- 选择商品分类的级联选择框 -->
          <el-cascader
            :options="cateList"
            :props="cascaderProps"
            v-model="selectedIds"
            @change="cateChanged"
            clearable
          ></el-cascader>
        </el-col>
      </el-row>
      <div id="messageDiv"><p id="message">{{ text }}</p></div>
      <!-- 图表容器区 -->
      <div id="main"></div>
    </el-card>
  </div>
</template>

<script>
import echarts from 'echarts'

export default {
  data() {
    return {
      cateList: [],
      cascaderProps: {
        label: 'cate_name',
        value: 'cate_id',
        children: 'children',
        checkStrictly: true,
        expandTrigger: 'hover'
      },
      selectedIds: [],
      option: {
        title: {
          text: '商品销量情况',
          left: 'center'
        },
        xAxis: {
          type: 'category',
          data: ['商品一', '商品二', '商品三', '商品四', '商品五', '商品六', '商品七'],
          show: true,
          name: '商品/子分类名'
        },
        tooltip: {},
        yAxis: {
          type: 'value',
          show: true,
          name: '销量'
        },
        series: [
          {
            data: [5, 9, 4, 1, 15, 7, 3],
            type: 'bar',
            showBackground: true,
            backgroundStyle: {
              color: 'rgba(220, 220, 220, 0.8)'
            }
          }
        ]
      },
      text: '请先选择相应的商品分类！(下图为样例数据显示情况)'
    }
  },
  created() {
    this.getCateList()
  },
  mounted() {
      var myChart = echarts.init(document.getElementById('main'))
      myChart.setOption(this.option)
  },
  methods: {
    getCateList() {
      this.$http
        .get('http://localhost/vue/vue_shop/cate/getCateList.php', {
          params: { type: 3 }
        })
        .then(response => {
          const { data: res } = response.data
          if (response.data.meta.status !== 200) {
            return this.$message.error(res.meta.message)
          }
          this.cateList = res.result
        })
    },
    cateChanged() {
      this.text = '该分类下的销售情况为：（横轴为该分类下的商品或子分类，纵轴为销量）'
        console.log(this.selectedIds)
        if (this.selectedIds.length === 1) {
            return this.$message.error('请选择第二或第三级分类！')
        }
        this.getOrderStatistics()
    },
    async getOrderStatistics() {
        let len = this.selectedIds.length
        const { data: res } = await this.$http
        .get('http://localhost/vue/vue_shop/order/getOrderStatistics.php', {
          params: {
            parentCateId: this.selectedIds[len - 1],
            cateLevel: len
        }
        })
        console.log(res)
          if (res.meta.status !== 200) {
            return this.$message.error(res.meta.message)
          }
            this.option.xAxis.data = res.name
            this.option.series[0].data = res.data
            var myChart = echarts.init(document.getElementById('main'))
            myChart.setOption(this.option)
    }
  }
}
</script>

<style lang="css" scoped>
.cat_opt {
  margin: 10px;
}

#messageDiv {
  width: 100%;
  height: 50px;
  background-color: darkgray;
  border-radius: 7px;
}
#message {
  line-height: 100%;
  text-align: center;
  padding-top: 15px;
  color: black;
}
#main {
  margin: 10px;
  width: 70%;
  height: 600px;
}
.el-cascader {
  margin: 10px;
  width: 600px;
}
</style>
