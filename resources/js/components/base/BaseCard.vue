<template>
  <div :class="cardClasses">
    <!-- Header -->
    <div
      v-if="$slots.header || title || $slots.actions"
      :class="headerClasses"
    >
      <div class="flex-1">
        <slot name="header">
          <h3 v-if="title" :class="titleClasses">
            {{ title }}
          </h3>
          <p v-if="subtitle" :class="subtitleClasses">
            {{ subtitle }}
          </p>
        </slot>
      </div>
      
      <div v-if="$slots.actions" class="flex-shrink-0 ml-4">
        <slot name="actions" />
      </div>
    </div>

    <!-- Body -->
    <div :class="bodyClasses">
      <slot />
    </div>

    <!-- Footer -->
    <div
      v-if="$slots.footer"
      :class="footerClasses"
    >
      <slot name="footer" />
    </div>
  </div>
</template>

<script>
import { computed } from 'vue';

export default {
  name: 'BaseCard',
  props: {
    title: {
      type: String,
      default: ''
    },
    
    subtitle: {
      type: String,
      default: ''
    },
    
    variant: {
      type: String,
      default: 'default',
      validator: (value) => ['default', 'bordered', 'shadow', 'elevated'].includes(value)
    },
    
    padding: {
      type: String,
      default: 'default',
      validator: (value) => ['none', 'sm', 'default', 'lg'].includes(value)
    },
    
    headerPadding: {
      type: Boolean,
      default: true
    },
    
    footerPadding: {
      type: Boolean,
      default: true
    },
    
    divider: {
      type: Boolean,
      default: true
    },
    
    hover: {
      type: Boolean,
      default: false
    },
    
    clickable: {
      type: Boolean,
      default: false
    }
  },
  
  emits: ['click'],
  
  setup(props, { emit }) {
    // Base card classes
    const baseClasses = 'bg-white rounded-lg overflow-hidden';
    
    // Variant classes
    const variantClasses = computed(() => {
      const variants = {
        default: '',
        bordered: 'border border-gray-200',
        shadow: 'shadow-sm border border-gray-200',
        elevated: 'shadow-lg border border-gray-200'
      };
      return variants[props.variant];
    });
    
    // Hover and clickable classes
    const interactionClasses = computed(() => {
      let classes = '';
      
      if (props.hover) {
        classes += ' transition-shadow duration-200 hover:shadow-md';
      }
      
      if (props.clickable) {
        classes += ' cursor-pointer transition-all duration-200 hover:shadow-lg';
      }
      
      return classes;
    });
    
    // Padding classes
    const paddingMap = {
      none: '',
      sm: 'p-4',
      default: 'p-6',
      lg: 'p-8'
    };
    
    const bodyPaddingClasses = computed(() => {
      return paddingMap[props.padding];
    });
    
    // Combined card classes
    const cardClasses = computed(() => {
      return [
        baseClasses,
        variantClasses.value,
        interactionClasses.value
      ].join(' ');
    });
    
    // Header classes
    const headerClasses = computed(() => {
      const baseHeaderClasses = 'flex items-start justify-between';
      const paddingClasses = props.headerPadding ? paddingMap[props.padding] : '';
      const borderClasses = props.divider ? 'border-b border-gray-200' : '';
      
      return [
        baseHeaderClasses,
        paddingClasses,
        borderClasses
      ].join(' ');
    });
    
    // Title classes
    const titleClasses = computed(() => {
      return 'text-lg font-medium text-gray-900';
    });
    
    // Subtitle classes
    const subtitleClasses = computed(() => {
      return 'mt-1 text-sm text-gray-500';
    });
    
    // Body classes
    const bodyClasses = computed(() => {
      return bodyPaddingClasses.value;
    });
    
    // Footer classes
    const footerClasses = computed(() => {
      const baseFooterClasses = '';
      const paddingClasses = props.footerPadding ? paddingMap[props.padding] : '';
      const borderClasses = props.divider ? 'border-t border-gray-200 bg-gray-50' : '';
      
      return [
        baseFooterClasses,
        paddingClasses,
        borderClasses
      ].join(' ');
    });
    
    // Click handler
    const handleClick = (event) => {
      if (props.clickable) {
        emit('click', event);
      }
    };
    
    return {
      cardClasses,
      headerClasses,
      titleClasses,
      subtitleClasses,
      bodyClasses,
      footerClasses,
      handleClick
    };
  }
};
</script>