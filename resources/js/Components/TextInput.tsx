import {
  forwardRef,
  useEffect,
  useImperativeHandle,
  useRef,
  type ReactNode,
  type InputHTMLAttributes,
} from 'react'

export default forwardRef(function TextInput(
  {
    type = 'text',
    className = '',
    isFocused = false,
    icon,
    ...props
  }: InputHTMLAttributes<HTMLInputElement> & {
    isFocused?: boolean
    icon?: ReactNode
  },
  ref,
) {
  const localRef = useRef<HTMLInputElement>(null)

  useImperativeHandle(ref, () => ({
    focus: () => localRef.current?.focus(),
  }))

  useEffect(() => {
    if (isFocused) {
      localRef.current?.focus()
    }
  }, [])

  return (
    <div className="relative">
      <input
        {...props}
        type={type}
        className={
          'border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm ' +
          `${icon ? 'pr-10 ' : ''}` +
          className
        }
        ref={localRef}
      />
      {icon ? (
        <div className="absolute right-3 top-1/2 -translate-y-1/2 text-gray-500">
          {icon}
        </div>
      ) : null}
    </div>
  )
})
