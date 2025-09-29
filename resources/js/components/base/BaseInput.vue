<template>
  <div class="base-input">
    <!-- Label -->
    <label
      v-if="label"
      :for="inputId"
      :class="labelClasses"
    >
      {{ label }}
      <span v-if="required" class="text-red-500 ml-1">*</span>
    </label>

    <!-- Input wrapper -->
    <div class="relative">
      <!-- Prefix icon -->
      <div
        v-if="prefixIcon"
        class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none"
      >
        <component
          :is="prefixIcon"
          class="h-5 w-5 text-gray-400"
        />
      </div>

      <!-- Input element -->
      <component
        :is="inputComponent"
        :id="inputId"
        ref="inputRef"
        :type="inputType"
        :value="modelValue"
        :placeholder="placeholder"
        :disabled="disabled"
        :readonly="readonly"
        :required="required"
        :min="min"
        :max="max"
        :step="step"
        :rows="rows"
        :cols="cols"
        :class="inputClasses"
        @input="handleInput"
        @change="handleChange"
        @blur="handleBlur"
        @focus="handleFocus"
        @keydown="handleKeydown"
      >
        <!-- Textarea content -->
        <template v-if="inputComponent === 'textarea'">{{ modelValue }}</template>
        
        <!-- Select options -->
        <template v-if="inputComponent === 'select'">
          <!-- Slot content takes priority -->
          <slot v-if="$slots.default"></slot>
          
          <!-- Options prop fallback -->
          <template v-else>
            <option
              v-if="placeholder"
              value=""
              disabled
            >
              {{ placeholder }}
            </option>
            <option
              v-for="option in options"
              :key="option.value"
              :value="option.value"
              :disabled="option.disabled"
            >
              {{ option.label }}
            </option>
          </template>
        </template>
      </component>

      <!-- Suffix icon -->
      <div
        v-if="suffixIcon || showPasswordToggle"
        class="absolute inset-y-0 right-0 pr-3 flex items-center"
        :class="{ 'cursor-pointer': showPasswordToggle }"
        @click="togglePasswordVisibility"
      >
        <component
          v-if="showPasswordToggle"
          :is="passwordVisible ? EyeSlashIcon : EyeIcon"
          class="h-5 w-5 text-gray-400 hover:text-gray-600"
        />
        <component
          v-else-if="suffixIcon"
          :is="suffixIcon"
          class="h-5 w-5 text-gray-400"
        />
      </div>

      <!-- Loading spinner -->
      <div
        v-if="loading"
        class="absolute inset-y-0 right-0 pr-3 flex items-center"
      >
        <svg class="animate-spin h-5 w-5 text-gray-400" fill="none" viewBox="0 0 24 24">
          <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
          <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"/>
        </svg>
      </div>
    </div>

    <!-- Helper text or error message -->
    <div
      v-if="helperText || errorMessage"
      :class="messageClasses"
      class="mt-1 text-sm"
    >
      {{ errorMessage || helperText }}
    </div>
  </div>
</template>

<script>
import { ref, computed, nextTick } from 'vue';

// Icons (you would typically import these from a library like Heroicons)
const EyeIcon = {
  template: `
    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
    </svg>
  `
};

const EyeSlashIcon = {
  template: `
    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.878 9.878L3 3m6.878 6.878L21 21"/>
    </svg>
  `
};

