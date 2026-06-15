'use client'

import { X } from 'lucide-react'
import { useEffect, useRef } from 'react'
import { Button } from '@/components/ui/button'
import { cn } from '@/lib/utils'

function Dialog({
  open,
  onClose,
  title,
  description,
  children,
  className,
}: {
  open: boolean
  onClose: () => void
  title: string
  description?: string
  children: React.ReactNode
  className?: string
}) {
  const dialogRef = useRef<HTMLDialogElement>(null)

  useEffect(() => {
    const dialog = dialogRef.current
    if (!dialog) return
    if (open && !dialog.open) dialog.showModal()
    if (!open && dialog.open) dialog.close()
  }, [open])

  return (
    <dialog
      ref={dialogRef}
      className={cn(
        'fixed inset-0 z-50 m-auto max-w-lg w-[calc(100%-2rem)] rounded-lg border border-border bg-card p-0 shadow-xl backdrop:bg-black/50',
        className,
      )}
      onClose={onClose}
      aria-labelledby="dialog-title"
      aria-describedby={description ? 'dialog-description' : undefined}
    >
      <div className="p-6">
        <div className="flex items-start justify-between mb-4">
          <div>
            <h2 id="dialog-title" className="text-lg font-semibold text-foreground">
              {title}
            </h2>
            {description && (
              <p id="dialog-description" className="text-sm text-muted-foreground mt-1">
                {description}
              </p>
            )}
          </div>
          <Button variant="ghost" size="icon" onClick={onClose} aria-label="Close dialog">
            <X className="w-4 h-4" />
          </Button>
        </div>
        {children}
      </div>
    </dialog>
  )
}

export { Dialog }
