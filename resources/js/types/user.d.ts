// We call this "DPUser" because the existing use of "User" refers to admin user.
// TODO: Rename User to AdminUser instead so we can use "User" for DP user.
export interface DPUser {
  id: string
  userId: number
  username: string
  phoneNumber: string
  name?: string
  cityName?: string
  company?: string
  picture?: string
  isDelegateEligible?: boolean
  delegatePhone?: string
}
