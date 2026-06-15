import { cva, type VariantProps } from 'class-variance-authority'
import { AlertCircle, CheckCircle2, Info, XCircle } from 'lucide-react'
import { cn } from '@/lib/utils'

const alertVariants = cva('rounded-lg border p-4 flex gap-3', {
  variants: {
    variant: {
      info: 'bg-primary-50 border-primary-200 text-primary-800',
      success: 'bg-accent-50 border-accent-200 text-accent-800',
      warning: 'bg-secondary-50 border-secondary-200 text-secondary-800',
      error: 'bg-destructive/5 border-destructive/20 text-destructive',
    },
  },
  defaultVariants: {
    variant: 'info',
  },
})

const icons = {
  info: Info,
  success: CheckCircle2,
  warning: AlertCircle,
  error: XCircle,
}

function Alert({
  className,
  variant = 'info',
  title,
  children,
  ...props
}: React.HTMLAttributes<HTMLDivElement> &
  VariantProps<typeof alertVariants> & { title?: string }) {
  const Icon = icons[variant ?? 'info']

  return (
    <div
      role="alert"
      className={cn(alertVariants({ variant }), className)}
      {...props}
    >
      <Icon className="w-5 h-5 flex-shrink-0 mt-0.5" aria-hidden="true" />
      <div className="text-sm">
        {title && <p className="font-semibold mb-1">{title}</p>}
        {children}
      </div>
    </div>
  )
}

export { Alert, alertVariants }
