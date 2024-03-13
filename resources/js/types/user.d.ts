export interface TelegramUser {
  user_id: number
  username: string
  first_name: string
  last_name: string
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
