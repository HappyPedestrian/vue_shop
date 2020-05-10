<template>
  <div>
    <!-- 面包屑导航区 -->
    <el-breadcrumb separator-class="el-icon-arrow-right">
      <el-breadcrumb-item :to="{ path: '/home' }">首页</el-breadcrumb-item>
      <el-breadcrumb-item>商品管理</el-breadcrumb-item>
      <el-breadcrumb-item>添加商品</el-breadcrumb-item>
    </el-breadcrumb>

    <!-- 卡片视图区 -->
    <el-card>
      <!-- 提示区 -->
      <el-alert
        title="添加商品信息"
        type="info"
        center
        show-icon
        :closable="false"
      ></el-alert>
      <!-- 步骤条区域 -->
      <el-steps
        :space="200"
        :active="activeIndex - 0"
        finish-status="success"
        align-center
      >
        <el-step title="基本信息"></el-step>
        <el-step title="商品参数"></el-step>
        <el-step title="商品属性"></el-step>
        <el-step title="商品图片"></el-step>
        <el-step title="商品内容"></el-step>
        <el-step title="完成"></el-step>
      </el-steps>
      <!-- tap栏标签页区 -->
      <el-form
        :model="addForm"
        :rules="addFormRules"
        ref="addFormRef"
        label-width="100px"
        label-position="top"
      >
        <el-tabs
          v-model="activeIndex"
          :tab-position="'left'"
          :before-leave="beforeTabLeave"
          @tab-click="tabClicked"
        >
          <el-tab-pane label="基本信息" name="0">
            <!-- prop指定验证规则 -->
            <el-form-item label="商品名称" prop="goods_name">
              <el-input v-model="addForm.goods_name"></el-input>
            </el-form-item>
            <el-form-item label="商品价格" prop="goods_price">
              <el-input v-model="addForm.goods_price" type="number"></el-input>
            </el-form-item>
            <el-form-item label="商品重量" prop="goods_weight">
              <el-input v-model="addForm.goods_weight" type="number"></el-input>
            </el-form-item>
            <el-form-item label="商品数量" prop="goods_number">
              <el-input v-model="addForm.goods_number" type="number"></el-input>
            </el-form-item>
            <el-form-item label="商品分类" prop="goods_cate">
              <!-- options指定数据源 -->
              <el-cascader
                v-model="addForm.goods_cate"
                :options="cateList"
                :props="cateProps"
                @change="handleChange"
              ></el-cascader>
            </el-form-item>
            <el-form-item label="商品所属店铺" prop="goods_seller">
              <el-select v-model="addForm.goods_seller" filterable placeholder="请选择">
                <el-option
                  v-for="item in sellerList"
                  :key="item.user_id"
                  :label="item.user_name"
                  :value="item.user_id"
                >
                </el-option>
              </el-select>
            </el-form-item>
          </el-tab-pane>
          <el-tab-pane label="商品参数" name="1">
            <!-- 渲染mantTableData每一项 -->
            <el-form-item
              :label="attr.attr_name"
              v-for="attr in manyTableData"
              :key="attr.attr_id"
            >
              <template>
                <div>
                  <!-- 循环渲染tag -->
                  <el-tag
                    v-for="(item, i) in attr.attr_values"
                    :key="i"
                    closable
                    @close="handleClose(i, attr_values)"
                    >{{ item }}</el-tag
                  >
                  <!-- 输入框 -->
                  <el-input
                    ref="saveTagInput"
                    class="input_new_tag"
                    v-if="attr.inputVisible"
                    v-model="attr.inputValue"
                    size="small"
                    @keyup.enter.native="handleInputConfirm(attr)"
                    @blur="handleInputConfirm(attr)"
                  ></el-input>
                  <!-- 添加的按钮 -->
                  <el-button v-else size="small" @click="showInput(attr)"
                    >▲ 添加属性</el-button
                  >
                </div>
              </template>
            </el-form-item>
          </el-tab-pane>
          <el-tab-pane label="商品属性" name="2">
            <el-form-item
              :label="item.attr_name"
              v-for="(item, i) in onlyTableData"
              :key="i"
            >
              <el-input v-model="item.attr_values"></el-input>
            </el-form-item>
          </el-tab-pane>
          <el-tab-pane label="商品图片" name="3">
            <!-- action表示图片要上传到后台的API地址 -->
            <el-upload
              class="upload-demo"
              action="http://localhost/vue/vue_shop/goods/addPicture.php"
              accept="image/jpeg, image/gif, image/png"
              :on-preview="handlePreview"
              :on-remove="handleRemove"
              :http-request="uploadImage"
              :on-success="onSuccess"
              :on-error="onError"
              list-type="picture"
              :headers="headerObj"
            >
              <el-button size="small" type="primary">点击上传</el-button>
              <div slot="tip" class="el-upload__tip">
                只能上传jpg/png文件，且不超过500kb
              </div>
            </el-upload>
          </el-tab-pane>
          <el-tab-pane label="商品内容" name="4">
            <quill-editor v-model="addForm.goods_introduce" />
            <el-button type="primary" class="addBtn" @click="add"
              >添加商品</el-button
            >
          </el-tab-pane>
        </el-tabs>
      </el-form>
    </el-card>

    <!-- 显示图片对话框 -->
    <el-dialog
      :title="pic_info.name"
      :visible="picDialogVisible"
      width="50%"
      @close="picDialogClosed"
    >
      <el-image :src="pic_info.url" :fit="'contain'"></el-image>
    </el-dialog>
  </div>
