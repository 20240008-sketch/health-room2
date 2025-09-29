<template>
  <span :class="badgeClasses">
    <!-- Icon (before text) -->
    <component
      v-if="icon && !iconAfter"
      :is="icon"
      :class="iconClasses"
    />
    
    <!-- Badge content -->
    <slot>{{ text }}</slot>
    
    <!-- Icon (after text) -->
    <component
      v-if="icon && iconAfter"
      :is="icon"
      :class="iconClasses"
    />
    
    <!-- Close button -->
    <button
      v-if="closable"
      @click="handleClose"
      class="ml-1 inline-flex items-center justify-center"
    >
      <CloseIcon class="h-3 w-3 hover:text-current" />
    </button>
  </span>
</template>

<script>
import { computed } from 'vue';

// Close Icon Component
const CloseIcon = {
  template: `
    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
    </svg>
  `
};

export default {
  name: 'BaseBadge',
  components: {
    CloseIcon
  },
  props: {
    // Badge content
    text: {
      type: [String, Number],
      default: ''
    },
    
    // Badge variants
    variant: {
      type: String,
      default: 'primary',
      validator: (value) => [
        'primary', 'secondary', 'success', 'danger', 'warning', 
        'info', 'light', 'dark', 'gray'
      ].includes(value)
    },
    
    // Badge sizes
    size: {
      type: String,
      default: 'md',
      validator: (value) => ['xs', 'sm', 'md', 'lg'].includes(value)
    },
    
    // Badge styles
    outline: {
      type: Boolean,
      default: false
    },
    
    dot: {
      type: Boolean,
      default: false
    },
    
    pill: {
      type: Boolean,
      default: false
    },
    
    // Interactive
    closable: {
      type: Boolean,
      default: false
    },
    
    // Icon
    icon: {
      type: [String, Object],
      default: null
    },
    
    iconAfter: {
      type: Boolean,
      default: false
    }
  },
  
  emits: ['close'],
  
  setup(props, { emit }) {
    // Base classes
    const baseClasses = 'inline-flex items-center font-medium';
    
    // Size classes
    const sizeClasses = computed(() => {
      if (props.dot) {
        const dotSizes = {
          xs: 'h-2 w-2',
          sm: 'h-2.5 w-2.5', 
          md: 'h-3 w-3',
          lg: 'h-4 w-4'
        };
        return dotSizes[props.size];
      }
      
      const textSizes = {
        xs: 'px-2 py-0.5 text-xs',
        sm: 'px-2.5 py-0.5 text-xs',
        md: 'px-3 py-1 text-sm',
        lg: 'px-4 py-1.5 text-base'
      };
      return textSizes[props.size];
    });
    
    // Shape classes
    const shapeClasses = computed(() => {
      if (props.dot) return 'rounded-full';
      if (props.pill) return 'rounded-full';
      return 'rounded-md';
    });
    
    // Variant classes
    const variantClasses = computed(() => {
      const variants = {
        primary: props.outline
          ? 'text-blue-700 bg-blue-50 ring-1 ring-inset ring-blue-700/10'
          : 'text-white bg-blue-600',
        secondary: props.outline
          ? 'text-gray-600 bg-gray-50 ring-1 ring-inset ring-gray-500/10'
          : 'text-white bg-gray-600',
        success: props.outline
          ? 'text-green-700 bg-green-50 ring-1 ring-inset ring-green-600/20'
          : 'text-white bg-green-600',
        danger: props.outline
          ? 'text-red-700 bg-red-50 ring-1 ring-inset ring-red-600/10'
          : 'text-white bg-red-600',
        warning: props.outline
          ? 'text-yellow-800 bg-yellow-50 ring-1 ring-inset ring-yellow-600/20'
          : 'text-yellow-900 bg-yellow-400',
        info: props.outline
          ? 'text-blue-700 bg-blue-50 ring-1 ring-inset ring-blue-700/10'
          : 'text-white bg-blue-500',
        light: props.outline
          ? 'text-gray-600 bg-gray-50 ring-1 ring-inset ring-gray-500/10'
          : 'text-gray-800 bg-gray-100',
        dark: props.outline
          ? 'text-gray-700 bg-white ring-1 ring-inset ring-gray-700/10'
          : 'text-white bg-gray-900',
        gray: props.outline
          ? 'text-gray-600 bg-gray-50 ring-1 ring-inset ring-gray-500/10'
          : 'text-gray-600 bg-gray-100'
      };
      
      return variants[props.variant];
    });
    
    // Combined badge classes
    const badgeClasses = computed(() => {
      return [
        baseClasses,
        sizeClasses.value,
        shapeClasses.value,
        variantClasses.value
      ].join(' ');
    });
    
    // Icon classes
    const iconClasses = computed(() => {
      const iconSizes = {
        xs: 'h-3 w-3',
        sm: 'h-3 w-3',
        md: 'h-4 w-4',
        lg: 'h-5 w-5'
      };
      
      const spacing = props.iconAfter ? 'ml-1' : 'mr-1';
      
      return `${iconSizes[props.size]} ${spacing}`;
    });
    
    // Event handlers
    const handleClose = (event) => {
      event.preventDefault();
      emit('close');
    };
    
    return {
      badgeClasses,
      iconClasses,
      handleClose
    };
  }
};
</script>