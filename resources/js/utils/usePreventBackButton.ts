import { useEffect } from 'react'
import { router } from '@inertiajs/react'

const usePreventBackButton = (): void => {
  const onpopstate = (): void => {
    router.reload()
  }

  useEffect(() => {
    window.addEventListener('popstate', onpopstate)
    return () => {
      window.removeEventListener('popstate', onpopstate)
    }
  }, [])
}

export default usePreventBackButton
