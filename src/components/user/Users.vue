<template>
  <div>
    <el-breadcrumb separator-class="el-icon-arrow-right">
      <el-breadcrumb-item :to="{ path: '/home' }">首页</el-breadcrumb-item>
      <el-breadcrumb-item>用户管理</el-breadcrumb-item>
      <el-breadcrumb-item>用户列表</el-breadcrumb-item>
    </el-breadcrumb>

    <el-card>
      <el-row :gutter="20">
        <el-col :span="8">
          <el-input
            placeholder="请输入搜索信息"
            v-model="queryInfo.query"
            :clearable="true"
            @clear="getUsers"
          >
            <el-button slot="append" icon="el-icon-search" @click="getUsers"
              >搜索</el-button
            >
          </el-input>
        </el-col>
        <el-col :span="4">
          <el-button type="primary" @click="showAddDialog()">添加</el-button>
        </el-col>
      </el-row>

      <!-- 用户列表 -->
      <el-table :data="userList" border stripe>
        <el-table-column type="index"></el-table-column>
        <el-table-column label="姓名" prop="user_name"></el-table-column>
        <el-table-column label="邮箱" prop="email"></el-table-column>
        <el-table-column label="电话" prop="mobile"></el-table-column>
        <el-table-column label="角色" prop="role_name"></el-table-column>
        <el-table-column label="操作" width="180px">
          <template slot-scope="scope">
            <div>
              <el-button
                type="primary"
                icon="el-icon-edit"
                size="mini"
                @click="showEditDialog(scope.row.user_id)"
              ></el-button>
              <el-button
                type="danger"
                icon="el-icon-delete"
                size="mini"
                @click="deleteUserById(scope.row.user_id)"
              ></el-button>
              <el-tooltip
                :enterable="false"
                content="分配角色"
                placement="top"
                effect="dark"
              >
                <el-button
                  type="warning"
                  icon="el-icon-setting"
                  size="mini"
                  @click="showSetRoleDialog(scope.row)"
                ></el-button>
              </el-tooltip>
            </div>
          </template>
        </el-table-column>
      </el-table>
      <!-- 底部页码 -->
      <el-pagination
        id="bottomPagination"
        @size-change="handleSizeChange"
        @current-change="handleCurrentChange"
        :current-page="queryInfo.pagenum"
        :page-sizes="[10, 20, 35, 50]"
        :page-size="queryInfo.pagesize"
        layout="total, sizes, prev, pager, next, jumper"
        :total="total"
      ></el-pagination>
    </el-card>
    <!-- 添加对话框 -->
    <el-dialog
      title="添加用户"
      :visible.sync="addDialogVisible"
      @close="addDialogClose"
    >
      <el-form
        :model="addForm"
        :rules="addFormRules"
        ref="addFormRef"
        label-width="70px"
      >
        <el-form-item label="用户名" prop="username">
          <el-input v-model="addForm.username"></el-input>
        </el-form-item>
        <el-form-item label="密码" prop="password">
          <el-input v-model="addForm.password"></el-input>
        </el-form-item>
        <el-form-item label="邮箱" prop="email">
          <el-input v-model="addForm.email"></el-input>
        </el-form-item>
        <el-form-item label="手机" prop="mobile">
          <el-input v-model="addForm.mobile"></el-input>
        </el-form-item>
        <el-form-item label="角色">
          <el-cascader
            v-model="addForm.roleId"
            :options="roleList"
            :props="roleProps"
          ></el-cascader>
        </el-form-item>
      </el-form>
      <span slot="footer" class="dialog-footer">
        <el-button @click="addDialogVisible = false">取消</el-button>
        <el-button @click="addUser" type="primary">确定</el-button>
      </span>
    </el-dialog>
    <!-- 编辑对话框 -->
    <el-dialog
      title="编辑用户"
      :visible.sync="editDialogVisible"
      @close="editDialogClose"
    >
      <el-form
        :model="editForm"
        :rules="editFormRules"
        ref="editFormRef"
        label-width="70px"
      >
        <el-form-item label="用户名">
          <el-input v-model="editForm.user_name" disabled></el-input>
        </el-form-item>
        <el-form-item label="邮箱" prop="email">
          <el-input v-model="editForm.email"></el-input>
        </el-form-item>
        <el-form-item label="手机" prop="mobile">
          <el-input v-model="editForm.mobile"></el-input>
        </el-form-item>
      </el-form>
      <span slot="footer" class="dialog-footer">
        <el-button @click="editDialogClose">取消</el-button>
        <el-button @click="editUserInfo" type="primary">确定</el-button>
      </span>
    </el-dialog>
    <!-- 分配角色对话框  -->
    <el-dialog
      title="分配角色"
      :visible.sync="setRoleDialogVisible"
      width="50%"
    >
      <div>
        <p>用户名: {{ roleInfo.username }}</p>
        <p>角色名: {{ roleInfo.role_name }}</p>
        <p>
          分配角色
          <el-select v-model="selectedId" placeholder="请选择">
            <el-option
              v-for="item in roleList"
              :value="item.id"
              :label="item.roleName"
              :key="item.id"
            ></el-option>
          </el-select>
        </p>
      </div>
      <el-button @click="setRoleDialogVisible = false">取消</el-button>
      <el-button type="primary" @click="saveRoleInfo">确定</el-button>
    </el-dialog>
  </div>
