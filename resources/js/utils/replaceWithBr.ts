export default function replaceWithBr(text: string): string {
  return text.replace(/\\r\\n|\\n|\\r/g, '<br />')
}
