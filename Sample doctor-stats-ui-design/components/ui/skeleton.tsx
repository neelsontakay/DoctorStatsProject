import { cn } from '@/lib/utils'

function Skeleton({ className, ...props }: React.HTMLAttributes<HTMLDivElement>) {
  return (
    <div
      className={cn('animate-pulse rounded-md bg-muted', className)}
      aria-hidden="true"
      {...props}
    />
  )
}

function SkeletonCard() {
  return (
    <div className="rounded-lg border border-border bg-card p-6 space-y-4" aria-busy="true" aria-label="Loading">
      <Skeleton className="h-4 w-1/3" />
      <Skeleton className="h-8 w-1/2" />
    </div>
  )
}

function SkeletonRow() {
  return (
    <div className="flex items-center gap-4 p-4 rounded-lg border border-border" aria-busy="true">
      <Skeleton className="h-5 w-5 rounded-full" />
      <div className="flex-1 space-y-2">
        <Skeleton className="h-4 w-2/3" />
        <Skeleton className="h-3 w-1/4" />
      </div>
      <Skeleton className="h-6 w-20 rounded-full" />
    </div>
  )
}

export { Skeleton, SkeletonCard, SkeletonRow }
