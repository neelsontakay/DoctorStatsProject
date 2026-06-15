import { cn } from '@/lib/utils'

function Textarea({
  className,
  error,
  ...props
}: React.TextareaHTMLAttributes<HTMLTextAreaElement> & { error?: string }) {
  return (
    <div className="w-full">
      <textarea
        className={cn(
          'flex min-h-32 w-full rounded-lg border border-border bg-input px-4 py-3 text-sm text-foreground transition-colors resize-y',
          'placeholder:text-muted-foreground',
          'focus:outline-none focus:ring-2 focus:ring-primary focus:border-primary',
          'disabled:cursor-not-allowed disabled:opacity-50',
          error && 'border-destructive focus:ring-destructive/30',
          className,
        )}
        aria-invalid={error ? true : undefined}
        aria-describedby={error ? `${props.id}-error` : undefined}
        {...props}
      />
      {error && (
        <p id={`${props.id}-error`} className="mt-1.5 text-xs text-destructive" role="alert">
          {error}
        </p>
      )}
    </div>
  )
}

export { Textarea }
