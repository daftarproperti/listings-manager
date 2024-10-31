import { Head } from '@inertiajs/react'
import { Input, Typography } from '@material-tailwind/react'

import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout'
import { type DPUser, type PageProps } from '@/types'

const Detail = ({
  auth,
  data,
}: PageProps<{
  data: { member: DPUser }
}>): JSX.Element => {
  return (
    <AuthenticatedLayout
      user={auth.user}
      header={
        <h2 className="text-xl font-semibold leading-tight text-gray-800">
          Detail Member
          <span className="text-base font-normal"> #{data.member.userId}</span>
        </h2>
      }
    >
      <Head title="Members" />
      <div className="mx-auto max-w-7xl p-6 lg:px-8">
        <div className="grid grid-cols-1 gap-4 md:grid-flow-col md:grid-cols-2 md:grid-rows-7 lg:grid-cols-3">
          <div className="space-y-1 md:col-span-2 md:row-span-3 lg:col-span-3">
            <Typography variant="h6" color="blue-gray">
              Foto Profil
            </Typography>
            <img
              src={data.member.picture ?? '/images/logo_icon.svg'}
              alt="Profile Preview"
              className="size-20 rounded-full object-cover"
            />
          </div>
          <div className="col-span-1 space-y-1 md:row-span-2">
            <Typography variant="h6" color="blue-gray">
              Nama
            </Typography>
            <Input value={data.member.name ?? '-'} disabled />
          </div>
          <div className="col-span-1 space-y-1 md:row-span-2">
            <Typography variant="h6" color="blue-gray">
              Nomor HP
            </Typography>
            <Input value={data.member.phoneNumber} disabled />
          </div>
          <div className="col-span-1 space-y-1 md:row-span-2">
            <Typography variant="h6" color="blue-gray">
              Perusahaan
            </Typography>
            <Input value={data.member.company ?? '-'} disabled />
          </div>
          <div className="col-span-1 space-y-1 md:row-span-2">
            <Typography variant="h6" color="blue-gray">
              Kota
            </Typography>
            <Input value={data.member.cityName ?? '-'} disabled />
          </div>
        </div>
      </div>
    </AuthenticatedLayout>
  )
}

export default Detail
