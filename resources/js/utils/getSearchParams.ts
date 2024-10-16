export default function getSearchParams(name: string): string | null {
  const uri = window.location.search
  const match = RegExp('[?&]' + name + '=([^&]*)').exec(uri)
  return match !== null
    ? decodeURIComponent(match[1].replace(/\+/g, ' '))
    : null
}
