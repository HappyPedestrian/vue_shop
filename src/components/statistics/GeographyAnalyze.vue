<template>
  <div>
    <el-card>
      <!-- 面包屑导航区域 -->
      <el-breadcrumb separator-class="el-icon-arrow-right">
        <el-breadcrumb-item :to="{ path: '/home' }">首页</el-breadcrumb-item>
        <el-breadcrumb-item>数据统计</el-breadcrumb-item>
        <el-breadcrumb-item>地理销售情况分析</el-breadcrumb-item>
      </el-breadcrumb>
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
      <div id="main" style="width: 100%;height:700px;"></div>
    </el-card>
  </div>
</template>

<script>
import geoMap from './cityGeographyInfo.js'
import echarts from 'echarts'
import chinaJson from './china.json'
import cityData from '../order/cityData.js'
echarts.registerMap('china', chinaJson)

var dn = [
  {
    杭州: 131,
    苏州: 51,
    上海: 114,
    天津: 58,
    深圳: 104,
    郑州: 66,
    成都: 35,
    宁波: 59,
    合肥: 28,
    重庆: 68,
    广州: 120,
    大连: 24,
    青岛: 58,
    北京: 118,
    义乌: 36,
    东莞: 46,
    长沙: 34,
    贵阳: 8,
    珠海: 11,
    威海: 7,
    南昌: 24,
    西安: 35,
    南京: 42,
    海口: 6,
    厦门: 59,
    沈阳: 18,
    无锡: 21,
    呼和浩特: 7,
    长春: 13,
    哈尔滨: 16,
    武汉: 52,
    南宁: 14,
    昆明: 10,
    兰州: 5,
    唐山: 3,
    石家庄: 24,
    太原: 13,
    赤峰: 0,
    抚顺: 0,
    珲春: 1,
    绥芬河: 3,
    徐州: 5,
    南通: 12,
    温州: 32,
    绍兴: 11,
    芜湖: 3,
    福州: 72,
    泉州: 47,
    赣州: 3,
    济南: 40,
    烟台: 14,
    洛阳: 7,
    黄石: 1,
    岳阳: 1,
    汕头: 8,
    佛山: 31,
    泸州: 0,
    海东: 0,
    银川: 37
  },
  {
    杭州: 10,
    苏州: 2,
    上海: 21,
    天津: 4,
    深圳: 7,
    郑州: 7,
    成都: 5,
    宁波: 2,
    合肥: 1,
    重庆: 3,
    广州: 19,
    大连: 1,
    青岛: 2,
    北京: 16,
    义乌: 2,
    东莞: 1,
    长沙: 3,
    贵阳: 0,
    珠海: 0,
    威海: 0,
    南昌: 1,
    西安: 2,
    南京: 6,
    海口: 0,
    厦门: 3,
    沈阳: 3,
    无锡: 0,
    呼和浩特: 0,
    长春: 0,
    哈尔滨: 1,
    武汉: 5,
    南宁: 1,
    昆明: 1,
    兰州: 0,
    唐山: 0,
    石家庄: 2,
    太原: 1,
    赤峰: 0,
    抚顺: 0,
    珲春: 0,
    绥芬河: 0,
    徐州: 0,
    南通: 1,
    温州: 2,
    绍兴: 0,
    芜湖: 0,
    福州: 5,
    泉州: 2,
    赣州: 2,
    济南: 3,
    烟台: 0,
    洛阳: 1,
    黄石: 0,
    岳阳: 0,
    汕头: 0,
    佛山: 0,
    泸州: 0,
    海东: 0,
    银川: 0
  },
  {
    杭州: 311,
    苏州: 174,
    上海: 308,
    天津: 192,
    深圳: 304,
    郑州: 194,
    成都: 179,
    宁波: 191,
    合肥: 130,
    重庆: 189,
    广州: 345,
    大连: 139,
    青岛: 182,
    北京: 336,
    义乌: 136,
    东莞: 159,
    长沙: 151,
    贵阳: 81,
    珠海: 96,
    威海: 80,
    南昌: 112,
    西安: 163,
    南京: 155,
    海口: 59,
    厦门: 170,
    沈阳: 102,
    无锡: 110,
    呼和浩特: 54,
    长春: 76,
    哈尔滨: 113,
    武汉: 187,
    南宁: 104,
    昆明: 100,
    兰州: 48,
    唐山: 48,
    石家庄: 110,
    太原: 80,
    赤峰: 8,
    抚顺: 7,
    珲春: 19,
    绥芬河: 16,
    徐州: 63,
    南通: 78,
    温州: 111,
    绍兴: 88,
    芜湖: 29,
    福州: 189,
    泉州: 148,
    赣州: 31,
    济南: 161,
    烟台: 85,
    洛阳: 49,
    黄石: 10,
    岳阳: 15,
    汕头: 74,
    佛山: 153,
    泸州: 10,
    海东: 0,
    银川: 34
  },
  {
    杭州: 296,
    苏州: 184,
    上海: 332,
    天津: 136,
    深圳: 327,
    郑州: 208,
    成都: 235,
    宁波: 200,
    合肥: 142,
    重庆: 191,
    广州: 327,
    大连: 154,
    青岛: 168,
    北京: 358,
    义乌: 133,
    东莞: 166,
    长沙: 159,
    贵阳: 81,
    珠海: 86,
    威海: 58,
    南昌: 118,
    西安: 180,
    南京: 170,
    海口: 78,
    厦门: 160,
    沈阳: 114,
    无锡: 119,
    呼和浩特: 80,
    长春: 92,
    哈尔滨: 123,
    武汉: 190,
    南宁: 122,
    昆明: 128,
    兰州: 69,
    唐山: 60,
    石家庄: 118,
    太原: 93,
    赤峰: 16,
    抚顺: 9,
    珲春: 21,
    绥芬河: 16,
    徐州: 78,
    南通: 93,
    温州: 122,
    绍兴: 95,
    芜湖: 36,
    福州: 187,
    泉州: 148,
    赣州: 47,
    济南: 161,
    烟台: 87,
    洛阳: 55,
    黄石: 11,
    岳阳: 26,
    汕头: 78,
    佛山: 150,
    泸州: 10,
    海东: 0,
    银川: 45
  },
  {
    杭州: 334,
    苏州: 185,
    上海: 313,
    天津: 181,
    深圳: 379,
    郑州: 231,
    成都: 215,
    宁波: 183,
    合肥: 145,
    重庆: 205,
    广州: 344,
    大连: 166,
    青岛: 170,
    北京: 351,
    义乌: 150,
    东莞: 176,
    长沙: 174,
    贵阳: 89,
    珠海: 91,
    威海: 61,
    南昌: 135,
    西安: 181,
    南京: 183,
    海口: 80,
    厦门: 167,
    沈阳: 130,
    无锡: 121,
    呼和浩特: 89,
    长春: 122,
    哈尔滨: 139,
    武汉: 219,
    南宁: 138,
    昆明: 125,
    兰州: 71,
    唐山: 71,
    石家庄: 136,
    太原: 127,
    赤峰: 47,
    抚顺: 9,
    珲春: 30,
    绥芬河: 21,
    徐州: 88,
    南通: 90,
    温州: 138,
    绍兴: 92,
    芜湖: 26,
    福州: 283,
    泉州: 158,
    赣州: 30,
    济南: 171,
    烟台: 81,
    洛阳: 86,
    黄石: 15,
    岳阳: 41,
    汕头: 96,
    佛山: 165,
    泸州: 49,
    海东: 0,
    银川: 70
  },
  {
    杭州: 365,
    苏州: 213,
    上海: 352,
    天津: 187,
    深圳: 430,
    郑州: 251,
    成都: 226,
    宁波: 196,
    合肥: 165,
    重庆: 234,
    广州: 364,
    大连: 151,
    青岛: 193,
    北京: 358,
    义乌: 162,
    东莞: 197,
    长沙: 212,
    贵阳: 94,
    珠海: 108,
    威海: 70,
    南昌: 167,
    西安: 188,
    南京: 203,
    海口: 102,
    厦门: 187,
    沈阳: 148,
    无锡: 133,
    呼和浩特: 88,
    长春: 121,
    哈尔滨: 143,
    武汉: 224,
    南宁: 153,
    昆明: 144,
    兰州: 77,
    唐山: 98,
    石家庄: 150,
    太原: 147,
    赤峰: 16,
    抚顺: 16,
    珲春: 31,
    绥芬河: 18,
    徐州: 98,
    南通: 106,
    温州: 153,
    绍兴: 112,
    芜湖: 36,
    福州: 196,
    泉州: 178,
    赣州: 71,
    济南: 165,
    烟台: 88,
    洛阳: 78,
    黄石: 14,
    岳阳: 39,
    汕头: 115,
    佛山: 185,
    泸州: 12,
    海东: 1,
    银川: 49
  },
  {
    杭州: 352,
    苏州: 204,
    上海: 331,
    天津: 168,
    深圳: 421,
    郑州: 271,
    成都: 231,
    宁波: 199,
    合肥: 172,
    重庆: 141,
    广州: 365,
    大连: 132,
    青岛: 205,
    北京: 239,
    义乌: 147,
    东莞: 193,
    长沙: 213,
    贵阳: 105,
    珠海: 99,
    威海: 76,
    南昌: 163,
    西安: 184,
    南京: 193,
    海口: 109,
    厦门: 170,
    沈阳: 147,
    无锡: 138,
    呼和浩特: 81,
    长春: 126,
    哈尔滨: 141,
    武汉: 241,
    南宁: 154,
    昆明: 145,
    兰州: 89,
    唐山: 103,
    石家庄: 146,
    太原: 137,
    赤峰: 33,
    抚顺: 12,
    珲春: 22,
    绥芬河: 23,
    徐州: 101,
    南通: 100,
    温州: 134,
    绍兴: 102,
    芜湖: 52,
    福州: 190,
    泉州: 156,
    赣州: 80,
    济南: 161,
    烟台: 81,
    洛阳: 100,
    黄石: 24,
    岳阳: 48,
    汕头: 118,
    佛山: 164,
    泸州: 14,
    海东: 0,
    银川: 61
  }
]

