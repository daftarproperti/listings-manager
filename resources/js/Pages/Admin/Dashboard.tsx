import React, { useState } from 'react'
import { Head, router } from '@inertiajs/react'

import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout'
import Table from '@/Components/Table'
import SecondaryButton from '@/Components/SecondaryButton'
import TextInput from '@/Components/TextInput'
import { type Listing, type PageProps } from '@/types'
import { getSearchParams, paginationRange } from '@/utils'

export default function Dashboard({
  auth,
  data,
}: PageProps<{
  data: { listings: Listing[]; lastPage: number }
}>): JSX.Element {
  const [keyword, setKeyword] = useState(getSearchParams('q') ?? '')
  const [pageNumber, setPageNumber] = useState(
    parseInt(getSearchParams('page') ?? '1'),
  )

  const [startPage, endPage] = paginationRange(pageNumber, data.lastPage)

  const TABLE_HEAD = ['Judul', 'Agen', 'Harga', 'LT', 'LB', 'KT', 'KM']

  const fetchData = (q?: string, page?: number): void => {
    router.get(
      '/admin/dashboard',
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
          Dashboard
        </h2>
      }
    >
      <Head title="Dashboard" />

      <div className="py-12">
        <div className="mx-auto max-w-7xl sm:px-6 lg:px-8">
          <div className="overflow-hidden bg-white shadow-sm sm:rounded-lg">
            <div className="mb-2 grid grid-cols-3 gap-4 p-6 md:flex-row md:items-center md:gap-8">
              <div className="col-span-3 md:col-span-2">
                <p className="text-2xl font-bold leading-none text-neutral-700">
                  Daftar Properti
                </p>
              </div>
              <div className="col-span-3 md:col-span-1">
                <TextInput
                  value={keyword}
                  placeholder="Search"
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
                    colSpan={head === 'Judul' ? 3 : 1}
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
                    },
                    index,
                  ) => (
                    <tr key={index}>
                      <Table.BodyItem colSpan={3}>{title}</Table.BodyItem>
                      <Table.BodyItem>{user?.name}</Table.BodyItem>
                      <Table.BodyItem>
                        {new Intl.NumberFormat('id-ID', {
                          currency: 'IDR',
                          style: 'currency',
                          notation: 'compact',
                        }).format(price)}
                      </Table.BodyItem>
                      <Table.BodyItem>{`${lotSize}`}</Table.BodyItem>
                      <Table.BodyItem>{`${buildingSize}`}</Table.BodyItem>
                      <Table.BodyItem>{`${bedroomCount}`}</Table.BodyItem>
                      <Table.BodyItem>{`${bathroomCount}`}</Table.BodyItem>
                    </tr>
                  ),
                )}
                {data.listings.length === 0 && (
                  <tr>
                    <Table.BodyItem colSpan={9} className="text-center text-sm">
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
