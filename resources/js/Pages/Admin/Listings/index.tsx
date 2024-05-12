import React, { useState } from 'react'
import { Head, router } from '@inertiajs/react'

import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout'
import Table from '@/Components/Table'
import SecondaryButton from '@/Components/SecondaryButton'
import TextInput from '@/Components/TextInput'

import { type Option, type Listing, type PageProps } from '@/types'
import { getSearchParams } from '@/utils'
import SelectInput from '@/Components/SelectInput'

export default function index ({
  auth,
  data
}: PageProps<{
  data: {
    listings: Listing[]
    lastPage: number
    verifyStatusOptions: Option[]
  }
}>): JSX.Element {
  const [keyword, setKeyword] = useState(getSearchParams('q') ?? '')
  const [pageNumber, setPageNumber] = useState(
    parseInt(getSearchParams('page') ?? '1')
  )
  const [verifyStatus, setVerifyStatus] = useState(
    getSearchParams('verifyStatus') ?? ''
  )

  const TABLE_HEAD = ['Judul', 'Agen', 'Harga', 'LT', 'LB', 'KT', 'KM', 'Status']

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
                                    value={verifyStatus}
                                    options={data.verifyStatusOptions}
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
                                        fetchData(keyword, 1, verifyStatus)
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
                                    >
                                        {head}
                                    </Table.HeaderItem>
                                ))}
                            </Table.Header>
                            <Table.Body>
                                {data.listings.map(
                                  (
                                    {
                                      title,
                                      user,
                                      price,
                                      lotSize,
                                      buildingSize,
                                      bedroomCount,
                                      bathroomCount,
                                      verifyStatus
                                    },
                                    index
                                  ) => (
                                        <tr key={index}>
                                            <Table.BodyItem colSpan={2}>
                                                {title}
                                            </Table.BodyItem>
                                            <Table.BodyItem>
                                                {user?.name}
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
                                                <span
                                                    className={`${
                                                        verifyStatus ===
                                                        'approved'
                                                            ? 'bg-green-100 text-green-800'
                                                            : verifyStatus ===
                                                              'rejected'
                                                            ? 'bg-red-100 text-red-800'
                                                            : 'bg-yellow-100 text-yellow-800'
                                                    } truncate text-xs font-medium me-2 px-2.5 py-0.5 rounded-full`}
                                                >
                                                    {
                                                      data.verifyStatusOptions.find(
                                                        (v) =>
                                                          v.value ===
                                                          verifyStatus
                                                      )?.label
                                                    }
                                                </span>
                                            </Table.BodyItem>
                                        </tr>
                                  )
                                )}
                                {data.listings.length === 0 && (
                                    <tr>
                                        <Table.BodyItem
                                            colSpan={10}
                                            className="text-center text-sm"
                                        >
                                            No data
                                        </Table.BodyItem>
                                    </tr>
                                )}
                            </Table.Body>
                        </Table>
                        <div className="flex items-center justify-between p-4">
                            <SecondaryButton
                                onClick={() => {
                                  setPageNumber((prev) => {
                                    const page = prev - 1
                                    fetchData(keyword, page, verifyStatus)
                                    return page
                                  })
                                }}
                                disabled={pageNumber === 1}
                            >
                                Previous
                            </SecondaryButton>
                            <div className="flex items-center gap-2">
                                {Array.from(Array(data.lastPage).keys()).map(
                                  (item) => (
                                        <SecondaryButton
                                            key={item}
                                            className={
                                                pageNumber === item + 1
                                                  ? 'bg-stone-200'
                                                  : ''
                                            }
                                            onClick={() => {
                                              const page = item + 1
                                              fetchData(keyword, page, verifyStatus)
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
                                    fetchData(keyword, page, verifyStatus)
                                    return page
                                  })
                                }}
                                disabled={pageNumber === data.lastPage}
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
