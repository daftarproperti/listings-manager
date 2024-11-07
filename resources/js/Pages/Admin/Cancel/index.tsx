import React, { useEffect, useState } from 'react'
import { Head, router } from '@inertiajs/react'
import { Tooltip } from '@material-tailwind/react'

import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout'
import Table from '@/Components/Table'
import SecondaryButton from '@/Components/SecondaryButton'
import type { Option, Listing, PageProps } from '@/types'
import { getSearchParams, paginationRange } from '@/utils'
import TextInput from '@/Components/TextInput'
import SelectInput from '@/Components/SelectInput'

export default function CancelPage({
  auth,
  data,
}: PageProps<{
  data: {
    listings: Listing[]
    lastPage: number
    cancellationStatusOptions: Option[]
  }
}>): JSX.Element {
  const q = getSearchParams('q') ?? ''
  const page = parseInt(getSearchParams('page') ?? '1')
  const sortByParam = getSearchParams('sortBy') ?? 'created_at'
  const sortOrderParam = getSearchParams('sortOrder') ?? 'desc'

  const [startPage, endPage] = paginationRange(page, data.lastPage)
  const [keyword, setKeyword] = useState(q)
  const [, setPageNumber] = useState(page)
  const [currentSortBy, setSortBy] = useState(sortByParam)
  const [currentSortOrder, setSortOrder] = useState(sortOrderParam)

  const TABLE_HEAD = [
    'Listing ID',
    'Judul',
    'Agen',
    'No HP',
    'Tanggal',
    'Status',
    'Proses',
  ]

  const fetchData = (
    q?: string,
    page?: number,
    sortBy?: string,
    sortOrder?: string,
  ): void => {
    router.get(
      '/admin/cancel',
      {
        ...(q !== '' ? { q } : {}),
        ...(page !== 1 ? { page } : {}),
        ...(sortBy !== '' ? { sortBy } : {}),
        ...(sortOrder !== '' ? { sortOrder } : {}),
      },
      {
        preserveState: true,
        preserveScroll: true,
      },
    )
  }

  const getStatusLabel = (status: string): string => {
    const statusOption = data.cancellationStatusOptions.find(
      (v) => v.value === status,
    )
    return statusOption != null ? statusOption.label : 'N/A'
  }

  const handleSortChange = (e: React.ChangeEvent<HTMLSelectElement>): void => {
    const [sortBy, sortOrder] = e.target.value.split('|')
    setSortBy(sortBy)
    setSortOrder(sortOrder)
    fetchData(keyword, 1, sortBy, sortOrder)
  }

  useEffect(() => {
    setKeyword(q)
  }, [q])

  return (
    <AuthenticatedLayout
      user={auth.user}
      header={
        <h2 className="text-xl font-semibold leading-tight text-gray-800">
          Laporan Pembatalan
        </h2>
      }
    >
      <Head title="Laporan Pembatalan" />
      <div className="py-12">
        <div className="mx-auto max-w-7xl sm:px-6 lg:px-8">
          <div className="overflow-hidden bg-white shadow-sm sm:rounded-lg">
            <div className="mb-2 grid grid-cols-3 gap-4 p-6 md:flex-row md:items-center md:gap-8">
              <div className="col-span-3 md:col-span-1">
                <p className="text-2xl font-bold leading-none text-neutral-700">
                  Daftar Listing
                </p>
              </div>
              <div className="col-span-3 md:col-span-1">
                <SelectInput
                  value={`${currentSortBy}|${currentSortOrder}`}
                  options={[
                    { label: 'Terbaru', value: 'updated_at|desc' },
                    { label: 'Terlama', value: 'updated_at|asc' },
                  ]}
                  className="w-full"
                  onChange={handleSortChange}
                />
              </div>
              <div className="col-span-3 md:col-span-1">
                <TextInput
                  value={keyword}
                  placeholder="Cari berdasarkan listing id, alamat atau no HP"
                  className="w-full"
                  onKeyDown={(e) => {
                    if (e.key === 'Enter') {
                      fetchData(keyword, 1, currentSortBy, currentSortOrder)
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
                      head === 'Listing ID' || head === 'Judul'
                        ? 'w-[150px]'
                        : 'w-[80px]'
                    }
                  >
                    {head}
                  </Table.HeaderItem>
                ))}
              </Table.Header>
              <Table.Body>
                {data.listings.map(
                  ({ id, title, user, updatedAt, cancellationNote }, index) => (
                    <tr key={index}>
                      <Table.BodyItem>{id}</Table.BodyItem>
                      <Table.BodyItem>
                        {title != null && (
                          <Tooltip content={title}>
                            <span>
                              {title.length > 20
                                ? `${title.substring(0, 20)}...`
                                : title}
                            </span>
                          </Tooltip>
                        )}
                      </Table.BodyItem>
                      <Table.BodyItem>{user?.name}</Table.BodyItem>
                      <Table.BodyItem>{user?.phoneNumber}</Table.BodyItem>
                      <Table.BodyItem>{`${String(updatedAt)}`}</Table.BodyItem>
                      <Table.BodyItem>
                        <span
                          className={`${
                            cancellationNote.status === 'approved'
                              ? 'bg-green-100 text-green-800'
                              : cancellationNote.status === 'rejected'
                                ? 'bg-red-100 text-red-800'
                                : cancellationNote.status === 'on_review'
                                  ? 'bg-yellow-100 text-yellow-800'
                                  : 'bg-gray-100 text-gray-800'
                          } me-2 truncate rounded-full px-2.5 py-0.5 text-xs font-medium`}
                        >
                          {getStatusLabel(cancellationNote.status)}
                        </span>
                      </Table.BodyItem>
                      <Table.BodyItem>
                        <a href={`/admin/cancel/${id}`}>
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
                  ),
                )}
                {data.listings.length === 0 && (
                  <tr>
                    <Table.BodyItem colSpan={7} className="text-center text-sm">
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
                    fetchData(keyword, page, currentSortBy, currentSortOrder)
                    return page
                  })
                }}
                disabled={page === 1}
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
                      className={
                        page === item + 1 ? 'bg-stone-200' : 'hidden md:block'
                      }
                      onClick={() => {
                        const page = item + 1
                        fetchData(
                          keyword,
                          page,
                          currentSortBy,
                          currentSortOrder,
                        )
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
                    fetchData(keyword, page, currentSortBy, currentSortOrder)
                    return page
                  })
                }}
                disabled={page === data.lastPage}
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
