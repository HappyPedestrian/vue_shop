<template>
  <div>
    <el-breadcrumb separator-class="el-icon-arrow-right">
      <el-breadcrumb-item :to="{ path: '/home' }">首页</el-breadcrumb-item>
      <el-breadcrumb-item>权限管理</el-breadcrumb-item>
      <el-breadcrumb-item>角色列表</el-breadcrumb-item>
    </el-breadcrumb>

    <!-- 卡片区域 -->
    <el-card>
      <el-row>
        <el-col>
          <el-button type="primary" @click="showAddRoleDialog()"
            >添加角色</el-button
          >
        </el-col>
      </el-row>

      <el-table :data="rolesList">
        <!-- 展开列 -->
        <el-table-column type="expand">
          <template slot-scope="scope">
            <el-row
              :class="['bdbottom', index1 === 0 ? 'bdtop' : '', 'vcenter']"
              v-for="(item1, index1) in scope.row.children"
              :key="'first' + index1"
            >
              <!-- 一级权限 -->
              <el-col :span="5">
                <el-tag
                  closable
                  @close="removeRightById(scope.row, item1.id)"
                  >{{ item1.authName }}</el-tag
                >
                <i class="el-icon-caret-right"></i>
              </el-col>
              <!-- 二级权限 -->
              <el-col :span="19">
                <el-row
                  :class="[index2 === 0 ? '' : 'bdtop', 'vcenter']"
                  v-for="(item2, index2) in item1.children"
                  :key="'second' + index2"
                >
                  <el-col :span="6">
                    <el-tag
                      type="success"
                      closable
                      @close="removeRightById(scope.row, item2.id)"
                      >{{ item2.authName }}</el-tag
                    >
                    <i class="el-icon-caret-right"></i>
                  </el-col>
                  <!-- 三级权限 -->
                  <el-col :span="13">
                    <el-tag
                      type="danger"
                      v-for="(item3, index3) in item2.children"
                      :key="'third' + index3"
                      closable
                      @close="removeRightById(scope.row, item3.id)"
                      >{{ item3.authName }}</el-tag
                    >
                  </el-col>
                </el-row>
              </el-col>
            </el-row>
          </template>
        </el-table-column>
        <!-- 索引列 -->
        <el-table-column type="index"></el-table-column>
        <el-table-column prop="roleName" label="角色名称"></el-table-column>
        <el-table-column prop="roleDesc" label="角色描述"></el-table-column>
        <el-table-column label="操作" width="300px">
          <template slot-scope="scope">
            <el-button
              size="mini"
              type="primary"
              icon="el-icon-edit"
              @click="showEditDialog(scope.row)"
              >编辑</el-button
            >
            <el-button
              size="mini"
              type="danger"
              icon="el-icon-delete"
              @click="deleteRole(scope.row.id)"
              >删除</el-button
            >
            <el-button
              size="mini"
              type="warning"
              icon="el-icon-setting"
              @click="showSetRightDialog(scope.row)"
              >分配权限</el-button
            >
          </template>
        </el-table-column>
      </el-table>
    </el-card>
    <!-- 添加角色对话框 -->
    <el-dialog
      title="添加角色权限"
      :visible.sync="addRoleDialogVisible"
      width="70%"
    >
      <el-form
        :model="addForm"
        :rules="addFormRules"
        ref="addFormRef"
        label-width="90px"
      >
        <el-form-item label="角色名" prop="roleName">
          <el-input v-model="addForm.roleName"></el-input>
        </el-form-item>
        <el-form-item label="角色描述" prop="roleDesc">
          <el-input v-model="addForm.roleDesc"></el-input>
        </el-form-item>
      </el-form>
      <el-tree
        :data="rightsList"
        :props="treeProps"
        show-checkbox
        node-key="id"
        default-expand-all
        ref="treeRef"
      ></el-tree>

      <el-button @click="addRoleDialogVisible = false">取消</el-button>
      <el-button type="primary" @click="addRole()">确定</el-button>
    </el-dialog>
    <!-- 编辑角色对话框 -->
    <el-dialog title="编辑角色" :visible.sync="editDialogVisible" width="50%">
      <el-form
        :model="editForm"
        :rules="editFormRules"
        ref="editFormRef"
        label-width="90px"
      >
        <el-form-item label="角色名" prop="role_name">
          <el-input v-model="editForm.role_name"></el-input>
        </el-form-item>
        <el-form-item label="角色描述" prop="role_des">
          <el-input v-model="editForm.role_des"></el-input>
        </el-form-item>
      </el-form>
      <el-button @click="setRightDialogVisible = false">取消</el-button>
      <el-button type="primary" @click="updateRoleInfo">确定</el-button>
    </el-dialog>

    <!-- 分配权限对话框 -->
    <el-dialog
      title="分配权限"
      :visible.sync="setRightDialogVisible"
      width="50%"
    >
      <el-tree
        :data="rightsList"
        :props="treeProps"
        show-checkbox
        node-key="id"
        default-expand-all
        :default-checked-keys="defKeys"
        ref="treeRef"
      ></el-tree>

      <el-button @click="setRightDialogVisible = false">取消</el-button>
      <el-button type="primary" @click="updateRoleRights">确定</el-button>
    </el-dialog>
  </div>
