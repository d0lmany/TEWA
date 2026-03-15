import { AppMode, type PublicConfig } from "@/ts/types";
import { defineStore } from "pinia";

export const useAppStore = defineStore('app', {
  state: (): PublicConfig => ({
    mode: AppMode.Market,
  }),
  actions: {
    setConfig(config: PublicConfig) {
      this.mode = config.mode;
    },
    changeMode(mode: AppMode) {
      this.mode = mode;
    },
  },
  getters: {
    config: state => state
  }
});
