export const formatCurrencyToIDRText = (number?: number) => {
  if (number === undefined || number === null) return ''

  const isLargeNumber = number >= 1000000000

  const hasSignificantDigits =
    isLargeNumber && !/^[1-9]\d{0,2}000000000$/.test(number.toString())

  const formatOptions: Intl.NumberFormatOptions = {
    currency: 'IDR',
    style: 'currency',
    notation: 'compact',
    compactDisplay: 'short',
    minimumFractionDigits: hasSignificantDigits ? 2 : 0,
    maximumFractionDigits: hasSignificantDigits ? 2 : 0,
  }

  let formattedNumber = new Intl.NumberFormat('id-ID', formatOptions).format(
    number,
  )

  if (isLargeNumber && !hasSignificantDigits) {
    formattedNumber = formattedNumber.replace(/(\.00|,00)/g, '')
  }

  return formattedNumber
}
