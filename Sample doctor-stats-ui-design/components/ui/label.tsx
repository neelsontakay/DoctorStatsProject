import { cn } from '@/lib/utils'

function Label({
  className,
  required,
  children,
  ...props
}: React.LabelHTMLAttributes<HTMLLabelElement> & { required?: boolean }) {
  return (
    <label
      className={cn('block text-sm font-semibold text-foreground mb-2', className)}
      {...props}
    >
      {children}
      {required && <span className="text-destructive ml-1" aria-hidden="true">*</span>}
    </label>
  )
}

export { Label }
