export interface Agent {
  first_name: string
  last_name: string
  username: string
  profile: Profile
}

export interface Profile {
  name: string
  phoneNumber: string
  city: string
  description: string
  company: string
  picture: string
}
