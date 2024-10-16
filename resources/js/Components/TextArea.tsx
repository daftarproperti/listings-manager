import React, {
  forwardRef,
  useEffect,
  useImperativeHandle,
  useRef,
  type TextareaHTMLAttributes,
} from 'react'

export default forwardRef(function TextareaInput(
  {
    className = '',
    isFocused = false,
    ...props
  }: TextareaHTMLAttributes<HTMLTextAreaElement> & { isFocused?: boolean },
  ref,
) {
  const localRef = useRef<HTMLTextAreaElement>(null)

  useImperativeHandle(ref, () => ({
    focus: () => localRef.current?.focus(),
  }))

  useEffect(() => {
    if (isFocused) {
      localRef.current?.focus()
    }
  }, [isFocused])

  return (
    <textarea
      {...props}
      className={
        'border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm ' +
        className
      }
      ref={localRef}
    />
  )
})
