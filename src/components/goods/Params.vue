<template>
    <div>
        <!-- 面包屑导航区域 -->
        <el-breadcrumb separator-class="el-icon-arrow-right">
            <el-breadcrumb-item :to="{ path: '/home' }">首页</el-breadcrumb-item>
            <el-breadcrumb-item>商品管理</el-breadcrumb-item>
            <el-breadcrumb-item>商品参数</el-breadcrumb-item>
        </el-breadcrumb>

        <!-- 卡片视图区 -->
        <el-card>
            <!-- 警告区 -->
            <el-alert title="注意，只允许为特定的商品设置相关参数！" type="warning" :closable="false" show-icon></el-alert>

            <!-- 选择商品分类区域 -->
            <el-row class="cat_opt">
                <el-col>
                    <span>选择商品分类</span>
                    <!-- 选择商品分类的级联选择框 -->
                    <el-cascader :options="cateList"
                        :props="cascaderProps" v-model="selectedIds" @change="cateChanged"
                        clearable ></el-cascader>
                </el-col>
            </el-row>

            <!-- 标签区 -->
                <el-tabs v-model="activeName" @tab-click='handleTabClick'>
                    <!-- 添加动态参数面板 -->
                    <el-tab-pane label="动态参数" name="many">
                        <!-- 添加动态参数按钮 -->
                        <el-button type="primary" size="mini" :disabled="isBtnDisable" @click="addDialogVisible = true">添加参数</el-button>

                        <!-- 动态参数表格 -->
                        <el-table :data="manyTableData" border stripe>
                            <!-- 展开行 -->
                            <el-table-column type="expand">
                                <template slot-scope="scope">
                                    <div>
                                        <!-- 循环渲染tag -->
                                        <el-tag v-for="(item,i) in scope.row.attr_values" :key="i" closable @close="handleClose(i,scope.row)">{{item}}</el-tag>
                                        <!-- 输入框 -->
                                        <el-input ref="saveTagInput" class="input_new_tag" v-if="scope.row.inputVisible"
                                        v-model="scope.row.inputValue"
                                        size="small" @keyup.enter.native="handleInputConfirm(scope.row)" @blur="handleInputConfirm(scope.row)"></el-input>
                                        <!-- 添加的按钮 -->
                                        <el-button v-else size="small" @click="showInput(scope.row)">▲ New Tag</el-button>
                                    </div>
                                </template>
                            </el-table-column>
                            <!-- 索引列 -->
                            <el-table-column type="index"></el-table-column>
                            <el-table-column label="参数名称" prop="attr_name"></el-table-column>
                            <el-table-column label="操作">
                                <template slot-scope="scope">
                                    <div>
                                        <el-button type="primary" size="mini" icon="el-icon-edit" @click="showEditDialog(scope.row.attr_id)">编辑</el-button>
                                        <el-button type="danger" size="mini" icon="el-icon-delete"
                                        @click="removeParams(scope.row.attr_id)">删除</el-button>
                                    </div>
                                </template>
                            </el-table-column>
                        </el-table>
                    </el-tab-pane>

                    <!-- 添加静态属性面板 -->
                    <el-tab-pane label="静态属性" name="only">
                        <!-- 添加静态属性按钮 -->
                        <el-button type="primary" size="mini" :disabled="isBtnDisable" @click="addDialogVisible = true">添加属性</el-button>

                        <!-- 静态属性表格 -->
                        <el-table :data="onlyTableData" border stripe>
                            <!-- 展开行 -->
                            <el-table-column type="expand">
                                <template slot-scope="scope">
                                    <div>
                                        <!-- 循环渲染tag -->
                                        <el-tag v-for="(item,i) in scope.row.attr_values" :key="i" closable @close="handleClose(i,scope.row)">{{item}}</el-tag>
                                        <!-- 输入框 -->
                                        <el-input ref="saveTagInput" class="input_new_tag" v-if="scope.row.inputVisible"
                                        v-model="scope.row.inputValue"
                                        size="small" @keyup.enter.native="handleInputConfirm(scope.row)" @blur="handleInputConfirm(scope.row)"></el-input>
                                        <!-- 添加的按钮 -->
                                        <el-button type="primary" v-else size="small" @click="showInput(scope.row)">change or add</el-button>
                                    </div>
                                </template>
                            </el-table-column>
                            <!-- 索引列 -->
                            <el-table-column type="index"></el-table-column>
                            <el-table-column label="参数名称" prop="attr_name"></el-table-column>
                            <el-table-column label="操作">
                                <template slot-scope="scope">
                                        <el-button type="primary" size="mini" icon="el-icon-edit" @click="showEditDialog(scope.row.attr_id)">编辑</el-button>
                                        <el-button type="danger" size="mini" icon="el-icon-delete" @click="removeParams(scope.row.attr_id)">删除</el-button>
                                </template>
                            </el-table-column>
                        </el-table>
                    </el-tab-pane>
                </el-tabs>
        </el-card>
        <!-- 添加对话框 -->
        <el-dialog :title="'添加'+titleText" :visible="addDialogVisible" width="50%" @close="addDialogClosed">

            <!-- 添加表单 -->
            <el-form :model="addForm" :rules="addFormRules"
            ref="addFormRef" label-width="100px">
                <el-form-item :label="titleText" prop="attr_name">
                    <el-input v-model="addForm.attr_name"></el-input>
                </el-form-item>
            </el-form>
            <el-button type="primary" @click="addParams()">确定</el-button>
            <el-button  @click="addDialogVisible = false">取消</el-button></el-dialog>

            <!-- 编辑对话框 -->
        <el-dialog :title="'编辑'+titleText" :visible="editDialogVisible" width="50%" @close="editDialogClosed">

            <!-- 编辑表单 -->
            <el-form :model="editForm" :rules="editFormRules"
            ref="editFormRef" label-width="100px">
                <el-form-item :label="titleText" prop="attr_name">
                    <el-input v-model="editForm.attr_name"></el-input>
                </el-form-item>
            </el-form>
            <el-button type="primary" @click="editParams()">确定</el-button>
            <el-button  @click="editDialogVisible = false">取消</el-button>
        </el-dialog>
    </div>
