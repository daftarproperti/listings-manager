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
  city: string
  pictureUrls: string[]
  user?: User
}

export interface User {
  name: string
  userId: number
  source: string
}
