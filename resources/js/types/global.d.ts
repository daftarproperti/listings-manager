import { type AxiosInstance } from 'axios'
import type ziggyRoute from 'ziggy-js'

declare global {
  interface Window {
    axios: AxiosInstance
  }

  let route: typeof ziggyRoute
}
