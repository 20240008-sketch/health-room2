<template>
  <Transition
    name="alert"
    enter-active-class="transition duration-300 ease-out"
    enter-from-class="opacity-0 transform scale-95"
    enter-to-class="opacity-100 transform scale-100"
    leave-active-class="transition duration-200 ease-in"
    leave-from-class="opacity-100 transform scale-100"
    leave-to-class="opacity-0 transform scale-95"
  >
    <div
      v-show="visible"
      :class="alertClasses"
      role="alert"
      :aria-live="variant === 'danger' ? 'assertive' : 'polite'"
    >
      <!-- Icon -->
      <div v-if="showIcon" class="flex-shrink-0">
        <component
          :is="iconComponent"
          :class="iconClasses"
          aria-hidden="true"
        />
      </div>
      
      <!-- Content -->
      <div class="flex-1 min-w-0">
        <!-- Title -->
        <h3 v-if="title" :class="titleClasses">
          {{ title }}
        </h3>
        
        <!-- Message -->
        <div :class="messageClasses">
          <slot>
            <p v-if="message">{{ message }}</p>
          </slot>
        </div>
        
        <!-- Actions -->
        <div v-if="$slots.actions" class="mt-4">
          <div class="flex space-x-3">
            <slot name="actions"></slot>
          </div>
        </div>
      </div>
      
      <!-- Close button -->
      <div v-if="closable" class="flex-shrink-0 ml-4">
        <button
          @click="handleClose"
          :class="closeButtonClasses"
          aria-label="閉じる"
        >
          <CloseIcon class="h-5 w-5" />
        </button>
      </div>
    </div>
  </Transition>
</template>

<script>
import { ref, computed, onMounted } from 'vue';

// Icon Components
const SuccessIcon = {
  template: `
    <svg fill="currentColor" viewBox="0 0 20 20">
      <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
    </svg>
  `
};

const DangerIcon = {
  template: `
    <svg fill="currentColor" viewBox="0 0 20 20">
      <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/>
    </svg>
  `
};

const WarningIcon = {
  template: `
    <svg fill="currentColor" viewBox="0 0 20 20">
      <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
    </svg>
  `
};

const InfoIcon = {
  template: `
    <svg fill="currentColor" viewBox="0 0 20 20">
      <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"/>
    </svg>
  `
};

const CloseIcon = {
  template: `
    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
    </svg>
  `
};

export default {
  name: 'BaseAlert',
  components: {
    SuccessIcon,
    DangerIcon,
    WarningIcon,
    InfoIcon,
    CloseIcon
  },
  props: {
    // Alert content
    title: {
      type: String,
      default: ''
    },
    
    message: {
      type: String,
      default: ''
    },
    
    // Alert variants
    variant: {
      type: String,
      default: 'info',
      validator: (value) => ['success', 'danger', 'warning', 'info'].includes(value)
    },
    
    // Alert behavior
    closable: {
      type: Boolean,
      default: true
    },
    
    autoClose: {
      type: [Boolean, Number],
      default: false
    },
    
    // Icon
    showIcon: {
      type: Boolean,
      default: true
    },
    
    // Initial visibility
    modelValue: {
      type: Boolean,
      default: true
    }
  },
  
  emits: ['update:modelValue', 'close'],
  
  setup(props, { emit }) {
    const visible = ref(props.modelValue);
    
    // Icon component based on variant
    const iconComponent = computed(() => {
      const icons = {
        success: 'SuccessIcon',
        danger: 'DangerIcon',
        warning: 'WarningIcon',
        info: 'InfoIcon'
      };
      return icons[props.variant];
    });
    
    // Base alert classes
    const baseClasses = 'rounded-md p-4 shadow-sm border';
    
    // Variant classes
    const variantClasses = computed(() => {
      const variants = {
        success: 'bg-green-50 border-green-200',
        danger: 'bg-red-50 border-red-200',
        warning: 'bg-yellow-50 border-yellow-200',
        info: 'bg-blue-50 border-blue-200'
      };
      return variants[props.variant];
    });
    
    // Combined alert classes
    const alertClasses = computed(() => {
      return [
        baseClasses,
        variantClasses.value,
        'flex'
      ].join(' ');
    });
    
    // Icon classes
    const iconClasses = computed(() => {
      const iconColors = {
        success: 'text-green-400',
        danger: 'text-red-400',
        warning: 'text-yellow-400',
        info: 'text-blue-400'
      };
      return `h-5 w-5 ${iconColors[props.variant]}`;
    });
    
    // Title classes
    const titleClasses = computed(() => {
      const titleColors = {
        success: 'text-green-800',
        danger: 'text-red-800',
        warning: 'text-yellow-800',
        info: 'text-blue-800'
      };
      return `text-sm font-medium ${titleColors[props.variant]}`;
    });
    
    // Message classes  
    const messageClasses = computed(() => {
      const messageColors = {
        success: 'text-green-700',
        danger: 'text-red-700',
        warning: 'text-yellow-700',
        info: 'text-blue-700'
      };
      const marginTop = props.title ? 'mt-2' : '';
      return `text-sm ${messageColors[props.variant]} ${marginTop}`;
    });
    
    // Close button classes
    const closeButtonClasses = computed(() => {
      const buttonColors = {
        success: 'text-green-400 hover:text-green-500 focus:text-green-500',
        danger: 'text-red-400 hover:text-red-500 focus:text-red-500',
        warning: 'text-yellow-400 hover:text-yellow-500 focus:text-yellow-500',
        info: 'text-blue-400 hover:text-blue-500 focus:text-blue-500'
      };
      return `inline-flex rounded-md p-1.5 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-offset-transparent ${buttonColors[props.variant]}`;
    });
    
    // Event handlers
    const handleClose = () => {
      visible.value = false;
      emit('update:modelValue', false);
      emit('close');
    };
    
    // Auto close functionality
    let autoCloseTimer = null;
    
    const startAutoClose = () => {
      if (props.autoClose && typeof props.autoClose === 'number') {
        autoCloseTimer = setTimeout(handleClose, props.autoClose);
      }
    };
    
    const clearAutoClose = () => {
      if (autoCloseTimer) {
        clearTimeout(autoCloseTimer);
        autoCloseTimer = null;
      }
    };
    
    // Lifecycle
    onMounted(() => {
      if (visible.value && props.autoClose) {
        startAutoClose();
      }
    });
    
    // Watch for visibility changes
    const watchVisible = () => {
      if (visible.value && props.autoClose) {
        startAutoClose();
      } else {
        clearAutoClose();
      }
    };
    
    return {
      visible,
      iconComponent,
      alertClasses,
      iconClasses,
      titleClasses,
      messageClasses,
      closeButtonClasses,
      handleClose,
      watchVisible
    };
  }
};
</script>

<style scoped>
.alert-enter-active,
.alert-leave-active {
  transition: all 0.3s ease;
}

.alert-enter-from {
  opacity: 0;
  transform: translateY(-10px);
}

.alert-leave-to {
  opacity: 0;
  transform: translateY(-10px);
}
</style>