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
  data
}: PageProps<{
  data: {
    listing: Listing
    cancellationStatusOptions: Option[]
  }
}>): JSX.Element => {
  const [status, setStatus] = useState(data?.listing?.cancellationNote?.status ?? 'on_review')
  const [reason, setReason] = useState(data?.listing?.cancellationNote?.reason ?? '')

  const handleUpdateNote = (e: React.FormEvent<HTMLFormElement>): void => {
    e.preventDefault()
    const payload = {
      reason,
      status
    }

    router.put(
      `/admin/cancel/${data.listing.id}`,
      payload,
      {
        preserveScroll: true,
        preserveState: true,
        onSuccess: () => {
          router.visit('/admin/cancel', { replace: true })
        },
        onError: errors => {
          console.error('Error updating cancellation note', errors)
        }
      }
    )
  }

  return (
    <AuthenticatedLayout
        user={auth.user}
        header={<h2 className="font-semibold text-xl text-gray-800 leading-tight">Review Pembatalan Listing</h2>}
    >
        <Head title="Pembatalan Listing" />
        <div className="py-12">
            <div className="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div className="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div className="p-6 mb-2 grid grid-cols-3 gap-4 md:gap-8 md:flex-row md:items-center">
                        <div className="col-span-3 md:col-span-2">
                            <p className="font-bold text-2xl leading-none text-neutral-700">Review Pembatalan</p>
                        </div>
                    </div>
                    <div className="p-6">
                        <a href={`/admin/listings/${data?.listing?.id}`}
                            className="text-white bg-blue-700 hover:bg-blue-800 focus:ring-4 focus:ring-blue-300 font-medium rounded-lg text-sm px-5 py-2.5 focus:outline-none" target="_blank" rel="noreferrer">
                                Lihat Listing
                        </a>
                    </div>
                    <form onSubmit={handleUpdateNote}>
                        <div className="p-6 grid grid-cols-3 gap-4 md:gap-8 md:flex-row md:items-center">
                            <div className="col-span-2 md:col-span-2">
                                <InputLabel value="Status" />
                                <select
                                    name="status"
                                    className="border-solid border-gray-300 rounded-lg"
                                    onChange={e => { setStatus(e.target.value) }}
                                >
                                    {data.cancellationStatusOptions.map(option => (
                                        <option key={option.value} value={option.value} selected={option.value === status}>{option.label}</option>
                                    ))}
                                </select>
                            </div>
                            <div className="col-span-2 md:col-span-2">
                                <InputLabel value="Alasan" />
                                <TextArea name="reason" value={reason} onChange={e => { setReason(e.target.value) }} className="w-full" />
                            </div>
                            <div className="col-span-2">
                                <PrimaryButton className="md:col-span-2 mr-5">Simpan</PrimaryButton>
                                <SecondaryButton className="md:col-span-2" onClick={() => { router.visit('/admin/cancel') }}>Kembali</SecondaryButton>
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
