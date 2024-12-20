import React, { useState } from 'react'
import { Head, router } from '@inertiajs/react'

import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout'
import Table from '@/Components/Table'
import { type PageProps, type Closing } from '@/types'
import { getSearchParams, paginationRange } from '@/utils'
import SecondaryButton from '@/Components/SecondaryButton'
import TextInput from '@/Components/TextInput'

const Closings = ({
  auth,
  data,
}: PageProps<{
  data: { closings: Closing[]; lastPage: number; totalClosings: number }
}>): JSX.Element => {
  const q = getSearchParams('q') ?? ''
  const [keyword, setKeyword] = useState(q)
  const [pageNumber, setPageNumber] = useState(
    parseInt(getSearchParams('page') ?? '1'),
  )
  const [startPage, endPage] = paginationRange(pageNumber, data.lastPage)

  const statusLabels: Record<string, string> = {
    on_review: 'Sedang Ditinjau',
    approved: 'Disetujui',
    rejected: 'Ditolak',
  }

  const commissionStatusLabels: Record<string, string> = {
    pending: 'Menunggu Komisi',
    paid: 'Komisi Dibayarkan',
    unpaid: 'Komisi Belum Dibayarkan',
  }

  const TABLE_HEAD = [
    'Listing',
    'Type',
    'Client Name',
    'Client Phone',
    'Transaction',
    'Date',
    'Status',
    'Commission Status',
  ]

  const fetchData = (q?: string, page?: number): void => {
    router.get(
      '/admin/closings',
      {
        ...(q !== '' ? { q } : {}),
        ...(page !== 1 ? { page } : {}),
      },
      {
        preserveState: true,
        preserveScroll: true,
      },
    )
  }

  return (
    <AuthenticatedLayout
      user={auth.user}
      header={
        <h2 className="text-xl font-semibold leading-tight text-gray-800">
          Closings Report
        </h2>
      }
    >
      <Head title="Closings Report" />

      <div className="py-12">
        <div className="mx-auto max-w-7xl sm:px-6 lg:px-8">
          <div className="overflow-hidden bg-white shadow-sm sm:rounded-lg">
            <div className="mb-2 grid grid-cols-3 gap-4 p-6 md:flex-row md:items-center md:gap-8">
              <div className="col-span-3 md:col-span-2">
                <p className="mb-2 text-2xl font-bold leading-none text-neutral-700">
                  Closings Report
                </p>
                <p className="text-sm font-bold leading-none text-neutral-700">
                  Total : {data.totalClosings} Closing
                </p>
              </div>
              <div className="col-span-3 md:col-span-1">
                <TextInput
                  value={keyword}
                  placeholder="Cari..."
                  className="w-full"
                  onKeyDown={(e) => {
                    if (e.key === 'Enter') {
                      fetchData(keyword, 1)
                      setPageNumber(1)
                    }
                  }}
                  onChange={(e) => {
                    setKeyword(e.target.value)
                  }}
                />
              </div>
            </div>
            <Table>
              <Table.Header>
                {TABLE_HEAD.map((head) => (
                  <Table.HeaderItem
                    key={head}
                    className={
                      head === 'Listing'
                        ? 'w-[220px]'
                        : head === 'Type'
                          ? 'w-[70px]'
                          : ''
                    }
                  >
                    {head}
                  </Table.HeaderItem>
                ))}
              </Table.Header>
              <Table.Body>
                {data.closings.map((closing, index) => (
                  <tr key={index}>
                    <Table.BodyItem
                      className="cursor-pointer"
                      onClick={(event) => {
                        if (event.metaKey || event.ctrlKey) {
                          window.open(
                            `/admin/listings/${closing.listingId}`,
                            '_blank',
                          )
                        } else {
                          router.get(`/admin/listings/${closing.listingId}`)
                        }
                      }}
                    >
                      {closing.listingId}
                    </Table.BodyItem>
                    <Table.BodyItem>{closing.closingType}</Table.BodyItem>
                    <Table.BodyItem>{closing.clientName}</Table.BodyItem>
                    <Table.BodyItem>{closing.clientPhoneNumber}</Table.BodyItem>
                    <Table.BodyItem>{closing.transactionValue}</Table.BodyItem>
                    <Table.BodyItem>{closing.date}</Table.BodyItem>
                    <Table.BodyItem>
                      <span
                        className={`truncate rounded-lg px-3 py-2 text-xs font-medium ${
                          closing.status === 'approved'
                            ? 'bg-green-100 text-green-800'
                            : closing.status === 'rejected'
                              ? 'bg-red-100 text-red-800'
                              : 'bg-yellow-100 text-yellow-800'
                        }`}
                      >
                        {closing.status !== ''
                          ? statusLabels[closing.status]
                          : 'Sedang Ditinjau'}
                      </span>
                    </Table.BodyItem>
                    <Table.BodyItem>
                      <span
                        className={`truncate rounded-lg px-3 py-2 text-xs font-medium ${
                          closing.commissionStatus === 'paid'
                            ? 'bg-green-100 text-green-800'
                            : closing.commissionStatus === 'unpaid'
                              ? 'bg-red-100 text-red-800'
                              : closing.commissionStatus === 'pending'
                                ? 'bg-yellow-100 text-yellow-800'
                                : 'bg-gray-100 text-gray-800'
                        }`}
                      >
                        {closing.commissionStatus != null
                          ? commissionStatusLabels[closing.commissionStatus]
                          : 'N/A'}
                      </span>
                    </Table.BodyItem>
                    <Table.BodyItem>
                      <a href={`/admin/closings/${closing.id}`}>
                        <svg
                          xmlns="http://www.w3.org/2000/svg"
                          fill="none"
                          viewBox="0 0 24 24"
                          strokeWidth={1.5}
                          stroke="currentColor"
                          className="size-6"
                        >
                          <path
                            strokeLinecap="round"
                            strokeLinejoin="round"
                            d="m16.862 4.487 1.687-1.688a1.875 1.875 0 1 1 2.652 2.652L10.582 16.07a4.5 4.5 0 0 1-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 0 1 1.13-1.897l8.932-8.931Zm0 0L19.5 7.125M18 14v4.75A2.25 2.25 0 0 1 15.75 21H5.25A2.25 2.25 0 0 1 3 18.75V8.25A2.25 2.25 0 0 1 5.25 6H10"
                          />
                        </svg>
                      </a>
                    </Table.BodyItem>
                  </tr>
                ))}
                {data.closings.length === 0 && (
                  <tr>
                    <Table.BodyItem colSpan={8} className="text-center text-sm">
                      No data
                    </Table.BodyItem>
                  </tr>
                )}
              </Table.Body>
            </Table>
            <div className="grid grid-cols-3 items-center justify-stretch p-4">
              <SecondaryButton
                onClick={() => {
                  setPageNumber((prev) => {
                    const page = prev - 1
                    fetchData(keyword, page)
                    return page
                  })
                }}
                disabled={pageNumber === 1}
                className="w-fit justify-self-start"
              >
                Previous
              </SecondaryButton>
              <div className="flex items-center gap-2 justify-self-center">
                {Array.from(Array(data.lastPage).keys())
                  .slice(startPage, endPage)
                  .map((item) => (
                    <SecondaryButton
                      key={item}
                      className={pageNumber === item + 1 ? 'bg-stone-200' : ''}
                      onClick={() => {
                        const page = item + 1
                        fetchData(keyword, page)
                        setPageNumber(page)
                      }}
                    >
                      {item + 1}
                    </SecondaryButton>
                  ))}
              </div>
              <SecondaryButton
                onClick={() => {
                  setPageNumber((prev) => {
                    const page = prev + 1
                    fetchData(keyword, page)
                    return page
                  })
                }}
                disabled={pageNumber === data.lastPage}
                className="w-fit justify-self-end"
              >
                Next
              </SecondaryButton>
            </div>
          </div>
        </div>
      </div>
    </AuthenticatedLayout>
  )
}

export default Closings
