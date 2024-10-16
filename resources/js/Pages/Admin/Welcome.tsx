import React from 'react'
import { Head } from '@inertiajs/react'

import { usePreventBackButton } from '@/utils'
import ApplicationLogo from '@/Components/ApplicationLogo'

export default function Welcome(): JSX.Element {
  usePreventBackButton()

  return (
    <>
      <Head title="Welcome" />
      <div className="bg-dots relative min-h-screen bg-gray-900 bg-center selection:text-white sm:flex sm:items-center sm:justify-center">
        <div className="p-6 text-end sm:fixed sm:right-0 sm:top-0 lg:px-8">
          <a
            href={route('auth.google')}
            className="font-semibold text-gray-400 hover:text-white"
          >
            Log in
          </a>
        </div>

        <div className="mx-auto max-w-7xl p-6 lg:p-8">
          <div className="flex justify-center">
            <ApplicationLogo className="h-16 w-auto bg-transparent" />
          </div>
        </div>
      </div>

      <style>{`
                .bg-dots {
                    background-image: url("data:image/svg+xml,%3Csvg width='30' height='30' viewBox='0 0 30 30' fill='none' xmlns='http://www.w3.org/2000/svg'%3E%3Cpath d='M1.22676 0C1.91374 0 2.45351 0.539773 2.45351 1.22676C2.45351 1.91374 1.91374 2.45351 1.22676 2.45351C0.539773 2.45351 0 1.91374 0 1.22676C0 0.539773 0.539773 0 1.22676 0Z' fill='rgba(255,255,255,0.07)'/%3E%3C/svg%3E");
                }
            `}</style>
    </>
  )
}
