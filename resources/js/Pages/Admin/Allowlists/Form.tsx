import { Head, router } from '@inertiajs/react'

import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout'

import { type PageProps, type TelegramGroupAllowlist } from '@/types'
import TextInput from '@/Components/TextInput'
import InputLabel from '@/Components/InputLabel'
import TextArea from '@/Components/TextArea'
import SelectInput from '@/Components/SelectInput'
import PrimaryButton from '@/Components/PrimaryButton'
import SecondaryButton from '@/Components/SecondaryButton'

const AllowlistDetail = ({
  auth,
  data
}: PageProps<{
  data: { allowlist: TelegramGroupAllowlist }
}>): JSX.Element => {
  const allowlistUpdate = (e: React.FormEvent<HTMLFormElement>) => {
    e.preventDefault()
    const formData = new FormData(e.target as HTMLFormElement);
    router.post('/admin/telegram/allowlists/' + data.allowlist.id, formData);
  }

  return (
        <AuthenticatedLayout
            user={auth.user}
            header={
                <h2 className="font-semibold text-xl text-gray-800 leading-tight">
                    Allowlist Details
                </h2>
            }
        >
            <Head title="Allowlist Detail" />
            <div className="py-12">
                <div className="max-w-7xl mx-auto sm:px-6 lg:px-8">
                    <div className="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                        <div className="p-6 mb-2 grid grid-cols-3 gap-4 md:gap-8 md:flex-row md:items-center">
                            <div className="col-span-3 md:col-span-2">
                                <p className="font-bold text-2xl leading-none text-neutral-700">
                                    Allowlist Detail : {data.allowlist.groupName ?? data.allowlist.id}
                                </p>
                            </div>
                        </div>
                        <form onSubmit={allowlistUpdate}>
                            <div className="p-6 grid grid-cols-3 gap-4 md:gap-8 md:flex-row md:items-center">
                                <div className="col-span-2 md:col-span-2">
                                    <InputLabel value="Nama Group" />
                                    <div className="mt-1">
                                        <TextInput name="groupName" defaultValue={data.allowlist.groupName ?? ''}  className="w-full"/>
                                    </div>
                                </div>
                                <div className="col-span-2 md:col-span-2">
                                    <label className="block text-sm font-medium text-neutral-700">
                                        Contoh Pesan
                                    </label>
                                    <div className="mt-1">
                                        <TextArea defaultValue={data.allowlist.sampleMessage ?? ''} className="w-full" rows={20} readOnly/>
                                        <small>*Pesan masuk tanggal: {data.allowlist.createdAt}</small>
                                    </div>
                                </div>
                                <div className="col-span-2 md:col-span-2">
                                    <InputLabel value="Status" />
                                    <div className="mt-1">
                                        <SelectInput name="allowed" options={[{value: 0, label: 'Tidak Diijinkan'}, {value: 1, label: 'Diijinkan'}]} defaultValue={data.allowlist.allowed ? 1 : 0}/>
                                    </div>
                                </div>
                                <div className="col-span-2">
                                    <PrimaryButton className="md:col-span-2 mr-5">Simpan</PrimaryButton>
                                    <SecondaryButton className="md:col-span-2" onClick={() => router.visit('/admin/telegram/allowlists')}>Kembali</SecondaryButton>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

        </AuthenticatedLayout>
  )
}

export default AllowlistDetail
