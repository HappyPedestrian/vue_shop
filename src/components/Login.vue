<template>
    <div class="login_container">
        <div class='login_box'>
            <!-- 头部头像 -->
            <div class="login_avater"><img src="../assets/logo.png" alt=""></div>
            <!-- 输入表单 -->
            <el-form ref="loginForm" :model="loginForm" :rules="rules" babel='0px' class="login_form">
                <!-- 用户名登录 -->
                <el-form-item prop="username">
                    <el-input v-model="loginForm.username" prefix-icon="iconfont icon-yonghu" placeholder="admin"></el-input>
                </el-form-item>
                <!-- 密码输入 -->
                <el-form-item prop="password">
                    <el-input v-model="loginForm.password" prefix-icon="iconfont icon-suo" placeholder="password" type="password"></el-input>
                </el-form-item>
                <el-form-item class="btns">
                   <el-button type="primary" @click="submit">登录</el-button><el-button @click="reset" type="info">重置</el-button>
                </el-form-item>
            </el-form>
        </div>
    </div>
</template>

<script>
export default {
    data () {
        return {
            loginForm: {
                username: 'admin',
                password: '123456'
            },
            rules: {
                username: [
                    { required: true, message: '请输入用户名！', trigger: 'blur' },
                    { min: 3, max: 10, message: '请输入 3 到 10 个字符！', trigger: 'blur' }
                ],
                password: [
                    { required: true, message: '请输入密码!', trigger: 'blur' },
                    { min: 6, max: 15, message: '请输入 6 到 15 个字符！', trigger: 'blur' }
                ]
            }
        }
    },
    methods: {
        submit () {
            this.$refs.loginForm.validate((valid) => {
                if (!valid) {
                    this.$message.error('登录失败！')
                    return
                }
                this.$http.post('http://localhost/vue/vue_shop/login.php', this.loginForm)
                 .then(response => {
                    //  console.log(response)
                     const { data: res } = response
                     console.log(res)
                     if (res.meta.status !== 200) return this.$message.error('账号或密码错误！')

                    window.sessionStorage.setItem('token', res.data.token)
                    this.$message.success('登录成功！')
                    this.$router.push('/home')
                 }).catch()
                // window.sessionStorage.setItem('token', 'asdfghjkl')
                // this.$message.success('登录成功！')
                // this.$router.push('/home')
            })
        },
        reset () {
            this.$refs.loginForm.resetFields()
        }
    }
}
</script>

<style lang="css" scope="this api replaced by slot-scope in 2.5.0+">
    .login_container{
        width: 100%;
        height: 100%;
        background-color: #2b3b4b;
    }
    .login_box{
        width: 450px;
        height: 300px;
        position: absolute;
        left: 50%;
        top: 50%;
        transform: translate(-50%,-50%);
        border-radius: 3px;
        background-color: #fff;
    }
    .login_avater{
        height: 150px;
        width: 150px;
        padding: 10px;
        position: absolute;
        left: 50%;
        transform: translate(-50%,-50%);
        background-color: #eee;
        border-radius: 50%;
        border: 1px solid  #111;
        box-shadow: 0 0 10px #fff;
    }
    .login_avater img{
        width: 100%;
        height: 100%;
        border-radius: 50%;
        background-color: #aaa;
    }
    .login_form{
        width: 100%;
        padding: 20px;
        position: absolute;
        bottom: 0;
        box-sizing: border-box;
    }
    .btns{
        display: flex;
        justify-content: flex-end;
    }
</style>
