export interface TelegramUser {
  user_id: number
  username: string
  phoneNumber: string
  name: string
  cityId: number
  company: string
  profile?: Profile
}

export interface Profile {
  name: string
  phoneNumber: string
  city: string
  description: string
  company: string
  picture: string
  isPublicProfile: boolean
}