</template>

<script>
export default {
  data() {
    return {
      activeIndex: '0',
      addForm: {
        goods_name: '',
        goods_price: 0,
        goods_weight: 0,
        goods_number: 0,
        goods_cate: [],
        goods_introduce: '',
        pics: [],
        attrs: [],
        goods_seller: ''
      },
      addFormRules: {
        goods_name: [
          { required: true, message: '请输入商品名称', trigger: 'blur' }
        ],
        goods_price: [
          { required: true, message: '请输入商品价格', trigger: 'blur' }
        ],
        goods_weight: [
          { required: true, message: '请输入商品重量', trigger: 'blur' }
        ],
        goods_number: [
          { required: true, message: '请输入商品数量', trigger: 'blur' }
        ],
        goods_cate: [
          { required: true, message: '请选择商品分类', trigger: 'blur' }
        ],
        goods_seller: [
          { required: true, message: '请选择商品所属店铺', trigger: 'blur' }
        ]
      },
      cateList: [],
      cateProps: {
        // 看到的属性
        label: 'cate_name',
        // 选中的属性
        value: 'cate_id',
        // 父子嵌套使用的属性
        children: 'children',
        expandTrigger: 'hover'
      },
      sellerProps: {
        // 看到的属性
        label: 'user_name',
        // 选中的属性
        value: 'user_id',
        // 父子嵌套使用的属性
        children: 'children',
        expandTrigger: 'hover'
      },
      // 动态参数列表数据
      manyTableData: [],
      // 静态属性列表数据
      onlyTableData: [],
      // 图片上传组件的headers请求头对象
      headerObj: {
        Authorization: window.sessionStorage.getItem('token'),
        'Content-Type': 'multipart/form-data'
      },
      fileList: [],
      pic_info: {},
      picDialogVisible: false,
      sellerList: []
    }
  },
  created() {
    this.getCateList()
    this.getSellsers()
  },
  methods: {
    getCateList() {
      this.$http
        .get('http://localhost/vue/vue_shop/cate/getCateList.php', {
          params: { type: 3 }
        })
        .then(response => {
          const { data: res } = response
          if (res.meta.status !== 200) {
            return this.$message.error('获取分类列表失败！')
          }
          console.log(res)
          this.cateList = res.data.result
        })
    },
    // 级联选择器选中项改变,触发函数
    handleChange() {
      if (this.addForm.goods_cate.length !== 3) {
        this.addForm.goods_cate = []
      }
    },
    beforeTabLeave(activeName, oldActiveName) {
      if (oldActiveName === '0' && this.addForm.goods_cate.length !== 3) {
        this.$message.error('请先选择商品分类')
        return false
      }
    },
    async tabClicked() {
      if (this.activeIndex === '1') {
        const {
          data: res
        } = await this.$http.get(
          'http://localhost/vue/vue_shop/cate/getAttrs.php',
          { params: { id: this.cateId, sel: 'many' } }
        )
        console.log(res)
        if (res.meta.status !== 200) {
          return this.$message.error(res.meta.message)
        }
        res.data.forEach(item => {
          item.attr_values = []
          item.inputVisible = false
        })
        this.manyTableData = res.data
      } else if (this.activeIndex === '2') {
        const {
          data: res
        } = await this.$http.get(
          'http://localhost/vue/vue_shop/cate/getAttrs.php',
          { params: { id: this.cateId, sel: 'only' } }
        )
        if (res.meta.status !== 200) {
          return this.$message.error(res.meta.message)
        }
        res.data.forEach(item => {
          item.attr_values = ''
        })
        console.log(res)
        this.onlyTableData = res.data
      }
    },
    // 处理图片预览效果
    handlePreview(file) {
      this.pic_info = file
      this.picDialogVisible = true
    },
    // 处理图片移除操作
    handleRemove(file) {
      console.log(file)
      // 图片将要删除的临时路径
      let imagePath = file.response.data.tmp_path

      // 在图片数组中找到该图片索引值
      const i = this.addForm.pics.findIndex(item => {
        return item.pic === imagePath
      })
      // 删除图片路径
      this.addForm.pics.splice(i, 1)
      console.log(this.addForm.pics)
    },
    uploadImage(f) {
      // 创建form对象
      let param = new FormData()
      // 通过append向form对象添加数据
      param.append('file', f.file)
      param.append('token', window.sessionStorage.getItem('token'))
      // 添加请求头
      let config = {
        headers: { 'Content-Type': 'multipart/form-data' }
      }
      // 上传图片
      this.$http
        .post(f.action, param, config)
        .then(response => {
          f.onSuccess(response.data)
        })
        .catch(({ err }) => {
          f.onError(err)
        })
    },
    onSuccess(res) {
      // 图片信息
      let picInfo = {
        pic: res.data.tmp_path
      }

      this.addForm.pics.push(picInfo)
      console.log(this.addForm)
    },
    onError(err) {
      this.$message.error('上传失败！' + err)
    },
    add() {
      this.$refs.addFormRef.validate(async valid => {
        // 如果验证不通过
        if (!valid) {
          return this.$message.error('请填写所以必要的数据！')
        }
        // 验证通过，发送添加请求
        let newAddFormStr = JSON.stringify(this.addForm)
        let newAddForm = JSON.parse(newAddFormStr)
        // 往表单里添加动态参数和静态属性
        this.manyTableData.forEach(item => {
          const newItem = {
            attr_id: item.attr_id,
            attr_values: item.attr_values.join(' ')
          }
          newAddForm.attrs.push(newItem)
        })

        this.onlyTableData.forEach(item => {
          const newItem = {
            attr_id: item.attr_id,
            attr_values: item.attr_values
          }
          newAddForm.attrs.push(newItem)
        })

        // 对字符串前后的<p>、</p>标签进行裁切
        newAddForm.goods_introduce = newAddForm.goods_introduce.slice(3, -4)

        console.log(newAddForm)
        // 发送添加请求
        const { data: res } = await this.$http.post(
          'http://localhost/vue/vue_shop/goods/addGoods.php',
          newAddForm
        )
        console.log(res)
        if (res.meta.status !== 200) {
          return this.$message.error(res.meta.message)
        }
        this.$router.push('/GoodsList')
      })
    },
    // 处理图片对话框关闭事件
    picDialogClosed() {
      this.picDialogVisible = false
    },
    handleInputConfirm(attr) {
      if (attr.inputValue.trim().length === 0) {
        attr.inputValue = ''
        attr.inputVisible = false
        return
      }
      // 如果输入合法执行的操作
      attr.attr_values.push(attr.inputValue)
      attr.inputVisible = false
      attr.inputValue = ''
      console.log(this.manyTableData)
    },
    showInput(attr) {
      attr.inputVisible = true
    },
    async getSellsers() {
      const { data: res } = await this.$http.get(
        'http://localhost/vue/vue_shop/user/getTheTypeUser.php',
        {
          params: {
            type: '商家'
          }
        }
      )
      if (res.meta.status !== 200) {
        return this.$message.error(res.meta.message)
      }
      this.sellerList = res.data
      console.log('商家')
      console.log(res)
    }
  },
  computed: {
    cateId() {
      if (this.addForm.goods_cate.length === 3) {
        return this.addForm.goods_cate[2]
      }
      return null
    }
  }
}
</script>

<style lang="css" scoped>
.el-steps {
  margin: 15px 0;
}
.el-step__title {
  font-size: 12px;
}
.el-checkbox {
  margin: 0 5px 0 0 !important;
}
.addBtn {
  margin-top: 15px;
}
.input_new_tag {
  width: 130px;
}
.el-tag {
  margin: 10px;
}
.el-image {
  width: 700px;
  height: 450px;
}
</style>
