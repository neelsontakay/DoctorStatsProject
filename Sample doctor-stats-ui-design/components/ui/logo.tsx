import { cn } from '@/lib/utils'

function Logo({
  subtitle,
  className,
  size = 'md',
}: {
  subtitle?: string
  className?: string
  size?: 'sm' | 'md'
}) {
  const boxSize = size === 'sm' ? 'size-8 text-sm' : 'size-10 text-base'

  return (
    <div className={cn('flex items-center gap-3', className)}>
      <div
        className={cn(
          'rounded-lg bg-primary flex items-center justify-center text-primary-foreground font-bold',
          boxSize,
        )}
        aria-hidden="true"
      >
        DS
      </div>
      <div>
        <div className="font-semibold text-foreground leading-tight">DoctorStats</div>
        {subtitle && (
          <div className="text-xs text-muted-foreground leading-tight">{subtitle}</div>
        )}
      </div>
    </div>
  )
}

export { Logo }
