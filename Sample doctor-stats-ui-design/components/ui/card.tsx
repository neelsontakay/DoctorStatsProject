import { cva, type VariantProps } from 'class-variance-authority'
import { cn } from '@/lib/utils'

const cardVariants = cva('rounded-lg border border-border bg-card p-6', {
  variants: {
    variant: {
      default: '',
      interactive:
        'hover:border-primary hover:bg-primary-50 hover:shadow-md transition-all cursor-pointer',
      stat: 'hover:shadow-md transition-shadow',
    },
  },
  defaultVariants: {
    variant: 'default',
  },
})

function Card({
  className,
  variant,
  ...props
}: React.HTMLAttributes<HTMLDivElement> & VariantProps<typeof cardVariants>) {
  return <div className={cn(cardVariants({ variant }), className)} {...props} />
}

function CardHeader({ className, ...props }: React.HTMLAttributes<HTMLDivElement>) {
  return <div className={cn('mb-4', className)} {...props} />
}

function CardTitle({ className, ...props }: React.HTMLAttributes<HTMLHeadingElement>) {
  return <h3 className={cn('text-lg font-semibold text-foreground', className)} {...props} />
}

function CardDescription({ className, ...props }: React.HTMLAttributes<HTMLParagraphElement>) {
  return <p className={cn('text-sm text-muted-foreground', className)} {...props} />
}

function CardContent({ className, ...props }: React.HTMLAttributes<HTMLDivElement>) {
  return <div className={cn('', className)} {...props} />
}

export { Card, CardHeader, CardTitle, CardDescription, CardContent, cardVariants }
