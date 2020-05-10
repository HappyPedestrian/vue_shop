<template>
  <div>
    <!-- 面包屑导航区域 -->
    <el-breadcrumb separator-class="el-icon-arrow-right">
      <el-breadcrumb-item :to="{ path: '/home' }">首页</el-breadcrumb-item>
      <el-breadcrumb-item>订单管理</el-breadcrumb-item>
      <el-breadcrumb-item>订单列表</el-breadcrumb-item>
    </el-breadcrumb>

    <!-- 卡片区域 -->
    <el-card>
      <el-row :gutter="20">
        <el-col :span="8">
          <el-input placeholder="请输入内容" v-model="queryInfo.query" clearable @clear="getOrderList">
            <el-button type="primary" slot="append" icon="el-icon-search" @click="getOrderList" ></el-button>
          </el-input>
        </el-col>
        <el-col :span="4">
          <el-button type="primary" @click="goAddPage()">添加订单</el-button>
        </el-col>
      </el-row>

      <el-row>
        <!-- 订单数据表格区 -->
        <el-table :data="orderList" border stripe>
          <el-table-column type="index"></el-table-column>
          <el-table-column
            label="订单编号"
            prop="order_number"
          ></el-table-column>
          <el-table-column
            label="用户名"
            prop="user_name"
          ></el-table-column>
          <el-table-column
            label="商品名称"
            prop="goods_name"
          ></el-table-column>
          <el-table-column
            label="订单价格"
            prop="order_price"
          ></el-table-column>
          <el-table-column label="是否付款" prop="pay_status">
            <template slot-scope="scope">
              <div>
                <el-tag type="success" v-if="scope.row.pay_status"
                  >已付款</el-tag
                >
                <el-tag type="danger" v-else>未付款</el-tag>
              </div>
            </template>
          </el-table-column>
          <el-table-column label="是否发货" prop="is_send">
            <template slot-scope="scope">
              <div>{{ scope.row.is_send ? '是' : '否' }}</div>
            </template>
          </el-table-column>
          <el-table-column label="下单时间" prop="create_time">
            <template slot-scope="scope">
              <div>{{ scope.row.create_time | dateFormat }}</div>
            </template>
          </el-table-column>
          <el-table-column label="操作">
            <template slot-scope="scope">
              <div>
                <el-button
                  type="primary"
                  size="mini"
                  icon="el-icon-edit"
                  @click="showEditAddressDialog(scope.row)"
                ></el-button>
                <el-button
                  type="danger"
                  size="mini"
                  icon="el-icon-delete"
                  @click="removeOrder(scope.row.order_id)"
                ></el-button>
              </div>
            </template>
          </el-table-column>
        </el-table>
      </el-row>

      <!-- 分页区 -->
      <el-pagination
        @size-change="handleSizeChange"
        @current-change="handleCurrentChange"
        :current-page="queryInfo.pageNum"
        :page-sizes="[10, 20, 40, 100]"
        :page-size="queryInfo.pageSize"
        layout="total, sizes, prev, pager, next, jumper"
        :total="total"
        background
      ></el-pagination>
    </el-card>

    <!-- 编辑地址对话框 -->
    <el-dialog
      title="修改地址"
      :visible.sync="editAddressDialogVisible"
      width="30%"
      @close="closeEditAdressDialog"
    >
      <el-form
        ref="addressFormRef"
        :model="addressForm"
        :rules="addressRules"
        label-width="80px"
      >
        <el-form-item label="省市区/县" prop="address1">
          <el-cascader
            v-model="addressForm.address1"
            :options="cityData"
            :props="addressProps"
          >
          </el-cascader>
        </el-form-item>
        <el-form-item label="详细地址" prop="address2">
          <el-input v-model="addressForm.address2"></el-input>
        </el-form-item>
      </el-form>
      <span slot="footer" class="dialog-footer">
        <el-button @click="closeEditAdressDialog">取 消</el-button>
        <el-button type="primary" @click="updateOrder"
          >确 定</el-button
        >
      </span>
    </el-dialog>
  </div>
</template>

<script>
import cityData from './cityData.js'
export default {
  data() {
    return {
      queryInfo: {
        query: '',
        pageNum: 1,
        pageSize: 10
      },
      total: 0,
      orderList: [],
      editAddressDialogVisible: false,
      addressForm: {
        address1: [],
        address2: '',
        order_id: 0
      },
      addressRules: {
        address1: [
          {
            required: true,
            message: '请选择省市区/县',
            trigger: 'blur'
          }
        ],
        address2: [
          {
            required: true,
            message: '请输入详细地址',
            trigger: 'blur'
          }
        ]
      },
      cityData: [],
      addressProps: {
        // 看到的属性
        label: 'text',
        // 选中的属性
        value: 'text',
        // 父子嵌套使用的属性
        children: 'children',
        expandTrigger: 'hover'
      }
    }
  },
  created() {
    this.cityData = cityData.cityData
    this.getOrderList()
  },
  methods: {
    async getOrderList() {
      const { data: res } = await this.$http.get(
        'http://localhost/vue/vue_shop/order/getOrderList.php',
        {
          params: this.queryInfo
        }
      )
      console.log(res)
      if (res.meta.status !== 200) {
        return this.$message.error(res.meta.message)
      }
      this.total = res.data.total
      this.queryInfo.pageNum = res.pageNum
      this.orderList = res.data.orders
    },
    handleSizeChange(newSize) {
      this.queryInfo.pageSize = newSize
      this.getOrderList()
    },
    handleCurrentChange(newNum) {
      this.queryInfo.pageNum = newNum
      this.getOrderList()
    },
    showEditAddressDialog(order) {
      this.addressForm.order_id = order.order_id
      let address = order.consignee_addr
      address = address.split(' ')
      this.addressForm.address1 = address[0].split('/')
      this.addressForm.address2 = address[1]
      this.editAddressDialogVisible = true
    },
    // handleChange() {
    //   if (this.addressForm.address1.length !== 2) {
    //     this.addressForm.address1 = []
    //   }
    // },
    closeEditAdressDialog() {
      this.editAddressDialogVisible = false
      this.$refs.addressFormRef.resetFields()
    },
    async updateOrder() {
        let newAddressFormStr = JSON.stringify(this.addressForm)
        let newAddressForm = JSON.parse(newAddressFormStr)
        newAddressForm.address1 = newAddressForm.address1.join('/')
        const { data: res } = await this.$http.post('http://localhost/vue/vue_shop/order/updateOrder.php', newAddressForm)
        console.log(res)
        if (res.meta.status !== 200) {
            return this.$message.error(res.meta.message)
        }
        this.$message.success(res.meta.message)
        this.getOrderList()
        this.editAddressDialogVisible = false
    },
    async removeOrder(orderId) {
      const confirmResult = await this.$confirm('是否删除该订单？', '提示', {
        confirmButtonText: '确认',
        cancelButtonText: '取消',
        type: 'warning'
      }).catch(error => error)
      // 当用户取消了该操作
      if (confirmResult !== 'confirm') {
        return this.$message.info('您取消了删除操作')
      }
      const { data: res } = await this.$http.post('http://localhost/vue/vue_shop/order/deleteOrder.php', {
        order_id: orderId
      })
      if (res.meta.status !== 200) {
        return this.$message.error(res.meta.message)
      }
      this.$message.success('删除订单成功！')
      this.getOrderList()
    },
    goAddPage() {
      this.$router.push('/orders/add')
    }
  }
}
</script>

<style lang="css" scoped>
.el-cascader {
    width: 100%;
}
.el-table__header-wrapper {
  margin-top: 10px;
}
</style>
