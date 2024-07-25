import React, { Fragment, useEffect, useState, type PropsWithChildren } from 'react'
import { Head, router } from '@inertiajs/react'
import { Button, Carousel, Tooltip } from '@material-tailwind/react'
import { InformationCircleIcon, ExclamationCircleIcon } from '@heroicons/react/24/outline'

import GoogleMaps from '@/Components/GoogleMaps'
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout'
import { replaceWithBr } from '@/utils'
import {
  BathIconSVG,
  BedIconSVG,
  HouseIconSVG,
  LotIconSVG
} from '@/Assets/Icons'
import type { Listing, Option, PageProps } from '@/types'
import StatusDialog from './StatusDialog'

export const LISTING_ICON: Record<string, JSX.Element> = {
  buildingSize: <HouseIconSVG />,
  lotSize: <LotIconSVG />,
  bedroomCount: <BedIconSVG />,
  bathroomCount: <BathIconSVG />
}

const ListingItem = ({
  item,
  children
}: PropsWithChildren<{ item: string }>): JSX.Element => {
  return (
    <div className="flex items-center gap-1 text-slate-800 mr-3">
        {LISTING_ICON[item]}
        {children}
    </div>
  )
}

const TableItem = ({
  title,
  children
}: PropsWithChildren<{ title: string }>): JSX.Element => {
  return (
    <tr className="text-sm md:text-base text-slate-800">
        <td className="w-1/3 min-w-24 align-top">{title}</td>
        <td>: {children}</td>
    </tr>
  )
}

const detectPhoneNumber = (text: string): RegExpMatchArray | never[] => {
  const phoneRegex = /(\+62|62|0)\d{2,4}-?\d{3,4}-?\d{3,4}/g
  const phones = text.match(phoneRegex)
  return phones ?? []
}

