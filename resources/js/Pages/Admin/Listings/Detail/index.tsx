import React, {
  type Dispatch,
  type SetStateAction,
  type PropsWithChildren,
  Fragment,
  useEffect,
  useState,
} from 'react'
import { Head, router, usePage } from '@inertiajs/react'
import { Button, Carousel, Tooltip, Typography } from '@material-tailwind/react'
import {
  InformationCircleIcon,
  ChevronDownIcon,
  SparklesIcon,
} from '@heroicons/react/24/outline'

import StatusDialog from './StatusDialog'

import GoogleMaps from '@/Components/GoogleMaps'
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout'
import { replaceWithBr } from '@/utils'
import {
  BathIconSVG,
  BedIconSVG,
  HouseIconSVG,
  LotIconSVG,
} from '@/Assets/Icons'
import type {
  Listing,
  LikelyConnectedListing,
  Option,
  PageProps,
} from '@/types'
import { type ListingHistory } from '@/types/listing'

export const LISTING_ICON: Record<string, JSX.Element> = {
  buildingSize: <HouseIconSVG />,
  lotSize: <LotIconSVG />,
  bedroomCount: <BedIconSVG />,
  bathroomCount: <BathIconSVG />,
}

const ListingItem = ({
  item,
  children,
}: PropsWithChildren<{ item: string }>): JSX.Element => {
  return (
    <div className="mr-3 flex items-center gap-1 text-slate-800">
      {LISTING_ICON[item]}
      {children}
    </div>
  )
}

const TableItem = ({
  title,
  children,
}: PropsWithChildren<{ title: string }>): JSX.Element => {
  return (
    <tr className="text-sm text-slate-800 md:text-base">
      <td className="w-1/3 min-w-24 align-top">{title}</td>
      <td>: {children}</td>
    </tr>
  )
}

