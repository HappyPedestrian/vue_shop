<template>
  <div>
    <!-- 面包屑导航区域 -->
    <el-breadcrumb separator-class="el-icon-arrow-right">
      <el-breadcrumb-item :to="{ path: '/home' }">首页</el-breadcrumb-item>
      <el-breadcrumb-item>数据统计</el-breadcrumb-item>
      <el-breadcrumb-item>商品销售时间统计</el-breadcrumb-item>
    </el-breadcrumb>

    <!-- 卡片区域 -->
    <el-card>
      <!-- 警告区 -->
      <el-alert
        title="注意，只允许选择具体的商品！"
        type="warning"
        :closable="false"
        show-icon
      ></el-alert>

      <!-- 选择商品分类区域 -->
      <el-row>
        <el-col>
          <span>选择商品</span>
          <!-- 选择商品分类的级联选择框 -->
          <el-cascader
            :options="cateList"
            :props="cascaderProps"
            v-model="selectedIds"
            @change="cateChanged"
            clearable
            ref="cascaderRef"
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
let data = [
  ['2012/1/20', 1],
  ['2013/3/5', 3],
  ['2014/6/15', 7],
  ['2015/1/20', 45],
  ['2016/4/8', 14],
  ['2017/11/28', 56],
  ['2018/7/12', 89],
  ['2019/5/10', 200],
  ['2020/2/9', 400]
]
export default {
  data() {
    return {
      cateList: [],
      cascaderProps: {
        label: 'cate_name',
        value: 'cate_id',
        children: 'children',
        expandTrigger: 'hover'
      },
      selectedIds: [],
      text: '请先选择相应的商品分类！(下图为样例数据显示情况)',
      option: {
        title: {
          text: '商品各时间销售情况'
        },
        tooltip: {
          trigger: 'axis'
        },
        xAxis: {
          data: data.map(function(item) {
            return item[0]
          }),
          show: true,
          name: '商品销售时间'
        },
        yAxis: {
          splitLine: {
            show: false
          },
          show: true,
          name: '商品销量'
        },
        toolbox: {
          left: 'center',
          feature: {
            dataZoom: {
              yAxisIndex: 'none'
            },
            restore: {},
            saveAsImage: {}
          }
        },
        dataZoom: [
          {
            startValue: '2014/06/01'
          },
          {
            type: 'inside'
          }
        ],
        visualMap: {
          top: 0,
          right: 0,
          pieces: [
            {
              gt: 0,
              lte: 10,
              color: '#096'
            },
            {
              gt: 10,
              lte: 20,
              color: '#ffde33'
            },
            {
              gt: 20,
              lte: 40,
              color: '#ff9933'
            },
            {
              gt: 40,
              lte: 80,
              color: '#cc0033'
            },
            {
              gt: 80,
              lte: 160,
              color: '#660099'
            },
            {
              gt: 160,
              color: '#7e0023'
            }
          ],
          outOfRange: {
            color: '#999'
          }
        },
        series: {
          name: '请先选择商品！',
          type: 'line',
          data: data.map(function(item) {
            return item[1]
          }),
          markLine: {
            silent: true,
            data: [
              {
                yAxis: 10
              },
              {
                yAxis: 20
              },
              {
                yAxis: 40
              },
              {
                yAxis: 80
              },
              {
                yAxis: 160
              }
            ]
          }
        }
      }
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
          params: { type: 3, getGoods: true }
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
      this.text = '该商品的销售情况为：（横轴为时间，纵轴为销量）'
      let titieText = (this.$refs.cascaderRef.getCheckedNodes())[0].data.cate_name
      this.option.title.text = titieText + '各时间销售情况'
      this.option.series.name = titieText + '销量'
      this.getOrderTimeStatistics()
    },
    async getOrderTimeStatistics() {
        let len = this.selectedIds.length
        const { data: res } = await this.$http
        .get('http://localhost/vue/vue_shop/order/getOrderStatistics.php', {
          params: {
            goodsId: this.selectedIds[len - 1],
            getTimeArray: true
        }
        })
          if (res.meta.status !== 200) {
            return this.$message.error(res.meta.message)
          }
          this.manageTimeData(res.data)
            // this.option.xAxis.data = res.data.name
            // this.option.series[0].data = res.data.data
            // var myChart = echarts.init(document.getElementById('main'))
            // myChart.setOption(this.option)
    },
    manageTimeData(data) {
      data.sort((timeArr1, timeArr2) => {
        return timeArr1[0] - timeArr2[0]
      })
      let timeLineArr = []
      let quantityArr = []
      data.forEach(element => {
        let time = new Date(element[0] * 1000)
        const y = time.getFullYear()
        const M = (time.getMonth() + 1 + '').padStart(2, '0')
        const d = (time.getDate() + '').padStart(2, '0')
        const timeStr = y + '/' + M + '/' + d
        const exsitIndex = timeLineArr.findIndex((currenValue, index) => {
            return currenValue === timeStr
        }, timeStr)
        if (exsitIndex === -1) {
          let index = timeLineArr.push(timeStr) - 1
          quantityArr.push(0)
          quantityArr[index] = element[1]
        } else {
          quantityArr[exsitIndex] += element[1]
        }
      })
      this.option.xAxis.data = timeLineArr
      this.option.series.data = quantityArr
      var myChart = echarts.init(document.getElementById('main'))
      myChart.setOption(this.option)
    }
  }
}
</script>

<style lang="css" scoped>
.el-alert {
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
  width: 100%;
  height: 600px;
}
.el-cascader {
  margin: 10px;
  width: 600px;
}
</style>
