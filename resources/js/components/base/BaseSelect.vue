<template>
  <div class="base-select">
    <!-- Label -->
    <label
      v-if="label"
      :for="selectId"
      class="block text-sm font-medium text-gray-700 mb-1"
    >
      {{ label }}
      <span v-if="required" class="text-red-500 ml-1">*</span>
    </label>

    <!-- Select element -->
    <select
      :id="selectId"
      :value="modelValue"
      :required="required"
      :disabled="disabled"
      :class="selectClasses"
      @input="handleChange"
      @change="handleChange"
      @blur="handleBlur"
      @focus="handleFocus"
    >
      <slot></slot>
    </select>

    <!-- Error message -->
    <p
      v-if="error"
      class="mt-1 text-sm text-red-600"
    >
      {{ error }}
    </p>

    <!-- Help text -->
    <p
      v-if="helpText && !error"
      class="mt-1 text-sm text-gray-500"
    >
      {{ helpText }}
    </p>
  </div>
</template>

<script>
import { ref, computed } from 'vue';

export default {
  name: 'BaseSelect',
  props: {
    modelValue: {
      type: [String, Number],
      default: ''
    },
    label: {
      type: String,
      default: ''
    },
    required: {
      type: Boolean,
      default: false
    },
    disabled: {
      type: Boolean,
      default: false
    },
    error: {
      type: String,
      default: ''
    },
    helpText: {
      type: String,
      default: ''
    },
    size: {
      type: String,
      default: 'md',
      validator: (value) => ['sm', 'md', 'lg'].includes(value)
    }
  },
  emits: ['update:modelValue', 'input', 'change', 'blur', 'focus'],
  setup(props, { emit }) {
    const selectId = ref(`select-${Math.random().toString(36).substr(2, 9)}`);

    const selectClasses = computed(() => {
      const baseClasses = [
        'block',
        'w-full',
        'border-gray-300',
        'rounded-md',
        'shadow-sm',
        'focus:ring-blue-500',
        'focus:border-blue-500',
        'disabled:bg-gray-50',
        'disabled:text-gray-500',
        'disabled:cursor-not-allowed'
      ];

      // Size classes
      const sizeClasses = {
        sm: ['text-sm', 'py-1', 'px-2'],
        md: ['text-sm', 'py-2', 'px-3'],
        lg: ['text-base', 'py-3', 'px-4']
      };

      // Error state
      if (props.error) {
        baseClasses.push('border-red-300', 'text-red-900', 'focus:ring-red-500', 'focus:border-red-500');
      }

      return [...baseClasses, ...sizeClasses[props.size]].join(' ');
    });

    const handleChange = (event) => {
      const value = event.target.value;
      console.log('BaseSelect handleChange:', value); // Debug log
      
      // Clear any custom validity message
      event.target.setCustomValidity('');
      
      emit('update:modelValue', value);
      emit('input', value);
      emit('change', value);
    };

    const handleBlur = (event) => {
      emit('blur', event);
    };

    const handleFocus = (event) => {
      emit('focus', event);
    };

    return {
      selectId,
      selectClasses,
      handleChange,
      handleBlur,
      handleFocus
    };
  }
};
</script>
