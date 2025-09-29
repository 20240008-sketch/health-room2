<template>
  <div v-if="overlay" :class="overlayClasses">
    <div :class="spinnerContainerClasses">
      <SpinnerIcon :class="spinnerClasses" />
      <p v-if="text" :class="textClasses">{{ text }}</p>
    </div>
  </div>
  <div v-else :class="containerClasses">
    <SpinnerIcon :class="spinnerClasses" />
    <span v-if="text" :class="textClasses">{{ text }}</span>
  </div>
</template>

<script>
import { computed } from 'vue';

// Spinner Icon Component
const SpinnerIcon = {
  template: `
    <svg class="animate-spin" fill="none" viewBox="0 0 24 24">
      <circle 
        class="opacity-25" 
        cx="12" 
        cy="12" 
        r="10" 
        stroke="currentColor" 
        stroke-width="4"
      />
      <path 
        class="opacity-75" 
        fill="currentColor" 
        d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"
      />
    </svg>
  `
};

export default {
  name: 'BaseSpinner',
  components: {
    SpinnerIcon
  },
  props: {
    // Spinner sizes
    size: {
      type: String,
      default: 'md',
      validator: (value) => ['xs', 'sm', 'md', 'lg', 'xl'].includes(value)
    },
    
    // Spinner colors
    color: {
      type: String,
      default: 'blue',
      validator: (value) => [
        'blue', 'indigo', 'purple', 'pink', 'red', 'orange', 
        'yellow', 'green', 'teal', 'cyan', 'gray', 'white'
      ].includes(value)
    },
    
    // Text
    text: {
      type: String,
      default: ''
    },
    
    // Display options
    overlay: {
      type: Boolean,
      default: false
    },
    
    center: {
      type: Boolean,
      default: false
    },
    
    // Layout
    vertical: {
      type: Boolean,
      default: false
    }
  },
  
  setup(props) {
    // Size classes for spinner
    const sizeClasses = computed(() => {
      const sizes = {
        xs: 'h-3 w-3',
        sm: 'h-4 w-4',
        md: 'h-6 w-6',
        lg: 'h-8 w-8',
        xl: 'h-12 w-12'
      };
      return sizes[props.size];
    });
    
    // Color classes for spinner
    const colorClasses = computed(() => {
      const colors = {
        blue: 'text-blue-600',
        indigo: 'text-indigo-600',
        purple: 'text-purple-600',
        pink: 'text-pink-600',
        red: 'text-red-600',
        orange: 'text-orange-600',
        yellow: 'text-yellow-600',
        green: 'text-green-600',
        teal: 'text-teal-600',
        cyan: 'text-cyan-600',
        gray: 'text-gray-600',
        white: 'text-white'
      };
      return colors[props.color];
    });
    
    // Combined spinner classes
    const spinnerClasses = computed(() => {
      return [
        sizeClasses.value,
        colorClasses.value
      ].join(' ');
    });
    
    // Text size classes
    const textSizes = computed(() => {
      const sizes = {
        xs: 'text-xs',
        sm: 'text-sm',
        md: 'text-sm',
        lg: 'text-base',
        xl: 'text-lg'
      };
      return sizes[props.size];
    });
    
    // Text classes
    const textClasses = computed(() => {
      return [
        textSizes.value,
        colorClasses.value,
        props.vertical ? 'mt-2' : 'ml-2'
      ].join(' ');
    });
    
    // Container classes (non-overlay)
    const containerClasses = computed(() => {
      const baseClasses = 'inline-flex items-center';
      const layoutClasses = props.vertical ? 'flex-col' : '';
      const centerClasses = props.center ? 'justify-center w-full' : '';
      
      return [
        baseClasses,
        layoutClasses,
        centerClasses
      ].filter(Boolean).join(' ');
    });
    
    // Overlay classes
    const overlayClasses = computed(() => {
      return [
        'fixed inset-0 z-50 flex items-center justify-center',
        'bg-black bg-opacity-50 backdrop-blur-sm'
      ].join(' ');
    });
    
    // Spinner container classes for overlay
    const spinnerContainerClasses = computed(() => {
      const baseClasses = 'flex items-center justify-center p-6 bg-white rounded-lg shadow-lg';
      const layoutClasses = props.vertical ? 'flex-col' : '';
      
      return [
        baseClasses,
        layoutClasses
      ].filter(Boolean).join(' ');
    });
    
    return {
      spinnerClasses,
      textClasses,
      containerClasses,
      overlayClasses,
      spinnerContainerClasses
    };
  }
};
</script>

<style scoped>
@keyframes spin {
  from {
    transform: rotate(0deg);
  }
  to {
    transform: rotate(360deg);
  }
}

.animate-spin {
  animation: spin 1s linear infinite;
}
</style>