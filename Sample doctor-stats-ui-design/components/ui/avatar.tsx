import { cn } from '@/lib/utils'

function Avatar({
  name,
  src,
  size = 'md',
  className,
}: {
  name: string
  src?: string
  size?: 'sm' | 'md' | 'lg'
  className?: string
}) {
  const initials = name
    .split(' ')
    .map((n) => n[0])
    .join('')
    .slice(0, 2)
    .toUpperCase()

  const sizes = {
    sm: 'size-8 text-xs',
    md: 'size-10 text-sm',
    lg: 'size-12 text-base',
  }

  if (src) {
    return (
      <img
        src={src}
        alt={name}
        className={cn('rounded-full object-cover', sizes[size], className)}
      />
    )
  }

  return (
    <div
      className={cn(
        'rounded-full bg-primary-100 text-primary-800 font-semibold flex items-center justify-center',
        sizes[size],
        className,
      )}
      aria-hidden={!name}
      title={name}
    >
      {initials}
    </div>
  )
}

export { Avatar }
