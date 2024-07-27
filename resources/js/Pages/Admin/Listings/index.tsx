import React, { useEffect, useState } from 'react'
import { Head, router } from '@inertiajs/react'

import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout'
import Table from '@/Components/Table'
import SecondaryButton from '@/Components/SecondaryButton'
import TextInput from '@/Components/TextInput'

import { type Option, type Listing, type PageProps } from '@/types'
import { getSearchParams, paginationRange } from '@/utils'
import SelectInput from '@/Components/SelectInput'

export default function index ({
  auth,
  data
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

  const [startPage, endPage] = paginationRange(page, data.lastPage)

  const [keyword, setKeyword] = useState(q)
  const [, setPageNumber] = useState(page)
  const [, setVerifyStatus] = useState(status)

  const TABLE_HEAD = ['Judul', 'Agen', 'No HP','Harga', 'LT', 'LB', 'KT', 'KM', 'Tanggal', 'Verifikasi', 'Aktifasi']

  const fetchData = (
    q?: string,
    page?: number,
    verifyStatus?: string
  ): void => {
    router.get(
      '/admin/listings',
      {
        ...(q !== '' ? { q } : {}),
        ...(page !== 1 ? { page } : {}),
        ...(verifyStatus !== '' ? { verifyStatus } : {})
      },
      {
        preserveState: true,
        preserveScroll: true
      }
    )
  }

  const getVerifyStatusLabel = (status: string): string => {
    const statusOption = data.verifyStatusOptions.find(v => v.value === status)
    return statusOption != null ? statusOption.label : 'N/A'
  }

  const getActiveStatusLabel = (status: string): string => {
    const statusOption = data.activeStatusOptions.find(v => v.value === status)
    return statusOption != null ? statusOption.label : 'N/A'
  }

  useEffect(() => {
    setKeyword(q)
  }, [q])

  return (
        <AuthenticatedLayout
            user={auth.user}
            header={
                <h2 className="font-semibold text-xl text-gray-800 leading-tight">
                    Listings
                </h2>
            }
        >
            <Head title="Listings" />

            <div className="py-12">
                <div className="max-w-7xl mx-auto sm:px-6 lg:px-8">
                    <div className="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                        <div className="p-6 mb-2 grid grid-cols-4 gap-4 md:gap-8 md:flex-row md:items-center">
                            <div className="col-span-4 md:col-span-2">
                                <p className="font-bold text-2xl leading-none text-neutral-700">
                                    Daftar Listing
                                </p>
                            </div>
                            <div className="col-span-4 md:col-span-1">
                                <SelectInput
                                    value={status}
                                    options={[{ label: 'Semua', value: '' }, ...data.verifyStatusOptions]}
                                    className="w-full"
                                    onChange={(e) => {
                                      fetchData(keyword, 1, e.target.value)
                                      setVerifyStatus(e.target.value)
                                      setPageNumber(1)
                                    }}
                                />
                            </div>
                            <div className="col-span-4 md:col-span-1">
                                <TextInput
                                    value={keyword}
                                    placeholder="Cari berdasarkan judul atau id"
                                    className="w-full"
                                    onKeyDown={(e) => {
                                      if (e.key === 'Enter') {
                                        fetchData(keyword, 1, status)
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
                                        colSpan={head === 'Judul' ? 2 : 1}
                                        className={head === 'KT' || head === 'KM' ? 'w-[80px]' : 'w-[100px]'}
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
                                      price,
                                      lotSize,
                                      buildingSize,
                                      bedroomCount,
                                      bathroomCount,
                                      verifyStatus,
                                      activeStatus,
                                      createdAt
                                    },
                                    index
                                  ) => (
                                        <tr
                                            key={index}
                                            className="cursor-pointer"
                                            onClick={() => {
                                              router.get(`/admin/listings/${id}`)
                                            }}
                                        >
                                            <Table.BodyItem colSpan={2}>
                                                {title}
                                            </Table.BodyItem>
                                            <Table.BodyItem>
                                                {user?.name}
                                            </Table.BodyItem>
                                            <Table.BodyItem>
                                                {user?.phoneNumber}
                                            </Table.BodyItem>
                                            <Table.BodyItem>
                                                {new Intl.NumberFormat(
                                                  'id-ID',
                                                  {
                                                    currency: 'IDR',
                                                    style: 'currency',
                                                    notation: 'compact'
                                                  }
                                                ).format(price)}
                                            </Table.BodyItem>
                                            <Table.BodyItem className='text-neutral-600 font-normal'>
                                                {`${lotSize}`} m&sup2;
                                            </Table.BodyItem>
                                            <Table.BodyItem className='text-neutral-600 font-normal'>
                                                {`${buildingSize}`} m&sup2;
                                            </Table.BodyItem>
                                            <Table.BodyItem>
                                                {`${bedroomCount}`}
                                            </Table.BodyItem>
                                            <Table.BodyItem>
                                                {`${bathroomCount}`}
                                            </Table.BodyItem>
                                            <Table.BodyItem>
                                                {`${String(createdAt)}`}
                                            </Table.BodyItem>
                                            <Table.BodyItem>
                                                <span
                                                    className={`${
                                                        verifyStatus ===
                                                        'approved'
                                                            ? 'bg-green-100 text-green-800'
                                                            : verifyStatus ===
                                                              'rejected'
                                                            ? 'bg-red-100 text-red-800'
                                                            : verifyStatus ===
                                                              'on_review'
                                                            ? 'bg-yellow-100 text-yellow-800'
                                                            : 'bg-gray-100 text-gray-800'
                                                    } truncate text-xs font-medium me-2 px-2.5 py-0.5 rounded-full`}
                                                >
                                                    {getVerifyStatusLabel(verifyStatus)}
                                                </span>
                                            </Table.BodyItem>
                                            <Table.BodyItem>
                                                <span
                                                    className={`${
                                                        activeStatus ===
                                                        'active'
                                                            ? 'bg-green-100 text-green-800'
                                                            : activeStatus ===
                                                              'archived'
                                                            ? 'bg-red-100 text-red-800'
                                                            : activeStatus ===
                                                              'waitlisted'
                                                            ? 'bg-yellow-100 text-yellow-800'
                                                            : 'bg-gray-100 text-gray-800'
                                                    } truncate text-xs font-medium me-2 px-2.5 py-0.5 rounded-full`}
                                                >
                                                    {getActiveStatusLabel(activeStatus)}
                                                </span>
                                            </Table.BodyItem>
                                        </tr>
                                  )
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
