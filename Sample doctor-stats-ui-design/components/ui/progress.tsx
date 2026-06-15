import { cn } from '@/lib/utils'

function ProgressBar({
  value,
  max = 100,
  className,
  label,
  indeterminate,
}: {
  value?: number
  max?: number
  className?: string
  label?: string
  indeterminate?: boolean
}) {
  const percent = indeterminate ? undefined : Math.min(100, Math.max(0, ((value ?? 0) / max) * 100))

  return (
    <div className={cn('w-full', className)}>
      {label && (
        <div className="flex justify-between text-xs text-muted-foreground mb-1.5">
          <span>{label}</span>
          {!indeterminate && <span>{Math.round(percent ?? 0)}%</span>}
        </div>
      )}
      <div
        className="h-2 w-full rounded-full bg-muted overflow-hidden"
        role="progressbar"
        aria-valuenow={indeterminate ? undefined : value}
        aria-valuemin={0}
        aria-valuemax={max}
        aria-label={label}
      >
        <div
          className={cn(
            'h-full rounded-full bg-primary transition-all duration-300',
            indeterminate && 'w-1/3 animate-[indeterminate_1.5s_ease-in-out_infinite]',
          )}
          style={indeterminate ? undefined : { width: `${percent}%` }}
        />
      </div>
    </div>
  )
}

export { ProgressBar }
