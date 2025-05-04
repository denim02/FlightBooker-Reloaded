import { createFileRoute } from '@tanstack/react-router'

export const Route = createFileRoute('/(auth)/login/confirm-email')({
  component: RouteComponent,
})

function RouteComponent() {
  return <div>Hello "/(auth)/login/confirm-email"!</div>
}
