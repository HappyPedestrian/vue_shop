<template>
  <div>
    <el-breadcrumb separator-class="el-icon-arrow-right">
      <el-breadcrumb-item :to="{ path: '/home' }">首页</el-breadcrumb-item>
      <el-breadcrumb-item>商品管理</el-breadcrumb-item>
      <el-breadcrumb-item>商品分类</el-breadcrumb-item>
    </el-breadcrumb>

    <el-card>
      <el-row>
        <el-col>
          <el-button type="primary" @click="showAddCateDialog">添加</el-button>
        </el-col>
      </el-row>
      <tree-table
        class="treeTable"
        :data="cateList"
        :columns="columns"
        :selection-type="false"
        :expand-type="false"
        :show-index="true"
        index-text="#"
        border
      >
        <!-- 是否有效 -->
        <template slot="isok" slot-scope="scope">
          <div>
            <i
              class="el-icon-success"
              v-if="scope.row.cate_delete == false"
              style="color: lightgreen"
            ></i>
            <i class="el-icon-error" v-else style="color: red"></i>
          </div>
        </template>

        <!-- 排序 -->
        <template slot="order" slot-scope="scope">
          <div>
            <el-tag v-if="scope.row.cate_level === 0" size="mini">一级</el-tag>
            <el-tag
              type="success"
              v-else-if="scope.row.cate_level === 1"
              size="mini"
              >二级</el-tag
            >
            <el-tag type="warning" v-else size="mini">三级</el-tag>
          </div>
        </template>
        <!-- 操作 -->
        <template slot="opt" slot-scope="scope">
          <div>
            <el-button
              type="primary"
              icon="el-icon-edit"
              size="mini"
              @click="showEditDialog(scope.row)"
              >编辑</el-button
            >
            <el-button type="danger" icon="el-icon-delete" size="mini"
              @click="deleteCate(scope.row.cate_id)">删除</el-button
            >
          </div>
        </template>
      </tree-table>

      <!-- 分页区 -->
      <el-pagination
        @size-change="handleSizeChange"
        @current-change="handleCurrentChange"
        :current-page="queryInfo.pageNum"
        :page-sizes="[10, 20, 30, 50]"
        :page-size="queryInfo.pageSize"
        layout="total,sizes,prev,pager,next,jumper"
        :total="total"
      >
      </el-pagination>
    </el-card>

    <!-- 添加分类对话框 -->
    <el-dialog
      title="添加分类"
      :visible="addCateDialogVisible"
      width="50%"
      @close="addCateDialogClosed"
    >
      <!-- 添加分类表单 -->
      <el-form
        :model="addCateForm"
        :rules="addCateFormRules"
        ref="addCateFormRef"
        label-width="100px"
      >
        <el-form-item label="分类名称:" prop="cate_name">
          <el-input v-model="addCateForm.cate_name"></el-input>
        </el-form-item>

        <el-form-item label="父类名称:">
          <!-- options指定数据源 -->
          <!-- props指定配置对象 -->
          <el-cascader
            :options="parentCateList"
            :props="cascaderProps"
            v-model="selectedIds"
            @change="parentCateChanged"
            clearable
          ></el-cascader>
        </el-form-item>
        <span class="dialog-footer">
          <el-button type="primary" @click="addCate">确定</el-button>
          <el-button @click="addCateDialogVisible = false">取消</el-button>
        </span>
      </el-form>
    </el-dialog>

    <!-- 编辑分类对话框 -->
    <el-dialog
      title="编辑分类"
      :visible="editCateDialogVisible"
      width="50%"
      @close="editCateDialogClosed"
    >
      <!-- 添加分类表单 -->
      <el-form
        :model="editCateForm"
        :rules="editCateFormRules"
        ref="editCateFormRef"
        label-width="100px"
      >
        <el-form-item label="分类名称:" prop="cate_name">
          <el-input v-model="editCateForm.cate_name"></el-input>
        </el-form-item>

        <el-form-item label="是否有效:">
          <el-select v-model="editCateForm.cate_delete" placeholder="请选择">
            <el-option
              v-for="item in cateDeleteOptions"
              :key="item.value"
              :label="item.label"
              :value="item.value"
            >
            </el-option>
          </el-select>
        </el-form-item>

        <el-form-item label="父类名称:">
          <!-- options指定数据源 -->
          <!-- props指定配置对象 -->
          <el-cascader
            :options="parentCateList"
            :props="cascaderProps"
            v-model="selectedIds"
            @change="editParentCateChanged"
            clearable
          ></el-cascader>
        </el-form-item>
        <span class="dialog-footer">
          <el-button type="primary" @click="updateCate">确定</el-button>
          <el-button @click="editCateDialogVisible = false">取消</el-button>
        </span>
      </el-form>
    </el-dialog>
  </div>
</template>

