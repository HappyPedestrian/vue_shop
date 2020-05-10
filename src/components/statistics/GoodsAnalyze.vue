<template>
  <div>
    <!-- 面包屑导航区域 -->
    <el-breadcrumb separator-class="el-icon-arrow-right">
      <el-breadcrumb-item :to="{ path: '/home' }">首页</el-breadcrumb-item>
      <el-breadcrumb-item>数据统计</el-breadcrumb-item>
      <el-breadcrumb-item>商品推荐分析</el-breadcrumb-item>
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
      <div id="messageDiv">
        <p id="message">{{ text }}</p>
      </div>
      <!-- 图表容器区 -->
      <div id="main" style="width: 100%;height:900px;"></div>
    </el-card>
  </div>
</template>

<script>
import echarts from 'echarts'
export default {
  data() {
    return {
      cateList: [],
      selectedIds: [],
      cascaderProps: {
        label: 'cate_name',
        value: 'cate_id',
        children: 'children',
        expandTrigger: 'hover'
      },
      option: {
        backgroundColor: '#2c343c',

        title: {
          text: '商品关联分析',
          left: 'center',
          top: 20,
          textStyle: {
            color: '#ccc'
          }
        },

        tooltip: {
          trigger: 'item',
          formatter: '{a} <br/>{b} : {c} ({d}%)'
        },

        visualMap: {
          show: false,
          min: 1,
          max: 5,
          inRange: {
            colorLightness: [0.4, 1]
          }
        },
        series: [
          {
            name: '用户占比',
            type: 'pie',
            radius: '55%',
            center: ['50%', '50%'],
            data: [
              { value: 10, name: '测试样例1' },
              { value: 56, name: '测试样例2' },
              { value: 89, name: '测试样例3' },
              { value: 47, name: '测试样例4' },
              { value: 100, name: '测试样例5' }
            ].sort(function(a, b) {
              return a.value - b.value
            }),
            roseType: 'radius',
            label: {
              color: 'rgba(255, 255, 255, 0.5)'
            },
            labelLine: {
              lineStyle: {
                color: 'rgba(255, 255, 255, 0.5)'
              },
              smooth: 0.2,
              length: 10,
              length2: 20
            },
            itemStyle: {
              color: '#c23531',
              shadowBlur: 200,
              shadowColor: 'rgba(0, 0, 0, 0.5)'
            },

            animationType: 'scale',
            animationEasing: 'elasticOut',
            animationDelay: function(idx) {
              return Math.random() * 200
            }
          }
        ]
      },
      text: '请先选择商品!(下图为样例数据显示情况)'
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
      this.text = '与该商品相关的商品情况如下：'
      this.getFrequentStatistics()
    },
    async getFrequentStatistics() {
      const { data: res } = await this.$http.get(
        'http://localhost/vue/vue_shop/statistics/getfrequentSet.php',
        {
          params: { goods_id: this.selectedIds[this.selectedIds.length - 1] }
        }
      )
      console.log(res)
      if (res.meta.status !== 200) {
        return this.$message.error(res.meta.message)
      }
      let min = 1
      let max = 2
      res.data.forEach(el => {
        if (el.value < min) {
          min = el.value
        } else if (el.value > max) {
          max = el.value
        }
      })
      this.option.visualMap.min = min
      this.option.visualMap.max = 2 * max
      res.data.sort((a, b) => {
        return b.value - a.value
      })

      this.option.series[0].data = res.data.slice(0, 10)
      if (res.data.length > 10) {
        let left = res.data.slice(10) // 截取前十的数据
        let leftObj = {
          name: '其他',
          value: 0
        }
        left.forEach(el => {
          leftObj.value += el.value
        })
        this.option.series[0].data.push(leftObj)
      }
      this.option.series[0].data.sort(function(a, b) {
        return a.value - b.value
      })
      var myChart = echarts.init(document.getElementById('main'))
      myChart.setOption(this.option)
    }
  }
}
</script>

<style lang="css" scoped>
.el-row {
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
.el-cascader {
  margin: 10px;
  width: 600px;
}
</style>
