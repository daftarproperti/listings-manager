export interface Listing {
  id: string
  title: string
  address: string
  description: string
  price: number
  lotSize: number
  buildingSize: number
  bedroomCount: number
  bathroomCount: number
  floorCount: number
  facing: string
  ownership: string
  verifyStatus: string
  city: string
  pictureUrls: string[]
  listingType: string
  propertyType: string
  user?: User
}

export interface User {
  name: string
  userId: number
  source: string
}