<script>
export default {
  data() {
    return {
      queryInfo: {
        type: 3,
        pageNum: 1,
        pageSize: 10
      },
      total: 0,
      cateList: [],
      columns: [
        {
          label: '分类名称',
          prop: 'cate_name'
        },
        {
          label: '是否有效',
          // 将当前列渲染成模板列
          type: 'template',
          // 使用的模板名称
          template: 'isok'
        },
        {
          label: '排序',
          // 将当前列渲染成模板列
          type: 'template',
          // 使用的模板名称
          template: 'order'
        },
        {
          label: '操作',
          // 将当前列渲染成模板列
          type: 'template',
          // 使用的模板名称
          template: 'opt'
        }
      ],
      addCateDialogVisible: false,
      addCateForm: {
        cate_name: '',
        cate_pid: 0,
        cate_level: 0
      },
      // 添加分类表单的验证规则
      addCateFormRules: {
        cate_name: [
          {
            required: true,
            message: '请输入分类名称',
            trigger: 'blur'
          }
        ]
      },
      editCateForm: {
        cate_name: '',
        cate_pid: 0,
        cate_level: 0,
        cate_delete: false
      },
      editCateFormRules: {
          cate_name: [
          {
            required: true,
            message: '请输入分类名称',
            trigger: 'blur'
          }
        ]
      },
      editCateDialogVisible: false, // 控制编辑对话框的显示
      // 父级分类列表
      parentCateList: [],
      // 级联选择器配置对象
      cascaderProps: {
        // 看到的属性
        label: 'cate_name',
        // 选中的属性
        value: 'cate_id',
        // 父子嵌套使用的属性
        children: 'children',
        expandTrigger: 'hover',
        checkStrictly: true
      },
      selectedIds: [],
      cateDeleteOptions: [
          {
              label: '是',
              value: 0
          },
          {
              label: '否',
              value: 1
          }
      ]
    }
  },
  created() {
    this.getCateList()
  },
  methods: {
    getCateList() {
      this.$http
        .get('http://localhost/vue/vue_shop/cate/getCateList.php', {
          params: this.queryInfo
        })
        .then(response => {
          const { data: res } = response
          console.log(res)
          if (res.meta.status !== 200) {
            return this.$message.error(res.meta.message)
          }
          this.cateList = res.data.result
          this.total = res.total
        })
    },
    // 监听pageSize改变的处理函数
    handleSizeChange(newSize) {
      this.queryInfo.pageSize = newSize
      this.getCateList()
    },
    handleCurrentChange(newPageNum) {
      this.queryInfo.pageNum = newPageNum
      this.getCateList()
    },
    showAddCateDialog() {
      // 获取父级分类列表
      this.getParentCateList()
      // 展示添加分类对话框
      this.addCateDialogVisible = true
    },
    getParentCateList() {
      this.$http
        .get('http://localhost/vue/vue_shop/cate/getCateList.php', {
          params: { type: 2 }
        })
        .then(response => {
          const res = response.data
          if (res.meta.status !== 200) {
            return this.$message.error(res.meta.message)
          }
          console.log(res)
          this.parentCateList = res.data
        })
    },
    parentCateChanged() {
      if (this.selectedIds.length !== 0) {
        this.addCateForm.cate_pid = this.selectedIds[this.selectedIds.length - 1]
        this.addCateForm.cate_level = this.selectedIds.length
      } else {
        this.addCateForm.cate_pid = 0
        this.addCateForm.cate_level = 0
      }
    },
    addCate() {
      // console.log(this.addCateForm)
      this.$refs.addCateFormRef.validate(async valid => {
        if (!valid) return
        const { data: res } = await this.$http.post(
          'http://localhost/vue/vue_shop/cate/addCate.php',
          this.addCateForm
        )
        if (res.meta.status !== 200) {
          this.$message.error(res.meta.message)
        }
        this.getCateList()
        this.addCateDialogVisible = false
        // console.log(res)
      })
    },
    addCateDialogClosed() {
      this.$refs.addCateFormRef.resetFields()
      this.selectedIds = []
      this.addCateForm.cate_pid = 0
      this.addCateForm.cate_level = 0
      this.addCateDialogVisible = false
    },
    showEditDialog(scope) {
      this.getParentCateList()
      this.editCateForm.cate_id = scope.cate_id
      this.editCateForm.cate_name = scope.cate_name
      this.editCateForm.cate_level = scope.cate_level
      this.editCateForm.cate_delete = scope.cate_delete === false ? 0 : 1
      console.log(this.editCateForm)
      this.editCateDialogVisible = true
    },
    editCateDialogClosed() {
      this.$refs.editCateFormRef.resetFields()
      this.selectedIds = []
      this.editCateForm.cate_pid = 0
      this.editCateForm.cate_level = 0
      this.editCateDialogVisible = false
    },
    editParentCateChanged() {
      if (this.selectedIds.length !== 0) {
        this.editCateForm.cate_pid = this.selectedIds[this.selectedIds.length - 1]
        this.editCateForm.cate_level = this.selectedIds.length
      } else {
        this.editCateForm.cate_pid = 0
        this.editCateForm.cate_level = 0
      }
    },
    updateCate() {
         this.$refs.editCateFormRef.validate(async valid => {
        if (!valid) return
        const { data: res } = await this.$http.post(
          'http://localhost/vue/vue_shop/cate/updateCate.php',
          this.editCateForm
        )
        if (res.meta.status !== 200) {
           this.$message.error(res.meta.message)
        }
        this.getCateList()
        this.editCateDialogVisible = false
        // console.log(res)
      })
    },
    async deleteCate(id) {
        const confirmResult = await this.$confirm('是否删除该商品分类？', '提示', {
        confirmButtonText: '确认',
        cancelButtonText: '取消',
        type: 'warning'
      }).catch(error => error)
      // 当用户取消了该操作
      if (confirmResult !== 'confirm') {
        return this.$message.info('您取消了删除操作')
      }
      const { data: res } = await this.$http.post(
        'http://localhost/vue/vue_shop/cate/deleteCateById.php',
        {
          cate_id: id
        }
      )
      if (res.meta.status !== 200) {
        return this.$message.error(res.meta.message)
      }

      this.$message.success('删除商品分类成功！')
      this.getCateList()
    }
  }
}
</script>

<style lang="css" scoped>
.treeTable {
  margin-top: 15px;
}
.el-cascader {
  width: 100%;
}
</style>
