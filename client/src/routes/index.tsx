import api from '@/lib/api'
import { createFileRoute } from '@tanstack/react-router'

export const Route = createFileRoute('/')({
  component: Index,
  loader: () => api.get('/'),
})

function Index() {
  const data = Route.useLoaderData()
  return (
    <div className="p-2">
      <h3>Welcome Home!</h3>
      {data && <p>{data.data['Laravel']}</p>}
    </div>
  )
}
