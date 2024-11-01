import { useState } from 'react'
import { Head, Link, router } from '@inertiajs/react'
import { Button, Checkbox, Input, Typography } from '@material-tailwind/react'
import { toast } from 'react-toastify'

import MemberTable from './MemberTable'

import Table from '@/Components/Table'
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout'
import { type DPUser, type PageProps } from '@/types'

const Detail = ({
  auth,
  data,
}: PageProps<{
  data: { member: DPUser; delegate?: DPUser; principals?: DPUser[] }
}>): JSX.Element => {
  const { member, delegate, principals } = data
  const [isDelegate, setIsDelegate] = useState(member.isDelegateEligible)

  const TABLE_HEAD = [
    {
      label: 'Nama',
      key: 'name',
    },
    {
      label: 'Nomor HP',
      key: 'phoneNumber',
    },
    {
      label: 'Kota',
      key: 'cityName',
    },
    {
      label: 'Perusahaan',
      key: 'company',
    },
  ]

  const handleSave = (): void => {
    router.put(
      `/admin/members/${member.id}`,
      {
        isDelegateEligible: isDelegate,
      },
      {
        preserveState: true,
        onSuccess: () => {
          toast.success('Berhasil disimpan')
        },
        onError: (errors) => {
          toast.error(`Maaf, terjadi kesalahan: ${errors.message}`)
        },
      },
    )
  }

  return (
    <AuthenticatedLayout
      user={auth.user}
      header={
        <h2 className="text-xl font-semibold leading-tight text-gray-800">
          Detail Member
          <span className="text-base font-normal"> #{member.userId}</span>
        </h2>
      }
    >
      <Head title="Members" />
      <div className="mx-auto max-w-7xl space-y-4 p-6 md:space-y-0 lg:px-8">
        <div className="grid grid-cols-1 gap-4 md:grid-flow-col md:grid-cols-2 md:grid-rows-10 lg:grid-cols-3 lg:grid-rows-9">
          <div className="space-y-1 md:col-span-2 md:row-span-3 lg:col-span-3">
            <Typography variant="h6" color="blue-gray">
              Foto Profil
            </Typography>
            <img
              src={member.picture ?? '/images/logo_icon.svg'}
              alt="Profile Preview"
              className="size-20 rounded-full object-cover"
            />
          </div>
          <div className="col-span-1 space-y-1 md:row-span-2">
            <Typography variant="h6" color="blue-gray">
              Nama
            </Typography>
            <Input value={member.name ?? '-'} disabled />
          </div>
          <div className="col-span-1 space-y-1 md:row-span-2 lg:row-span-4">
            <Typography variant="h6" color="blue-gray">
              Nomor HP
            </Typography>
            <Input value={member.phoneNumber} disabled />
          </div>
          <div className="col-span-1 space-y-1 md:row-span-2">
            <Typography variant="h6" color="blue-gray">
              Perusahaan
            </Typography>
            <Input value={member.company ?? '-'} disabled />
          </div>
          <div className="col-span-1 space-y-1 md:row-span-2">
            <Typography variant="h6" color="blue-gray">
              Kota
            </Typography>
            <Input value={member.cityName ?? '-'} disabled />
          </div>
          <div className="col-span-1 grid items-start space-y-1 md:row-span-2 md:grid-cols-2">
            <Checkbox
              checked={isDelegate}
              onChange={(e) => setIsDelegate(e.target.checked)}
              label={
                <Typography
                  color="gray"
                  variant="small"
                  className="font-normal"
                >
                  Sebagai Delegasi
                </Typography>
              }
              containerProps={{ className: '-ml-2.5' }}
            />
            {member.isDelegateEligible !== isDelegate ? (
              <div className="inline-block text-right">
                <Button variant="text" onClick={handleSave}>
                  Simpan
                </Button>
              </div>
            ) : null}
          </div>
          <div className="col-span-1 space-y-1 md:row-span-3">
            <Typography variant="h6" color="blue-gray">
              Delegasi
            </Typography>
            <Table>
              <Table.Body>
                <tr
                  className="cursor-pointer"
                  onClick={(event) => {
                    if (!delegate) return
                    if (event.metaKey || event.ctrlKey) {
                      window.open(`/admin/members/${delegate?.id}`, '_blank')
                    } else {
                      router.get(`/admin/members/${delegate?.id}`)
                    }
                  }}
                >
                  <Table.BodyItem>
                    <Typography className="leading-normal text-neutral-600">
                      {delegate?.name}
                    </Typography>
                    <Typography
                      variant="small"
                      className="truncate leading-none text-blue-gray-300"
                    >
                      #{delegate?.userId}
                    </Typography>
                  </Table.BodyItem>
                  <Table.BodyItem>{delegate?.phoneNumber}</Table.BodyItem>
                </tr>
              </Table.Body>
            </Table>
          </div>
        </div>

        {principals?.length ? (
          <div className="space-y-1">
            <div className="flex items-center">
              <Typography variant="h6" color="blue-gray">
                Principals
              </Typography>
              <Link
                href={`/admin/members?delegatePhone=${encodeURIComponent(member?.phoneNumber)}`}
                className="ml-auto inline-block text-sm font-medium text-blue-500 hover:text-blue-600"
              >
                Lihat semua principals
              </Link>
            </div>
            <MemberTable headers={TABLE_HEAD} members={principals} />
          </div>
        ) : null}
      </div>
    </AuthenticatedLayout>
  )
}

export default Detail
