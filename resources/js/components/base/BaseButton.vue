<template>
  <component
    :is="tag"
    :type="buttonType"
    :disabled="disabled || loading"
    :class="buttonClasses"
    @click="handleClick"
  >
    <!-- Loading spinner -->
    <svg
      v-if="loading"
      class="animate-spin -ml-1 mr-2 h-4 w-4"
      fill="none"
      viewBox="0 0 24 24"
    >
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

    <!-- Icon (before text) -->
    <component
      v-if="icon && !iconAfter"
      :is="icon"
      :class="iconClasses"
    />

    <!-- Slot content -->
    <span v-if="$slots.default">
      <slot />
    </span>

    <!-- Icon (after text) -->
    <component
      v-if="icon && iconAfter"
      :is="icon"
      :class="iconClasses"
    />
  </component>
</template>

<script>
import { computed } from 'vue';

export default {
  name: 'BaseButton',
  props: {
    // Button variants
    variant: {
      type: String,
      default: 'primary',
      validator: (value) => [
        'primary', 'secondary', 'success', 'danger', 'warning', 'info', 'light', 'dark'
      ].includes(value)
    },
    
    // Button sizes
    size: {
      type: String,
      default: 'md',
      validator: (value) => ['xs', 'sm', 'md', 'lg', 'xl'].includes(value)
    },
    
    // Button states
    disabled: {
      type: Boolean,
      default: false
    },
    
    loading: {
      type: Boolean,
      default: false
    },
    
    // Button styles
    outline: {
      type: Boolean,
      default: false
    },
    
    rounded: {
      type: Boolean,
      default: false
    },
    
    block: {
      type: Boolean,
      default: false
    },
    
    // HTML attributes
    type: {
      type: String,
      default: 'button',
      validator: (value) => ['button', 'submit', 'reset'].includes(value)
    },
    
    // Link functionality
    to: {
      type: [String, Object],
      default: null
    },
    
    href: {
      type: String,
      default: null
    },
    
    // Icon props
    icon: {
      type: [String, Object],
      default: null
    },
    
    iconAfter: {
      type: Boolean,
      default: false
    }
  },
  
  emits: ['click'],
  
  setup(props, { emit }) {
    // Determine the tag to render
    const tag = computed(() => {
      if (props.to) return 'router-link';
      if (props.href) return 'a';
      return 'button';
    });
    
    // Button type for form buttons
    const buttonType = computed(() => {
      return tag.value === 'button' ? props.type : undefined;
    });
    
    // Base button classes
    const baseClasses = 'inline-flex items-center justify-center font-medium focus:outline-none focus:ring-2 focus:ring-offset-2 transition-colors duration-200';
    
    // Size classes
    const sizeClasses = computed(() => {
      const sizes = {
        xs: 'px-2 py-1 text-xs',
        sm: 'px-3 py-1.5 text-sm',
        md: 'px-4 py-2 text-sm',
        lg: 'px-6 py-3 text-base',
        xl: 'px-8 py-4 text-lg'
      };
      return sizes[props.size];
    });
    
    // Variant classes
    const variantClasses = computed(() => {
      const variants = {
        primary: props.outline
          ? 'border-2 border-blue-600 text-blue-600 hover:bg-blue-600 hover:text-white focus:ring-blue-500'
          : 'bg-blue-600 text-white hover:bg-blue-700 focus:ring-blue-500',
        secondary: props.outline
          ? 'border-2 border-gray-600 text-gray-600 hover:bg-gray-600 hover:text-white focus:ring-gray-500'
          : 'bg-gray-600 text-white hover:bg-gray-700 focus:ring-gray-500',
        success: props.outline
          ? 'border-2 border-green-600 text-green-600 hover:bg-green-600 hover:text-white focus:ring-green-500'
          : 'bg-green-600 text-white hover:bg-green-700 focus:ring-green-500',
        danger: props.outline
          ? 'border-2 border-red-600 text-red-600 hover:bg-red-600 hover:text-white focus:ring-red-500'
          : 'bg-red-600 text-white hover:bg-red-700 focus:ring-red-500',
        warning: props.outline
          ? 'border-2 border-yellow-600 text-yellow-600 hover:bg-yellow-600 hover:text-white focus:ring-yellow-500'
          : 'bg-yellow-600 text-white hover:bg-yellow-700 focus:ring-yellow-500',
        info: props.outline
          ? 'border-2 border-blue-400 text-blue-400 hover:bg-blue-400 hover:text-white focus:ring-blue-400'
          : 'bg-blue-400 text-white hover:bg-blue-500 focus:ring-blue-400',
        light: props.outline
          ? 'border-2 border-gray-300 text-gray-700 hover:bg-gray-300 hover:text-gray-900 focus:ring-gray-300'
          : 'bg-gray-100 text-gray-900 hover:bg-gray-200 focus:ring-gray-300',
        dark: props.outline
          ? 'border-2 border-gray-900 text-gray-900 hover:bg-gray-900 hover:text-white focus:ring-gray-900'
          : 'bg-gray-900 text-white hover:bg-gray-800 focus:ring-gray-900'
      };
      return variants[props.variant];
    });
    
    // Disabled/Loading classes
    const stateClasses = computed(() => {
      if (props.disabled || props.loading) {
        return 'opacity-50 cursor-not-allowed';
      }
      return 'cursor-pointer';
    });
    
    // Shape classes
    const shapeClasses = computed(() => {
      return props.rounded ? 'rounded-full' : 'rounded-md';
    });
    
    // Width classes
    const widthClasses = computed(() => {
      return props.block ? 'w-full' : '';
    });
    
    // Combined button classes
    const buttonClasses = computed(() => {
      return [
        baseClasses,
        sizeClasses.value,
        variantClasses.value,
        stateClasses.value,
        shapeClasses.value,
        widthClasses.value
      ].join(' ');
    });
    
    // Icon classes
    const iconClasses = computed(() => {
      const iconSizes = {
        xs: 'w-3 h-3',
        sm: 'w-4 h-4',
        md: 'w-4 h-4',
        lg: 'w-5 h-5',
        xl: 'w-6 h-6'
      };
      
      const spacing = props.iconAfter ? 'ml-2' : 'mr-2';
      
      return `${iconSizes[props.size]} ${spacing}`;
    });
    
    // Click handler
    const handleClick = (event) => {
      if (props.disabled || props.loading) {
        event.preventDefault();
        return;
      }
      emit('click', event);
    };
    
    return {
      tag,
      buttonType,
      buttonClasses,
      iconClasses,
      handleClick
    };
  }
};
</script>