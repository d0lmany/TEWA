import type { Directive, DirectiveBinding } from 'vue';
import { ElMessage } from 'element-plus';

export const vUnimplemented: Directive = {
    mounted(el: HTMLElement, binding: DirectiveBinding<string>) {
        const clickHandler = (e: Event) => {
            e.preventDefault();
            e.stopPropagation();
            
            ElMessage.warning(`Нереализованная функция: ${binding.value ?? '?NULL?'}`);
            
            console.warn('Unimplemented clicked:', { element: el.tagName });
        };
        
        (el as any)._unimplementedClickHandler = clickHandler;
        el.addEventListener('click', clickHandler, true);
    },
    
    unmounted(el: HTMLElement) {
        const handler = (el as any)._unimplementedClickHandler;
        if (handler) {
            el.removeEventListener('click', handler, true);
            delete (el as any)._unimplementedClickHandler;
        }
    }
};