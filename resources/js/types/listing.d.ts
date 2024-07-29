import { type ReactNode } from 'react'

export interface Listing {
  updatedAt: ReactNode
  createdAt: ReactNode
  id: string
  title: string
  address: string
  description: string
  price: number
  lotSize: number
  buildingSize: number
  bedroomCount: number
  bathroomCount: number
  electricPower: number
  floorCount: number
  carCount: number
  facing: string
  ownership: string
  verifyStatus: string
  activeStatus: string
  adminNote: AdminNote
  city: string
  cityName: string
  pictureUrls: string[]
  listingType: string
  propertyType: string
  coordinate: Coordinate
  user?: User
}

export interface User {
  name: string
  userId: number
  source: string
  phoneNumber: string
  city: string
  description: string
  company: string
  profilePictureURL: string
}

export interface Coordinate {
  latitude: number
  longitude: number
}

export interface AdminNote {
  message: string
  email: string
  date: ReactNode
}
