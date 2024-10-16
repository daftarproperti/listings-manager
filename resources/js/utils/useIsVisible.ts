import { useState, useEffect, useRef } from 'react'

export default function useIsVisible<T extends HTMLElement>(): [
  React.RefObject<T>,
  boolean,
] {
  const [isIntersecting, setIntersecting] = useState<boolean>(false)
  const ref = useRef<T>(null)

  useEffect(() => {
    if (ref.current == null) return

    const observer = new IntersectionObserver(
      ([entry]) => {
        setIntersecting(entry.isIntersecting)
      },
      {
        threshold: 0.1,
      },
    )

    observer.observe(ref.current)

    return () => {
      observer.disconnect()
    }
  }, [])

  return [ref, isIntersecting]
}
