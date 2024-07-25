import React, { useEffect, useState } from 'react'
import { router } from '@inertiajs/react'
import {
  Dialog,
  DialogHeader,
  DialogBody,
  Button
} from '@material-tailwind/react'

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
  currentNote: string
}

const StatusDialog: React.FC<StatusDialogProps> = ({
  showDialog,
  setShowDialog,
  listingId,
  verifyStatusOptions,
  activeStatusOptions,
  currentVerifyStatus,
  currentActiveStatus,
  currentNote
}) => {
  const [verifyStatus, setVerifyStatus] = useState(currentVerifyStatus)
  const [activeStatus, setActiveStatus] = useState(currentActiveStatus)
  const [note, setNotes] = useState('')

  useEffect(() => {
    setVerifyStatus(currentVerifyStatus)
    setActiveStatus(currentActiveStatus)
  }, [currentVerifyStatus, currentActiveStatus])

  const handleSubmit = (event: { preventDefault: () => void }): void => {
    event.preventDefault()
    router.put(`/admin/listings/${listingId}`, {
      verifyStatus,
      activeStatus,
      adminNote: note
    }, {
      preserveState: true,
      onSuccess: () => {
        console.log('Update status successful')
        setShowDialog(false)
      },
      onError: errors => {
        console.error('Error updating status', errors)
      }
    })
  }

  if (!showDialog) return null

  return (
    <Dialog size="sm" open={showDialog} handler={() => { setShowDialog(false) }}>
      <DialogHeader>
        Ubah Status
      </DialogHeader>
      <DialogBody>
        <form onSubmit={handleSubmit} className="dialog">
            <div className="mb-3">
                <label>Status Verifikasi</label>
                <select
                    name="verifyStatus"
                    className="w-full border-solid border-gray-300 rounded-lg"
                    onChange={e => { setVerifyStatus(e.target.value) }}
                >
                    {verifyStatusOptions.map(option => (
                        <option key={option.value} value={option.value} selected={option.value === verifyStatus}>{option.label}</option>
                    ))}
                </select>
            </div>
            <div className="mb-3">
                <label>Status Aktif</label>
                <select
                    name="activeStatus"
                    onChange={e => { setActiveStatus(e.target.value) }}
                    className="w-full border-solid border-gray-300 rounded-lg"
                >
                    <option value="">Pilih Status Aktif</option>
                    {activeStatusOptions.map(option => (
                        <option key={option.value} value={option.value} selected={option.value === activeStatus}>{option.label}</option>
                    ))}
                </select>
            </div>
            <div className="mb-3">
                <label>Catatan:</label>
                <textarea
                    name="note"
                    onChange={e => { setNotes(e.target.value) }}
                    className="w-full border-solid border-gray-300 rounded-lg"
                >{currentNote}</textarea>
            </div>
            <div className="actions justify-end gap-3 flex mt-5">
                <Button type="button" onClick={() => { setShowDialog(false) }}>Batal</Button>
                <Button color="green" type="submit">Simpan</Button>
            </div>
        </form>
      </DialogBody>
    </Dialog>
  )
}

export default StatusDialog