</template>

<script>
export default {
  data() {
    var checkEmail = (rule, value, callback) => {
      const reg = /^([a-zA-Z0-9_-])+@([a-zA-Z0-9_-])+(\.[a-zA-Z0-9_-])+/
      if (reg.test(value)) {
        return callback()
      }
      callback(new Error('请输入正确的邮箱！'))
    }
    var checkMobile = (rule, value, callback) => {
      const reg = /^(0|86|17951)?(13[0-9]|15[012356789]|17[678]|18[0-9]|14[57])[0-9]{8}$/
      if (reg.test(value)) {
        return callback()
      }
      callback(new Error('请输入正确的电话号码！'))
    }
    return {
      queryInfo: {
        query: '',
        pagenum: 1,
        pagesize: 10
      },
      userList: [],
      total: 0,
      // 控制添加对话框的显示
      addDialogVisible: false,
      addForm: {
        username: '',
        password: '',
        email: '',
        mobile: '',
        roleId: []
      },
      roleProps: {
        // 看到的属性
        label: 'roleName',
        // 选中的属性
        value: 'id',
        // 父子嵌套使用的属性
        children: '',
        expandTrigger: 'hover'
      },
      // 添加用户验证规则
      addFormRules: {
        username: [
          { required: true, message: '请输入用户名', trigger: 'blur' },
          { min: 3, max: 10, message: '请输入3到10个字符', trigger: 'blur' }
        ],
        password: [
          { required: true, message: '请输入密码', trigger: 'blur' },
          { min: 6, max: 16, message: '请输入 6 到 16 个字符', trigger: 'blur' }
        ],
        email: [
          { required: true, message: '请输入邮箱', trigger: 'blur' },
          { validator: checkEmail, trigger: 'blur' }
        ],
        mobile: [
          { required: true, message: '请输入手机号', trigger: 'blur' },
          { validator: checkMobile, trigger: 'blur' }
        ]
      },
      editDialogVisible: false,
      editForm: {},
      editFormRules: {
        email: [
          { required: true, message: '请输入邮箱', trigger: 'blur' },
          { validator: checkEmail, trigger: 'blur' }
        ],
        mobile: [
          { required: true, message: '请输入手机号', trigger: 'blur' },
          { validator: checkMobile, trigger: 'blur' }
        ]
      },
      setRoleDialogVisible: false,
      roleInfo: {},
      // 角色列表
      roleList: [],
      // 选择的角色Id
      selectedId: ''
    }
  },
  created() {
    this.getUsers()
  },
  methods: {
    getUsers() {
      this.$http
        .post('http://localhost/vue/vue_shop/user/getUsers.php', this.queryInfo)
        .then(response => {
          const { data: res } = response
          console.log(res)
          if (res.meta.status !== 200) {
            return this.$message.error(res.meta.message)
          }
          this.total = res.total
          this.userList = res.data
          this.queryInfo.pagenum = res.pageNum
        })
    },
    handleSizeChange(newSize) {
      this.queryInfo.pagesize = newSize
      this.getUsers()
    },
    handleCurrentChange(newSize) {
      this.queryInfo.pagenum = newSize
      this.getUsers()
    },
    showAddDialog() {
      this.getRolesList()
      this.addDialogVisible = true
    },
    addDialogClose() {
      this.$refs.addFormRef.resetFields()
    },
    addUser() {
      if (this.addForm.roleId.length === 0) {
        return this.$message.error('请选择用户角色！！！')
      }
      this.$refs.addFormRef.validate(valid => {
        if (!valid) {
          return this.$message.error('添加失败！')
        }
        this.$http
          .post('http://localhost/vue/vue_shop/user/addUser.php', this.addForm)
          .then(response => {
            const { data: res } = response
            console.log(res)
            if (res.meta.status !== 201) {
              return this.$message.error(res.meta.message)
            }
            this.addDialogVisible = false
            this.$message.success('添加成功')
            this.getUsers()
          })
      })
    },
    showEditDialog(id) {
      this.$http
        .post('http://localhost/vue/vue_shop/user/getTheUser.php', { id })
        .then(response => {
          const { data: res } = response
          console.log(res)
          if (res.meta.status !== 200) {
            this.$refs.editFormRef.resetFields()
            this.editDialogVisible = false
            return this.$message.error(res.meta.message)
          }
          this.editForm = res.data[0]
          this.editDialogVisible = true
        })
    },
    editDialogClose() {
      this.$refs.editFormRef.resetFields()
      this.editDialogVisible = false
    },
    editUserInfo() {
      this.$refs.editFormRef.validate(valid => {
        if (!valid) return
        this.$http
          .post('http://localhost/vue/vue_shop/user/updateTheUser.php', {
            id: this.editForm.user_id,
            email: this.editForm.email,
            mobile: this.editForm.mobile
          })
          .then(response => {
            const { data: res } = response
            if (res.meta.status !== 200) {
              return this.$message.error(res.meta.message)
            }
            this.editDialogVisible = false
            this.$message.success(res.meta.message)
            this.getUsers()
          })
      })
    },
    async deleteUserById(id) {
      // 弹窗询问用户是否删除用户
      let confirmResult = await this.$confirm('是否删除用户?', '提示', {
        confirmButtonText: '确定',
        cancelButtonText: '取消',
        type: 'warning'
      }).catch(error => error)
      if (confirmResult !== 'confirm') {
        return this.$message.info('取消了删除')
      }
      this.$http
        .post('http://localhost/vue/vue_shop/user/deleteUserById.php', {
          id: id
        })
        .then(data => {
          let { data: res } = data
          if (res.meta.status !== 200) {
            return this.$message.error('删除用户失败')
          }
          this.getUsers()
          this.$message.success('删除用户成功')
        })
    },
    async showSetRoleDialog(role) {
      this.roleInfo = role
      console.log(this.roleInfo)
      const { data: res } = await this.$http.get(
        'http://localhost/vue/vue_shop/power/getRolesList.php'
      )
      this.roleList = res.data
      this.setRoleDialogVisible = true
    },
    async saveRoleInfo() {
      if (this.selectedId === '') {
        return this.$message.error('请先选择一个角色')
      }
      const { data: res } = await this.$http.post(
        'http://localhost/vue/vue_shop/user/saveRoleInfo.php',
        {
          id: this.roleInfo.user_id,
          rId: this.selectedId
        }
      )
      if (res.meta.status !== 200) {
        return this.$message.error('分配角色失败')
      }
      this.selectedId = ''
      this.getUsers()
      this.setRoleDialogVisible = false
    },
    getRolesList() {
      this.$http
        .get('http://localhost/vue/vue_shop/power/getRolesList.php')
        .then(response => {
          const { data: res } = response
          if (res.meta.status !== 200) {
            return this.$message.error('获取角色列表失败')
          }
          this.roleList = res.data
          console.log(this.roleList)
        })
    }
  }
}
</script>

<style lang="css" scoped></style>
