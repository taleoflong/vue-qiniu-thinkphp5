<template>
<div id="test">
	<a class="btn" @click="toggleShow">上传图片</a>
	<my-upload url="/upload"
		lang-type="en"
		field="img"
		key="1"
		max-size="500"
		:only-single="true"
		:value="true"
		:only-img="true"
		:params="params"></my-upload>
</div>
</template>

<script>
import mySwitch from 'vue-upload-file';
import { Toast } from 'vant';
export default {
  name:'Test',
  
}
</script>

<style scoped>
#test {
		position: relative; /*控件上级容器必须是相对定位或绝对定位*/
	}
</style>
