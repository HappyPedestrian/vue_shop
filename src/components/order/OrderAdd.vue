<template>
  <div>
    <!-- 面包屑导航区域 -->
    <el-breadcrumb separator-class="el-icon-arrow-right">
      <el-breadcrumb-item :to="{ path: '/home' }">首页</el-breadcrumb-item>
      <el-breadcrumb-item>订单管理</el-breadcrumb-item>
      <el-breadcrumb-item>添加订单</el-breadcrumb-item>
    </el-breadcrumb>

    <!-- 卡片区域 -->
    <el-card>
      <!-- 步骤条区域 -->
      <el-steps
        :active="activeIndex - 0"
        finish-status="success"
        align-center
      >
        <el-step title="选择顾客"></el-step>
        <el-step title="选择商品"></el-step>
        <el-step title="选择日期"></el-step>
        <el-step title="完成"></el-step>
      </el-steps>
      <el-tabs
        v-model="activeIndex"
        :tab-position="'left'"
        :before-leave="beforeTabLeave"
        @tab-click="tabClicked"
      >
        <el-tab-pane label="选择顾客" name="0">
          <el-row>
            <el-alert
              title="选择订单的买家（可多选）/若多选，则为每个顾客添加订单！"
              :closable="false"
              show-icon
            ></el-alert>
            <el-table
              ref="customersTable"
              :data="customers"
              tooltip-effect="dark"
              style="width: 100%;"
              @selection-change="handleSelectionChange"
              stripe
            >
              <el-table-column type="selection"> </el-table-column>
              <el-table-column prop="user_name" label="用户名">
              </el-table-column>
              <el-table-column prop="mobile" label="电话"> </el-table-column>
              <el-table-column prop="email" label="邮箱"> </el-table-column>
              <el-table-column label="收货地区">
                <template slot-scope="scope">
                  <div>
                    <el-cascader
                      v-model="scope.row.address1"
                      :options="cityData"
                      :props="addressProps"
                      clearable
                    >
                    </el-cascader>
                  </div>
                </template>
              </el-table-column>
              <el-table-column label="详细地址">
                <template slot-scope="scope">
                  <div>
                    <el-input
                    type="text"
                      v-model="scope.row.address2"
                      placeholder="请输入详细地址"
                    ></el-input>
                  </div>
                </template>
              </el-table-column>
            </el-table>
          </el-row>
        </el-tab-pane>
        <el-tab-pane label="选择商品" name="1">
          <el-row>
            <el-alert
              title="选择订单的商品（可多选）/若选择多个用户，则每个顾客都会购买所有商品!"
              :closable="false"
              show-icon
            ></el-alert>
            <span>选择商品</span>
            <el-cascader
              :options="cateList"
              :props="cascaderProps"
              v-model="selectedGoodsId"
              @change="goodsChanged"
              clearable
            ></el-cascader>
            <br />
            <span> 已选择的商品：</span>
            <el-table :data="orderForm.selectedGoods" border stripe>
              <el-table-column label="#" type="index"></el-table-column>
              <el-table-column
                label="商品名称"
                prop="cate_name"
              ></el-table-column>
              <el-table-column
                label="商品介绍"
                prop="goods_state"
              ></el-table-column>
              <el-table-column
                label="商品价格"
                prop="goods_price"
              ></el-table-column>
              <el-table-column label="商品数量">
                <template slot-scope="scope">
                  <div>
                    <el-input
                      type="number"
                      v-model="scope.row.order_quantity"
                      min="1"
                      @blur="checkAndResetQuantity(scope)"
                    ></el-input>
                  </div>
                </template>
              </el-table-column>
              <el-table-column label="删除">
                <template slot-scope="scope">
                  <div>
                    <el-button
                      type="danger"
                      size="mini"
                      icon="el-icon-delete"
                      @click="removeGood(scope.row.cate_id)"
                    ></el-button>
                  </div>
                </template>
              </el-table-column>
            </el-table>
          </el-row>
        </el-tab-pane>
        <el-tab-pane label="选择日期" name="2">
          <el-row>
            <el-alert
              title="选择各个订单的购买时间（可多选）/若选择多个时间，则每个顾客都会在每个时间购买所有商品!"
              :closable="false"
              show-icon
            ></el-alert>
            <span>请选择一个或多个日期：</span>
            <el-date-picker
              type="dates"
              v-model="orderForm.dates"
              placeholder="选择一个或多个日期"
            >
            </el-date-picker>
            <div>
              <el-tag v-for="(item, i) in orderForm.dates" :key="i" closable @close="handleTagClose(i)">{{ item | dateFormat }}</el-tag>
            </div>
          </el-row>
          <el-row>
            <span class="dialog-footer">
              <el-button type="primary" @click="addOrders">添加订单</el-button>
            </span>
          </el-row>
        </el-tab-pane>
      </el-tabs>
    </el-card>
  </div>
</template>

