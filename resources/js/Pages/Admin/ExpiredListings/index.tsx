import React, { useEffect, useState } from 'react'
import { Head, router } from '@inertiajs/react'

import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout'
import Table from '@/Components/Table'
import SecondaryButton from '@/Components/SecondaryButton'

import type { Option, Listing, PageProps } from '@/types'
import { getSearchParams, paginationRange } from '@/utils'
import { Tooltip } from '@material-tailwind/react'
import TextInput from '@/Components/TextInput'
import SelectInput from '@/Components/SelectInput'

export default function index ({
  auth,
  data
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

  const TABLE_HEAD = ['Listing ID', 'Judul', 'Agen', 'No HP', 'Tanggal Expired']

  const fetchData = (
    q?: string,
    page?: number,
    sortBy?: string,
    sortOrder?: string
  ): void => {
    router.get(
      '/admin/expired',
      {
        ...(q !== '' ? { q } : {}),
        ...(page !== 1 ? { page } : {}),
        ...(sortBy !== '' ? { sortBy } : {}),
        ...(sortOrder !== '' ? { sortOrder } : {})
      },
      {
        preserveState: true,
        preserveScroll: true
      }
    )
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
                <h2 className="font-semibold text-xl text-gray-800 leading-tight">
                    Listing Expired
                </h2>
            }
        >
            <Head title="Listing Expired" />
            <div className="py-12">
                <div className="max-w-7xl mx-auto sm:px-6 lg:px-8">
                    <div className="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                        <div className="p-6 mb-2 grid grid-cols-3 gap-4 md:gap-8 md:flex-row md:items-center">
                            <div className="col-span-3 md:col-span-1">
                                <p className="font-bold text-2xl leading-none text-neutral-700">
                                    Daftar Listing
                                </p>
                            </div>
                            <div className="col-span-3 md:col-span-1">
                              <SelectInput
                                value={`${currentSortBy}|${currentSortOrder}`}
                                options={[
                                  { label: 'Terbaru', value: 'expiredAt|desc' },
                                  { label: 'Terlama', value: 'expiredAt|asc' }
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
                                        className={head === 'Listing ID' || head === 'Judul' ? 'w-[150px]' : 'w-[80px]'}
                                    >
                                        {head}
                                    </Table.HeaderItem>
                                ))}
                            </Table.Header>
                            <Table.Body>
                                {data.listings.map(
                                  (
                                    {
                                      id,
                                      title,
                                      user,
                                      expiredAt
                                    },
                                    index
                                  ) => (
                                        <tr
                                            key={index}
                                            onClick={(event) => {
                                              if (event.metaKey || event.ctrlKey) {
                                                window.open(`/admin/listings/${id}`, '_blank')
                                              } else {
                                                router.get(`/admin/listings/${id}`)
                                              }
                                            }}
                                            className="cursor-pointer"
                                        >
                                            <Table.BodyItem>
                                                {id}
                                            </Table.BodyItem>
                                            <Table.BodyItem>
                                              {title != null && (
                                                <Tooltip content={title}>
                                                  <span>{title.length > 20 ? `${title.substring(0, 20)}...` : title}</span>
                                                </Tooltip>
                                              )}
                                            </Table.BodyItem>
                                            <Table.BodyItem>
                                                {user?.name}
                                            </Table.BodyItem>
                                            <Table.BodyItem>
                                                {user?.phoneNumber}
                                            </Table.BodyItem>
                                            <Table.BodyItem>
                                                <span className="text-red-500">{`${String(expiredAt)}`}</span>
                                            </Table.BodyItem>
                                        </tr>
                                  )
                                )}
                                {data.listings.length === 0 && (
                                    <tr>
                                        <Table.BodyItem
                                            colSpan={5}
                                            className="text-center text-sm"
                                        >
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
                                    fetchData(keyword, page, status)
                                    return page
                                  })
                                }}
                                disabled={page === 1}
                                className='w-fit justify-self-start'
                            >
                                Previous
                            </SecondaryButton>
                            <div className="flex justify-self-center items-center gap-2">
                                {Array.from(Array(data.lastPage).keys())
                                  .slice(startPage, endPage)
                                  .map((item) => (
                                        <SecondaryButton
                                            key={item}
                                            className={
                                                page === item + 1
                                                  ? 'bg-stone-200'
                                                  : 'hidden md:block'
                                            }
                                            onClick={() => {
                                              const page = item + 1
                                              fetchData(keyword, page, status)
                                              setPageNumber(page)
                                            }}
                                        >
                                            {item + 1}
                                        </SecondaryButton>
                                  )
                                  )}
                            </div>
                            <SecondaryButton
                                onClick={() => {
                                  setPageNumber((prev) => {
                                    const page = prev + 1
                                    fetchData(keyword, page, status)
                                    return page
                                  })
                                }}
                                disabled={page === data.lastPage}
                                className='w-fit justify-self-end'
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
