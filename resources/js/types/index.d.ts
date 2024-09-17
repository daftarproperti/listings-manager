export interface User {
  id: number
  name: string
  email: string
  email_verified_at: string
}

export type PageProps<T extends Record<string, unknown> = Record<string, unknown>> = T & {
  auth: {
    user: User
  }
}

export type { Option } from './option'
export type { Listing, LikelyConnectedListing } from './listing'
export type { Agent, Profile } from './agent'
export type { TelegramUser } from './user'
export type { Closing } from './closing'
export type { CancellationNote } from './listing'
