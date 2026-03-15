<script setup lang="ts">
import { useAppStore } from '@/stores/appStore';
import type { Services } from '@/ts/services';
import { AppMode, type Config } from '@/ts/types';
import { ElMessage } from 'element-plus';
import { inject, onMounted, reactive, toValue } from 'vue';

const { config: ConfigService } = inject('services') as Services;
const appStore = useAppStore();
const context = reactive<Config>({
  mode: AppMode.Market,
})

const initContext = () => {
  context.mode = appStore.mode;
}
const changeMode = async () => {
  try {
    const response = await ConfigService.changeMode({ mode: context.mode });
    if (!response.success) {
      context.mode = context.mode === AppMode.Market ? AppMode.Shop : AppMode.Market;
      console.error(response);
      throw new Error(response.message);
    }
    appStore.changeMode(context.mode)
  } catch (e) {
    ElMessage.error(`Не удалось изменить режим: ${ e instanceof Error ? e.message : 'Неизвестная ошибка'}`)
  }
}

onMounted(initContext)
</script>
<template>
<div class="section">
    <h2 class="section-header">Главная</h2>
    <div class="section-body gap">
        <section>
            <div class="flex gap">
                <el-text size="large">Режим сайта</el-text>
                <el-switch
                    v-model="context.mode"
                    :activeValue="AppMode.Market"
                    :inactiveValue="AppMode.Shop"
                    activeText="Маркетплейс"
                    inactiveText="Магазин"
                    @click="changeMode"
                />
            </div>
        </section>
        <section></section>
    </div>
</div>
</template>
<style scoped>
.section-body {
  display: grid;
  grid-template-columns: 1fr 1fr;
}
</style>