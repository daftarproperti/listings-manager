import React, { useState } from 'react'
import { Head, router } from '@inertiajs/react'

import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout'
import Table from '@/Components/Table'

import { type PageProps, type TelegramGroupAllowlist } from '@/types'
import { getSearchParams, paginationRange } from '@/utils'
import SecondaryButton from '@/Components/SecondaryButton'

const Allowlist = ({
  auth,
  data
}: PageProps<{
  data: { allowlists: TelegramGroupAllowlist[], lastPage: number }
}>): JSX.Element => {
  const [pageNumber, setPageNumber] = useState(
    parseInt(getSearchParams('page') ?? '1')
  )

  const [startPage, endPage] = paginationRange(pageNumber, data.lastPage)

  const TABLE_HEAD = ['Nama Group', 'Contoh Pesan', 'Tanggal Pesan', 'Status', '']

  const fetchData = (page?: number): void => {
    router.get(
      '/admin/telegram/allowlists',
      {
        ...(page !== 1 ? { page } : {})
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
                    Telegram Allowlists
                </h2>
            }
        >
            <Head title="Allowlists" />

            <div className="py-12">
                <div className="max-w-7xl mx-auto sm:px-6 lg:px-8">
                    <div className="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                        <div className="p-6 mb-2 grid grid-cols-3 gap-4 md:gap-8 md:flex-row md:items-center">
                            <div className="col-span-3 md:col-span-2">
                                <p className="font-bold text-2xl leading-none text-neutral-700">
                                    Telegram Group Allowlists
                                </p>
                            </div>
                        </div>
                        <Table>
                            <Table.Header>
                                {TABLE_HEAD.map((head) => (
                                    <Table.HeaderItem key={head}>
                                        {head}
                                    </Table.HeaderItem>
                                ))}
                            </Table.Header>
                            <Table.Body>
                                {data.allowlists.map((allowlist, index) => (
                                    <tr key={index}>
                                        <Table.BodyItem>
                                            {allowlist.groupName}
                                        </Table.BodyItem>
                                        <Table.BodyItem>
                                            {allowlist.sampleMessage}
                                        </Table.BodyItem>
                                        <Table.BodyItem>
                                            {allowlist.createdAt}
                                        </Table.BodyItem>
                                        <Table.BodyItem>
                                            {allowlist.allowed
                                              ? 'Diijinkan'
                                              : 'Tidak Diijinkan'}
                                        </Table.BodyItem>
                                        <Table.BodyItem>
                                            {/* Edit Allowlist */}
                                            <a href={route('telegram.allowlists.detail', allowlist.id)}>
                                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" strokeWidth={1.5} stroke="currentColor" className="w-6 h-6">
                                                    <path strokeLinecap="round" strokeLinejoin="round" d="m16.862 4.487 1.687-1.688a1.875 1.875 0 1 1 2.652 2.652L10.582 16.07a4.5 4.5 0 0 1-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 0 1 1.13-1.897l8.932-8.931Zm0 0L19.5 7.125M18 14v4.75A2.25 2.25 0 0 1 15.75 21H5.25A2.25 2.25 0 0 1 3 18.75V8.25A2.25 2.25 0 0 1 5.25 6H10" />
                                                </svg>
                                            </a>
                                        </Table.BodyItem>
                                    </tr>
                                ))}
                                {data.allowlists.length === 0 && (
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
                                    fetchData(page)
                                    return page
                                  })
                                }}
                                disabled={pageNumber === 1}
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
                                                pageNumber === item + 1
                                                  ? 'bg-stone-200'
                                                  : ''
                                            }
                                            onClick={() => {
                                              const page = item + 1
                                              fetchData(page)
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
                                    fetchData(page)
                                    return page
                                  })
                                }}
                                disabled={pageNumber === data.lastPage}
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

export default Allowlist