</template>

<script>
export default {
    data() {
        return {
            // 分类列表
            cateList: [],
            // 级联选择框双向绑定到的id值
            selectedIds: [],
            cascaderProps: {
                label: 'cate_name',
                value: 'cate_id',
                children: 'children',
                expandTrigger: 'hover'
            },
            activeName: 'many',
            manyTableData: [],
            onlyTableData: [],
            addDialogVisible: false,
            addForm: {
                attr_name: ''
            },
            addFormRules: {
                attr_name: [
                    {
                        required: true,
                        message: '请输入参数名称',
                        trigger: 'blur'
                    }
                ]
            },
            editDialogVisible: false,
            editForm: {
                attr_name: ''
            },
            editFormRules: {
                attr_name: [
                    {
                        required: true,
                        message: '请输入参数名称',
                        trigger: 'blur'
                    }
                ]
            }
        }
    },

    created() {
        this.getCateList()
    },

    methods: {
        getCateList() {
            this.$http.get('http://localhost/vue/vue_shop/cate/getCateList.php', { params: { type: 3, getGoods: true } })
                .then(response => {
                    const { data: res } = response
                    console.log(res)
                    if (res.meta.status !== 200) {
                        return this.$message.error(res.meta.message)
                    }
                    this.cateList = res.data.result
                })
        },
        cateChanged() {
            this.getParams()
        },
        handleTabClick() {
            this.getParams()
        },
        // 获取分类参数
        async getParams() {
            // 若选中的不是三级分类
            if (this.selectedIds.length !== 4) {
                this.selectedIds = []
                this.manyTableData = []
                this.onlyTableData = []
                return
            }
            // 若选中确定的商品
            const { data: res } = await this.$http.get('http://localhost/vue/vue_shop/goods/getGoodsAttrs.php', { params: { cateId: this.cateId, goodId: this.goodId, sel: this.activeName } })

            console.log(res)
            if (res.meta.status !== 200) {
                return this.$message.error(res.meta.message)
            }

            res.data.forEach(item => {
                item.attr_values = item.attr_values ? item.attr_values.split(' ') : []
                // 添加控制相应的input框的显示与隐藏
                item.inputVisible = false
                // 输入框的值
                item.inputValue = ''
            })
            console.log(res)
            if (this.activeName === 'many') {
                this.manyTableData = res.data
            } else {
                this.onlyTableData = res.data
            }
            console.log(res.data)
        },

        addDialogClosed() {
            this.$refs.addFormRef.resetFields()
        },
        // 点击添加参数
        addParams() {
            this.$refs.addFormRef.validate(async valid => {
                if (!valid) return
                const { data: res } = await this.$http.post('http://localhost/vue/vue_shop/cate/addParams.php', {
                    attr_name: this.addForm.attr_name,
                    attr_sel: this.activeName,
                    cate_id: this.cateId
                })
                console.log(res)
                if (res.meta.status !== 200) {
                    return this.$message.error(res.meta.message)
                }
                this.$message.success('添加参数成功')
                this.addDialogVisible = false
                this.getParams()
            })
        },
        // 显示修改对话框
        async showEditDialog(attrId) {
            console.log(attrId)
            const { data: res } = await this.$http.get('http://localhost/vue/vue_shop/cate/getParams.php', {
                params: {
                    attr_id: attrId
                }
            })
            console.log(res)
            if (res.meta.status !== 200) {
                return this.$message.error(res.meta.message)
            }
            this.editForm = res.data
            this.editDialogVisible = true
        },
        // 关闭修改对话框重置表单
        editDialogClosed() {
            this.$refs.editFormRef.resetFields()
            this.editDialogVisible = false
        },
        // 点击修改参数
        editParams() {
            this.$refs.editFormRef.validate(async valid => {
                if (!valid) return
                const { data: res } = await this.$http.post('http://localhost/vue/vue_shop/cate/updateParams.php', {
                attr_id: this.editForm.attr_id,
                attr_name: this.editForm.attr_name
            })
            console.log(res)
            if (res.meta.status !== 200) {
                return this.$message.error(res.meta.message)
            }
            this.$message.success('修改参数成功！')
            this.getParams()
            this.editDialogVisible = false
            })
        },
        async removeParams(attrId) {
            const confirmResult = await this.$confirm('是否删除该参数？(此操作会删除所有该分类下的此属性！)', '提示', {
                confirmButtonText: '确认',
                cancelButtonText: '取消',
                type: 'warning'
            }).catch(error => error)
            // 当用户取消了该操作
            if (confirmResult !== 'confirm') {
                return this.$message.info('您取消了删除操作')
            }
            const { data: res } = await this.$http.post('http://localhost/vue/vue_shop/cate/deleteParams.php', {
                attr_id: attrId
            })
            if (res.meta.status !== 200) {
                return this.$message.success('删除成功！')
            }
            this.getParams()
            this.editDialogVisible = false
        },
        // 文本框失去焦点或按下了enter键
        handleInputConfirm(row) {
            if (row.inputValue.trim().length === 0) {
                row.inputValue = ''
                row.inputVisible = false
                return
            }
            // 如果输入合法执行的操作
            if (this.activeName === 'only') {
                row.attr_values = []
            }
            row.attr_values.push(row.inputValue)
            row.inputVisible = false
            row.inputValue = ''
            this.savaAttrValue(row)
        },
        showInput(row) {
            row.inputVisible = true
            // 让文本框自动获取焦点
            this.$nextTick(_ => {
                this.$refs.saveTagInput.$refs.input.focus()
            })
        },
        handleClose(i, row) {
            row.attr_values.splice(i, 1)
            this.savaAttrValue(row)
        },
        // 将对attr_values的操作保存到数据库
        async savaAttrValue(row) {
            // 向服务器发送添加请求
            const { data: res } = await this.$http.post('http://localhost/vue/vue_shop/goods/updateParams.php', {
                attr_id: row.attr_id,
                goods_id: row.goods_id,
                attr_name: row.attr_name,
                attr_values: row.attr_values.join(' ')
            })
            console.log(res)

            if (res.meta.status !== 200) {
                return this.$message.error(res.meta.message)
            }
            return this.$message.success('保存成功！')
        }
    },
    computed: {
        // 控制按钮是否禁用，不可用返回true 可以返回false
        isBtnDisable() {
            return this.selectedIds.length !== 4
        },
        cateId() {
            if (this.selectedIds.length === 4) {
                return this.selectedIds[2]
            }
            return null
        },
        goodId() {
            if (this.selectedIds.length === 4) {
                return this.selectedIds[3]
            }
            return null
        },
        // 动态计算标题的文本
        titleText() {
            if (this.activeName === 'many') {
                return '动态参数'
            } else {
                return '静态属性'
            }
        }
    }
}
</script>

<style lang="css" scoped>
.cat_opt{
    margin: 10px;
}

.el-tag{
    margin: 10px;
}

.input_new_tag{
    width: 130px;
}

.el-cascader {
    width: 400px;
    margin-left: 10px;
}
</style>