export default function index ({
  auth,
  data
}: PageProps<{
  data: {
    listing: Listing
    verifyStatusOptions: Option[]
    activeStatusOptions: Option[]
  }
}>): JSX.Element {
  const { listing, verifyStatusOptions, activeStatusOptions } = data
  const [coord, setCoord] = useState<google.maps.LatLngLiteral>({
    lat: listing.coordinate.latitude,
    lng: listing.coordinate.longitude
  })
  const [phoneNumberWarnings, setPhoneNumberWarnings] = useState('')
  const [verifyStatus, setVerifyStatus] = useState(listing.verifyStatus)
  const [activeStatus, setActiveStatus] = useState(listing.activeStatus)
  const [editMode, setEditMode] = useState(false)
  const [showDialog, setShowDialog] = useState(false)

  useEffect(() => {
    if (listing.description.length > 0) {
      const foundPhones = detectPhoneNumber(listing.description)
      const phonesString = foundPhones.join(', ')
      setPhoneNumberWarnings(phonesString)
    }
  }, [listing.description])

  const updateData = (): void => {
    router.put(
      `/admin/listings/${listing.id}`,
      {
        coordinate: {
          latitude: coord.lat,
          longitude: coord.lng
        }
      }
    )
  }

  const updateStatus = (): void => {
    router.put(`/admin/listings/${listing.id}`, { verifyStatus })
    setEditMode(false)
  }

  return (
        <AuthenticatedLayout
            user={auth.user}
            header={
                <div className='md:flex flex-wrap justify-between relative'>
                    <h2 className="font-semibold text-xl text-gray-800 leading-tight mb-3 md:mb-0">
                        Detail Listing
                    </h2>
                    <div className="flex gap-3">
                        {!editMode
                          ? (
                            <div className="flex">
                              <div className="flex">
                                <span className="pt-2 mr-2">Status Verifikasi: </span>
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
                                    } truncate text-sm font-medium px-6 py-2.5 rounded-lg mr-4`}
                                >
                                    {verifyStatusOptions.find((v) => v.value === listing.verifyStatus)?.label}
                                </span>
                              </div>
                              {verifyStatus === 'approved' && (
                                <div className="flex">
                                  <span className="pt-2 mr-2">Status Aktif: </span>
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
                                      } truncate text-sm font-medium px-6 py-2.5 rounded-lg mr-4`}
                                  >
                                      {activeStatusOptions.find((v) => v.value === listing.activeStatus)?.label}
                                  </span>
                                </div>
                              )}
                              <Button color="green" onClick={() => { setShowDialog(true) }}>
                                Ubah Status
                              </Button>
                            </div>
                            )
                          : (
                              <>
                                  <select
                                      value={verifyStatus}
                                      onChange={(e) => { setVerifyStatus(e.target.value) } }
                                      className="bg-white border border-gray-300 rounded-md text-gray-800 text-sm leading-6 py-1.5 pl-3 pr-8 focus:outline-none focus:ring-1 focus:ring-blue-500 focus:border-blue-500"
                                    >
                                      {verifyStatusOptions.map((option) => (
                                        <option key={option.value} value={option.value}>
                                          {option.label}
                                        </option>
                                      ))}
                                  </select>
                                <Button
                                    color="green"
                                    variant='gradient'
                                    onClick={updateStatus}
                                    className=""
                                  >
                                      Simpan Status
                                </Button>
                              </>
                            )}
                      </div>
                </div>
            }
        >
            <Head title="Listings" />

            <div className="max-w-7xl mx-auto sm:px-6 lg:px-8">
                {listing.pictureUrls.length > 0
                  ? (
                    <Carousel className="h-[512px] w-full bg-neutral-700">
                        {listing.pictureUrls.map((url, index) => (
                            <img
                                src={url}
                                alt={url}
                                key={index}
                                className="h-full w-full object-contain"
                            />
                        ))}
                    </Carousel>
                    )
                  : null}
                <div className="pt-4 md:pt-6">
                    <div className="px-4 md:px-6">
                        <div className="text-lg md:text-xl font-semibold text-slate-500">
                            {listing.title}
                        </div>
                        <div className="mt-1 text-2xl md:text-3xl font-semibold leading-8 text-slate-800">
                            {new Intl.NumberFormat('id-ID', {
                              currency: 'IDR',
                              style: 'currency',
                              notation: 'compact'
                            }).format(listing.price)}
                        </div>
                        <div className="flex justify-between mt-1.5 line-clamp-3 text-xs md:text-sm leading-4 text-slate-500">
                            <div>
                              {listing.address}
                              <div className="text-[11px] text-slate-500">
                                Diposting pada: {listing.createdAt}
                              </div>
                            </div>
                            <div className="text-sm">
                            <table>
                              <tbody>
                                <tr>
                                  <td>Nama</td>
                                  <td>: {((listing.user?.name) != null) ? listing.user.name : '-'}</td>
                                </tr>
                                <tr>
                                  <td>No HP</td>
                                  <td>: {((listing.user?.phoneNumber) != null) ? listing.user.phoneNumber : '-'}</td>
                                </tr>
                              </tbody>
                            </table>
                          </div>
                        </div>
                    </div>
                    <div className="mt-1 px-4 md:px-6 flex flex-col flex-wrap content-start border-y border-solid border-y-slate-200 py-2">
                        <div className="flex flex-wrap">
                            {Object.keys(listing).map((item, id) => {
                              const listItem =
                                listing[item as keyof typeof listing]
                              return typeof listItem !== 'object' &&
                                listItem !== undefined
                                ? (
                                    <Fragment key={id}>
                                        {(() => {
                                          switch (item as keyof Listing) {
                                            case 'bedroomCount':
                                              return (
                                                <ListingItem item={item}>
                                                    {listItem} KT
                                                </ListingItem>
                                              )
                                            case 'bathroomCount':
                                              return (
                                                <ListingItem item={item}>
                                                    {listItem} KM
                                                </ListingItem>
                                              )
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
                                  )
                                : null
                            })}
                        </div>
                    </div>
                    <div className='px-4 md:px-6 grid md:grid-cols-3 gap-4'>
                        <div className="py-3 space-y-2">
                            <div className="space-y-1">
                                <h1 className="font-semibold leading-7 text-slate-500">
                                    Detail Listing
                                </h1>
                                <table>
                                    <tbody>
                                        {Object.keys(listing).map((item, id) => {
                                          const listItem =
                                            listing[item as keyof typeof listing]
                                          return typeof listItem !== 'object' &&
                                            listItem !== undefined
                                            ? (
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
                                                            <TableItem title="Kota">
                                                                {listItem}
                                                            </TableItem>
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
                                                        default:
                                                          return null
                                                      }
                                                    })()}
                                                </Fragment>
                                              )
                                            : null
                                        })}
                                    </tbody>
                                </table>
                            </div>
                            <div className="space-y-1">
                                <h1 className="font-semibold leading-7 text-slate-500">
                                    Deskripsi
                                </h1>
                                <p
                                    dangerouslySetInnerHTML={{
                                      __html: replaceWithBr(listing.description)
                                    }}
                                    className="text-sm md:text-base text-slate-800 whitespace-pre-wrap"
                                />
                            </div>
                            {phoneNumberWarnings.length > 0 && (
                              <div className="text-red-500 text-sm flex items-start pt-5">
                                <ExclamationCircleIcon className="w-5 h-5 mr-2" />
                                Terdapat No HP {phoneNumberWarnings} di deskripsi.
                              </div>
                            )}
                        </div>
                        <div className='py-3 md:col-span-2 space-y-2 md:space-y-0'>
                            <div className='flex flex-wrap md:mb-2 w-full items-center text-sm md:text-base text-slate-800 space-y-2 md:space-y-0'>
                                <div className='flex basis-1/5 min-w-24 gap-1 items-center'>
                                    Koordinat
                                    <Tooltip
                                        className="border border-slate-50 bg-white px-4 py-3 shadow-md"
                                        content={
                                            <div className="w-80">
                                                <div className='text-sm text-slate-500'>
                                                    Ketuk map atau pindahkan pin di dalam map untuk mendapatkan koordinat yang sesuai
                                                </div>
                                            </div>
                                        }
                                    >
                                        <InformationCircleIcon className="w-5 h-5 cursor-pointer text-slate-500" />
                                    </Tooltip>
                                </div>
                                <div className='md:basis-3/5 flex gap-2 items-center'>
                                    <div className='relative'>
                                        <label className="absolute left-2 top-[50%] -translate-y-[50%] text-gray-400">
                                            Lat
                                        </label>
                                        <input
                                            readOnly
                                            disabled
                                            value={coord.lat ?? ''}
                                            className='text-right w-full border-gray-300 rounded-md shadow-sm'
                                        />
                                    </div>
                                    <div className='relative'>
                                        <label className="absolute left-2 top-[50%] -translate-y-[50%] text-gray-400">
                                            Lng
                                        </label>
                                        <input
                                            readOnly
                                            disabled
                                            value={coord.lng ?? ''}
                                            className='text-right w-full border-gray-300 rounded-md shadow-sm'
                                        />
                                    </div>
                                </div>
                                <div className='basis-full md:basis-1/5 text-right'>
                                    <Button
                                        color="blue"
                                        variant='gradient'
                                        onClick={updateData}
                                    >
                                        Simpan
                                    </Button>
                                </div>
                            </div>
                            <GoogleMaps coord={coord} setCoord={setCoord} />
                        </div>
                    </div>
                </div>
            </div>
            {listing?.user?.name != null && (
                <nav className="bg-ribbon-50 border-t border-solid border-t-slate-200">
                    <div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-3 flex items-center justify-between">
                        <div className="flex gap-3 items-center">
                            {listing?.user?.profilePictureURL != null
                              ? (
                                <img
                                    className="h-12 w-12 rounded-full object-cover shadow"
                                    src={listing?.user?.profilePictureURL}
                                    alt={listing?.user?.name}
                                />
                                )
                              : null}
                            <div className='text-base'>
                                <p className="text-slate-800">{listing?.user?.name}</p>
                                <p className="text-slate-500">{listing?.user?.company ?? 'Independen'}</p>
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
              currentNote={listing.adminNote}
            />
        </AuthenticatedLayout>
  )
}