export default function ListingDetailPage({
  auth,
  data,
}: PageProps<{
  data: {
    listing: Listing
    listingHistory: ListingHistory[]
    likelyConnectedListing: LikelyConnectedListing[]
    verifyStatusOptions: Option[]
    activeStatusOptions: Option[]
    needsAdminAttention: boolean
  }
}>): JSX.Element {
  const {
    listing,
    verifyStatusOptions,
    activeStatusOptions,
    needsAdminAttention,
  } = data
  const [coord, setBaseCoord] = useState<google.maps.LatLngLiteral>({
    lat: listing.coordinate.latitude,
    lng: listing.coordinate.longitude,
  })
  const { errors } = usePage().props
  const [showDialog, setShowDialog] = useState(false)
  const [showAdminNote, setShowAdminNote] = useState(false)
  const [note, setNote] = useState<string>(listing.adminNote?.message ?? '')
  const [showNoteForm, setShowNoteForm] = useState(false)
  const [unsavedChanges, setUnsavedChanges] = useState(false)
  const [aiReviewResponse, setAiReviewResponse] = useState<string[]>([])
  const [aiReviewStatus, setAiReviewStatus] = useState<string>('')
  const [aiReviewIsOutdated, setAiReviewIsOutdated] = useState(true)
  const [attentionRemoved, setAttentionRemoved] = useState(!needsAdminAttention)
  const [errorMessage, setErrorMessage] = useState<string | null>(null)

  const handleDate = (dateInput: string): Date => {
    const months: Record<string, string> = {
      Januari: 'January',
      Februari: 'February',
      Maret: 'March',
      April: 'April',
      Mei: 'May',
      Juni: 'June',
      Juli: 'July',
      Agustus: 'August',
      September: 'September',
      Oktober: 'October',
      November: 'November',
      Desember: 'December',
    }

    // Check if the input contains an Indonesian month and convert it
    for (const [indonesianMonth, englishMonth] of Object.entries(months)) {
      if (dateInput.includes(indonesianMonth)) {
        dateInput = dateInput.replace(indonesianMonth, englishMonth)
        break
      }
    }

    const date = new Date(dateInput)

    // Check if the resulting date is valid
    return isNaN(date.getTime()) ? new Date() : date
  }

  const handleDateInput = (input: React.ReactNode): string => {
    if (typeof input === 'string' || typeof input === 'number') {
      return String(input) // Safely convert to a string
    }
    return ''
  }

  const updateData = (): void => {
    router.put(`/admin/listings/${listing.id}`, {
      coordinate: {
        latitude: coord.lat,
        longitude: coord.lng,
      },
      revision: listing.revision,
    })
    setUnsavedChanges(false)
  }

  const handleRemoveAttention = async () => {
    try {
      router.delete(`/admin/listings/${listing.id}/remove-attention`, {
        onSuccess: () => {
          setAttentionRemoved(true)
          router.reload()
        },
      })
    } catch (error) {
      console.error('Error removing attention:', error)
    }
  }

  const doAiReview = async (): Promise<void> => {
    try {
      setAiReviewStatus('processing')
      setAiReviewResponse([])
      router.post(`/admin/listings/${listing.id}/ai-review`)

      await getAiReview()
    } catch (error) {
      console.error('Error during AI Review:', error)
      // Handle error (e.g., show an error message to the user)
    }
  }

  const getAiReview = async (): Promise<void> => {
    try {
      const response = await fetch(`/admin/listings/${listing.id}/ai-review`, {
        method: 'GET',
        headers: {
          Accept: 'application/json',
        },
      })

      if (response.ok) {
        const data = await response.json()

        let results: string[] = []
        let status: string = 'processing'

        if (Array.isArray(data.results)) {
          results = data.results
        }

        if (typeof data.status === 'string') {
          status = data.status
        }

        setAiReviewResponse(results)
        setAiReviewStatus(status)

        if (status === 'done') {
          const listingDate = handleDateInput(listing.updatedAt)
          if (handleDate(data.updatedAt as string) < handleDate(listingDate)) {
            setAiReviewIsOutdated(true)
          } else {
            setAiReviewIsOutdated(false)
          }
        } else if (status === 'processing') {
          setTimeout(async function () {
            await getAiReview()
          }, 5000)
        }
      }
    } catch (error) {
      console.error('Error during AI Review request:', error)
    }
  }

  const setCoord: Dispatch<SetStateAction<google.maps.LatLngLiteral>> = (
    newCoord,
  ) => {
    if (typeof newCoord === 'function') {
      setBaseCoord((prevCoord) => {
        const updatedCoord = newCoord(prevCoord)
        setUnsavedChanges(true)
        return updatedCoord
      })
    } else {
      setBaseCoord(newCoord)
      setUnsavedChanges(true)
    }
  }

  useEffect(() => {
    if (errors?.error) {
      setErrorMessage(errors.error)
      setShowDialog(false)
    }
  }, [errors])

  useEffect(() => {
    void (async () => {
      const fetchAiReview = async (): Promise<void> => {
        await getAiReview() // Call your async function
      }
      await fetchAiReview()

      const handleBeforeUnload = (event: BeforeUnloadEvent): void => {
        if (unsavedChanges) {
          event.preventDefault()
          event.returnValue = ''
        }
      }

      if (unsavedChanges) {
        window.addEventListener('beforeunload', handleBeforeUnload)
      }

      return () => {
        window.removeEventListener('beforeunload', handleBeforeUnload)
      }
    })()
  }, [unsavedChanges])

  const handleUpdateNote = (): void => {
    router.put(
      `/admin/listings/${listing.id}`,
      {
        adminNote: note,
        revision: listing.revision,
      },
      {
        preserveScroll: true,
        preserveState: true,
        onSuccess: () => {
          setShowNoteForm(false)
          router.reload()
        },
      },
    )
  }

  return (
    <AuthenticatedLayout
      user={auth.user}
      header={
        <div className="relative flex-wrap justify-between md:flex">
          <h2 className="mb-0 pt-2 text-xl font-semibold leading-tight text-gray-800">
            Detail Listing
          </h2>
          <div className="lg:jus flex gap-3">
            <div className="mb-2 flex lg:mb-0">
              <span className="mr-2 pt-3 text-sm">Status Verifikasi: </span>
              <span
                className={`${
                  listing.verifyStatus === 'approved'
                    ? 'bg-green-100 text-green-800'
                    : listing.verifyStatus === 'rejected'
                      ? 'bg-red-100 text-red-800'
                      : listing.verifyStatus === 'on_review'
                        ? 'bg-yellow-100 text-yellow-800'
                        : 'bg-gray-100 text-gray-800'
                } mr-4 h-10 truncate rounded-lg px-6 py-2.5 text-sm font-medium`}
              >
                {
                  verifyStatusOptions.find(
                    (v) => v.value === listing.verifyStatus,
                  )?.label
                }
              </span>
            </div>
            {listing.verifyStatus === 'approved' && (
              <div className="mb-2 flex lg:mb-0">
                <span className="mr-2 pt-3 text-sm">Status Aktif: </span>
                <span
                  className={`${
                    listing.activeStatus === 'active'
                      ? 'bg-green-100 text-green-800'
                      : listing.activeStatus === 'archived'
                        ? 'bg-red-100 text-red-800'
                        : listing.activeStatus === 'waitlisted'
                          ? 'bg-yellow-100 text-yellow-800'
                          : 'bg-gray-100 text-gray-800'
                  } mr-4 h-10 truncate rounded-lg px-6 py-2.5 text-sm font-medium`}
                >
                  {
                    activeStatusOptions.find(
                      (v) => v.value === listing.activeStatus,
                    )?.label
                  }
                </span>
              </div>
            )}
            <div className="lg:justify-end lg:text-right">
              <Button
                color="blue"
                onClick={handleRemoveAttention}
                disabled={attentionRemoved}
                className="mb-2 lg:mb-0"
              >
                Hapus Atensi
              </Button>
            </div>
            <div className="lg:justify-end lg:text-right">
              <Button
                color="blue"
                onClick={() => {
                  setShowDialog(true)
                }}
                className="mb-2 lg:mb-0"
              >
                Ubah Status
              </Button>
            </div>
          </div>
        </div>
      }
    >
      <Head title="Listings" />
      <div className="mx-auto max-w-7xl sm:px-6 lg:px-8">
        {errorMessage && (
          <div className="mb-4 mt-2 rounded bg-red-100 p-4 text-red-700">
            {errorMessage}
          </div>
        )}
        {listing.pictureUrls.length > 0 ? (
          <Carousel className="h-[512px] w-full bg-neutral-700">
            {listing.pictureUrls.map((url, index) => (
              <img
                src={url}
                alt={url}
                key={index}
                className="size-full object-contain"
              />
            ))}
          </Carousel>
        ) : null}
        <div className="pt-4 md:pt-6">
          <div className="px-4 md:px-6">
            {data.likelyConnectedListing.length > 0 && (
              <div className="mb-4 rounded-lg bg-red-600 p-3 text-white">
                <h3 className="font-semibold">
                  Sepertinya ada data yang mirip dengan Listing ini. Silahkan
                  cek daftar berikut:
                </h3>
                <br />
                <table className="min-w-full text-sm md:text-base">
                  <thead>
                    <tr className="text-left">
                      <th className="pb-2">Address</th>
                      <th className="pb-2">Pictures</th>
                    </tr>
                  </thead>
                  <tbody>
                    {data.likelyConnectedListing.map((connectedListing) => (
                      <tr
                        key={connectedListing.id}
                        className="border-t border-red-500"
                      >
                        <td className="py-2">{connectedListing.address}</td>
                        <td className="py-2">
                          <div className="group relative">
                            <Carousel className="size-24">
                              {connectedListing.pictureUrls
                                .slice(0, 3)
                                .map((url, index) => (
                                  <img
                                    src={url}
                                    alt={`Image ${index + 1}`}
                                    key={index}
                                    className="size-full rounded object-cover shadow"
                                  />
                                ))}
                            </Carousel>
                          </div>
                        </td>
                      </tr>
                    ))}
                  </tbody>
                </table>
              </div>
            )}
            <Typography variant="small" className="mb-2">
              Listing ID: {listing.listingIdStr}
            </Typography>
            <div className="text-lg font-semibold text-slate-500 md:text-xl">
              {listing.address}
            </div>
            <div className="mt-1 text-2xl font-semibold leading-8 text-slate-800 md:text-3xl">
              {new Intl.NumberFormat('id-ID', {
                currency: 'IDR',
                style: 'currency',
                notation: 'compact',
              }).format(listing.price)}
            </div>
            <div className="mt-1.5 line-clamp-3 flex justify-between text-xs leading-4 text-slate-500 md:text-sm">
              <div>
                <div className="text-[11px] text-slate-500">
                  Diposting pada: {listing.createdAt}
                </div>
                <div className="text-[11px] text-slate-500">
                  Diperbarui pada: {listing.updatedAt}
                </div>
              </div>
              <div className="text-sm">
                <table>
                  <tbody>
                    <tr>
                      <td>Nama</td>
                      <td>
                        : {listing.user?.name != null ? listing.user.name : '-'}
                      </td>
                    </tr>
                    <tr>
                      <td>No HP</td>
                      <td>
                        :{' '}
                        {listing.user?.phoneNumber != null
                          ? listing.user.phoneNumber
                          : '-'}
                      </td>
                    </tr>
                  </tbody>
                </table>
              </div>
            </div>
          </div>
          <div className="mt-1 flex flex-col flex-wrap content-start border-y border-solid border-y-slate-200 px-4 py-2 md:px-6">
            <div className="flex flex-wrap">
              {Object.keys(listing).map((item, id) => {
                const listItem = listing[item as keyof typeof listing]
                return typeof listItem !== 'object' &&
                  listItem !== undefined ? (
                  <Fragment key={id}>
                    {(() => {
                      switch (item as keyof Listing) {
                        case 'bedroomCount': {
                          const additionalBedrooms =
                            listing.additionalBedroomCount > 0
                              ? `+${listing.additionalBedroomCount}`
                              : ''
                          return (
                            <ListingItem item={item}>
                              {listItem}
                              {additionalBedrooms} KT
                            </ListingItem>
                          )
                        }
                        case 'bathroomCount': {
                          const additionalBathrooms =
                            listing.additionalBathroomCount > 0
                              ? `+${listing.additionalBathroomCount}`
                              : ''
                          return (
                            <ListingItem item={item}>
                              {listItem}
                              {additionalBathrooms} KM
                            </ListingItem>
                          )
                        }
                        case 'lotSize':
                        case 'buildingSize':
                          return (
                            <ListingItem item={item}>
                              {listItem} m&sup2;
                            </ListingItem>
                          )
                        default:
                          return null
                      }
                    })()}
                  </Fragment>
                ) : null
              })}
            </div>
          </div>
          <div className="grid gap-4 px-4 md:grid-cols-3 md:px-6">
            <div className="py-3">
              <div className="space-y-1">
                <h1 className="font-semibold leading-7 text-slate-500">
                  Detail Listing
                </h1>
                <table>
                  <tbody>
                    {Object.keys(listing).map((item, id) => {
                      const listItem = listing[item as keyof typeof listing]
                      return typeof listItem !== 'object' &&
                        listItem !== undefined ? (
                        <Fragment key={id}>
                          {(() => {
                            switch (item as keyof Listing) {
                              case 'address':
                                return (
                                  <TableItem title="Alamat">
                                    {listItem}
                                  </TableItem>
                                )
                              case 'cityName':
                                return (
                                  <TableItem title="Kota">{listItem}</TableItem>
                                )
                              case 'facing':
                                return (
                                  <TableItem title="Hadap">
                                    {listItem}
                                  </TableItem>
                                )
                              case 'floorCount':
                                return (
                                  <TableItem title="Lantai">
                                    {listItem}
                                  </TableItem>
                                )
                              case 'ownership':
                                return (
                                  <TableItem title="Sertifikat">
                                    {listItem}
                                  </TableItem>
                                )
                              case 'electricPower':
                                return (
                                  <TableItem title="Listrik">
                                    {listItem}
                                  </TableItem>
                                )
                              case 'carCount':
                                return (
                                  <TableItem title="Kapasitas Mobil">
                                    {listItem}
                                  </TableItem>
                                )
                              case 'isMultipleUnits':
                                return (
                                  <TableItem title="Multiple Unit">
                                    {listing.isMultipleUnits ? 'Ya' : 'Tidak'}
                                  </TableItem>
                                )
                              case 'withRewardAgreement':
                                return (
                                  <TableItem title="Setuju Imbalan">
                                    {listing.withRewardAgreement
                                      ? 'Ya'
                                      : 'Tidak'}
                                  </TableItem>
                                )
                              default:
                                return null
                            }
                          })()}
                        </Fragment>
                      ) : null
                    })}
                  </tbody>
                </table>
              </div>
              <div className="mb-5 space-y-1">
                <h1 className="font-semibold leading-7 text-slate-500">
                  Deskripsi
                </h1>
                <p
                  dangerouslySetInnerHTML={{
                    __html: replaceWithBr(listing.description),
                  }}
                  className="whitespace-pre-wrap text-sm text-slate-800 md:text-base"
                />
              </div>

              <div className="mb-5 space-y-1 border border-gray-200 p-4">
                <Button
                  className="mb-3 inline-flex items-center text-xs"
                  color="indigo"
                  onClick={() => {
                    void doAiReview()
                  }}
                  // disabled={aiReviewStatus === 'processing' || !aiReviewIsOutdated}
                >
                  <SparklesIcon className="mr-2 size-4" />
                  {aiReviewStatus === 'processing'
                    ? 'Menunggu Ai Review...'
                    : aiReviewIsOutdated
                      ? '(Outdated) Jalankan Ai Review'
                      : 'Ai Review UP-TO-DATE'}
                </Button>
                {aiReviewStatus === 'processing' && (
                  <div className="text-xs text-gray-500">
                    Ai Review sedang diproses. Silahkan refresh halaman secara
                    berkala untuk memuat hasil.
                  </div>
                )}
                <div className="text-sm text-red-500">
                  {aiReviewResponse.length > 0 && (
                    <ul className="ml-5 list-disc">
                      {aiReviewResponse.map((item, index) => (
                        <li key={index} className="mb-1">
                          {item}
                        </li>
                      ))}
                    </ul>
                  )}
                </div>
              </div>

              <div>
                Catatan untuk{' '}
                <span className="font-bold text-red-500">
                  dilihat pendaftar
                </span>
                :
              </div>
              <div className="mb-5 rounded-lg bg-gray-200 p-4">
                <div className="flex justify-between">
                  {listing.adminNote?.message !== undefined &&
                    listing.adminNote?.message !== '' && (
                      <div
                        className="flex cursor-pointer pt-4 text-xs text-blue-600 hover:text-blue-800"
                        onClick={() => {
                          setShowAdminNote(!showAdminNote)
                        }}
                      >
                        <span>Lihat Catatan</span>
                        <ChevronDownIcon className="size-4" />
                      </div>
                    )}
                  {!showNoteForm && (
                    <Button
                      color="blue"
                      onClick={() => {
                        setShowNoteForm(!showNoteForm)
                      }}
                    >
                      {listing.adminNote?.message !== undefined
                        ? 'Ubah'
                        : 'Tambah'}{' '}
                      Catatan
                    </Button>
                  )}
                </div>
                {showNoteForm && (
                  <>
                    <textarea
                      value={note}
                      onChange={(e) => {
                        setNote(e.target.value)
                      }}
                      placeholder="Tambah catatan"
                      className="mt-3 min-h-56 w-full rounded-lg border-gray-300 p-3"
                    ></textarea>
                    <div className="mt-2 flex justify-end gap-3">
                      <Button
                        color="gray"
                        onClick={() => {
                          setShowNoteForm(!showNoteForm)
                        }}
                      >
                        Batal
                      </Button>
                      <Button color="green" onClick={handleUpdateNote}>
                        Simpan
                      </Button>
                    </div>
                  </>
                )}
                {showAdminNote &&
                  listing.adminNote?.message !== undefined &&
                  listing.adminNote?.message !== '' && (
                    <div className="pt-4 text-sm">
                      <p
                        dangerouslySetInnerHTML={{
                          __html: replaceWithBr(listing.adminNote?.message),
                        }}
                        className="whitespace-pre-wrap text-sm text-slate-800"
                      />
                      <p>{listing.adminNote?.email}</p>
                    </div>
                  )}
              </div>
            </div>
            <div className="space-y-2 py-3 md:col-span-2 md:space-y-0">
              <div className="flex w-full flex-wrap items-center space-y-2 text-sm text-slate-800 md:mb-2 md:space-y-0 md:text-base">
                <div className="flex min-w-24 basis-1/5 items-center gap-1">
                  Koordinat
                  <Tooltip
                    className="border border-slate-50 bg-white px-4 py-3 shadow-md"
                    content={
                      <div className="w-80">
                        <div className="text-sm text-slate-500">
                          Ketuk map atau pindahkan pin di dalam map untuk
                          mendapatkan koordinat yang sesuai
                        </div>
                      </div>
                    }
                  >
                    <InformationCircleIcon className="size-5 cursor-pointer text-slate-500" />
                  </Tooltip>
                </div>
                <div className="flex items-center gap-2 md:basis-3/5">
                  <div className="relative">
                    <label className="absolute left-2 top-1/2 -translate-y-1/2 text-gray-400">
                      Lat
                    </label>
                    <input
                      readOnly
                      disabled
                      value={coord.lat ?? ''}
                      className="w-full rounded-md border-gray-300 text-right shadow-sm"
                    />
                  </div>
                  <div className="relative">
                    <label className="absolute left-2 top-1/2 -translate-y-1/2 text-gray-400">
                      Lng
                    </label>
                    <input
                      readOnly
                      disabled
                      value={coord.lng ?? ''}
                      className="w-full rounded-md border-gray-300 text-right shadow-sm"
                    />
                  </div>
                </div>
                <div className="basis-full text-right md:basis-1/5">
                  <Button color="blue" variant="gradient" onClick={updateData}>
                    Simpan
                  </Button>
                </div>
              </div>
              <GoogleMaps coord={coord} setCoord={setCoord} />
            </div>
          </div>
        </div>
        <div className="px-4 py-6 md:px-6">
          <h1 className="mb-4 font-semibold leading-7 text-slate-500">
            Riwayat Perubahan
          </h1>

          {data.listingHistory.length > 0 ? (
            data.listingHistory.map((history, index) => (
              <div key={index} className="mb-6">
                <h2 className="text-md mb-2 font-semibold text-slate-800">
                  Perubahan pada{' '}
                  {new Date(history.created_at).toLocaleDateString('en-US', {
                    year: 'numeric',
                    month: 'long',
                    day: 'numeric',
                    hour: '2-digit',
                    minute: '2-digit',
                    second: '2-digit',
                  })}
                </h2>
                {Object.keys(history.changes).length > 0 ? (
                  <div className="overflow-x-auto">
                    <table className="min-w-full table-auto text-left text-sm text-gray-500">
                      <thead>
                        <tr className="bg-gray-100 text-xs uppercase text-gray-700">
                          <th className="px-6 py-3">Atribut</th>
                          <th className="px-6 py-3">Sebelum</th>
                          <th className="px-6 py-3">Sesudah</th>
                        </tr>
                      </thead>
                      <tbody>
                        {Object.keys(history.changes).length > 0 &&
                        Object.values(history.changes).some(
                          (change) =>
                            change.before !== null || change.after !== null,
                        ) ? (
                          Object.keys(history.changes).map(
                            (field, fieldIndex) => {
                              const before = history.changes[field]?.before
                              const after = history.changes[field]?.after
                              if (
                                typeof before === 'object' ||
                                typeof after === 'object'
                              ) {
                                return (
                                  <tr key={fieldIndex} className="border-b">
                                    <td className="px-6 py-4 text-gray-900">
                                      {field}
                                    </td>
                                    <td className="px-6 py-4">
                                      {JSON.stringify(before) ?? '-'}
                                    </td>
                                    <td className="px-6 py-4">
                                      {JSON.stringify(after) ?? '-'}
                                    </td>
                                  </tr>
                                )
                              }

                              if (before !== null && after !== null) {
                                return (
                                  <tr key={fieldIndex} className="border-b">
                                    <td className="px-6 py-4 text-gray-900">
                                      {field}
                                    </td>
                                    <td className="px-6 py-4">
                                      {before ?? '-'}
                                    </td>
                                    <td className="px-6 py-4">
                                      {after ?? '-'}
                                    </td>
                                  </tr>
                                )
                              }
                              return null
                            },
                          )
                        ) : (
                          <tr>
                            <td className="py-4 text-center">
                              Tidak ada perubahan
                            </td>
                          </tr>
                        )}
                      </tbody>
                    </table>
                  </div>
                ) : (
                  <p className="text-sm text-gray-500">Tidak ada perubahan</p>
                )}
              </div>
            ))
          ) : (
            <p className="text-sm text-gray-500">Tidak ada riwayat</p>
          )}
        </div>
      </div>
      {listing?.user?.name != null && (
        <nav className="border-t border-solid border-t-slate-200 bg-ribbon-50">
          <div className="mx-auto flex max-w-7xl items-center justify-between px-4 py-3 sm:px-6 lg:px-8">
            <div className="flex items-center gap-3">
              {listing?.user?.profilePictureURL != null ? (
                <img
                  className="size-12 rounded-full object-cover shadow"
                  src={listing?.user?.profilePictureURL}
                  alt={listing?.user?.name}
                />
              ) : null}
              <div className="text-base">
                <p className="text-slate-800">{listing?.user?.name}</p>
                <p className="text-slate-500">
                  {listing?.user?.company ?? 'Independen'}
                </p>
              </div>
            </div>
          </div>
        </nav>
      )}
      <StatusDialog
        showDialog={showDialog}
        setShowDialog={setShowDialog}
        listingId={listing.id}
        verifyStatusOptions={data.verifyStatusOptions}
        activeStatusOptions={data.activeStatusOptions}
        currentVerifyStatus={listing.verifyStatus}
        currentActiveStatus={listing.activeStatus}
        currentExpiredAt={listing.rawExpiredAt}
        revision={listing.revision}
      />
    </AuthenticatedLayout>
  )
}
