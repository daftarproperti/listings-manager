import React, { useEffect, useState } from 'react'
import { Head, router } from '@inertiajs/react'
import { Tooltip } from '@material-tailwind/react'

import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout'
import Table from '@/Components/Table'
import SecondaryButton from '@/Components/SecondaryButton'
import TextInput from '@/Components/TextInput'
import { type Option, type Listing, type PageProps } from '@/types'
import { getSearchParams, paginationRange } from '@/utils'
import SelectInput from '@/Components/SelectInput'

export default function ListingsPage({
  auth,
  data,
}: PageProps<{
  data: {
    listings: Listing[]
    lastPage: number
    verifyStatusOptions: Option[]
    activeStatusOptions: Option[]
  }
}>): JSX.Element {
  const q = getSearchParams('q') ?? ''
  const page = parseInt(getSearchParams('page') ?? '1')
  const status = getSearchParams('verifyStatus') ?? ''
  const sortByParam = getSearchParams('sortBy') ?? 'updated_at'
  const sortOrderParam = getSearchParams('sortOrder') ?? 'desc'

  const [startPage, endPage] = paginationRange(page, data.lastPage)

  const [keyword, setKeyword] = useState(q)
  const [, setPageNumber] = useState(page)
  const [, setVerifyStatus] = useState(status)
  const [currentSortBy, setSortBy] = useState(sortByParam)
  const [currentSortOrder, setSortOrder] = useState(sortOrderParam)

  const TABLE_HEAD = [
    'Alamat',
    'Agen',
    'No HP',
    'Harga',
    'LT',
    'LB',
    'KT',
    'KM',
    'Tanggal',
    'Verifikasi',
    'Aktifasi',
  ]

  const fetchData = (
    q?: string,
    page?: number,
    verifyStatus?: string,
    sortBy?: string,
    sortOrder?: string,
  ): void => {
    router.get(
      '/admin/listingsWithAttention',
      {
        ...(q !== '' ? { q } : {}),
        ...(page !== 1 ? { page } : {}),
        ...(verifyStatus !== '' ? { verifyStatus } : {}),
        ...(sortBy !== '' ? { sortBy } : {}),
        ...(sortOrder !== '' ? { sortOrder } : {}),
      },
      {
        preserveState: true,
        preserveScroll: true,
      },
    )
  }

  const getVerifyStatusLabel = (status: string): string => {
    const statusOption = data.verifyStatusOptions.find(
      (v) => v.value === status,
    )
    return statusOption != null ? statusOption.label : 'N/A'
  }

  const getActiveStatusLabel = (status: string): string => {
    const statusOption = data.activeStatusOptions.find(
      (v) => v.value === status,
    )
    return statusOption != null ? statusOption.label : 'N/A'
  }

  useEffect(() => {
    setKeyword(q)
  }, [q])

  const handleSortChange = (e: React.ChangeEvent<HTMLSelectElement>): void => {
    const [sortBy, sortOrder] = e.target.value.split('|')
    setSortBy(sortBy)
    setSortOrder(sortOrder)
    fetchData(keyword, 1, status, sortBy, sortOrder)
    setPageNumber(1)
  }

  return (
    <AuthenticatedLayout
      user={auth.user}
      header={
        <h2 className="text-xl font-semibold leading-tight text-gray-800">
          Attention Listings
        </h2>
      }
    >
      <Head title="Listings" />

      <div className="py-12">
        <div className="mx-auto max-w-7xl sm:px-6 lg:px-8">
          <div className="overflow-hidden bg-white shadow-sm sm:rounded-lg">
            <div className="mb-2 grid grid-cols-4 gap-4 p-6 md:flex-row md:items-center md:gap-8">
              <div className="col-span-3 md:col-span-1">
                <p className="text-2xl font-bold leading-none text-neutral-700">
                  Daftar Listing perlu Atensi
                </p>
              </div>
              <div className="col-span-3 md:col-span-1">
                <SelectInput
                  value={`${currentSortBy}|${currentSortOrder}`}
                  options={[
                    { label: 'Listing Terbaru', value: 'created_at|desc' },
                    { label: 'Listing Terlama', value: 'created_at|asc' },
                    {
                      label: 'Listing Terbaru Diubah',
                      value: 'updated_at|desc',
                    },
                    {
                      label: 'Listing Terlama Diubah',
                      value: 'updated_at|asc',
                    },
                  ]}
                  className="w-full"
                  onChange={handleSortChange}
                />
              </div>
              <div className="col-span-3 md:col-span-1">
                <SelectInput
                  value={status}
                  options={[
                    { label: 'Semua', value: '' },
                    ...data.verifyStatusOptions,
                  ]}
                  className="w-full"
                  onChange={(e) => {
                    fetchData(
                      keyword,
                      1,
                      e.target.value,
                      currentSortBy,
                      currentSortOrder,
                    )
                    setVerifyStatus(e.target.value)
                    setPageNumber(1)
                  }}
                />
              </div>
              <div className="col-span-3 md:col-span-1">
                <TextInput
                  value={keyword}
                  placeholder="Cari berdasarkan alamat, id atau no HP"
                  className="w-full"
                  onKeyDown={(e) => {
                    if (e.key === 'Enter') {
                      fetchData(
                        keyword,
                        1,
                        status,
                        currentSortBy,
                        currentSortOrder,
                      )
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
                      head === 'KT' || head === 'KM'
                        ? 'w-[60px]'
                        : head === 'Judul'
                          ? 'w-[130px]'
                          : 'w-[100px]'
                    }
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
                      address,
                      user,
                      price,
                      lotSize,
                      buildingSize,
                      bedroomCount,
                      additionalBedroomCount,
                      bathroomCount,
                      additionalBathroomCount,
                      verifyStatus,
                      activeStatus,
                      createdAt,
                    },
                    index,
                  ) => (
                    <tr
                      key={index}
                      className="cursor-pointer"
                      onClick={(event) => {
                        if (event.metaKey || event.ctrlKey) {
                          window.open(`/admin/listings/${id}`, '_blank')
                        } else {
                          router.get(`/admin/listings/${id}`)
                        }
                      }}
                    >
                      <Table.BodyItem>
                        {address != null && (
                          <Tooltip content={address}>
                            <span>
                              {address.length > 10
                                ? `${address.substring(0, 15)}...`
                                : address}
                              <span style={{ color: 'red', marginLeft: '8px' }}>
                                !
                              </span>
                            </span>
                          </Tooltip>
                        )}
                      </Table.BodyItem>
                      <Table.BodyItem>{user?.name}</Table.BodyItem>
                      <Table.BodyItem>{user?.phoneNumber}</Table.BodyItem>
                      <Table.BodyItem>
                        {new Intl.NumberFormat('id-ID', {
                          currency: 'IDR',
                          style: 'currency',
                          notation: 'compact',
                        }).format(price)}
                      </Table.BodyItem>
                      <Table.BodyItem className="font-normal text-neutral-600">
                        {`${lotSize}`} m&sup2;
                      </Table.BodyItem>
                      <Table.BodyItem className="font-normal text-neutral-600">
                        {`${buildingSize}`} m&sup2;
                      </Table.BodyItem>
                      <Table.BodyItem>
                        {additionalBedroomCount > 0
                          ? `${bedroomCount}+${additionalBedroomCount}`
                          : `${bedroomCount}`}
                      </Table.BodyItem>
                      <Table.BodyItem>
                        {additionalBathroomCount > 0
                          ? `${bathroomCount}+${additionalBathroomCount}`
                          : `${bathroomCount}`}
                      </Table.BodyItem>
                      <Table.BodyItem>{`${String(createdAt)}`}</Table.BodyItem>
                      <Table.BodyItem>
                        <span
                          className={`${
                            verifyStatus === 'approved'
                              ? 'bg-green-100 text-green-800'
                              : verifyStatus === 'rejected'
                                ? 'bg-red-100 text-red-800'
                                : verifyStatus === 'on_review'
                                  ? 'bg-yellow-100 text-yellow-800'
                                  : 'bg-gray-100 text-gray-800'
                          } me-2 truncate rounded-full px-2.5 py-0.5 text-xs font-medium`}
                        >
                          {getVerifyStatusLabel(verifyStatus)}
                        </span>
                      </Table.BodyItem>
                      <Table.BodyItem>
                        <span
                          className={`${
                            activeStatus === 'active'
                              ? 'bg-green-100 text-green-800'
                              : activeStatus === 'archived'
                                ? 'bg-red-100 text-red-800'
                                : activeStatus === 'waitlisted'
                                  ? 'bg-yellow-100 text-yellow-800'
                                  : 'bg-gray-100 text-gray-800'
                          } me-2 truncate rounded-full px-2.5 py-0.5 text-xs font-medium`}
                        >
                          {getActiveStatusLabel(activeStatus)}
                        </span>
                      </Table.BodyItem>
                    </tr>
                  ),
                )}
                {data.listings.length === 0 && (
                  <tr>
                    <Table.BodyItem
                      colSpan={11}
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
                    fetchData(
                      keyword,
                      page,
                      status,
                      currentSortBy,
                      currentSortOrder,
                    )
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
                        const newPage = item + 1
                        fetchData(
                          keyword,
                          newPage,
                          status,
                          currentSortBy,
                          currentSortOrder,
                        )
                        setPageNumber(newPage)
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
                    fetchData(
                      keyword,
                      page,
                      status,
                      currentSortBy,
                      currentSortOrder,
                    )
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
