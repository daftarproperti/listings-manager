import { Head, router } from '@inertiajs/react'

import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout'

import { type PageProps, type Closing } from '@/types'
import TextInput from '@/Components/TextInput'
import InputLabel from '@/Components/InputLabel'
import TextArea from '@/Components/TextArea'
import SelectInput from '@/Components/SelectInput'
import PrimaryButton from '@/Components/PrimaryButton'
import SecondaryButton from '@/Components/SecondaryButton'

const ClosingForm = ({
  auth,
  data
}: PageProps<{
  data: { closing: Closing }
}>): JSX.Element => {
  const closingUpdate = (e: React.FormEvent<HTMLFormElement>) => {
    e.preventDefault()
    const formData = new FormData(e.target as HTMLFormElement);
    router.post('/admin/closings/' + data.closing.id, formData);
  }

  return (
        <AuthenticatedLayout
            user={auth.user}
            header={
                <h2 className="font-semibold text-xl text-gray-800 leading-tight">
                    Closing Review
                </h2>
            }
        >
            <Head title="Closing Detail" />
            <div className="py-12">
                <div className="max-w-7xl mx-auto sm:px-6 lg:px-8">
                    <div className="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                        <div className="p-6 mb-2 grid grid-cols-3 gap-4 md:gap-8 md:flex-row md:items-center">
                            <div className="col-span-3 md:col-span-2">
                                <p className="font-bold text-2xl leading-none text-neutral-700">
                                    Closing Review
                                </p>
                            </div>
                        </div>
                        <div className="p-6">
                            <a href={`/admin/listings/${data.closing.listingId}`}
                                className="text-white bg-blue-700 hover:bg-blue-800 focus:ring-4 focus:ring-blue-300 font-medium rounded-lg text-sm px-5 py-2.5 focus:outline-none" target="_blank">
                                    Lihat Listing</a>
                        </div>
                        <form onSubmit={closingUpdate}>
                            <div className="p-6 grid grid-cols-3 gap-4 md:gap-8 md:flex-row md:items-center">
                                <div className="col-span-2 md:col-span-2">
                                    <InputLabel value="Type" />
                                    <div className="mt-1">
                                        <TextInput name="type" defaultValue={data.closing.closingType ?? ''}  className="w-full" readOnly/>
                                    </div>
                                </div>
                                <div className="col-span-2 md:col-span-2">
                                    <InputLabel value="Client Name" />
                                    <div className="mt-1">
                                        <TextInput name="clientName" defaultValue={data.closing.clientName ?? ''}  className="w-full" readOnly/>
                                    </div>
                                </div>
                                <div className="col-span-2 md:col-span-2">
                                    <InputLabel value="Client Phone Number" />
                                    <div className="mt-1">
                                        <TextInput name="clientPhoneNumber" defaultValue={data.closing.clientPhoneNumber ?? ''}  className="w-full" readOnly/>
                                    </div>
                                </div>
                                <div className="col-span-2 md:col-span-2">
                                    <InputLabel value="Transaction Value" />
                                    <div className="mt-1">
                                        <TextInput name="transactionValue" defaultValue={data.closing.transactionValue ?? ''}  className="w-full" readOnly/>
                                    </div>
                                </div>
                                <div className="col-span-2 md:col-span-2">
                                    <InputLabel value="Closing Date" />
                                    <div className="mt-1">
                                        <TextInput name="date" defaultValue={data.closing.date ?? ''}  className="w-full" readOnly/>
                                    </div>
                                </div>
                                <div className="col-span-2 md:col-span-2">
                                    <InputLabel value="Status" />
                                    <div className="mt-1">
                                        <SelectInput name="status" options={[{value: 'on_review', label: 'Sedang Ditinjau'}, {value: 'approved', label: 'Disetujui'}, {value: 'rejected', label: 'Ditolak'}]} defaultValue={data.closing.status ?? 'on_review'}/>
                                    </div>
                                </div>
                                <div className="col-span-2 md:col-span-2">
                                    <InputLabel value="Commission Status" />
                                    <div className="mt-1">
                                        <SelectInput name="commissionStatus" options={[{value:'', label: '- Pilih Status -'}, {value: 'pending', label: 'Menunggu Komisi'}, {value: 'paid', label: 'Komisi Telah Dibayarkan'}, {value: 'unpaid', label: 'Komisi Belum Dibayarkan'}]} defaultValue={data.closing.commissionStatus ?? ''}/>
                                    </div>
                                </div>
                                <div className="col-span-2 md:col-span-2">
                                    <InputLabel value="Notes" />
                                    <div className="mt-1">
                                        <TextArea name="notes" defaultValue={data.closing.notes ?? ''}  className="w-full"/>
                                    </div>
                                </div>
                                <div className="col-span-2">
                                    <PrimaryButton className="md:col-span-2 mr-5">Simpan</PrimaryButton>
                                    <SecondaryButton className="md:col-span-2" onClick={() => router.visit('/admin/closings')}>Kembali</SecondaryButton>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

        </AuthenticatedLayout>
  )
}

export default ClosingForm
