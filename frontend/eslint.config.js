import js from "@eslint/js";
import globals from "globals";
import pluginVue from "eslint-plugin-vue";
import json from "@eslint/json";
import markdown from "@eslint/markdown";
import css from "@eslint/css";
import { defineConfig } from "eslint/config";
import typescript from "@typescript-eslint/eslint-plugin";
import typescriptParser from "@typescript-eslint/parser";

export default defineConfig([
  { 
    files: ["**/*.{js,mjs,cjs}"], 
    plugins: { js }, 
    extends: ["js/recommended"], 
    languageOptions: { 
      globals: globals.browser 
    } 
  },
  {
    files: ["**/*.vue"],
    plugins: {
      vue: pluginVue,
      '@typescript-eslint': typescript
    },
    languageOptions: {
      parser: pluginVue.parser,
      parserOptions: {
        parser: typescriptParser,
        ecmaVersion: "latest",
        sourceType: "module"
      },
      globals: globals.browser
    },
    extends: [
      "js/recommended",
      ...pluginVue.configs["flat/essential"],
      ...typescript.configs.recommended
    ]
  },
  {
    files: ["**/*.ts"],
    plugins: {
      '@typescript-eslint': typescript
    },
    languageOptions: {
      parser: typescriptParser,
      globals: globals.browser
    },
    extends: [
      "js/recommended",
      ...typescript.configs.recommended
    ]
  },
  { 
    files: ["**/*.json"], 
    plugins: { json }, 
    language: "json/json", 
    extends: ["json/recommended"] 
  },
  { 
    files: ["**/*.md"], 
    plugins: { markdown }, 
    language: "markdown/gfm", 
    extends: ["markdown/recommended"] 
  },
  { 
    files: ["**/*.css"], 
    plugins: { css }, 
    language: "css/css", 
    extends: ["css/recommended"] 
  },
]);