export default {
  name: 'BaseInput',
  components: {
    EyeIcon,
    EyeSlashIcon
  },
  props: {
    // v-model
    modelValue: {
      type: [String, Number, Boolean],
      default: ''
    },
    
    // Input type and component
    type: {
      type: String,
      default: 'text',
      validator: (value) => [
        'text', 'password', 'email', 'number', 'tel', 'url', 'search',
        'date', 'time', 'datetime-local', 'month', 'week',
        'textarea', 'select'
      ].includes(value)
    },
    
    // Label and placeholder
    label: {
      type: String,
      default: ''
    },
    
    placeholder: {
      type: String,
      default: ''
    },
    
    // Form attributes
    required: {
      type: Boolean,
      default: false
    },
    
    disabled: {
      type: Boolean,
      default: false
    },
    
    readonly: {
      type: Boolean,
      default: false
    },
    
    // Size variants
    size: {
      type: String,
      default: 'md',
      validator: (value) => ['sm', 'md', 'lg'].includes(value)
    },
    
    // Number input attributes
    min: {
      type: [String, Number],
      default: undefined
    },
    
    max: {
      type: [String, Number],
      default: undefined
    },
    
    step: {
      type: [String, Number],
      default: undefined
    },
    
    // Textarea attributes
    rows: {
      type: [String, Number],
      default: 3
    },
    
    cols: {
      type: [String, Number],
      default: undefined
    },
    
    // Select options
    options: {
      type: Array,
      default: () => []
    },
    
    // Icons
    prefixIcon: {
      type: [String, Object],
      default: null
    },
    
    suffixIcon: {
      type: [String, Object],
      default: null
    },
    
    // States
    loading: {
      type: Boolean,
      default: false
    },
    
    // Validation
    errorMessage: {
      type: String,
      default: ''
    },
    
    helperText: {
      type: String,
      default: ''
    },
    
    // ID
    id: {
      type: String,
      default: null
    }
  },
  
  emits: ['update:modelValue', 'input', 'change', 'blur', 'focus', 'keydown'],
  
  setup(props, { emit }) {
    const inputRef = ref(null);
    const passwordVisible = ref(false);
    const focused = ref(false);
    
    // Generate unique ID
    const inputId = computed(() => {
      return props.id || `input-${Math.random().toString(36).substr(2, 9)}`;
    });
    
    // Determine input component
    const inputComponent = computed(() => {
      if (props.type === 'textarea') return 'textarea';
      if (props.type === 'select') return 'select';
      return 'input';
    });
    
    // Determine actual input type
    const inputType = computed(() => {
      if (props.type === 'password' && passwordVisible.value) return 'text';
      if (['textarea', 'select'].includes(props.type)) return undefined;
      return props.type;
    });
    
    // Show password toggle
    const showPasswordToggle = computed(() => {
      return props.type === 'password' && !props.disabled && !props.readonly;
    });
    
    // Size classes
    const sizeClasses = computed(() => {
      const sizes = {
        sm: 'px-3 py-1.5 text-sm',
        md: 'px-3 py-2 text-sm',
        lg: 'px-4 py-3 text-base'
      };
      return sizes[props.size];
    });
    
    // Padding adjustments for icons
    const paddingClasses = computed(() => {
      let classes = '';
      if (props.prefixIcon) classes += ' pl-10';
      if (props.suffixIcon || showPasswordToggle.value || props.loading) classes += ' pr-10';
      return classes;
    });
    
    // Input state classes
    const stateClasses = computed(() => {
      if (props.errorMessage) {
        return 'border-red-300 text-red-900 placeholder-red-300 focus:outline-none focus:ring-red-500 focus:border-red-500';
      }
      
      if (focused.value) {
        return 'border-blue-300 ring-1 ring-blue-500 focus:outline-none focus:ring-blue-500 focus:border-blue-500';
      }
      
      return 'border-gray-300 focus:outline-none focus:ring-blue-500 focus:border-blue-500';
    });
    
    // Combined input classes
    const inputClasses = computed(() => {
      const baseClasses = 'block w-full rounded-md shadow-sm transition-colors duration-200';
      const disabledClasses = props.disabled ? 'bg-gray-50 text-gray-500 cursor-not-allowed' : 'bg-white';
      const readonlyClasses = props.readonly ? 'bg-gray-50' : '';
      
      return [
        baseClasses,
        sizeClasses.value,
        paddingClasses.value,
        stateClasses.value,
        disabledClasses,
        readonlyClasses
      ].join(' ');
    });
    
    // Label classes
    const labelClasses = computed(() => {
      const baseClasses = 'block text-sm font-medium mb-1';
      const colorClasses = props.errorMessage ? 'text-red-700' : 'text-gray-700';
      return `${baseClasses} ${colorClasses}`;
    });
    
    // Message classes
    const messageClasses = computed(() => {
      return props.errorMessage ? 'text-red-600' : 'text-gray-500';
    });
    
    // Event handlers
    const handleInput = (event) => {
      const value = event.target.value;
      emit('update:modelValue', value);
      emit('input', event);
    };
    
    const handleChange = (event) => {
      emit('change', event);
    };
    
    const handleFocus = (event) => {
      focused.value = true;
      emit('focus', event);
    };
    
    const handleBlur = (event) => {
      focused.value = false;
      emit('blur', event);
    };
    
    const handleKeydown = (event) => {
      emit('keydown', event);
    };
    
    const togglePasswordVisibility = () => {
      if (showPasswordToggle.value) {
        passwordVisible.value = !passwordVisible.value;
      }
    };
    
    // Public methods
    const focus = () => {
      nextTick(() => {
        inputRef.value?.focus();
      });
    };
    
    const blur = () => {
      inputRef.value?.blur();
    };
    
    const select = () => {
      inputRef.value?.select();
    };
    
    return {
      inputRef,
      passwordVisible,
      focused,
      inputId,
      inputComponent,
      inputType,
      showPasswordToggle,
      inputClasses,
      labelClasses,
      messageClasses,
      handleInput,
      handleChange,
      handleFocus,
      handleBlur,
      handleKeydown,
      togglePasswordVisibility,
      focus,
      blur,
      select,
      EyeIcon,
      EyeSlashIcon
    };
  }
};
</script>