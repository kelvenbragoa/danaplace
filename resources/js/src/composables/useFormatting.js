export function useFormatting() {
  const formatMoney = (value) => {
    if (!value && value !== 0) return 'R$ 0,00'
    
    const numValue = typeof value === 'string' ? parseFloat(value) : value
    
    return new Intl.NumberFormat('pt-BR', {
      style: 'currency',
      currency: 'MZN'
    }).format(numValue)
  }

  const formatDate = (date) => {
    if (!date) return '-'
    
    const dateObj = new Date(date)
    return dateObj.toLocaleDateString('pt-BR')
  }

  const formatDateTime = (date) => {
    if (!date) return '-'
    
    const dateObj = new Date(date)
    return dateObj.toLocaleDateString('pt-BR') + ' ' + dateObj.toLocaleTimeString('pt-BR', {
      hour: '2-digit',
      minute: '2-digit'
    })
  }

  const formatPercent = (value) => {
    if (!value && value !== 0) return '0%'
    
    const numValue = typeof value === 'string' ? parseFloat(value) : value
    
    return new Intl.NumberFormat('pt-BR', {
      style: 'percent',
      minimumFractionDigits: 2
    }).format(numValue / 100)
  }

  const formatNumber = (value) => {
    if (!value && value !== 0) return '0'
    
    const numValue = typeof value === 'string' ? parseFloat(value) : value
    
    return new Intl.NumberFormat('pt-BR').format(numValue)
  }

  return {
    formatMoney,
    formatDate,
    formatDateTime,
    formatPercent,
    formatNumber
  }
}