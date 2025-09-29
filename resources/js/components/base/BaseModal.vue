<template>
  <Teleport to="body">
    <Transition
      name="modal"
      enter-active-class="duration-300 ease-out"
      enter-from-class="opacity-0"
      enter-to-class="opacity-100"
      leave-active-class="duration-200 ease-in"
      leave-from-class="opacity-100"
      leave-to-class="opacity-0"
    >
      <div
        v-if="show"
        class="fixed inset-0 z-50 overflow-y-auto"
        @keydown.esc="handleEscape"
      >
        <!-- Backdrop -->
        <div
          class="fixed inset-0 bg-black bg-opacity-50 transition-opacity"
          @click="handleBackdropClick"
        />
        
        <!-- Modal container -->
        <div class="flex min-h-full items-center justify-center p-4 text-center sm:p-0">
          <Transition
            name="modal-content"
            enter-active-class="duration-300 ease-out"
            enter-from-class="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
            enter-to-class="opacity-100 translate-y-0 sm:scale-100"
            leave-active-class="duration-200 ease-in"
            leave-from-class="opacity-100 translate-y-0 sm:scale-100"
            leave-to-class="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
          >
            <div
              v-if="show"
              :class="modalClasses"
              class="relative transform overflow-hidden rounded-lg bg-white text-left shadow-xl transition-all"
            >
              <!-- Header -->
              <div
                v-if="$slots.header || title || showCloseButton"
                class="flex items-center justify-between p-6 border-b border-gray-200"
              >
                <div class="flex-1">
                  <slot name="header">
                    <h3 v-if="title" class="text-lg font-medium text-gray-900">
                      {{ title }}
                    </h3>
                  </slot>
                </div>
                
                <button
                  v-if="showCloseButton"
                  @click="close"
                  class="ml-4 text-gray-400 hover:text-gray-600 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 rounded-sm"
                >
                  <span class="sr-only">閉じる</span>
                  <CloseIcon class="h-6 w-6" />
                </button>
              </div>

              <!-- Body -->
              <div :class="bodyClasses">
                <slot />
              </div>

              <!-- Footer -->
              <div
                v-if="$slots.footer || showDefaultFooter"
                class="flex justify-end space-x-3 p-6 bg-gray-50 border-t border-gray-200"
              >
                <slot name="footer">
                  <BaseButton
                    v-if="showDefaultFooter"
                    variant="secondary"
                    @click="close"
                  >
                    キャンセル
                  </BaseButton>
                  <BaseButton
                    v-if="showDefaultFooter"
                    variant="primary"
                    :loading="loading"
                    @click="confirm"
                  >
                    {{ confirmText }}
                  </BaseButton>
                </slot>
              </div>
            </div>
          </Transition>
        </div>
      </div>
    </Transition>
  </Teleport>
</template>

<script>
import { computed, onMounted, onUnmounted, nextTick, watch } from 'vue';
import BaseButton from './BaseButton.vue';

// Close Icon Component
const CloseIcon = {
  template: `
    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
    </svg>
  `
};

export default {
  name: 'BaseModal',
  components: {
    BaseButton,
    CloseIcon
  },
  props: {
    show: {
      type: Boolean,
      default: false
    },
    
    title: {
      type: String,
      default: ''
    },
    
    size: {
      type: String,
      default: 'md',
      validator: (value) => ['xs', 'sm', 'md', 'lg', 'xl', 'full'].includes(value)
    },
    
    closable: {
      type: Boolean,
      default: true
    },
    
    showCloseButton: {
      type: Boolean,
      default: true
    },
    
    showDefaultFooter: {
      type: Boolean,
      default: false
    },
    
    confirmText: {
      type: String,
      default: 'OK'
    },
    
    loading: {
      type: Boolean,
      default: false
    },
    
    closeOnBackdrop: {
      type: Boolean,
      default: true
    },
    
    closeOnEscape: {
      type: Boolean,
      default: true
    },
    
    persistent: {
      type: Boolean,
      default: false
    },
    
    bodyPadding: {
      type: Boolean,
      default: true
    }
  },
  
  emits: ['close', 'confirm', 'update:show'],
  
  setup(props, { emit }) {
    // Size classes
    const modalSizes = {
      xs: 'sm:max-w-xs',
      sm: 'sm:max-w-sm',
      md: 'sm:max-w-md',
      lg: 'sm:max-w-lg',
      xl: 'sm:max-w-xl sm:w-full',
      full: 'sm:max-w-none sm:m-4'
    };
    
    const modalClasses = computed(() => {
      const baseClasses = 'w-full sm:my-8 sm:align-middle';
      const sizeClasses = modalSizes[props.size];
      return `${baseClasses} ${sizeClasses}`;
    });
    
    const bodyClasses = computed(() => {
      const baseClasses = '';
      const paddingClasses = props.bodyPadding ? 'p-6' : '';
      return `${baseClasses} ${paddingClasses}`;
    });
    
    // Event handlers
    const close = () => {
      if (props.persistent) return;
      emit('update:show', false);
      emit('close');
    };
    
    const confirm = () => {
      emit('confirm');
    };
    
    const handleBackdropClick = () => {
      if (props.closeOnBackdrop && props.closable) {
        close();
      }
    };
    
    const handleEscape = (event) => {
      if (props.closeOnEscape && props.closable) {
        event.preventDefault();
        close();
      }
    };
    
    // Body scroll management
    const lockBody = () => {
      document.body.style.overflow = 'hidden';
      document.body.style.paddingRight = `${window.innerWidth - document.documentElement.clientWidth}px`;
    };
    
    const unlockBody = () => {
      document.body.style.overflow = '';
      document.body.style.paddingRight = '';
    };
    
    // Watch show prop to manage body scroll
    watch(() => props.show, (newValue) => {
      if (newValue) {
        nextTick(() => {
          lockBody();
        });
      } else {
        unlockBody();
      }
    });
    
    // Keyboard event listener
    const keydownHandler = (event) => {
      if (event.key === 'Escape' && props.show) {
        handleEscape(event);
      }
    };
    
    onMounted(() => {
      document.addEventListener('keydown', keydownHandler);
      
      // Lock body if modal is initially shown
      if (props.show) {
        lockBody();
      }
    });
    
    onUnmounted(() => {
      document.removeEventListener('keydown', keydownHandler);
      unlockBody();
    });
    
    return {
      modalClasses,
      bodyClasses,
      close,
      confirm,
      handleBackdropClick,
      handleEscape
    };
  }
};
</script>

<style scoped>
/* Additional custom styles if needed */
</style>