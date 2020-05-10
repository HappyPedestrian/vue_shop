<template>
  <div>
    <!-- 面包屑导航区域 -->
    <el-breadcrumb separator-class="el-icon-arrow-right">
      <el-breadcrumb-item :to="{ path: '/home' }">首页</el-breadcrumb-item>
      <el-breadcrumb-item>商品管理</el-breadcrumb-item>
      <el-breadcrumb-item>商品列表</el-breadcrumb-item>
    </el-breadcrumb>

    <!-- 卡片区域 -->
    <el-card>
      <el-row :gutter="20">
        <el-col :span="8">
          <el-input
            placeholder="请输入内容"
            v-model="queryInfo.query"
            clearable
            @clear="getGoodsList"
          >
            <el-button
              slot="append"
              icon="el-icon-search"
              @click="getGoodsList"
            ></el-button>
          </el-input>
        </el-col>
        <el-col :span="4">
          <el-button type="primary" @click="goAddPage()">添加商品</el-button>
        </el-col>
      </el-row>

      <!-- 表格区 -->
      <el-table :data="goodsList" border stripe>
        <el-table-column label="#" type="index"></el-table-column>
        <el-table-column label="商品名称" prop="goods_name"></el-table-column>
        <el-table-column
          label="商品数量"
          prop="goods_number"
          width="95px"
        ></el-table-column>
        <el-table-column
          label="商品价格(元)"
          prop="goods_price"
          width="95px"
        ></el-table-column>
        <el-table-column
          label="商品重量"
          prop="goods_weight"
          width="95px"
        ></el-table-column>
        <el-table-column>
          <template slot-scope="scope">
            <div>
              <el-tag type="danger" v-if="scope.row.goods_delete === 1"
                >已下架</el-tag
              >
              <el-tag type="success" v-else>有效</el-tag>
            </div>
          </template>
        </el-table-column>
        <el-table-column label="创建时间" prop="add_time" width="140px">
          <template slot-scope="scope">
            <div>{{ scope.row.add_time | dateFormat }}</div>
          </template>
        </el-table-column>
        <el-table-column label="更新时间" prop="upd_time" width="140px">
          <template slot-scope="scope">
            <div>{{ scope.row.upd_time | dateFormat }}</div>
          </template>
        </el-table-column>
        <el-table-column label="操作" width="230px">
          <template slot-scope="scope">
            <div>
              <el-button
                type="primary"
                icon="el-icon-edit"
                size="mini"
                @click="showEditDialog(scope.row)"
              ></el-button>
              <el-button
                type="danger"
                icon="el-icon-delete"
                size="mini"
                @click="removeGoodsById(scope.row.goods_id)"
              ></el-button>
            </div>
          </template>
        </el-table-column>
      </el-table>

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
    <!-- 编辑对话框 -->
    <el-dialog
      title="编辑商品"
      :visible="editGoodsDialogVisible"
      width="50%"
      @close="editGoodsDialogClosed"
    >
      <!-- 编辑分类表单 -->
      <el-form
        :model="editGoodsForm"
        :rules="editGoodsFormRules"
        ref="editGoodsFormRef"
        label-width="130px"
      >
        <el-form-item label="商品名称:" prop="goods_name">
          <el-input v-model="editGoodsForm.goods_name"></el-input>
        </el-form-item>

        <el-form-item label="商品价格:" prop="goods_price">
          <el-input v-model="editGoodsForm.goods_price" type="number"></el-input>
        </el-form-item>

        <el-form-item label="商品数量:" prop="goods_number">
          <el-input v-model="editGoodsForm.goods_number" type="number"></el-input>
        </el-form-item>

        <el-form-item label="商品重量:" prop="goods_weight">
          <el-input v-model="editGoodsForm.goods_weight" type="number"></el-input>
        </el-form-item>

        <el-form-item label="商品介绍:" prop="goods_state">
          <el-input v-model="editGoodsForm.goods_state"></el-input>
        </el-form-item>
        <el-form-item label="是否有效:" prop="goods_delete">
          <el-select v-model="editGoodsForm.goods_delete" placeholder="请选择">
            <el-option
              v-for="item in options"
              :key="item.value"
              :label="item.label"
              :value="item.value"
            >
            </el-option>
          </el-select>
        </el-form-item>

        <el-form-item label="父类名称:" prop="goods_cate">
          <!-- options指定数据源 -->
          <!-- props指定配置对象 -->
          <el-cascader
            :options="parentCateList"
            :props="cascaderProps"
            v-model="selectedIds"
            @change="parentCateChanged"
          ></el-cascader>
        </el-form-item>
        <el-form-item label="商品所属店铺" prop="goods_seller">
              <el-select v-model="editGoodsForm.goods_seller" filterable placeholder="请选择">
                <el-option
                  v-for="item in sellerList"
                  :key="item.user_id"
                  :label="item.user_name"
                  :value="item.user_id"
                >
                </el-option>
              </el-select>
        </el-form-item>
        <el-form-item label="商品图片:" prop="goods_pics">
          <ul>
            <li v-for="(pic, i) in editGoodsForm.goods_pics" :key="i" >
              <el-image :src="pic.pic_url" :fit="'contain'">
              </el-image>
              <el-switch
                class="rightTop"
                v-model="pic.isSave"
                active-text="保存"
                :active-value="false"
                inactive-text="删除"
                :inactive-value="true"
                inactive-color="red">
              </el-switch>
            </li>
          </ul>
          <!-- <el-image
            v-for="(pic, i) in editGoodsForm.goods_pics"
            :src="pic.pic_url"
            :fit="'contain'"
            :key="i"
          ></el-image> -->
        </el-form-item>
        <el-upload
              ref="uploadRef"
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
        <span class="dialog-footer">
          <el-button type="primary" @click="updateGoodsInfo">确定</el-button>
          <el-button @click="editGoodsDialogVisible = false">取消</el-button>
        </span>
      </el-form>
    </el-dialog>
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
      queryInfo: {
        query: '',
        pageNum: 1,
        pageSize: 10
      },
      goodsList: [],
      total: 0,
      editGoodsDialogVisible: false,
      editGoodsForm: {},
      editGoodsFormRules: {
        goods_name: [
          {
            required: true,
            message: '请输入商品名称',
            trigger: 'blur'
          }
        ],
        goods_price: [
          {
            required: true,
            message: '请输入商品价格',
            trigger: 'blur'
          }
        ],
        goods_number: [
          {
            required: true,
            message: '请输入商品数量',
            trigger: 'blur'
          }
        ],
        goods_weight: [
          {
            required: true,
            message: '请输入商品重量',
            trigger: 'blur'
          }
        ],
        goods_state: [
          {
            required: true,
            message: '请输入商品介绍',
            trigger: 'blur'
          }
        ],
        goods_seller: [
          {
            required: true,
            message: '请选择商品店铺',
            trigger: 'blur'
          }
        ],
        goods_cate: [
          {
            required: true,
            message: '请选择商品分类',
            trigger: 'blur'
          }
        ]
      },
      selectedIds: [],
      parentCateList: [],
      cascaderProps: {
        // 看到的属性
        label: 'cate_name',
        // 选中的属性
        value: 'cate_id',
        // 父子嵌套使用的属性
        children: 'children',
        expandTrigger: 'hover'
      },
      options: [{
          value: 0,
          label: '有效'
        }, {
          value: 1,
          label: '已下架'
        }],
      picDialogVisible: false,
      headerObj: {
        Authorization: window.sessionStorage.getItem('token'),
        'Content-Type': 'multipart/form-data'
      },
      pic_info: {},
      sellerList: []
    }
  },
  created() {
    this.getCateList()
    this.getGoodsList()
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
            return this.$message.error(res.meta.message)
          }
          this.parentCateList = res.data.result
        })
    },
    // 根据参数获取商品列表
    async getGoodsList() {
      const {
        data: res
      } = await this.$http.get(
        'http://localhost/vue/vue_shop/goods/getGoodsList.php',
        { params: this.queryInfo }
      )
      console.log(res)
      if (res.meta.status !== 200) {
        return this.$message.error(res.meta.message)
      }
      this.goodsList = res.data.goods
      this.queryInfo.pageNum = res.pageNum
      // this.goodsList.forEach(goods => {
      //   goods.goods_pics.forEach(pic => {
      //     pic.isSave = true
      //   })
      // })
      console.log(res)
      this.total = res.data.total
      this.$message.success('获取商品列表成功！')
    },
    handleSizeChange(newSize) {
      this.queryInfo.pageSize = newSize
      this.getGoodsList()
    },
    handleCurrentChange(newPageNum) {
      this.queryInfo.pageNum = newPageNum
      this.getGoodsList()
    },
    async removeGoodsById(id) {
      const confirmResult = await this.$confirm('是否删除该商品？', '提示', {
        confirmButtonText: '确认',
        cancelButtonText: '取消',
        type: 'warning'
      }).catch(error => error)
      // 当用户取消了该操作
      if (confirmResult !== 'confirm') {
        return this.$message.info('您取消了删除操作')
      }
      const { data: res } = await this.$http.post(
        'http://localhost/vue/vue_shop/goods/deleteGoods.php',
        {
          goods_id: id
        }
      )
      if (res.meta.status !== 200) {
        return this.$message.error(res.meta.message)
      }

      this.$message.success('删除商品成功！')
      this.getGoodsList()
    },
    goAddPage() {
      this.$router.push('/goods/add')
    },
    showEditDialog(row) {
      this.editGoodsForm = row
      this.editGoodsForm.addPics = []
      this.getCateParentIds(row.goods_cate)
      this.editGoodsDialogVisible = true
    },
    getCateParentIds(id) {
      for (let i = 0; i < this.parentCateList.length; i++) {
        let id1 = this.parentCateList[i].cate_id
        let secondCate = this.parentCateList[i].children
        for (let j = 0; j < secondCate.length; j++) {
          let id2 = secondCate[j].cate_id
          let thirdCate = secondCate[j].children
          for (let k = 0; k < thirdCate.length; k++) {
            let id3 = thirdCate[k].cate_id
            if (id3 === id) {
              this.selectedIds[0] = id1
              this.selectedIds[1] = id2
              this.selectedIds[2] = id3
              return
            }
          }
        }
      }
    },
    parentCateChanged() {},
    editGoodsDialogClosed() {
      this.editGoodsDialogVisible = false
    },
    async updateGoodsInfo() {
      if (this.selectedIds.length !== 3) {
        return this.$message.error('请选择商品的三级分类')
      }
      console.log(this.selectedIds)
      this.editGoodsForm.goods_cate = this.selectedIds
      console.log(this.editGoodsForm)
      const { data: res } = await this.$http.post(
        'http://localhost/vue/vue_shop/goods/updateGoods.php',
        this.editGoodsForm
      )
      console.log(res)
      if (res.meta.status !== 200) {
        return this.$message.error(res.meta.message)
      }
      this.editGoodsDialogVisible = false
      this.editGoodsForm.addPics = []
      this.$refs.uploadRef.clearFiles()
      this.getGoodsList()
    },
    picChange(pic) {
      pic.isSave = !pic.isSave
      console.log(pic)
      return pic.isSave
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
        .then((response) => {
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

      this.editGoodsForm.addPics.push(picInfo)
      console.log(this.editGoodsForm)
    },
    onError(err) {
      this.$message.error('上传失败！' + err)
    },
     handleRemove(file) {
      console.log(file)
      // 图片将要删除的临时路径
      let imagePath = file.response.data.tmp_path

      // 在图片数组中找到该图片索引值
      const i = this.editGoodsForm.addPics.findIndex((item) => {
        return item.pic === imagePath
      })
      // 删除图片路径
      this.editGoodsForm.pics.splice(i, 1)
      console.log(this.editGoodsForm.pics)
    },
    handlePreview(file) {
      this.pic_info = file
      this.picDialogVisible = true
    },
    picDialogClosed() {
      this.picDialogVisible = false
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
    }
  }
}
</script>

<style lang="css" scoped>
.el-image {
  width: 150px;
  height: 100px;
  display: inline-block;
}
li {
  padding: 0;
  background-color: #eee;
  margin: 5px;
}
.rightTop {
  position: relative;
  right: -30px;
  top: -30px;
}
</style>
