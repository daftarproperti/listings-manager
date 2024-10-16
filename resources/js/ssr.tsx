import { createInertiaApp } from '@inertiajs/react'
import createServer from '@inertiajs/react/server'
import ReactDOMServer from 'react-dom/server'
import React from 'react'

createServer(
  async (page) =>
    await createInertiaApp({
      page,
      render: ReactDOMServer.renderToString,
      resolve: (name: string) => {
        const pages = import.meta.glob('./Pages/**/*.tsx', {
          eager: true,
        }) as Record<string, () => Promise<{ default: React.ComponentType }>>
        return pages[`./Pages/${name}.tsx`]
      },
      setup: ({ App, props }) => <App {...props} />,
    }),
)