export default {
  data() {
    return {
      text: '请先选择具体商品!(下图为样例数据显示情况)',
      cateList: [],
      cascaderProps: {
        label: 'cate_name',
        value: 'cate_id',
        children: 'children',
        expandTrigger: 'hover'
      },
      selectedIds: [],
      colors: [
        [
          '#1DE9B6',
          '#F46E36',
          '#04B9FF',
          '#5DBD32',
          '#FFC809',
          '#FB95D5',
          '#BDA29A',
          '#6E7074',
          '#546570',
          '#C4CCD3'
        ],
        [
          '#37A2DA',
          '#67E0E3',
          '#32C5E9',
          '#9FE6B8',
          '#FFDB5C',
          '#FF9F7F',
          '#FB7293',
          '#E062AE',
          '#E690D1',
          '#E7BCF3',
          '#9D96F5',
          '#8378EA',
          '#8378EA'
        ],
        [
          '#DD6B66',
          '#759AA0',
          '#E69D87',
          '#8DC1A9',
          '#EA7E53',
          '#EEDD78',
          '#73A373',
          '#73B9BC',
          '#7289AB',
          '#91CA8C',
          '#F49F42'
        ]
      ],
      colorIndex: 0,
      provincesData: [
        [
          {
            year: '2013',
            name: '重庆',
            value: 68
          },
          {
            year: '2013',
            name: '宁夏',
            value: 37
          },
          {
            year: '2013',
            name: '湖北',
            value: 52
          },
          {
            year: '2013',
            name: '河南',
            value: 66
          },
          {
            year: '2013',
            name: '广东',
            value: 270
          },
          {
            year: '2013',
            name: '福建',
            value: 178
          },
          {
            year: '2013',
            name: '浙江',
            value: 226
          },
          {
            year: '2013',
            name: '上海',
            value: 114
          },
          {
            year: '2013',
            name: '江苏',
            value: 93
          },
          {
            year: '2013',
            name: '山东',
            value: 98
          },
          {
            year: '2013',
            name: '天津',
            value: 58
          },
          {
            year: '2013',
            name: '北京',
            value: 118
          }
        ],
        [
          {
            year: '2014',
            name: '重庆',
            value: 3
          },
          {
            year: '2014',
            name: '湖北',
            value: 5
          },
          {
            year: '2014',
            name: '河南',
            value: 7
          },
          {
            year: '2014',
            name: '江西',
            value: 2
          },
          {
            year: '2014',
            name: '广东',
            value: 26
          },
          {
            year: '2014',
            name: '福建',
            value: 10
          },
          {
            year: '2014',
            name: '浙江',
            value: 10
          },
          {
            year: '2014',
            name: '上海',
            value: 21
          },
          {
            year: '2014',
            name: '江苏',
            value: 6
          },
          {
            year: '2014',
            name: '山东',
            value: 3
          },
          {
            year: '2014',
            name: '天津',
            value: 4
          },
          {
            year: '2014',
            name: '北京',
            value: 16
          },
          {
            year: '2014',
            name: '四川',
            value: 5
          },
          {
            year: '2014',
            name: '辽宁',
            value: 3
          },
          {
            year: '2014',
            name: '陕西',
            value: 2
          }
        ],
        [
          {
            year: '2015',
            name: '重庆',
            value: 189
          },
          {
            year: '2015',
            name: '湖北',
            value: 187
          },
          {
            year: '2015',
            name: '河南',
            value: 194
          },
          {
            year: '2015',
            name: '广东',
            value: 621
          },
          {
            year: '2015',
            name: '福建',
            value: 359
          },
          {
            year: '2015',
            name: '浙江',
            value: 501
          },
          {
            year: '2015',
            name: '上海',
            value: 308
          },
          {
            year: '2015',
            name: '江苏',
            value: 329
          },
          {
            year: '2015',
            name: '山东',
            value: 161
          },
          {
            year: '2015',
            name: '天津',
            value: 192
          },
          {
            year: '2015',
            name: '北京',
            value: 336
          },
          {
            year: '2015',
            name: '四川',
            value: 179
          },
          {
            year: '2015',
            name: '陕西',
            value: 163
          }
        ],
        [
          {
            year: '2016',
            name: '重庆',
            value: 191
          },
          {
            year: '2016',
            name: '河南',
            value: 208
          },
          {
            year: '2016',
            name: '广东',
            value: 820
          },
          {
            year: '2016',
            name: '福建',
            value: 347
          },
          {
            year: '2016',
            name: '浙江',
            value: 496
          },
          {
            year: '2016',
            name: '上海',
            value: 332
          },
          {
            year: '2016',
            name: '江苏',
            value: 354
          },
          {
            year: '2016',
            name: '山东',
            value: 329
          },
          {
            year: '2016',
            name: '北京',
            value: 358
          },
          {
            year: '2016',
            name: '四川',
            value: 235
          },
          {
            year: '2016',
            name: '陕西',
            value: 180
          },
          {
            year: '2016',
            name: '湖南',
            value: 159
          },
          {
            year: '2016',
            name: '湖南',
            value: 154
          }
        ],
        [
          {
            year: '2017',
            name: '重庆',
            value: 205
          },
          {
            year: '2017',
            name: '四川',
            value: 215
          },
          {
            year: '2017',
            name: '湖南',
            value: 174
          },
          {
            year: '2017',
            name: '河南',
            value: 231
          },
          {
            year: '2017',
            name: '广东',
            value: 899
          },
          {
            year: '2017',
            name: '福建',
            value: 450
          },
          {
            year: '2017',
            name: '浙江',
            value: 517
          },
          {
            year: '2017',
            name: '上海',
            value: 313
          },
          {
            year: '2017',
            name: '江苏',
            value: 368
          },
          {
            year: '2017',
            name: '山东',
            value: 341
          },
          {
            year: '2017',
            name: '北京',
            value: 354
          },
          {
            year: '2017',
            name: '陕西',
            value: 181
          },
          {
            year: '2017',
            name: '湖北',
            value: 219
          },
          {
            year: '2017',
            name: '天津',
            value: 181
          }
        ],
        [
          {
            year: '2018',
            name: '重庆',
            value: 234
          },
          {
            year: '2018',
            name: '四川',
            value: 226
          },
          {
            year: '2018',
            name: '湖南',
            value: 212
          },
          {
            year: '2018',
            name: '河南',
            value: 251
          },
          {
            year: '2018',
            name: '广东',
            value: 1176
          },
          {
            year: '2018',
            name: '福建',
            value: 383
          },
          {
            year: '2018',
            name: '浙江',
            value: 561
          },
          {
            year: '2018',
            name: '上海',
            value: 352
          },
          {
            year: '2018',
            name: '江苏',
            value: 416
          },
          {
            year: '2018',
            name: '山东',
            value: 193
          },
          {
            year: '2018',
            name: '北京',
            value: 358
          },
          {
            year: '2018',
            name: '陕西',
            value: 188
          },
          {
            year: '2018',
            name: '天津',
            value: 187
          }
        ],
        [
          {
            year: '2019',
            name: '四川',
            value: 231
          },
          {
            year: '2019',
            name: '湖南',
            value: 213
          },
          {
            year: '2019',
            name: '河南',
            value: 271
          },
          {
            year: '2019',
            name: '广东',
            value: 1143
          },
          {
            year: '2019',
            name: '福建',
            value: 260
          },
          {
            year: '2019',
            name: '浙江',
            value: 551
          },
          {
            year: '2019',
            name: '上海',
            value: 331
          },
          {
            year: '2019',
            name: '江苏',
            value: 397
          },
          {
            year: '2019',
            name: '安徽',
            value: 172
          },
          {
            year: '2019',
            name: '河北',
            value: 241
          },
          {
            year: '2019',
            name: '山东',
            value: 205
          },
          {
            year: '2019',
            name: '北京',
            value: 239
          },
          {
            year: '2019',
            name: '陕西',
            value: 184
          },
          {
            year: '2019',
            name: '天津',
            value: 168
          }
        ]
      ],
      provinces: cityData.cityData, // 省份信息
      mapData: [],
      barData: [],
      categoryData: [],
      geoCoordMap: geoMap.geoMap,
      optionXyMap01: {
        timeline: {
          data: ['2013', '2014', '2015', '2016', '2017', '2018', '2019'],
          axisType: 'category',
          autoPlay: true,
          playInterval: 3000,
          left: '10%',
          right: '10%',
          bottom: '3%',
          width: '80%',
          label: {
            normal: {
              textStyle: {
                color: '#ddd'
              }
            },
            emphasis: {
              textStyle: {
                color: '#fff'
              }
            }
          },
          symbolSize: 10,
          lineStyle: {
            color: '#555'
          },
          checkpointStyle: {
            borderColor: '#777',
            borderWidth: 2
          },
          controlStyle: {
            showNextBtn: true,
            showPrevBtn: true,
            normal: {
              color: '#666',
              borderColor: '#666'
            },
            emphasis: {
              color: '#aaa',
              borderColor: '#aaa'
            }
          }
        },
        baseOption: {
          animation: true,
          animationDuration: 1000,
          animationEasing: 'cubicInOut',
          animationDurationUpdate: 1000,
          animationEasingUpdate: 'cubicInOut',
          grid: {
            right: '1%',
            top: '15%',
            bottom: '10%',
            width: '20%'
          },
          tooltip: {
            trigger: 'item', // hover触发器
            formatter: function(params) {
              if (params.value instanceof Array) {
                return (
                  params.name + '</br>销量：' + params.value[params.value.length - 1]
                )
              }
              return params.name + '</br>销量：' + params.value
            },
            axisPointer: {
              // 坐标轴指示器，坐标轴触发有效
              type: 'shadow', // 默认为直线，可选为：'line' | 'shadow'
              shadowStyle: {
                color: 'rgba(150,150,150,0.1)' // hover颜色
              }
            },
            seriesIndex: 0
          },
          geo: {
            show: true,
            map: 'china',
            roam: true,
            zoom: 1,
            center: [113.83531246, 34.0267395887],
            label: {
              emphasis: {
                show: false
              }
            },
            itemStyle: {
              normal: {
                borderColor: 'rgba(147, 235, 248, 1)',
                borderWidth: 1,
                areaColor: {
                  type: 'radial',
                  x: 0.5,
                  y: 0.5,
                  r: 0.8,
                  colorStops: [
                    {
                      offset: 0,
                      color: 'rgba(147, 235, 248, 0)' // 0% 处的颜色
                    },
                    {
                      offset: 1,
                      color: 'rgba(147, 235, 248, .2)' // 100% 处的颜色
                    }
                  ],
                  globalCoord: false // 缺省为 false
                },
                shadowColor: 'rgba(128, 217, 248, 1)',
                // shadowColor: 'rgba(255, 255, 255, 1)',
                shadowOffsetX: -2,
                shadowOffsetY: 2,
                shadowBlur: 10
              },
              emphasis: {
                areaColor: '#389BB7',
                borderWidth: 0
              }
            }
          }
        },
        options: []
      }
    }
  },
  created() {
    this.addToMapData(dn)
    this.addDataToOptions()
    this.getCateList()
  },
  mounted() {
    console.log(this.optionXyMap01)
    var myChart = echarts.init(document.getElementById('main'))
    myChart.setOption(this.optionXyMap01)
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
      this.text = '与该商品相关地理销售情况如下：'
      if (this.selectedIds.length === 4) {
        this.getGeographyStatistics()
      }
    },
    async getGeographyStatistics() {
      const { data: res } = await this.$http.get(
        'http://localhost/vue/vue_shop/statistics/getGeographySet.php',
        {
          params: { goods_id: this.selectedIds[this.selectedIds.length - 1] }
        }
      )
      console.log(res)
      if (res.meta.status !== 200) {
        return this.$message.error(res.meta.message)
      }
      res.data.sort((item1, item2) => {
        return item1.create_time - item2.create_time
      })
      this.optionXyMap01.timeline.data = []
      this.mapData = []
      this.barData = []
      this.categoryData = []
      this.optionXyMap01.options = []
      this.provincesData = []
      this.text = '该商品各地区销售分布情况如下图'
      console.log(res.data)
      this.dealData(res.data)
      this.addDataToOptions()
      var myChart = echarts.init(document.getElementById('main'))
      myChart.setOption(this.optionXyMap01)
    },
    dealData(data) {
      let provinceResetArr = []
      this.provinces.forEach(el => {
        let name = el.text
        if (
          el.text.length > 2 &&
          (el.text.charAt(el.text.length - 1) === '市' ||
            el.text.charAt(el.text.length - 1) === '省')
        ) {
          name = el.text.slice(0, el.text.length - 1)
        }
        let provinceData = {
          name: name,
          value: 0
        }
        provinceResetArr.push(provinceData)
      })
      let provinceResetArrStr = JSON.stringify(provinceResetArr)
      let eachYearInfoArr = []
      data.forEach(el => {
        let time = new Date(el.create_time * 1000)
        let addressStr = el.consignee_addr
        let address1 = addressStr.split(' ')[0]
        let cityStr = address1.split('/')[1]
        let province = address1.split('/')[0]
        if (cityStr.charAt(cityStr.length - 1) === '市' && cityStr.length > 2) {
          cityStr = cityStr.slice(0, cityStr.length - 1)
        }
        if (
          province.length > 2 &&
          (province.charAt(province.length - 1) === '市' ||
            province.charAt(province.length - 1) === '省')
        ) {
          province = province.slice(0, province.length - 1)
        }
        let yearStr = time.getFullYear()
        const exsitIndex = this.optionXyMap01.timeline.data.findIndex(
          (currenValue, index) => {
            return currenValue === yearStr
          },
          yearStr
        )
        let provinceData = {
          year: yearStr,
          name: province,
          value: el.order_quantity
        }

        if (exsitIndex === -1) {
          let index = this.optionXyMap01.timeline.data.push(yearStr) - 1
          this.provincesData.push(JSON.parse(provinceResetArrStr))
          eachYearInfoArr[index] = {}
          if (eachYearInfoArr[index][cityStr]) {
            eachYearInfoArr[index][cityStr] += el.order_quantity
          } else {
            eachYearInfoArr[index][cityStr] = el.order_quantity
          }
          this.addToProvinceData(provinceData, index)
        } else {
          if (eachYearInfoArr[exsitIndex][cityStr]) {
            eachYearInfoArr[exsitIndex][cityStr] += el.order_quantity
          } else {
            eachYearInfoArr[exsitIndex][cityStr] = el.order_quantity
          }
          this.addToProvinceData(provinceData, exsitIndex)
        }
      })
      console.log(this.provincesData)
      this.addToMapData(eachYearInfoArr)
    },
    addToProvinceData(provinceData, i) {
      for (let len = 0; len < this.provincesData[i].length; len++) {
        if (this.provincesData[i][len].name === provinceData.name) {
          this.provincesData[i][len].year = provinceData.year
          this.provincesData[i][len].value += provinceData.value
          break
        }
      }
    },
    addToMapData(eachYearInfoArr) {
      for (let len = 0; len < eachYearInfoArr.length; len++) {
        this.mapData[len] = []
      }
      console.log(eachYearInfoArr)
      for (var key in this.geoCoordMap) {
        eachYearInfoArr.forEach((el, i) => {
          if (el.hasOwnProperty(key)) {
            this.mapData[i].push({
              year: this.optionXyMap01.timeline.data[i],
              name: key,
              value: el[key]
            })
          }
        })
      }
      console.log(this.mapData)
      for (let i = 0; i < this.mapData.length; i++) {
        this.mapData[i].sort(function sortNumber(a, b) {
          return a.value - b.value
        })
        this.barData.push([])
        this.categoryData.push([])
        for (let j = 0; j < this.mapData[i].length; j++) {
          this.barData[i].push(this.mapData[i][j].value)
          this.categoryData[i].push(this.mapData[i][j].name)
        }
      }
    },
    addDataToOptions() {
      for (var n = 0; n < this.optionXyMap01.timeline.data.length; n++) {
        let max = 1
        for (let i = 0; i < this.provincesData[n].length; i++) {
          if (this.provincesData[n][i].value > max) max = this.provincesData[n][i].value
        }
        this.optionXyMap01.options.push({
          backgroundColor: '#013954',
          title: [
            {
              text: '商品各年度的区域售卖情况',
              left: '25%',
              top: '7%',
              textStyle: {
                color: '#fff',
                fontSize: 25
              }
            },
            {
              id: 'statistic',
              text: this.optionXyMap01.timeline.data[n] + '年数据统计情况',
              left: '75%',
              top: '8%',
              textStyle: {
                color: '#fff',
                fontSize: 25
              }
            }
          ],
          xAxis: {
            type: 'value',
            scale: true,
            position: 'top',
            min: 0,
            boundaryGap: false,
            splitLine: {
              show: false
            },
            axisLine: {
              show: false
            },
            axisTick: {
              show: false
            },
            axisLabel: {
              margin: 2,
              textStyle: {
                color: '#aaa'
              }
            }
          },
          yAxis: {
            type: 'category',
            //  name: 'TOP 20',
            nameGap: 16,
            axisLine: {
              show: true,
              lineStyle: {
                color: '#ddd'
              }
            },
            axisTick: {
              show: false,
              lineStyle: {
                color: '#ddd'
              }
            },
            axisLabel: {
              interval: 0,
              textStyle: {
                color: '#ddd'
              }
            },
            data: this.categoryData[n].slice(-30)
          },
          visualMap: {
            min: 0,
            max: max,
            text: ['High', 'Low'],
            realtime: false,
            calculable: true,
            seriesIndex: 0,
            inRange: {
              color: ['#023954', 'yellow', 'orangered']
            },
            textStyle: {
              color: '#fff'
            }
          },
          series: [
            // 地图
            {
              type: 'map',
              map: 'china',
              geoIndex: 0,
              aspectScale: 0.75, // 长宽比
              showLegendSymbol: false, // 存在legend时显示
              label: {
                normal: {
                  show: false
                },
                emphasis: {
                  show: false,
                  textStyle: {
                    color: '#fff'
                  }
                }
              },
              roam: true,
              itemStyle: {
                normal: {
                  areaColor: '#031525',
                  borderColor: '#FFFFFF'
                },
                emphasis: {
                  areaColor: '#2B91B7'
                }
              },
              animation: false,
              data: this.mapData.concat(this.provincesData[n])
            },
            // 地图中闪烁的点
            {
              name: 'Top 20',
              type: 'effectScatter',
              coordinateSystem: 'geo',
              data: this.convertData(
                this.mapData[n]
                  .sort(function(a, b) {
                    return b.value - a.value
                  })
                  .slice(0, 20)
              ),
              symbolSize: function(val) {
                return val[2] / 10
              },
              showEffectOn: 'render',
              rippleEffect: {
                brushType: 'stroke'
              },
              hoverAnimation: true,
              label: {
                normal: {
                  formatter: '{b}',
                  position: 'right',
                  show: true
                }
              },
              itemStyle: {
                normal: {
                  color: this.colors[this.colorIndex][n],
                  shadowBlur: 10,
                  shadowColor: this.colors[this.colorIndex][n]
                }
              },
              zlevel: 1
            },
            // 柱状图
            {
              zlevel: 1.5,
              type: 'bar',
              symbol: 'none',
              itemStyle: {
                normal: {
                  color: this.colors[this.colorIndex][n]
                }
              },
              data: this.barData[n].slice(-30)
            }
            // 省份地图
            // {
            //   name: '香港18区人口密度',
            //   type: 'map',
            //   mapType: 'china', // 自定义扩展图表类型
            //   label: {
            //     show: true
            //   },
            //   data: [
            //     { name: '西藏', value: 20057.34 }
            //   ]
            // }
          ]
        })
      }
    },
    convertData(data) {
      var res = []
      for (var i = 0; i < data.length; i++) {
        var geoCoord = this.geoCoordMap[data[i].name]
        if (geoCoord) {
          res.push({
            name: data[i].name,
            value: geoCoord.concat(data[i].value)
          })
        }
      }
      return res
    },
    showAllGeography() {
      let geoStr =
        '安徽省 合肥 北纬31.52 东经117.17<br>安徽省 安庆 北纬30.31 东经117.02<br>安徽省 蚌埠 北纬32.56 东经117.21<br>安徽省 亳州 北纬33.52 东经115.47<br>安徽省 巢湖 北纬31.36 东经117.52<br>安徽省 滁州 北纬32.18 东经118.18<br>安徽省 阜阳 北纬32.54 东经115.48<br>安徽省 贵池 北纬30.39 东经117.28<br>安徽省 淮北 北纬33.57 东经116.47<br>安徽省 淮南 北纬32.37 东经116.58<br>安徽省 黄山 北纬29.43 东经118.18<br>安徽省 界首 北纬33.15 东经115.21<br>安徽省 六安 北纬31.44 东经116.28<br>安徽省 马鞍山 北纬31.43 东经118.28<br>安徽省 明光 北纬32.47 东经117.58<br>安徽省 宿州 北纬33.38 东经116.58<br>安徽省 天长 北纬32.41 东经118.59<br>安徽省 铜陵 北纬30.56 东经117.48<br>安徽省 芜湖 北纬31.19 东经118.22<br>安徽省 宣州 北纬30.57 东经118.44<br>澳门 澳门市 北纬21.33 东经115.07<br>北京市 北京市 北纬39.55 东经116.24<br>福建省 福州 北纬26.05 东经119.18<br>福建省 长乐 北纬25.58 东经119.31<br>福建省 福安 北纬27.06 东经119.39<br>福建省 福清 北纬25.42 东经119.23<br>福建省 建瓯 北纬27.03 东经118.20<br>福建省 建阳 北纬27.21 东经118.07<br>福建省 晋江 北纬24.49 东经118.35<br>福建省 龙海 北纬24.26 东经117.48<br>福建省 龙岩 北纬25.06 东经117.01<br>福建省 南安 北纬24.57 东经118.23<br>福建省 南平 北纬26.38 东经118.10<br>福建省 宁德 北纬26.39 东经119.31<br>福建省 莆田 北纬24.26 东经119.01<br>福建省 泉州 北纬24.56 东经118.36<br>福建省 三明 北纬26.13 东经117.36<br>福建省 邵武 北纬27.20 东经117.29<br>福建省 石狮 北纬24.44 东经118.38<br>福建省 武夷山 北纬27.46 东经118.02<br>福建省 厦门 北纬24.27 东经118.06<br>福建省 永安 北纬25.58 东经117.23<br>福建省 漳平 北纬25.17 东经117.24<br>福建省 漳州 北纬24.31 东经117.39<br>甘肃省 兰州 北纬36.04 东经103.51<br>甘肃省 白银 北纬36.33 东经104.12<br>甘肃省 敦煌 北纬40.08 东经94.41<br>甘肃省 嘉峪关 北纬39.48 东经98.14<br>甘肃省 金昌 北纬38.28 东经102.10<br>甘肃省 酒泉 北纬39.44 东经98.31<br>甘肃省 临夏 北纬35.37 东经103.12<br>甘肃省 平凉 北纬35.32 东经106.40<br>甘肃省 天水 北纬34.37 东经105.42<br>甘肃省 武威 北纬37.56 东经102.39<br>甘肃省 西峰 北纬35.45 东经107.40<br>甘肃省 玉门 北纬39.49 东经97.35<br>甘肃省 张掖 北纬38.56 东经100.26<br>广东省 广州 北纬23.08 东经113.14<br>广东省 潮阳 北纬23.16 东经116.36<br>广东省 潮州 北纬23.40 东经116.38<br>广东省 澄海 北纬23.28 东经116.46<br>广东省 从化 北纬23.33 东经113.33<br>广东省 东莞 北纬23.02 东经113.45<br>广东省 恩平 北纬22.12 东经112.19<br>广东省 佛山 北纬23.02 东经113.06<br>广东省 高明 北纬22.53 东经112.50<br>广东省 高要 北纬23.02 东经112.26<br>广东省 高州 北纬21.54 东经110.50<br>广东省 鹤山 北纬22.46 东经112.57<br>广东省 河源 北纬23.43 东经114.41<br>广东省 花都 北纬23.23 东经113.12<br>广东省 化州 北纬21.39 东经110.37<br>广东省 惠阳 北纬22.48 东经114.28<br>广东省 惠州 北纬23.05 东经114.22<br>广东省 江门 北纬22.35 东经113.04<br>广东省 揭阳 北纬22.32 东经116.21<br>广东省 开平 北纬22.22 东经112.40<br>广东省 乐昌 北纬25.09 东经113.21<br>广东省 雷州 北纬20.54 东经110.04<br>广东省 廉江 北纬21.37 东经110.17<br>广东省 连州 北纬24.48 东经112.23<br>广东省 罗定 北纬22.46 东经111.33<br>广东省 茂名 北纬21.40 东经110.53<br>广东省 梅州 北纬24.19 东经116.07<br>广东省 南海 北纬23.01 东经113.09<br>广东省 番禺 北纬22.57 东经113.22<br>广东省 普宁 北纬23.18 东经116.10<br>广东省 清远 北纬23.42 东经113.01<br>广东省 三水 北纬23.10 东经112.52<br>广东省 汕头 北纬23.22 东经116.41<br>广东省 汕尾 北纬22.47 东经115.21<br>广东省 韶关 北纬24.48 东经113.37<br>广东省 深圳 北纬22.33 东经114.07<br>广东省 顺德 北纬22.50 东经113.15<br>广东省 四会 北纬23.21 东经112.41<br>广东省 台山 北纬22.15 东经112.48<br>广东省 吴川 北纬21.26 东经110.47<br>广东省 新会 北纬22.32 东经113.01<br>广东省 兴宁 北纬24.09 东经115.43<br>广东省 阳春 北纬22.10 东经111.48<br>广东省 阳江 北纬21.50 东经111.58<br>广东省 英德 北纬24.10 东经113.22<br>广东省 云浮 北纬22.57 东经112.02<br>广东省 增城 北纬23.18 东经113.49<br>广东省 湛江 北纬21.11 东经110.24<br>广东省 肇庆 北纬23.03 东经112.27<br>广东省 中山 北纬22.31 东经113.22<br>广东省 珠海 北纬22.17 东经113.34<br>广西自治区 南宁 北纬22.48 东经108.19<br>广西自治区 北海 北纬21.28 东经109.07<br>广西自治区 北流 北纬22.42 东经110.21<br>广西自治区 百色 北纬23.54 东经106.36<br>广西自治区 防城港 北纬21.37 东经108.20<br>广西自治区 贵港 北纬23.06 东经109.36<br>广西自治区 桂林 北纬25.17 东经110.17<br>广西自治区 桂平 北纬23.22 东经110.04<br>广西自治区 河池 北纬24.42 东经108.03<br>广西自治区 合山 北纬23.47 东经108.52<br>广西自治区 柳州 北纬23.19 东经109.24<br>广西自治区 赁祥 北纬22.07 东经106.44<br>广西自治区 钦州 北纬21.57 东经108.37<br>广西自治区 梧州 北纬23.29 东经111.20<br>广西自治区 玉林 北纬22.38 东经110.09<br>广西自治区 宜州 北纬24.28 东经108.40<br>贵州省 贵阳 北纬26.35 东经106.42<br>贵州省 安顺 北纬26.14 东经105.55<br>贵州省 毕节 北纬27.18 东经105.18<br>贵州省 赤水 北纬28.34 东经105.42<br>贵州省 都匀 北纬26.15 东经107.31<br>贵州省 凯里 北纬26.35 东经107.58<br>贵州省 六盘水 北纬26.35 东经104.50<br>贵州省 清镇 北纬26.33 东经106.27<br>贵州省 铜仁 北纬27.43 东经109.12<br>贵州省 兴义 北纬25.05 东经104.53<br>贵州省 遵义 北纬27.42 东经106.55<br>海南省 海口 北纬20.02 东经110.20<br>海南省 儋州 北纬19.31 东经109.34<br>海南省 琼海 北纬19.14 东经110.28<br>海南省 琼山 北纬19.59 东经110.21<br>海南省 三亚 北纬18.14 东经109.31<br>海南省 通什 北纬18.46 东经109.31<br>河北省 石家庄 北纬38.02 东经114.30<br>河北省 安国 北纬38.24 东经115.20<br>河北省 保定 北纬38.51 东经115.30<br>河北省 霸州 北纬39.06 东经116.24<br>河北省 泊头 北纬38.04 东经116.34<br>河北省 沧州 北纬38.18 东经116.52<br>河北省 承德 北纬40.59 东经117.57<br>河北省 定州 北纬38.30 东经115.00<br>河北省 丰南 北纬39.34 东经118.06<br>河北省 高碑店 北纬39.20 东经115.51<br>河北省 蒿城 北纬38.02 东经114.50<br>河北省 邯郸 北纬36.36 东经114.28<br>河北省 河间 北纬38.26 东经116.05<br>河北省 衡水 北纬37.44 东经115.42<br>河北省 黄骅 北纬38.21 东经117.21<br>河北省 晋州 北纬38.02 东经115.02<br>河北省 冀州 北纬37.34 东经115.33<br>河北省 廓坊 北纬39.31 东经116.42<br>河北省 鹿泉 北纬38.04 东经114.19<br>河北省 南宫 北纬37.22 东经115.23<br>河北省 秦皇岛 北纬39.55 东经119.35<br>河北省 任丘 北纬38.42 东经116.07<br>河北省 三河 北纬39.58 东经117.04<br>河北省 沙河 北纬36.51 东经114.30<br>河北省 深州 北纬38.01 东经115.32<br>河北省 唐山 北纬39.36 东经118.11<br>河北省 武安 北纬36.42 东经114.11<br>河北省 邢台 北纬37.04 东经114.30<br>河北省 辛集 北纬37.54 东经115.12<br>河北省 新乐 北纬38.20 东经114.41<br>河北省 张家口 北纬40.48 东经114.53<br>河北省 涿州 北纬39.29 东经115.59<br>河北省 遵化 北纬40.11 东经117.58<br>河南省 郑州 北纬34.46 东经11340<br>河南省 安阳 北纬36.06 东经114.21<br>河南省 长葛 北纬34.12 东经113.47<br>河南省 登封 北纬34.27 东经113.02<br>河南省 邓州 北纬32.42 东经112.05<br>河南省 巩义 北纬34.46 东经112.58<br>河南省 鹤壁 北纬35.54 东经114.11<br>河南省 辉县 北纬35.27 东经113.47<br>河南省 焦作 北纬35.14 东经113.12<br>河南省 济源 北纬35.04 东经112.35<br>河南省 开封 北纬34.47 东经114.21<br>河南省 灵宝 北纬34.31 东经110.52<br>河南省 林州 北纬36.03 东经113.49<br>河南省 漯河 北纬33.33 东经114.02<br>河南省 洛阳 北纬34.41 东经112.27<br>河南省 南阳 北纬33.00 东经112.32<br>河南省 平顶山 北纬33.44 东经113.17<br>河南省 濮阳 北纬35.44 东经115.01<br>河南省 沁阳 北纬35.05 东经112.57<br>河南省 汝州 北纬34.09 东经112.50<br>河南省 三门峡 北纬34.47 东经111.12<br>河南省 商丘 北纬34.26 东经115.38<br>河南省 卫辉 北纬35.24 东经114.03<br>河南省 舞钢 北纬33.17 东经113.30<br>河南省 项城 北纬33.26 东经114.54<br>河南省 荥阳 北纬34.46 东经113.21<br>河南省 新密 北纬34.31 东经113.22<br>河南省 新乡 北纬35.18 东经113.52<br>河南省 信阳 北纬32.07 东经114.04<br>河南省 新郑 北纬34.24 东经113.43<br>河南省 许昌 北纬34.01 东经113.49<br>河南省 偃师 北纬34.43 东经112.47<br>河南省 义马 北纬34.43 东经111.55<br>河南省 禹州 北纬34.09 东经113.28<br>河南省 周口 北纬33.37 东经114.38<br>河南省 驻马店 北纬32.58 东经114.01<br>黑龙江省 哈尔滨 北纬45.44 东经126.36<br>黑龙江省 阿城 北纬45.32 东经126.58<br>黑龙江省 安达 北纬46.24 东经125.18<br>黑龙江省 北安 北纬48.15 东经126.31<br>黑龙江省 大庆 北纬46.36 东经125.01<br>黑龙江省 富锦 北纬47.15 东经132.02<br>黑龙江省 海林 北纬44.35 东经129.21<br>黑龙江省 海伦 北纬47.28 东经126.57<br>黑龙江省 鹤岗 北纬47.20 东经130.16<br>黑龙江省 黑河 北纬50.14 东经127.29<br>黑龙江省 佳木斯 北纬46.47 东经130.22<br>黑龙江省 鸡西 北纬45.17 东经130.57<br>黑龙江省 密山 北纬45.32 东经131.50<br>黑龙江省 牡丹江 北纬44.35 东经129.36<br>黑龙江省 讷河 北纬48.29 东经124.51<br>黑龙江省 宁安 北纬44.21 东经129.28<br>黑龙江省 齐齐哈尔 北纬47.20 东经123.57<br>黑龙江省 七台河 北纬45.48 东经130.49<br>黑龙江省 双城 北纬45.22 东经126.15<br>黑龙江省 尚志 北纬45.14 东经127.55<br>黑龙江省 双鸭山 北纬46.38 东经131.11<br>黑龙江省 绥芬河 北纬44.25 东经131.11<br>黑龙江省 绥化 北纬46.38 东经126.59<br>黑龙江省 铁力 北纬46.59 东经128.01<br>黑龙江省 同江 北纬47.39 东经132.30<br>黑龙江省 五常 北纬44.55 东经127.11<br>黑龙江省 五大连池 北纬48.38 东经126.07<br>黑龙江省 伊春 北纬47.42 东经128.56<br>黑龙江省 肇东 北纬46.04 东经125.58<br>湖北省 武汉 北纬30.35 东经114.17<br>湖北省 安陆 北纬31.15 东经113.41<br>湖北省 当阳 北纬30.50 东经111.47<br>湖北省 丹江口 北纬32.33 东经108.30<br>湖北省 大冶 北纬30.06 东经114.58<br>湖北省 恩施 北纬30.16 东经109.29<br>湖北省 鄂州 北纬30.23 东经114.52<br>湖北省 广水 北纬31.37 东经113.48<br>湖北省 洪湖 北纬29.48 东经113.27<br>湖北省 黄石 北纬30.12 东经115.06<br>湖北省 黄州 北纬30.27 东经114.52<br>湖北省 荆门 北纬31.02 东经112.12<br>湖北省 荆沙 北纬30.18 东经112.16<br>湖北省 老河口 北纬32.23 东经111.40<br>湖北省 利川 北纬30.18 东经108.56<br>湖北省 麻城 北纬31.10 东经115.01<br>湖北省 浦圻 北纬29.42 东经113.51<br>湖北省 潜江 北纬30.26 东经112.53<br>湖北省 石首 北纬29.43 东经112.24<br>湖北省 十堰 北纬32.40 东经110.47<br>湖北省 随州 北纬31.42 东经113.22<br>湖北省 天门 北纬60.39 东经113.10<br>湖北省 武穴 北纬29.51 东经115.33<br>湖北省 襄樊 北纬32.02 东经112.08<br>湖北省 咸宁 北纬29.53 东经114.17<br>湖北省 仙桃 北纬30.22 东经113.27<br>湖北省 孝感 北纬30.56 东经113.54<br>湖北省 宜昌 北纬30.42 东经111.17<br>湖北省 宜城 北纬31.42 东经112.15<br>湖北省 应城 北纬30.57 东经113.33<br>湖北省 枣阳 北纬32.07 东经112.44<br>湖北省 枝城 北纬30.23 东经111.27<br>湖北省 钟祥 北纬31.10 东经112.34<br>湖南省 长沙 北纬28.12 东经112.59<br>湖南省 常德 北纬29.02 东经111.51<br>湖南省 郴州 北纬25.46 东经113.02<br>湖南省 衡阳 北纬26.53 东经112.37<br>湖南省 洪江 北纬27.07 东经109.59<br>湖南省 怀化 北纬27.33 东经109.58<br>湖南省 津市 北纬29.38 东经111.52<br>湖南省 吉首 北纬28.18 东经109.43<br>湖南省 耒阳 北纬26.24 东经112.51<br>湖南省 冷水江 北纬27.42 东经111.26<br>湖南省 冷水滩 北纬26.26 东经111.35<br>湖南省 涟源 北纬27.41 东经111.41<br>湖南省 醴陵 北纬27.40 东经113.30<br>湖南省 临湘 北纬29.29 东经113.27<br>湖南省 浏阳 北纬28.09 东经113.37<br>湖南省 娄底 北纬27.44 东经111.59<br>湖南省 汨罗 北纬28.49 东经113.03<br>湖南省 韶山 北纬27.54 东经112.29<br>湖南省 邵阳 北纬27.14 东经111.28<br>湖南省 武冈 北纬26.43 东经110.37<br>湖南省 湘潭 北纬27.52 东经112.53<br>湖南省 湘乡 北纬27.44 东经112.31<br>湖南省 益阳 北纬28.36 东经112.20<br>湖南省 永州 北纬26.13 东经111.37<br>湖南省 沅江 北纬28.50 东经112.22<br>湖南省 岳阳 北纬29.22 东经113.06<br>湖南省 张家界 北纬29.08 东经110.29<br>湖南省 株洲 北纬27.51 东经113.09<br>湖南省 资兴 北纬25.58 东经113.13<br>吉林省 长春 北纬43.54 东经125.19<br>吉林省 白城 北纬45.38 东经122.50<br>吉林省 白山 北纬41.56 东经126.26<br>吉林省 大安 北纬45.30 东经124.18<br>吉林省 德惠 北纬44.32 东经125.42<br>吉林省 敦化 北纬43.22 东经128.13<br>吉林省 公主岭 北纬43.31 东经124.49<br>吉林省 和龙 北纬42.32 东经129.00<br>吉林省 桦甸 北纬42.58 东经126.44<br>吉林省 珲春 北纬42.52 东经130.22<br>吉林省 集安 北纬41.08 东经126.11<br>吉林省 蛟河 北纬43.42 东经127.21<br>吉林省 吉林 北纬43.52 东经126.33<br>吉林省 九台 北纬44.09 东经125.51<br>吉林省 辽源 北纬42.54 东经125.09<br>吉林省 临江 北纬41.49 东经126.53<br>吉林省 龙井 北纬42.46 东经129.26<br>吉林省 梅河口 北纬42.32 东经125.40<br>吉林省 舒兰 北纬44.24 东经126.57<br>吉林省 四平 北纬43.10 东经124.22<br>吉林省 松原 北纬45.11 东经124.49<br>吉林省 洮南 北纬45.20 东经122.47<br>吉林省 通化 北纬41.43 东经125.56<br>吉林省 图们 北纬42.57 东经129.51<br>吉林省 延吉 北纬42.54 东经129.30<br>吉林省 愉树 北纬44.49 东经126.32<br>江苏省 南京 北纬32.03 东经118.46<br>江苏省 常熟 北纬31.39 东经120.43<br>江苏省 常州 北纬31.47 东经119.58<br>江苏省 丹阳 北纬32.00 东经119.32<br>江苏省 东台 北纬32.51 东经120.19<br>江苏省 高邮 北纬32.47 东经119.27<br>江苏省 海门 北纬31.53 东经121.09<br>江苏省 淮安 北纬33.30 东经119.09<br>江苏省 淮阴 北纬33.36 东经119.02<br>江苏省 江都 北纬32.26 东经119.32<br>江苏省 姜堰 北纬32.34 东经120.08<br>江苏省 江阴 北纬31.54 东经120.17<br>江苏省 靖江 北纬32.02 东经120.17<br>江苏省 金坛 北纬31.46 东经119.33<br>江苏省 昆山 北纬31.23 东经120.57<br>江苏省 连去港 北纬34.36 东经119.10<br>江苏省 溧阳 北纬31.26 东经119.29<br>江苏省 南通 北纬32.01 东经120.51<br>江苏省 邳州 北纬34.19 东经117.59<br>江苏省 启乐 北纬31.48 东经121.39<br>江苏省 如皋 北纬32.23 东经120.33<br>江苏省 宿迁 北纬33.58 东经118.18<br>江苏省 苏州 北纬31.19 东经120.37<br>江苏省 太仓 北纬31.27 东经121.06<br>江苏省 泰兴 北纬32.10 东经120.01<br>江苏省 泰州 北纬32.30 东经119.54<br>江苏省 通州 北纬32.05 东经121.03<br>江苏省 吴江 北纬31.10 东经120.39<br>江苏省 无锡 北纬31.34 东经120.18<br>江苏省 兴化 北纬32.56 东经119.50<br>江苏省 新沂 北纬34.22 东经118.20<br>江苏省 徐州 北纬34.15 东经117.11<br>江苏省 盐在 北纬33.22 东经120.08<br>江苏省 扬中 北纬32.14 东经119.49<br>江苏省 扬州 北纬32.23 东经119.26<br>江苏省 宜兴 北纬31.21 东经119.49<br>江苏省 仪征 北纬32.16 东经119.10<br>江苏省 张家港 北纬31.52 东经120.32<br>江苏省 镇江 北纬32.11 东经119.27<br>江西省 南昌 北纬28.40 东经115.55<br>江西省 德兴 北纬28.57 东经117.35<br>江西省 丰城 北纬28.12 东经115.48<br>江西省 赣州 北纬28.52 东经114.56<br>江西省 高安 北纬28.25 东经115.22<br>江西省 吉安 北纬27.07 东经114.58<br>江西省 景德镇 北纬29.17 东经117.13<br>江西省 井冈山 北纬26.34 东经114.10<br>江西省 九江 北纬29.43 东经115.58<br>江西省 乐平 北纬28.58 东经117.08<br>江西省 临川 北纬27.59 东经116.21<br>江西省 萍乡 北纬27.37 东经113.50<br>江西省 瑞昌 北纬29.40 东经115.38<br>江西省 瑞金 北纬25.53 东经116.01<br>江西省 上饶 北纬25.27 东经117.58<br>江西省 新余 北纬27.48 东经114.56<br>江西省 宜春 北纬27.47 东经114.23<br>江西省 鹰潭 北纬28.14 东经117.03<br>江西省 樟树 北纬28.03 东经115.32<br>辽宁省 沈阳 北纬41.48 东经123.25<br>辽宁省 鞍山 北纬41.07 东经123.00<br>辽宁省 北票 北纬41.48 东经120.47<br>辽宁省 本溪 北纬41.18 东经123.46<br>辽宁省 朝阳 北纬41.34 东经120.27<br>辽宁省 大连 北纬38.55 东经121.36<br>辽宁省 丹东 北纬40.08 东经124.22<br>辽宁省 大石桥 北纬40.37 东经122.31<br>辽宁省 东港 北纬39.53 东经124.08<br>辽宁省 凤城 北纬40.28 东经124.02<br>辽宁省 抚顺 北纬41.51 东经123.54<br>辽宁省 阜新 北纬42.01 东经121.39<br>辽宁省 盖州 北纬40.24 东经122.21<br>辽宁省 海城 北纬40.51 东经122.43<br>辽宁省 葫芦岛 北纬40.45 东经120.51<br>辽宁省 锦州 北纬41.07 东经121.09<br>辽宁省 开原 北纬42.32 东经124.02<br>辽宁省 辽阳 北纬41.16 东经123.12<br>辽宁省 凌海 北纬41.10 东经121.21<br>辽宁省 凌源 北纬41.14 东经119.22<br>辽宁省 盘锦 北纬41.07 东经122.03<br>辽宁省 普兰店 北纬39.23 东经121.58<br>辽宁省 铁法 北纬42.28 东经123.32<br>辽宁省 铁岭 北纬42.18 东经123.51<br>辽宁省 瓦房店 北纬39.37 东经122.00<br>辽宁省 兴城 北纬40.37 东经120.41<br>辽宁省 新民 北纬41.59 东经122.49<br>辽宁省 营口 北纬40.39 东经122.13<br>辽宁省 庄河 北纬39.41 东经122.58<br>内自治区 呼和浩特 北纬40.48 东经111.41<br>内自治区 包头 北纬40.39 东经109.49<br>内自治区 赤峰 北纬42.17 东经118.58<br>内自治区 东胜 北纬39.48 东经109.59<br>内自治区 二连浩特 北纬43.38 东经111.58<br>内自治区 额尔古纳 北纬50.13 东经120.11<br>内自治区 丰镇 北纬40.27 东经113.09<br>内自治区 根河 北纬50.48 东经121.29<br>内自治区 海拉尔 北纬49.12 东经119.39<br>内自治区 霍林郭勒 北纬45.32 东经119.38<br>内自治区 集宁 北纬41.02 东经113.06<br>内自治区 临河 北纬40.46 东经107.22<br>内自治区 满洲里 北纬49.35 东经117.23<br>内自治区 通辽 北纬43.37 东经122.16<br>内自治区 乌兰浩特 北纬46.03 东经122.03<br>内自治区 乌海 北纬39.40 东经106.48<br>内自治区 锡林浩特 北纬43.57 东经116.03<br>内自治区 牙克石 北纬49.17 东经120.40<br>内自治区 扎兰屯 北纬48.00 东经122.47<br>宁夏自治区 银川 北纬38.27 东经106.16<br>宁夏自治区 青铜峡 北纬37.56 东经105.59<br>宁夏自治区 石嘴山 北纬39.02 东经106.22<br>宁夏自治区 吴忠 北纬37.59 东经106.11<br>青海省 西宁 北纬36.38 东经101.48<br>青海省 德令哈 北纬37.22 东经97.23<br>青海省 格尔木 北纬36.26 东经94.55<br>山东省 济南 北纬36.40 东经117.00<br>山东省 安丘 北纬36.25 东经119.12<br>山东省 滨州 北纬37.22 东经118.02<br>山东省 昌邑 北纬39.52 东经119.24<br>山东省 德州 北纬37.26 东经116.17<br>山东省 东营 北纬37.27 东经118.30<br>山东省 肥城 北纬36.14 东经116.46<br>山东省 高密 北纬36.22 东经119.44<br>山东省 菏泽 北纬35.14 东经115.26<br>山东省 胶南 北纬35.53 东经119.58<br>山东省 胶州 北纬36.17 东经120.00<br>山东省 即墨 北纬36.22 东经120.28<br>山东省 济宁 北纬35.23 东经116.33<br>山东省 莱芜 北纬36.12 东经117.40<br>山东省 莱西 北纬36.52 东经120.31<br>山东省 莱阳 北纬36.58 东经120.42<br>山东省 莱州 北纬37.10 东经119.57<br>山东省 乐陵 北纬37.44 东经117.12<br>山东省 聊城 北纬36.26 东经115.57<br>山东省 临清 北纬36.51 东经115.42<br>山东省 临沂 北纬35.03 东经118.20<br>山东省 龙口 北纬37.39 东经120.21<br>山东省 蓬莱 北纬37.48 东经120.45<br>山东省 平度 北纬36.47 东经119.58<br>山东省 青岛 北纬36.03 东经120.18<br>山东省 青州 北纬36.42 东经118.28<br>山东省 曲阜 北纬35.36 东经116.58<br>山东省 日照 北纬35.23 东经119.32<br>山东省 荣成 北纬37.10 东经122.25<br>山东省 乳山 北纬36.54 东经121.31<br>山东省 寿光 北纬36.53 东经118.44<br>山东省 泰安 北纬36.11 东经117.08<br>山东省 滕州 北纬35.06 东经117.09<br>山东省 潍坊 北纬36.43 东经119.06<br>山东省 威海 北纬37.31 东经122.07<br>山东省 文登 北纬37.12 东经122.03<br>山东省 新泰 北纬35.54 东经117.45<br>山东省 烟台 北纬37.32 东经121.24<br>山东省 兖州 北纬35.32 东经116.49<br>山东省 禹城 北纬36.56 东经116.39<br>山东省 枣庄 北纬34.52 东经117.33<br>山东省 章丘 北纬36.43 东经117.32<br>山东省 招远 北纬37.21 东经120.23<br>山东省 诸城 北纬35.59 东经119.24<br>山东省 淄博 北纬36.48 东经118.03<br>山东省 邹城 北纬35.24 东经116.58<br>山西省 太原 北纬37.54 东经112.33<br>山西省 长治 北纬36.11 东经113.06<br>山西省 大同 北纬40.06 东经113.17<br>山西省 高平 北纬35.48 东经112.55<br>山西省 古交 北纬37.54 东经112.09<br>山西省 河津 北纬35.35 东经110.41<br>山西省 侯马 北纬35.37 东经111.21<br>山西省 霍州 北纬36.34 东经111.42<br>山西省 介休 北纬37.02 东经111.55<br>山西省 晋城 北纬35.30 东经112.51<br>山西省 临汾 北纬36.05 东经111.31<br>山西省 潞城 北纬36.21 东经113.14<br>山西省 朔州 北纬39.19 东经112.26<br>山西省 孝义 北纬37.08 东经111.48<br>山西省 忻州 北纬38.24 东经112.43<br>山西省 阳泉 北纬37.51 东经113.34<br>山西省 永济 北纬34.52 东经110.27<br>山西省 原平 北纬38.43 东经112.42<br>山西省 榆次 北纬37.41 东经112.43<br>山西省 运城 北纬35.02 东经110.59<br>陕西省 西安 北纬34.17 东经108.57<br>陕西省 安康 北纬32.41 东经109.01<br>陕西省 宝鸡 北纬34.22 东经107.09<br>陕西省 韩城 北纬35.28 东经110.27<br>陕西省 汉中 北纬33.04 东经107.01<br>陕西省 华阴 北纬34.34 东经110.05<br>陕西省 商州 北纬33.52 东经109.57<br>陕西省 铜川 北纬35.06 东经109.07<br>陕西省 渭南 北纬34.30 东经109.30<br>陕西省 咸阳 北纬34.20 东经108.43<br>陕西省 兴平 北纬34.18 东经108.29<br>陕西省 延安 北纬36.35 东经109.28<br>陕西省 榆林 北纬38.18 东经109.47<br>上海市 上海市 北纬31.14 东经121.29<br>四川省 成都 北纬30.40 东经104.04<br>四川省 巴中 北纬31.51 东经106.43<br>四川省 崇州 北纬30.39 东经103.40<br>四川省 达川 北纬31.14 东经107.29<br>四川省 德阳 北纬31.09 东经104.22<br>四川省 都江堰 北纬31.01 东经103.37<br>四川省 峨眉山 北纬29.36 东经103.29<br>四川省 涪陵 北纬29.42 东经107.22<br>四川省 广汉 北纬30.58 东经104.15<br>四川省 广元 北纬32.28 东经105.51<br>四川省 华蓥 北纬30.26 东经106.44<br>四川省 简阳 北纬30.24 东经104.32<br>四川省 江油 北纬31.48 东经104.42<br>四川省 阆中 北纬31.36 东经105.58<br>四川省 乐山 北纬29.36 东经103.44<br>四川省 泸州 北纬28.54 东经105.24<br>四川省 绵阳 北纬31.30 东经104.42<br>四川省 南充 北纬30.49 东经106.04<br>四川省 内江 北纬29.36 东经105.02<br>四川省 攀枝花 北纬26.34 东经101.43<br>四川省 彭州 北纬30.59 东经103.57<br>四川省 邛崃 北纬30.26 东经103.28<br>四川省 遂宁 北纬30.31 东经105.33<br>四川省 万县 北纬30.50 东经108.21<br>四川省 万源 北纬32.03 东经108.03<br>四川省 西昌 北纬27.54 东经102.16<br>四川省 雅安 北纬29.59 东经102.59<br>四川省 宜宾 北纬28.47 东经104.34<br>四川省 自贡 北纬29.23 东经104.46<br>四川省 资阳 北纬30.09 东经104.38<br>台湾省 台北市 北纬25.03 东经121.30<br>天津市 天津市 北纬39.02 东经117.12<br>西藏自治区 拉萨 北纬29.39 东经91.08<br>西藏自治区 日喀则 北纬29.16 东经88.51<br>香港 香港市 北纬21.23 东经115.12<br>新疆自治区 乌鲁木齐 北纬43.45 东经87.36<br>新疆自治区 阿克苏 北纬41.09 东经80.19<br>新疆自治区 阿勒泰 北纬47.50 东经88.12<br>新疆自治区 阿图什 北纬39.42 东经76.08<br>新疆自治区 博乐 北纬44.57 东经82.08<br>新疆自治区 昌吉 北纬44.02 东经87.18<br>新疆自治区 阜康 北纬44.09 东经87.58<br>新疆自治区 哈密 北纬42.50 东经93.28<br>新疆自治区 和田 北纬37.09 东经79.55<br>新疆自治区 克拉玛依 北纬45.36 东经84.51<br>新疆自治区 喀什 北纬39.30 东经75.59<br>新疆自治区 库尔勒 北纬41.46 东经86.07<br>新疆自治区 奎屯 北纬44.27 东经84.56<br>新疆自治区 石河子 北纬44.18 东经86.00<br>新疆自治区 塔城 北纬46.46 东经82.59<br>新疆自治区 吐鲁番 北纬42.54 东经89.11<br>新疆自治区 伊宁 北纬43.55 东经81.20<br>云南省 昆明 北纬25.04 东经102.42<br>云南省 保山 北纬25.08 东经99.10<br>云南省 楚雄 北纬25.01 东经101.32<br>云南省 大理 北纬25.34 东经100.13<br>云南省 东川 北纬26.06 东经103.12<br>云南省 个旧 北纬23.21 东经103.09<br>云南省 景洪 北纬22.01 东经100.48<br>云南省 开远 北纬23.43 东经103.13<br>云南省 曲靖 北纬25.30 东经103.48<br>云南省 瑞丽 北纬24.00 东经97.50<br>云南省 思茅 北纬22.48 东经100.58<br>云南省 畹町 北纬24.06 东经98.04<br>云南省 宣威 北纬26.13 东经104.06<br>云南省 玉溪 北纬24.22 东经102.32<br>云南省 昭通 北纬27.20 东经103.42<br>浙江省 杭州 北纬30.16 东经120.10<br>浙江省 慈溪 北纬30.11 东经121.15<br>浙江省 东阳 北纬29.16 东经120.14<br>浙江省 奉化 北纬29.39 东经121.24<br>浙江省 富阳 北纬30.03 东经119.57<br>浙江省 海宁 北纬30.32 东经120.42<br>浙江省 湖州 北纬30.52 东经120.06<br>浙江省 建德 北纬29.29 东经119.16<br>浙江省 江山 北纬28.45 东经118.37<br>浙江省 嘉兴 北纬30.46 东经120.45<br>浙江省 金华 北纬29.07 东经119.39<br>浙江省 兰溪 北纬29.12 东经119.28<br>浙江省 临海 北纬28.51 东经121.08<br>浙江省 丽水 北纬28.27 东经119.54<br>浙江省 龙泉 北纬28.04 东经119.08<br>浙江省 宁波 北纬29.52 东经121.33<br>浙江省 平湖 北纬30.42 东经121.01<br>浙江省 衢州 北纬28.58 东经118.52<br>浙江省 瑞安 北纬27.48 东经120.38<br>浙江省 上虞 北纬30.01 东经120.52<br>浙江省 绍兴 北纬30.00 东经120.34<br>浙江省 台州 北纬28.41 东经121.27<br>浙江省 桐乡 北纬30.38 东经120.32<br>浙江省 温岭 北纬28.22 东经121.21<br>浙江省 温州 北纬28.01 东经120.39<br>浙江省 萧山 北纬30.09 东经120.16<br>浙江省 义乌 北纬29.18 东经120.04<br>浙江省 乐清 北纬28.08 东经120.58<br>浙江省 余杭 北纬30.26 东经120.18<br>浙江省 余姚 北纬30.02 东经121.10<br>浙江省 永康 北纬29.54 东经120.01<br>浙江省 舟山 北纬30.01 东经122.06<br>浙江省 诸暨 北纬29.43 东经120.14<br>重庆市 重庆市 北纬29.35 东经106.33<br>重庆市 合川市 北纬30.02 东经106.15<br>重庆市 江津市 北纬29.18 东经106.16<br>重庆市 南川市 北纬29.10 东经107.05<br>重庆市 永川市 北纬29.23 东经105.53'
      let geoStrArr = geoStr.split('<br>')
      console.log(geoStrArr)
      let geoInfoObjArr = {}
      geoStrArr.forEach(el => {
        let infoStrArr = el.split(' ')
        let infoArr = [
          Number(infoStrArr[3].slice(2)),
          Number(infoStrArr[2].slice(2))
        ]
        // let infoObj = {
        // }
        // infoObj.name = infoStrArr[1]
        // infoObj.info = infoArr
        geoInfoObjArr[infoStrArr[1]] = infoArr
      })
      console.log(JSON.stringify(geoInfoObjArr))
    }
  }
}
</script>

<style lang="css" scoped>
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
