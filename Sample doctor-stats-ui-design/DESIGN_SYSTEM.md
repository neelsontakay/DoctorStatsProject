# DoctorStats UI Design System - Complete Documentation

## Overview
This document describes the complete UI design system for **DoctorStats**, a statistical analysis platform for clinical and research data. The design emphasizes professional aesthetics, data-driven decision-making, and accessibility for medical professionals.

---

## Color Palette

### Primary Colors
- **Primary Blue**: `#1e6cff` - Trust, professionalism, clinical context
- **Accent Green**: `#10b981` - Success, completion, positive actions  
- **Secondary Amber**: `#eab308` - Warnings, in-progress, attention needed
- **Error Red**: `#dc2626` - Critical alerts, failures

### Neutral Colors
- **White**: `#ffffff` - Background, card surfaces
- **Grays**: `#f3f4f6` → `#111827` - Progressive darkening for hierarchy
- **Text**: `#0f172a` (light) / `#f1f5f9` (dark)

### Design Tokens Usage
```css
--primary: Clinical blue (#1e6cff) - Main CTAs, primary actions
--accent: Clinical green (#10b981) - Success states, positive outcomes
--secondary: Amber (#eab308) - Warnings, processing states
--destructive: Red (#dc2626) - Errors, critical warnings
--border: Light gray (#e2e8f0) - Dividers, subtle separations
--muted: Very light gray (#f1f5f9) - Hover states, secondary backgrounds
```

---

## Typography

