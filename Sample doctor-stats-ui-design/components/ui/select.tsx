import { cn } from '@/lib/utils'

function Select({
  className,
  error,
  children,
  ...props
}: React.SelectHTMLAttributes<HTMLSelectElement> & { error?: string }) {
  return (
    <div className="w-full">
      <select
        className={cn(
          'flex h-10 w-full rounded-lg border border-border bg-input px-3 py-2 text-sm text-foreground transition-colors',
          'focus:outline-none focus:ring-2 focus:ring-primary focus:border-primary',
          'disabled:cursor-not-allowed disabled:opacity-50',
          error && 'border-destructive focus:ring-destructive/30',
          className,
        )}
        aria-invalid={error ? true : undefined}
        aria-describedby={error ? `${props.id}-error` : undefined}
        {...props}
      >
        {children}
      </select>
      {error && (
        <p id={`${props.id}-error`} className="mt-1.5 text-xs text-destructive" role="alert">
          {error}
        </p>
      )}
    </div>
  )
}

export { Select }
