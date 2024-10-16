import React, { type Dispatch, type SetStateAction, useEffect } from 'react'
import { type Status, Wrapper } from '@googlemaps/react-wrapper'

import { useIsVisible } from '@/utils'

const DEFAULT_MAP_ZOOM = 14
const DEFAULT_MAP_CENTER = { lat: -6.175403, lng: 106.824584 }

interface GoogleMapsProps {
  coord?: google.maps.LatLngLiteral
  setCoord: Dispatch<SetStateAction<google.maps.LatLngLiteral>>
}

export default function GoogleMaps({
  coord,
  setCoord,
}: GoogleMapsProps): JSX.Element {
  const [mapRef] = useIsVisible<HTMLDivElement>()
  const [panoRef] = useIsVisible<HTMLDivElement>()
  const [inputRef] = useIsVisible<HTMLInputElement>()

  const initMap = async (
    mapElement: HTMLElement,
    panoElement: HTMLElement,
    inputElement: HTMLInputElement,
  ): Promise<void> => {
    const { Map } = (await google.maps.importLibrary(
      'maps',
    )) as google.maps.MapsLibrary
    const { SearchBox } = (await google.maps.importLibrary(
      'places',
    )) as google.maps.PlacesLibrary
    const { StreetViewPanorama } = (await google.maps.importLibrary(
      'streetView',
    )) as google.maps.StreetViewLibrary
    const { AdvancedMarkerElement } = (await google.maps.importLibrary(
      'marker',
    )) as google.maps.MarkerLibrary

    const defaultCenter = {
      lat: coord?.lat ?? DEFAULT_MAP_CENTER.lat,
      lng: coord?.lng ?? DEFAULT_MAP_CENTER.lng,
    }

    const maps = new Map(mapElement, {
      zoom: DEFAULT_MAP_ZOOM,
      center: defaultCenter,
      mapId: import.meta.env.VITE_GOOGLE_MAP_ID,
      disableDefaultUI: true,
      streetViewControl: true,
    })
    const searchBox = new SearchBox(inputElement)
    const panorama = new StreetViewPanorama(panoElement, {
      position: defaultCenter,
    })
    const marker = new AdvancedMarkerElement({
      map: maps,
      position: defaultCenter,
      gmpDraggable: true,
    })

    maps.setStreetView(panorama)
    maps.addListener('bounds_changed', () => {
      searchBox.setBounds(maps.getBounds() as google.maps.LatLngBounds | null)
    })

    maps.addListener('click', (e: google.maps.MapMouseEvent) => {
      marker.position = e.latLng
      if (e.latLng !== null) {
        setCoord({
          lat: parseFloat(e.latLng.lat().toFixed(7)),
          lng: parseFloat(e.latLng.lng().toFixed(7)),
        })
      }
    })

    searchBox.addListener('places_changed', () => {
      const places = searchBox.getPlaces()
      if (places?.length === 0) {
        return
      }

      const bounds = new google.maps.LatLngBounds()
      places?.forEach((place) => {
        if (place.geometry?.location == null) {
          console.log('Returned place contains no geometry')
          return
        }
        if (place.geometry.viewport != null) {
          bounds.union(place.geometry.viewport)
        } else {
          bounds.extend(place.geometry.location)
        }

        panorama.setPosition(place.geometry.location)
        marker.position = place.geometry.location
      })
      maps.fitBounds(bounds)
    })

    marker.addListener('dragend', () => {
      const position = marker.position as google.maps.LatLngLiteral
      setCoord({
        lat: parseFloat(position.lat.toFixed(7)),
        lng: parseFloat(position.lng.toFixed(7)),
      })
    })
  }

  useEffect(() => {
    if (
      mapRef.current !== null &&
      panoRef.current !== null &&
      inputRef.current !== null
    ) {
      void initMap(mapRef.current, panoRef.current, inputRef.current)
    }
  }, [mapRef.current, panoRef.current, inputRef.current])

  return (
    <div className="space-y-1">
      <input
        type="text"
        ref={inputRef}
        placeholder="Masukkan alamat disini..."
        className="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
      />
      <Wrapper
        apiKey={import.meta.env.VITE_GOOGLE_MAPS_API_KEY as string}
        render={(status: Status) => <h1>{status}</h1>}
      >
        <div ref={mapRef} className="h-96 bg-neutral-300" />
        <div ref={panoRef} className="h-96 bg-neutral-300" />
      </Wrapper>
    </div>
  )
}
