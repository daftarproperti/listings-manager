import React, { useState } from 'react'
import { Head, router } from '@inertiajs/react'

import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout'
import InputLabel from '@/Components/InputLabel'
import TextArea from '@/Components/TextArea'
import PrimaryButton from '@/Components/PrimaryButton'
import SecondaryButton from '@/Components/SecondaryButton'
import type { PageProps, Listing, Option } from '@/types'

const CancelForm = ({
  auth,
  data,
}: PageProps<{
  data: {
    listing: Listing
    cancellationStatusOptions: Option[]
  }
}>): JSX.Element => {
  const [status, setStatus] = useState(
    data?.listing?.cancellationNote?.status ?? 'on_review',
  )
  const [reason, setReason] = useState(
    data?.listing?.cancellationNote?.reason ?? '',
  )

  const handleUpdateNote = (e: React.FormEvent<HTMLFormElement>): void => {
    e.preventDefault()
    const payload = {
      reason,
      status,
    }

    router.put(`/admin/cancel/${data.listing.id}`, payload, {
      preserveScroll: true,
      preserveState: true,
      onSuccess: () => {
        router.visit('/admin/cancel', { replace: true })
      },
      onError: (errors) => {
        console.error('Error updating cancellation note', errors)
      },
    })
  }

  return (
    <AuthenticatedLayout
      user={auth.user}
      header={
        <h2 className="text-xl font-semibold leading-tight text-gray-800">
          Review Pembatalan Listing
        </h2>
      }
    >
      <Head title="Pembatalan Listing" />
      <div className="py-12">
        <div className="mx-auto max-w-7xl sm:px-6 lg:px-8">
          <div className="overflow-hidden bg-white shadow-sm sm:rounded-lg">
            <div className="mb-2 grid grid-cols-3 gap-4 p-6 md:flex-row md:items-center md:gap-8">
              <div className="col-span-3 md:col-span-2">
                <p className="text-2xl font-bold leading-none text-neutral-700">
                  Review Pembatalan
                </p>
              </div>
            </div>
            <div className="p-6">
              <a
                href={`/admin/listings/${data?.listing?.id}`}
                className="rounded-lg bg-blue-700 px-5 py-2.5 text-sm font-medium text-white hover:bg-blue-800 focus:outline-none focus:ring-4 focus:ring-blue-300"
                target="_blank"
                rel="noreferrer"
              >
                Lihat Listing
              </a>
            </div>
            <form onSubmit={handleUpdateNote}>
              <div className="grid grid-cols-3 gap-4 p-6 md:flex-row md:items-center md:gap-8">
                <div className="col-span-2 md:col-span-2">
                  <InputLabel value="Status" />
                  <select
                    name="status"
                    className="rounded-lg border-solid border-gray-300"
                    onChange={(e) => {
                      setStatus(e.target.value)
                    }}
                  >
                    {data.cancellationStatusOptions.map((option) => (
                      <option
                        key={option.value}
                        value={option.value}
                        selected={option.value === status}
                      >
                        {option.label}
                      </option>
                    ))}
                  </select>
                </div>
                <div className="col-span-2 md:col-span-2">
                  <InputLabel value="Alasan" />
                  <TextArea
                    name="reason"
                    value={reason}
                    onChange={(e) => {
                      setReason(e.target.value)
                    }}
                    className="w-full"
                  />
                </div>
                <div className="col-span-2">
                  <PrimaryButton className="mr-5 md:col-span-2">
                    Simpan
                  </PrimaryButton>
                  <SecondaryButton
                    className="md:col-span-2"
                    onClick={() => {
                      router.visit('/admin/cancel')
                    }}
                  >
                    Kembali
                  </SecondaryButton>
                </div>
              </div>
            </form>
          </div>
        </div>
      </div>
    </AuthenticatedLayout>
  )
}

export default CancelForm
