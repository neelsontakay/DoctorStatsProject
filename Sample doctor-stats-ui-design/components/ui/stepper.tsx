'use client'

import { Check, type LucideIcon } from 'lucide-react'
import { cn } from '@/lib/utils'

export type StepItem = {
  number: number
  title: string
  icon?: LucideIcon
}

function Stepper({
  steps,
  currentStep,
  className,
}: {
  steps: StepItem[]
  currentStep: number
  className?: string
}) {
  return (
    <nav aria-label="Progress" className={cn('mb-12', className)}>
      <ol className="flex justify-between relative">
        <li
          className="absolute left-0 right-0 top-6 h-0.5 bg-border -translate-y-1/2 list-none"
          aria-hidden="true"
        />
        {steps.map((step) => {
          const isComplete = step.number < currentStep
          const isCurrent = step.number === currentStep
          const Icon = step.icon

          return (
            <li
              key={step.number}
              className="relative flex flex-col items-center flex-1 list-none"
              aria-current={isCurrent ? 'step' : undefined}
            >
              <div
                className={cn(
                  'w-12 h-12 rounded-full flex items-center justify-center text-sm font-semibold border-2 transition-all z-10',
                  isComplete && 'bg-primary border-primary text-primary-foreground',
                  isCurrent && 'bg-primary border-primary text-primary-foreground ring-4 ring-primary/20',
                  !isComplete && !isCurrent && 'bg-background border-border text-muted-foreground',
                )}
              >
                {isComplete ? (
                  <Check className="w-5 h-5" aria-hidden="true" />
                ) : Icon ? (
                  <Icon className="w-5 h-5" aria-hidden="true" />
                ) : (
                  step.number
                )}
              </div>
              <p
                className={cn(
                  'mt-3 text-sm font-medium text-center px-1',
                  isCurrent ? 'text-primary' : 'text-muted-foreground',
                )}
              >
                <span className="sr-only">
                  Step {step.number} of {steps.length}:
                </span>
                {step.title}
              </p>
            </li>
          )
        })}
      </ol>
    </nav>
  )
}

export { Stepper }
