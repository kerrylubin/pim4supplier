<template>
  <el-card class="box-card user-bio">
    <div slot="header" class="clearfix" />
    <div class="user-education user-bio-section">
      <div class="user-bio-section-header">
        <svg-icon icon-class="education" />
      </div>
      <div class="user-bio-section-body">
        <div class="text-muted" />
      </div>
    </div>
    <div class="user-skills user-bio-section">
      <div class="user-bio-section-header">
        <svg-icon icon-class="skill" />
      </div>
      <div class="user-bio-section-body" />
    </div>
  </el-card>
</template>

<script>
export default {
};
</script>

<style lang="scss" scoped>
.user-bio {
  margin-top: 20px;
  color: #606266;
  span {
    padding-left: 4px;
  }
  .user-bio-section {
    font-size: 14px;
    padding: 15px 0;
    .user-bio-section-header {
      border-bottom: 1px solid #dfe6ec;
      padding-bottom: 10px;
      margin-bottom: 10px;
      font-weight: bold;
    }
  }
}
</style>
