import React, { type LabelHTMLAttributes } from 'react'

export default function InputLabel({
  value,
  className = '',
  children,
  ...props
}: LabelHTMLAttributes<HTMLLabelElement> & { value?: string }): JSX.Element {
  return (
    <label
      {...props}
      className={'block font-medium text-sm text-gray-700 ' + className}
    >
      {value !== undefined && value !== '' ? value : children}
    </label>
  )
}