<script>
import cityData from './cityData.js'
export default {
  data() {
    return {
      cateList: [],
      customers: [], // 获取的顾客列表
      goods: [], // 获取的商品列表
      orderForm: {
        selectedCustomers: [], // 选中的顾客信息数组
        selectedGoods: [], // 选中的商品数组
        dates: [],
        isSend: true,
        isPay: true
      },
      cascaderProps: {
        // 看到的属性
        label: 'cate_name',
        // 选中的属性
        value: 'cate_id',
        // 父子嵌套使用的属性
        children: 'children',
        expandTrigger: 'click'
      },
      cityData: cityData.cityData,
      addressProps: {
        // 看到的属性
        label: 'text',
        // 选中的属性
        value: 'text',
        // 父子嵌套使用的属性
        children: 'children',
        expandTrigger: 'hover'
      },
      activeIndex: '0',
      selectedGoodsId: []
    }
  },
  created() {
    this.getCustomers()
  },
  methods: {
    getCateList() {
      this.$http
        .get('http://localhost/vue/vue_shop/cate/getCateList.php', {
          params: { type: 3, getGoods: true }
        })
        .then((response) => {
          const { data: res } = response
          if (res.meta.status !== 200) {
            return this.$message.error('获取分类列表失败！')
          }
          this.goods = []
          this.findAndAddGoods(res.data.result)
          console.log(this.goods)
          this.cateList = res.data.result
        })
    },
    findAndAddGoods(list) {
      list.forEach((el) => {
        if (el.children) {
          this.findAndAddGoods(el.children)
        } else {
          el.order_quantity = 1
          el.address1 = []
          el.address2 = ''
          this.goods.push(el)
        }
      })
    },
    async getCustomers() {
      const { data: res } = await this.$http.get(
        'http://localhost/vue/vue_shop/user/getTheTypeUser.php',
        {
          params: {
            type: '顾客'
          }
        }
      )
      this.customers = res.data
    },
    handleSelectionChange(val) {
      this.orderForm.selectedCustomers = val
    },
    goodsChanged(param) {
      let goodsId = this.selectedGoodsId[this.selectedGoodsId.length - 1]
      this.selectedGoodsId = []
      console.log(goodsId)
      for (let i = 0; i < this.orderForm.selectedGoods.length; i++) {
        if (this.orderForm.selectedGoods[i].cate_id === goodsId) {
          return this.$message.error('该商品已存在')
        }
      }
      this.goods.forEach((el) => {
        if (el.cate_id === goodsId) {
          this.orderForm.selectedGoods.push(el)
        }
      })
      console.log(this.orderForm.selectedGoods)
    },
    removeGood(id) {
      this.orderForm.selectedGoods.forEach((el, i) => {
        if (el.cate_id === id) {
          this.orderForm.selectedGoods.splice(i, 1)
        }
      })
    },
    checkAndResetQuantity(scope) {
      if (scope.row.order_quantity < 1) {
        scope.row.order_quantity = 1
        return this.$message.error('商品数量必须大于等于1！')
      }
    },
    // 检查用户地址是否存在和是否选择用户
    checkCustomers() {
      let result = true
      if (this.orderForm.selectedCustomers.length === 0) {
        return false
      }
      this.orderForm.selectedCustomers.forEach((el) => {
        if (el.address1.length !== 3 || el.address2.trim() === '') {
          result = false
        }
      })
      return result
    },
    beforeTabLeave(activeName, oldActiveName) {
      if (oldActiveName === '0') {
        if (!this.checkCustomers()) {
          this.$message.error('请至少选择一个顾客且为选择的顾客设置收货区域或输入详细地址！')
          return false
        }
      } else if (oldActiveName === '1') {
        if (this.orderForm.selectedGoods.length === 0) {
          this.$message.error('至少选择一个商品！')
          return false
        }
      }
    },
    async tabClicked() {
      if (this.activeIndex === '1') {
        this.getCateList()
      }
    },
    handleTagClose(i) {
      this.orderForm.dates.splice(i, 1)
    },
    async addOrders() {
      this.orderForm.dates.forEach((item, i) => {
        this.orderForm.dates[i] = Number(item) / 1000
      })
      if (this.orderForm.selectedCustomers.length !== 0 && this.orderForm.selectedGoods.length !== 0 && this.orderForm.dates.length !== 0) {
        let newOrderFormStr = JSON.stringify(this.orderForm)
        let newOrderForm = JSON.parse(newOrderFormStr)
        newOrderForm.selectedCustomers.forEach(el => {
          el.address1 = el.address1.join('/')
        })
        const { data: res } = await this.$http.post('http://localhost/vue/vue_shop/order/addOrders.php', newOrderForm)
        if (res.meta.status !== 200) {
          return this.$message.error(res.meta.message)
        }
        this.$message.success('添加订单成功！')
      } else {
        return this.$message.error('请填写所有必要数据！')
      }
    }
  }
}
</script>

<style lang="css" scoped>
span {
  margin: 10px;
}
.el-cascader {
  margin: 10px;
}
.el-alert {
  margin: 10px 0;
}
.el-table {
  border: 1px solid #333744;
}
</style>
