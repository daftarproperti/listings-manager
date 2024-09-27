// We call this "DPUser" because the existing use of "User" refers to admin user.
// TODO: Rename User to AdminUser instead so we can use "User" for DP user.
export interface DPUser {
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
