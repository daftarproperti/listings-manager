export default function paginationRange(
  page: number,
  lastPage: number,
  displayCount: number = 5,
): [number, number] {
  const halfDisplay = Math.ceil(displayCount / 2)

  let startPage: number
  if (page + halfDisplay > lastPage) {
    startPage = Math.max(0, lastPage - displayCount)
  } else {
    startPage = Math.max(0, page - halfDisplay)
  }

  const endPage: number = Math.min(startPage + displayCount, lastPage + 1)
  if (endPage - startPage < displayCount) {
    startPage = Math.max(0, endPage - displayCount)
  }

  return [startPage, endPage]
}
