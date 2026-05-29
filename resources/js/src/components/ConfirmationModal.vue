<template>
  <div v-if="show" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center p-4 z-50">
    <div class="bg-white rounded-lg shadow-xl w-full max-w-md">
      <!-- Header -->
      <div class="flex items-center justify-between p-6 border-b border-gray-200">
        <h3 class="text-lg font-semibold text-gray-900">{{ title || 'Confirmação' }}</h3>
        <button
          @click="cancel"
          class="text-gray-400 hover:text-gray-600 transition-colors"
        >
          <vue-feather type="x" size="24" />
        </button>
      </div>

      <!-- Content -->
      <div class="p-6">
        <!-- Icon -->
        <div v-if="type === 'danger'" class="flex items-center justify-center w-12 h-12 mx-auto mb-4 bg-red-100 rounded-full">
          <vue-feather type="alert-triangle" size="24" class="text-red-600" />
        </div>
        <div v-else-if="type === 'warning'" class="flex items-center justify-center w-12 h-12 mx-auto mb-4 bg-yellow-100 rounded-full">
          <vue-feather type="alert-triangle" size="24" class="text-yellow-600" />
        </div>
        <div v-else-if="type === 'info'" class="flex items-center justify-center w-12 h-12 mx-auto mb-4 bg-blue-100 rounded-full">
          <vue-feather type="info" size="24" class="text-blue-600" />
        </div>
        <div v-else class="flex items-center justify-center w-12 h-12 mx-auto mb-4 bg-green-100 rounded-full">
          <vue-feather type="check" size="24" class="text-green-600" />
        </div>

        <!-- Message -->
        <div class="text-center mb-6">
          <p class="text-gray-700">{{ message }}</p>
          <p v-if="details" class="text-sm text-gray-500 mt-2">{{ details }}</p>
        </div>

        <!-- Actions -->
        <div class="flex gap-3">
          <button
            type="button"
            @click="cancel"
            class="flex-1 px-4 py-2 text-gray-700 bg-gray-100 hover:bg-gray-200 rounded-lg transition-colors"
          >
            {{ cancelText || 'Cancelar' }}
          </button>
          <button
            type="button"
            @click="confirm"
            :disabled="loading"
            class="flex-1 px-4 py-2 rounded-lg transition-colors disabled:opacity-50 disabled:cursor-not-allowed flex items-center justify-center gap-2"
            :class="buttonClasses"
          >
            <div v-if="loading" class="w-4 h-4 border-2 border-current border-t-transparent rounded-full animate-spin"></div>
            {{ loading ? loadingText : confirmText || 'Confirmar' }}
          </button>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import { computed } from 'vue'
import VueFeather from 'vue-feather'

export default {
  name: 'ConfirmationModal',
  components: {
    VueFeather
  },
  props: {
    show: {
      type: Boolean,
      default: false
    },
    title: {
      type: String,
      default: 'Confirmação'
    },
    message: {
      type: String,
      required: true
    },
    details: {
      type: String,
      default: null
    },
    type: {
      type: String,
      default: 'info',
      validator: value => ['danger', 'warning', 'info', 'success'].includes(value)
    },
    confirmText: {
      type: String,
      default: 'Confirmar'
    },
    cancelText: {
      type: String,
      default: 'Cancelar'
    },
    loading: {
      type: Boolean,
      default: false
    },
    loadingText: {
      type: String,
      default: 'Processando...'
    }
  },
  emits: ['confirm', 'cancel'],

  setup(props, { emit }) {
    const buttonClasses = computed(() => {
      switch (props.type) {
        case 'danger':
          return 'bg-red-600 hover:bg-red-700 text-white'
        case 'warning':
          return 'bg-yellow-600 hover:bg-yellow-700 text-white'
        case 'success':
          return 'bg-green-600 hover:bg-green-700 text-white'
        default:
          return 'bg-blue-600 hover:bg-blue-700 text-white'
      }
    })

    const confirm = () => {
      emit('confirm')
    }

    const cancel = () => {
      emit('cancel')
    }

    return {
      buttonClasses,
      confirm,
      cancel
    }
  }
}
</script>