</template>

<script>
export default {
  data() {
    return {
      rolesList: [],
      setRightDialogVisible: false,
      rightsList: [],
      treeProps: {
        label: 'authName',
        children: 'children'
      },
      defKeys: [],
      roleId: '',
      editDialogVisible: false,
      editForm: {
        role_id: 0,
        role_name: '',
        role_des: ''
      },
      editFormRules: {
        role_name: [
          { required: true, message: '请输入角色名', trigger: 'blur' },
          { min: 2, max: 10, message: '请输入3到10个字符', trigger: 'blur' }
        ],
        role_des: [
          { required: true, message: '请输入角色描述', trigger: 'blur' }
        ]
      },
      addRoleDialogVisible: false,
      addForm: {
        roleName: '',
        roleDesc: '',
        rightKeys: []
      },
      addFormRules: {
        roleName: [
          { required: true, message: '请输入角色名', trigger: 'blur' },
          { min: 2, max: 10, message: '请输入3到10个字符', trigger: 'blur' }
        ],
        roleDesc: [
          { required: true, message: '请输入角色描述', trigger: 'blur' }
        ]
      }
    }
  },
  created() {
    this.getRolesList()
  },
  methods: {
    getRolesList() {
      this.$http
        .get('http://localhost/vue/vue_shop/power/getRolesList.php')
        .then(data => {
          const { data: res } = data
          console.log(res)
          if (res.meta.status !== 200) {
            return this.$message.error(res.meta.message)
          }
          this.rolesList = res.data
        })
    },
    async removeRightById(role, rightId) {
      const confirmResult = await this.$confirm('是否删除该权限？', '提示', {
        confirmButtonText: '确认',
        cancelButtonText: '取消',
        type: 'warning'
      }).catch(err => err)

      if (confirmResult !== 'confirm') {
        return this.$message.info('取消了删除')
      }
      const { data: res } = await this.$http.post(
        'http://localhost/vue/vue_shop/power/deleteRightById.php',
        {
          roleId: role.id,
          rightId: rightId
        }
      )
      console.log(res)
      if (res.meta.status !== 200) {
        return this.$message.error('删除失败')
      }
      role.children = res.data
      return this.$message.success('删除成功')
    },
    async showSetRightDialog(role) {
      this.roleId = role.id
      this.defKeys = []
      const { data: res } = await this.$http.get(
        'http://localhost/vue/vue_shop/power/getRightsList.php',
        {
          params: {
            type: 'tree'
          }
        }
      )
      if (res.meta.status !== 200) {
        return this.$message.error(res.meta.message)
      }
      this.rightsList = res.data
      this.getLeafKeys(role, this.defKeys)
      this.setRightDialogVisible = true
    },
    getLeafKeys(node, arr) {
      if (!node.children) {
        return arr.push(node.id)
      }
      node.children.forEach(item => this.getLeafKeys(item, arr))
    },
    async updateRoleRights() {
      let rightKeys = [
        ...this.$refs.treeRef.getHalfCheckedKeys(),
        ...this.$refs.treeRef.getCheckedKeys()
      ]
      const { data: res } = await this.$http.post(
        'http://localhost/vue/vue_shop/power/updateRoleRights.php',
        {
          roleId: this.roleId,
          rightKeys: rightKeys
        }
      )
      if (res.meta.status !== 200) {
        return this.$$message.error(res.meta.message)
      }
      this.getRolesList()
      this.setRightDialogVisible = false
      return this.$message.success('更改角色权限成功')
    },
    showEditDialog(role) {
      this.editForm.role_id = role.id
      this.editForm.role_name = role.roleName
      this.editForm.role_des = role.roleDesc
      this.editDialogVisible = true
    },
    async updateRoleInfo() {
        const { data: res } = await this.$http.post(
        'http://localhost/vue/vue_shop/power/updateRoleInfo.php',
        this.editForm
      )
      console.log(res)
      if (res.meta.status !== 200) {
        return this.$message.error(res.meta.message)
      }
      this.editDialogVisible = false
      this.getRolesList()
      this.$message.success(res.meta.message)
    },
    async deleteRole(roleId) {
        // 弹窗询问用户是否删除用户
      let confirmResult = await this.$confirm('是否删除该角色?', '提示', {
        confirmButtonText: '确定',
        cancelButtonText: '取消',
        type: 'warning'
      }).catch(error => error)
      if (confirmResult !== 'confirm') {
        return this.$message.info('取消了删除')
      }
      this.$http
        .post('http://localhost/vue/vue_shop/power/deleteRoleById.php', {
          role_id: roleId
        })
        .then(data => {
          let { data: res } = data
          console.log(res)
          if (res.meta.status !== 200) {
            return this.$message.error(res.meta.message)
          }
          this.getRolesList()
          this.$message.success('删除用户成功')
        })
    },
    async showAddRoleDialog() {
      const { data: res } = await this.$http.get(
        'http://localhost/vue/vue_shop/power/getRightsList.php',
        {
          params: {
            type: 'tree'
          }
        }
      )
      if (res.meta.status !== 200) {
        return this.$message.error(res.meta.message)
      }
      this.rightsList = res.data
      this.addRoleDialogVisible = true
    },
    addRole() {
      this.$refs.addFormRef.validate(async valid => {
        if (!valid || this.addForm.rightKeys.length === 0) {
          return this.$message.error('请填写必要数据！')
        } else {
              let rightKeys = [
            ...this.$refs.treeRef.getHalfCheckedKeys(),
            ...this.$refs.treeRef.getCheckedKeys()
          ]
          this.addForm.rightKeys = rightKeys
          const { data: res } = await this.$http.post(
            'http://localhost/vue/vue_shop/power/addRole.php',
            this.addForm
          )
          if (res.meta.status !== 200) {
            return this.$$message.error(res.meta.message)
          }
          this.getRolesList()
          this.setRightDialogVisible = false
          this.$message.success('添加角色成功')
          this.addRoleDialogVisible = false
        }
      })
    }
  }
}
</script>

<style lang="css" scoped>
.el-tag {
  margin: 7px;
}

.bdtop {
  border-top: 1px dotted rgb(33, 33, 33);
}

.bdbottom {
  border-bottom: 1px dotted rgb(33, 33, 33);
}

.vcenter {
  display: flex;
  align-items: center;
}
</style>