### Fonts (MVP — implemented)
- **Body & Headings**: [Inter](https://fonts.google.com/specimen/Inter) via `next/font/google` in `app/layout.tsx`
- **Monospace**: JetBrains Mono for appendices and raw statistical output
- **Icons**: Lucide React — 16px (inline), 20–24px (UI), 32px (feature cards)

### Scale
- **H1**: 56px (3.5rem) bold - Page titles, major sections
- **H2**: 48px (3rem) bold - Section headers
- **H3**: 32px (2rem) semibold - Subsection headers  
- **H4**: 24px (1.5rem) semibold - Card titles
- **Body**: 16px (1rem) regular - Main content
- **Small**: 14px (0.875rem) - Supporting text
- **XSmall**: 12px (0.75rem) - Captions, labels

### Line Heights
- Body: `1.6` (leading-6)
- Headings: `1.2` (tight tracking)

---

## Component Library

### Buttons

**States:**
- Primary (bg-primary, text-white) - Main actions
- Outline (border-primary, text-primary) - Secondary actions
- Danger (bg-destructive) - Destructive actions
- Disabled (opacity-50, cursor-not-allowed)

**Sizes:**
- Small (py-2 px-3) - Secondary, compact
- Medium (py-2.5 px-4) - Default
- Large (py-3 px-6) - Primary, prominent

### Cards
- Border: 1px solid border color
- Padding: 24px (p-6)
- Border-radius: 8px (rounded-lg)
- Shadow: hover:shadow-md transition
- Hover effect: Subtle elevation, border highlight

### Status Badges
```
✓ Completed: bg-accent-100, text-accent-800
⏳ Processing: bg-secondary-100, text-secondary-800
⏱️ Pending: bg-secondary-100, text-secondary-800
✗ Failed: bg-red-100, text-red-800
```

### Data Tables
- Header: bg-muted, py-3, px-6
- Rows: hover:bg-muted, border-b border-border
- Striped alternation for readability
- Status indicators (dots) in first column

### Forms
- Input: bg-background, border border-border, focus:ring-2 focus:ring-primary
- Labels: font-semibold, text-foreground, mb-2
- Helper text: text-xs, text-muted-foreground
- Validation: border-destructive for errors

---

## Layout Patterns

### Grid System
- Mobile: 1 column
- Tablet (md): 2-3 columns
- Desktop (lg): 4+ columns

### Spacing Scale
```
xs: 4px (gap-1)
sm: 8px (gap-2)
md: 12px (gap-3)
lg: 16px (gap-4)
xl: 24px (gap-6)
2xl: 32px (gap-8)
```

### Navigation
- Sticky header with logo, menu, auth buttons
- Responsive burger menu on mobile
- Active state: text-primary with underline or highlight

### Sidebar (if applicable)
- Width: 280px (w-72)
- bg-card with border-r border-border
- Vertical menu items with icons
- Active: bg-primary-100, text-primary-700

---

## Page Sections

### Landing Page Components

**Hero Section:**
- Split layout: left 50% text, right 50% visual
- Large headline with primary color accent
- Supporting copy (18px, leading-relaxed)
- 2-3 CTAs (primary + outline)
- Gradient background: `from-primary-50 to-background`

**Features Grid:**
- 3 columns on desktop, 1 on mobile
- Feature cards with icon, title, description
- Hover effect: border-primary, shadow-lg, bg-primary-50
- Icons: 24px with bg-primary-100 background

**Pricing Cards:**
- 3 tier layout
- Highlighted tier: `scale-105`, border-primary, bg-primary-50, shadow-lg
- "Most Popular" badge: bg-primary, text-primary-foreground, px-3 py-1, rounded-full
- Feature list: CheckCircle icons with accent color
- CTA button within each card

**How It Works:**
- Step-by-step layout
- Step numbers: large text (4xl), primary-100 color
- Icons/emojis next to titles
- Bg-muted section for contrast

**CTA Section:**
- Full-width bg-primary, text-primary-foreground
- Centered content, large headline, supporting copy
- Button: outline variant with white border

**Footer:**
- 4-column grid on desktop, 1 on mobile
- Links organized by category
- Logo + tagline in first column
- Copyright + social links at bottom

---

### Dashboard Components

**Header:**
- Logo + title + navigation
- Right side: Help button + user menu
- Sticky, border-bottom border-border

**Stats Grid:**
- 4 cards in one row (responsive: 2x2 on tablet, 1x4 on desktop)
- Each card: icon, label, large number
- Hover: shadow-md, subtle lift

**Recent Analyses Table:**
- Status icon + analysis name + date
- Status badge: completed (accent), processing (secondary), failed (destructive)
- "View" action button (hidden until hover)
- Each row: p-4, border-b border-border, hover:bg-muted

**Quick Actions Sidebar:**
- Vertical button stack (w-full)
- New Analysis (primary), View Reports (outline), Team Management (outline)
- Subscription info card: bg-primary-50, border-primary-200
- Progress bar showing usage
- Upgrade button

**Tips & Alerts:**
- bg-card with border
- Icon (AlertCircle, CheckCircle, etc.) + message
- Color coding: secondary for tips, accent for success, destructive for errors

---

### Analysis Workflow Components (5-step MVP)

**Progress Steps (`components/ui/stepper.tsx`):**
- Horizontal timeline with Lucide icons (no emoji)
- `aria-current="step"` on active step
- Completed steps: Check icon
- Mobile: horizontal scroll for step labels

**Steps:**
1. Upload — dropzone, chunk progress, virus scan, sheet picker, 10-row preview
2. Objectives — textarea with 50-char minimum counter, template chips
3. Column mapping — editable table with data type / variable role selects
4. Access (org only) — radio cards for all/specific/private members
5. Review — summary cards + subscription usage note

**Upload Section:**
- Dashed border dropzone with upload icon
- "Drag and drop" + "or click to browse" copy
- After upload: file card showing name, size, row count
- Progress bar (bg-muted with bg-primary overlay)

**Form Inputs:**
- Textarea for objectives (h-32, placeholder text)
- Select dropdown for study type
- Each with label, helper text
- Focus state: ring-2 ring-primary

**Column Mapping Table:**
- Columns: Column Name, Data Type, Variable Role, Notes
- Data type shown as small badge (primary-100, primary-800)
- Hover rows: bg-muted
- Warning alert: bg-secondary-50, border-secondary-200, text-secondary-800

**Review Step:**
- Summary items in p-3 border border-border rounded-lg layout
- Success message: bg-accent-50, border-accent-200, CheckCircle icon
- Checkbox with label for sharing option

**Navigation Buttons:**
- Back button (outline, disabled on first step)
- Next/Submit button (primary, disabled on last step)
- Gap-4 between buttons, full width on mobile

---

### Report Viewer Components

**Report Header:**
- Title + report ID
- Date generated timestamp
- Share + Export PDF buttons (sticky on scroll)

**Key Metrics:**
- 4-column grid (responsive)
- Large primary-colored numbers
- Supporting unit text (muted)

**Expandable Sections:**
- Full-width button with icon + title + chevron
- Hover: bg-muted
- Chevron rotates 180° when expanded
- Content: p-6 bg-background with border-t border-border

**Results Highlight Box:**
- bg-primary-50, border-primary-200
- Grid of stat items: label (xs), value (bold)
- Each stat in separate cell

**Visualizations:**
- Bar/scatter plots: h-64, bg-muted, flex items-end
- Bars: bg-gradient, opacity-80, hover:opacity-100
- Caption below: text-xs text-muted-foreground

**Action Buttons:**
- Share, Export Excel, View HTML
- Outlined variants, icon + label
- Full width on mobile, inline on desktop

---

## Responsive Design

### Breakpoints
- Mobile: < 768px (md)
- Tablet: 768px - 1024px (md-lg)
- Desktop: > 1024px (lg+)

### Responsive Utilities
- Grid: `grid-cols-1 md:grid-cols-2 lg:grid-cols-4`
- Text: `text-base md:text-lg lg:text-xl`
- Hidden: `hidden md:block` / `md:hidden`
- Spacing: `px-4 md:px-8 lg:px-12`

### Mobile Considerations
- Single-column layouts
- Full-width buttons and inputs
- Larger touch targets (min 44px)
- Stacked navigation (hamburger menu)
- Simplified tables (horizontal scroll if needed)

---

## Accessibility

### WCAG 2.1 AA Compliance
- ✅ Minimum contrast ratio 4.5:1 for body text
- ✅ Large text (18.66px bold / 24px) requires 3:1
- ✅ Semantic HTML (`<button>`, `<input>`, `<label>`)
- ✅ Keyboard navigation: Tab, Enter, Escape
- ✅ Screen reader support via `aria-label`, `aria-describedby`
- ✅ Focus indicators: `focus:ring-2 focus:ring-primary`

### Color Accessibility
- Never rely on color alone (use icons + badges)
- Sufficient contrast between foreground/background
- Status indicators include text labels, not just colors

### Interactive Elements
- Buttons min 44x44px for mobile
- Form labels always visible, not placeholders
- Error messages linked to inputs via `aria-describedby`
- Loading states with spinner + status text

---

## Animation & Interaction

### Transitions
- `transition` (150ms ease) for color, shadow, opacity changes
- `transition-transform` for hover/active states
- Hover effects: shadow, color, scale (1.05)

### Loading States
- Spinner icon (animate-spin class)
- Progress bar with percentage
- Skeleton loading placeholders
- Disabled state with reduced opacity

### Feedback
- Toast notifications for actions
- Confirmation modals for destructive actions
- In-line validation messages
- Success badges with checkmarks

---

## Dark Mode

All components include dark mode support via CSS variables:
- Background: `#0f172a` (dark slate)
- Text: `#f1f5f9` (light slate)
- Cards: `#1e293b` (slightly lighter dark)
- Accents: Brightened for visibility

---

## Usage Examples

### Creating a New Page
1. Wrap in `<div className="min-h-screen bg-background">`
2. Add sticky header with `border-b border-border bg-card`
3. Main content in `<main className="mx-auto max-w-7xl px-4">`
4. Use responsive grid: `grid grid-cols-1 md:grid-cols-3 lg:grid-cols-4 gap-6`

### Adding a Feature Card
```jsx
<div className="group p-6 rounded-lg border border-border hover:border-primary 
                hover:shadow-lg transition-all bg-card hover:bg-primary-50">
  <Icon className="w-6 h-6 text-primary mb-4" />
  <h3 className="text-lg font-semibold text-foreground mb-2">Title</h3>
  <p className="text-muted-foreground">Description</p>
</div>
```

### Status Badge
```jsx
<span className={`text-xs font-medium px-3 py-1 rounded-full ${
  status === 'completed' ? 'bg-accent-100 text-accent-800' :
  status === 'processing' ? 'bg-secondary-100 text-secondary-800' :
  'bg-destructive-100 text-destructive-800'
}`}>
  {status}
</span>
```

---

## Browser Support
- Chrome/Edge: Latest 2 versions
- Firefox: Latest 2 versions
- Safari: Latest 2 versions
- Mobile browsers: iOS Safari 14+, Chrome Android 90+

---

## Performance Considerations
- Lazy load images and heavy components
- Minimize color transitions (use hardware acceleration)
- Optimize SVG icons (size, complexity)
- Use CSS Grid/Flexbox (avoid floats)
- Implement virtual scrolling for large lists

---

## MVP Component Library (`components/ui/`)

| Component | File | Variants / States |
|-----------|------|-------------------|
| Button | `button.tsx` | default, outline, ghost, destructive, link; xs–lg, icon |
| Card | `card.tsx` | default, interactive, stat |
| Input | `input.tsx` | default, error, disabled |
| Textarea | `textarea.tsx` | default, error, disabled |
| Select | `select.tsx` | default, error, disabled |
| Badge | `badge.tsx` | completed, processing, pending, failed, admin, analyst, viewer |
| Alert | `alert.tsx` | info, success, warning, error |
| Table | `table.tsx` | default with horizontal scroll wrapper |
| Stepper | `stepper.tsx` | horizontal wizard with `aria-current` |
| Dialog | `dialog.tsx` | modal with focus trap via native `<dialog>` |
| Avatar | `avatar.tsx` | sm, md, lg; initials fallback |
| ProgressBar | `progress.tsx` | determinate, indeterminate |
| EmptyState | `empty-state.tsx` | icon + title + CTA |
| Skeleton | `skeleton.tsx` | text, SkeletonCard, SkeletonRow |
| Checkbox | `checkbox.tsx` | with optional label |
| Label | `label.tsx` | required indicator |
| Logo | `logo.tsx` | sm, md with optional subtitle |

**Layout shells:** `components/app-shell.tsx` — `AppShell` (authenticated) and `AuthLayout` (centered cards).

**Composition rule:** All content panels use `rounded-lg border border-border bg-card p-6`.

---

## MVP Screen Inventory

Browse all screens via `components/ui-showcase.tsx` (default route `/`).

| Group | Screens |
|-------|---------|
| Public | Landing (mobile nav, 2-tier pricing) |
| Auth | Sign In, Register, Forgot/Reset Password, Email Verification, Accept Invitation |
| App | Dashboard (default/empty/loading/error), 5-step Wizard, Job Status, Reports List, Report Viewer (7 sections), Profile, Org Members + Invite dialog |

---

## Accessibility (MVP — implemented)

- **Skip links** on app shell, auth layout, landing, and wizard (`.skip-link` in `globals.css`)
- **Mobile navigation** slide-over drawer with 44px touch targets
- **Wizard stepper** uses `aria-current="step"` and screen-reader step labels
- **Report accordion** uses `aria-expanded` and `aria-controls`
- **Forms** use visible labels, `aria-invalid`, `aria-describedby` for errors
- **Focus** `ring-2 ring-primary` on all interactive elements
- **Tables** horizontal scroll on mobile; sticky first column in column mapping

Dark mode tokens exist in CSS but UI toggle is deferred to post-MVP.

---

## Future Enhancements
- [x] Mobile hamburger navigation
- [x] Skip-to-content links
- [x] ARIA stepper and accordion patterns
- [x] 17-screen MVP showcase harness
- [ ] Light/dark mode toggle UI
- [ ] Component Storybook documentation
- [ ] Figma design file sync

---

**Design System Version**: 1.1 (MVP)
**Last Updated**: June 2026
**Maintained by**: DoctorStats Design Team
