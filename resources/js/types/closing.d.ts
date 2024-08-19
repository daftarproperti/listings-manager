export interface Closing {
    id: string
    listingId: number
    closingType: string
    clientName: string
    clientPhoneNumber: string
    transactionValue: number
    date: string
    status: string
    commissionStatus: string|null
    notes: string
}
