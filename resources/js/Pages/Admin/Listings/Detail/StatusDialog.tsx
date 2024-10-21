import React, { useEffect, useState } from 'react'
import { router } from '@inertiajs/react'
import {
  Dialog,
  DialogHeader,
  DialogBody,
  Button,
} from '@material-tailwind/react'
import DatePicker from 'react-datepicker'
import 'react-datepicker/dist/react-datepicker.css'
import { parseISO, isValid, format, addMonths, addYears } from 'date-fns'

interface Option {
  value: string
  label: string
}

interface StatusDialogProps {
  showDialog: boolean
  setShowDialog: (show: boolean) => void
  listingId: string
  verifyStatusOptions: Option[]
  activeStatusOptions: Option[]
  currentVerifyStatus: string
  currentActiveStatus: string
  currentExpiredAt: Date
  revision: number
}

const StatusDialog: React.FC<StatusDialogProps> = ({
  showDialog,
  setShowDialog,
  listingId,
  verifyStatusOptions,
  activeStatusOptions,
  currentVerifyStatus,
  currentActiveStatus,
  currentExpiredAt,
  revision,
}) => {
  const [verifyStatus, setVerifyStatus] = useState(currentVerifyStatus)
  const [activeStatus, setActiveStatus] = useState(currentActiveStatus)
  const parseDate = (date: string | Date | null | undefined): Date | null => {
    if (typeof date === 'string') {
      const parsed = parseISO(date)
      return isValid(parsed) ? parsed : null
    } else if (date instanceof Date) {
      return isValid(date) ? date : null
    } else {
      return null
    }
  }

  const [expiredAt, setExpiredAt] = useState<Date | null>(
    currentExpiredAt != null ? parseDate(currentExpiredAt) : null,
  )
  const [, setFormattedDate] = useState<string>('')

  useEffect(() => {
    const parsedDate = parseDate(currentExpiredAt)
    setExpiredAt(parsedDate)
  }, [currentExpiredAt])

  useEffect(() => {
    setVerifyStatus(currentVerifyStatus)
    setActiveStatus(currentActiveStatus)
  }, [currentVerifyStatus, currentActiveStatus])

  useEffect(() => {
    if (verifyStatus === 'approved') {
      setActiveStatus('active')
    } else {
      setActiveStatus('')
    }
  }, [verifyStatus])

  const adjustDate = (months: number, years: number): void => {
    setExpiredAt((current) => {
      const newDate =
        current != null
          ? addMonths(addYears(current, years), months)
          : addMonths(addYears(new Date(), years), months)
      return newDate
    })
  }

  const handleSubmit = (event: { preventDefault: () => void }): void => {
    event.preventDefault()
    router.put(
      `/admin/listings/${listingId}`,
      {
        verifyStatus,
        activeStatus,
        expiredAt: expiredAt != null ? expiredAt.toISOString() : null,
        revision,
      },
      {
        preserveState: true,
        onSuccess: () => {
          setShowDialog(false)
        },
        onError: (errors) => {
          console.error('Error updating status', errors)
        },
      },
    )
  }

  if (!showDialog) return null

  return (
    <Dialog
      size="sm"
      open={showDialog}
      handler={() => {
        setShowDialog(false)
      }}
    >
      <DialogHeader>Ubah Status</DialogHeader>
      <DialogBody>
        <form onSubmit={handleSubmit} className="dialog">
          <div className="mb-3">
            <label>Status Verifikasi</label>
            <select
              name="verifyStatus"
              className="w-full rounded-lg border-solid border-gray-300"
              onChange={(e) => {
                setVerifyStatus(e.target.value)
              }}
            >
              {verifyStatusOptions.map((option) => (
                <option
                  key={option.value}
                  value={option.value}
                  selected={option.value === verifyStatus}
                >
                  {option.label}
                </option>
              ))}
            </select>
          </div>
          <div className="mb-3">
            <label>Status Aktif</label>
            <select
              name="activeStatus"
              onChange={(e) => {
                setActiveStatus(e.target.value)
              }}
              className="w-full rounded-lg border-solid border-gray-300"
            >
              <option value="">Pilih Status Aktif</option>
              {activeStatusOptions.map((option) => (
                <option
                  key={option.value}
                  value={option.value}
                  selected={option.value === activeStatus}
                >
                  {option.label}
                </option>
              ))}
            </select>
          </div>
          <div className="mb-3">
            <label className="block w-full">Aktif sampai Tanggal</label>
            <div className="flex gap-2">
              <DatePicker
                selected={expiredAt}
                onChange={(date: Date | null) => {
                  setExpiredAt(date)
                  const newFormattedDate =
                    date != null ? format(date, 'yyyy-MM-dd HH:mm:ss') : ''
                  setFormattedDate(newFormattedDate)
                }}
                className="w-full rounded-lg border-solid border-gray-300"
                showTimeSelect
                dateFormat="yyyy-MM-dd HH:mm"
                name="expiredAt"
                autoComplete="off"
              />
              <div className="flex gap-2 py-1">
                <Button
                  type="button"
                  color="light-blue"
                  size="sm"
                  onClick={() => {
                    adjustDate(3, 0)
                  }}
                >
                  +3 Bulan
                </Button>
                <Button
                  type="button"
                  color="light-blue"
                  size="sm"
                  onClick={() => {
                    adjustDate(6, 0)
                  }}
                >
                  +6 Bulan
                </Button>
                <Button
                  type="button"
                  color="light-blue"
                  size="sm"
                  onClick={() => {
                    adjustDate(0, 1)
                  }}
                >
                  +1 Tahun
                </Button>
                <Button
                  type="button"
                  color="light-blue"
                  size="sm"
                  onClick={() => {
                    adjustDate(0, 2)
                  }}
                >
                  +2 Tahun
                </Button>
              </div>
            </div>
          </div>
          <div className="actions mt-8 flex justify-end gap-3">
            <Button
              type="button"
              onClick={() => {
                setShowDialog(false)
              }}
            >
              Batal
            </Button>
            <Button color="green" type="submit">
              Simpan
            </Button>
          </div>
        </form>
      </DialogBody>
    </Dialog>
  )
}

export default StatusDialog
