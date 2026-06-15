import { cn } from '@/lib/utils'

function Checkbox({
  className,
  label,
  ...props
}: React.InputHTMLAttributes<HTMLInputElement> & { label?: string }) {
  return (
    <label className="flex items-start gap-3 cursor-pointer">
      <input
        type="checkbox"
        className={cn(
          'mt-0.5 size-4 rounded border-border text-primary focus:ring-2 focus:ring-primary',
          className,
        )}
        {...props}
      />
      {label && <span className="text-sm text-muted-foreground">{label}</span>}
    </label>
  )
}

export { Checkbox }
