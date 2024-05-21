import { useEffect } from 'react'

const usePreventBackButton = (): void => {
  const onpopstate = (): void => {
    location.reload()
  }

  useEffect(() => {
    window.addEventListener('popstate', onpopstate)
    return () => {
      window.removeEventListener('popstate', onpopstate)
    }
  }, [])
}

export default usePreventBackButton
