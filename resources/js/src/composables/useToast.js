import { useToastr } from '../../toastr.js'

export function useToast() {
  const toastr = useToastr()

  const showToast = (message, type = 'success') => {
    switch (type) {
      case 'success':
        toastr.success(message)
        break
      case 'error':
        toastr.error(message)
        break
      case 'warning':
        toastr.warning(message)
        break
      case 'info':
        toastr.info(message)
        break
      default:
        toastr.success(message)
    }
  }

  return {
    showToast
  }
}