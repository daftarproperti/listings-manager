export interface TelegramGroupAllowlist {
    id: string
    chatId: number
    allowed: boolean
    sampleMessage: string
    groupName?: string
    createdAt: string
}
