import { useState } from 'react'
import { Head, router } from '@inertiajs/react'
import AsyncSelect from 'react-select/async'

import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout'
import TextInput from '@/Components/TextInput'
import Table from '@/Components/Table'
import { type DPUser, type PageProps } from '@/types'
import { getSearchParams, paginationRange } from '@/utils'
import SecondaryButton from '@/Components/SecondaryButton'
import SelectInput from '@/Components/SelectInput'

const Member = ({
  auth,
  data,
}: PageProps<{
  data: { members: DPUser[]; lastPage: number }
}>): JSX.Element => {
  const [keyword, setKeyword] = useState(getSearchParams('q') ?? '')
  const [pageNumber, setPageNumber] = useState(
    parseInt(getSearchParams('page') ?? '1'),
  )

  const [startPage, endPage] = paginationRange(pageNumber, data.lastPage)

  const TABLE_HEAD = ['Member', 'Nomor HP', 'Kota', 'Perusahaan', 'Delegasi']

  const fetchData = (q?: string, page?: number): void => {
    router.get(
      '/admin/members',
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
          Members
        </h2>
      }
    >
      <Head title="Members" />

      <div className="py-12">
        <div className="mx-auto max-w-7xl sm:px-6 lg:px-8">
          <div className="overflow-hidden bg-white shadow-sm sm:rounded-lg">
            <div className="mb-2 grid grid-cols-4 gap-4 p-6 md:flex-row md:items-center md:gap-8">
              <div className="col-span-4 md:col-span-1">
                <p className="text-2xl font-bold leading-none text-neutral-700">
                  Daftar Member
                </p>
              </div>
              <div className="col-span-4 md:col-span-1">
                <SelectInput
                  className="w-full"
                  options={[
                    { value: '', label: 'Semua' },
                    { value: 'true', label: 'Delegasi' },
                  ]}
                />
              </div>
              <div className="col-span-4 md:col-span-1">
                <AsyncSelect
                  cacheOptions
                  defaultOptions
                  placeholder="No HP Delegasi"
                  noOptionsMessage={() => 'Ketik nomor HP untuk mencari'}
                  classNames={{
                    placeholder: () => 'truncate',
                    indicatorSeparator: () => 'hidden',
                    control: () => 'h-[42px] !rounded-md shadow-sm',
                  }}
                  styles={{
                    input: (base) => ({
                      ...base,
                      'input:focus': {
                        boxShadow: 'none',
                      },
                    }),
                  }}
                />
              </div>
              <div className="col-span-4 md:col-span-1">
                <TextInput
                  value={keyword}
                  placeholder="No HP atau nama"
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
                  <Table.HeaderItem key={head}>{head}</Table.HeaderItem>
                ))}
              </Table.Header>
              <Table.Body>
                {data.members.map((member, index) => (
                  <tr key={index}>
                    <Table.BodyItem>
                      <div className="flex items-center gap-3">
                        <img
                          className="inline-block size-10 rounded-full ring-2 ring-white"
                          src={
                            member?.picture ??
                            '/images/logo_icon.svg' /* TODO: Wire picture URL here */
                          }
                          alt={member.name}
                        />
                        <p className="font-normal leading-none text-neutral-600">
                          {member?.name ?? '-'}
                        </p>
                      </div>
                    </Table.BodyItem>
                    <Table.BodyItem>{member.phoneNumber}</Table.BodyItem>
                    <Table.BodyItem>{member?.cityName ?? '-'}</Table.BodyItem>
                    <Table.BodyItem>{member?.company ?? '-'}</Table.BodyItem>
                    <Table.BodyItem>
                      {member?.isDelegateEligible ? 'Ya' : '-'}
                    </Table.BodyItem>
                  </tr>
                ))}
                {data.members.length === 0 && (
                  <tr>
                    <Table.BodyItem colSpan={5} className="text-center text-sm">
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

export default Member
