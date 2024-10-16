import React from 'react'

export const LotIconSVG = ({
  className,
}: {
  className?: string
}): JSX.Element => {
  return (
    <svg
      width="24"
      height="24"
      viewBox="0 0 24 24"
      fill="none"
      xmlns="http://www.w3.org/2000/svg"
      className={className ?? 'size-[18px] text-slate-400'}
    >
      <path
        d="M17 7L12 3L7 10L3 7V20H21V7H17ZM19 16.95L12 11.5L8 17L5 14.6V11L7.44 12.83L12.4 5.88L16.3 9H19V16.95Z"
        fill="currentColor"
      />
    </svg>
  )
}
