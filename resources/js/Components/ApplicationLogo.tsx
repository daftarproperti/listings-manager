import React from 'react'
import { type SVGAttributes } from 'react'

export default function ApplicationLogo (
  props: SVGAttributes<SVGElement>
): JSX.Element {
  return (
        <svg
            {...props}
            viewBox="0 0 273 315"
            fill="none"
            xmlns="http://www.w3.org/2000/svg"
        >
            <path
                fillRule="evenodd"
                clipRule="evenodd"
                d="M0 0V314.481H116.697C117.109 314.481 117.521 314.479 117.931 314.477V239.005H91.7245V314.481H31.4491H0L0.000941753 225.685L104.828 125.792L209.654 225.685V289.887C228.235 277.766 242.88 261.652 253.588 241.542C266.23 217.997 272.55 189.845 272.55 157.087C272.55 124.431 266.23 96.3814 253.588 72.9387C240.948 49.3936 222.964 31.3764 199.633 18.8873C176.407 6.29575 148.71 0 116.543 0H0Z"
                fill="#0C5AE9"
            />
        </svg>
  )
}
