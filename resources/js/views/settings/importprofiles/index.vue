<template>
  <div style="padding:30px;">
    <el-alert :closable="false" title="Import Profiles" type="success">
      <router-view />
    </el-alert>
  </div>
</template>

<style>
.el-alert__content{
width: 100%;
}
</style>
