import { cva, type VariantProps } from 'class-variance-authority'
import { cn } from '@/lib/utils'

const badgeVariants = cva(
  'inline-flex items-center rounded-full px-3 py-1 text-xs font-medium whitespace-nowrap',
  {
    variants: {
      variant: {
        default: 'bg-primary-100 text-primary-800',
        completed: 'bg-accent-100 text-accent-800',
        processing: 'bg-secondary-100 text-secondary-800',
        pending: 'bg-secondary-100 text-secondary-800',
        failed: 'bg-destructive/10 text-destructive',
        outline: 'border border-border text-foreground',
        admin: 'bg-primary-100 text-primary-800',
        analyst: 'bg-accent-100 text-accent-800',
        viewer: 'bg-muted text-muted-foreground',
        active: 'bg-accent-100 text-accent-800',
        inactive: 'bg-muted text-muted-foreground',
        invited: 'bg-secondary-100 text-secondary-800',
      },
    },
    defaultVariants: {
      variant: 'default',
    },
  },
)

function Badge({
  className,
  variant,
  ...props
}: React.HTMLAttributes<HTMLSpanElement> & VariantProps<typeof badgeVariants>) {
  return <span className={cn(badgeVariants({ variant }), className)} {...props} />
}

export { Badge, badgeVariants }
