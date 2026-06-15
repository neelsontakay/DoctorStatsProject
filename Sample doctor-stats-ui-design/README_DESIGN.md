# DoctorStats UI Design System

## 📋 Overview

This is a **complete, production-ready UI design system** for DoctorStats, a statistical analysis platform for clinical and research data. The design system includes a **landing page**, **dashboard**, **analysis workflow**, and **report viewer** components.

## 🎨 Design Philosophy

The design emphasizes:
- **Professional Aesthetic**: Clinical blue primary color (#1e6cff) for trust and authority
- **Data-Driven Design**: Clear hierarchy, readable typography, accessible information architecture
- **Medical Context**: Color-coded status indicators, clinical workflow patterns
- **Accessibility**: WCAG 2.1 AA compliant, keyboard navigation, screen reader support
- **Responsive**: Mobile-first approach with seamless scaling across devices

## 🎯 Components Included

### 1. **Landing Page** (`app/page.tsx`)
A comprehensive landing page featuring:
- Navigation with sticky header
- Hero section with split layout (text + visual)
- Features grid (6 features, 3x2 layout)
- How It Works section (4-step workflow)
- Pricing comparison (3 tiers with highlight)
- Call-to-action section
- Footer with links and copyright

**Key Sections:**
- Gradient backgrounds for visual depth
- Feature cards with hover effects
- Status indicators (Most Popular badge)
- Responsive pricing cards

### 2. **Dashboard** (`components/dashboard.tsx`)
A user-facing analytics dashboard with:
- Sticky header with user menu
- Quick stats grid (4 KPIs)
- Recent analyses table with status tracking
- Quick actions sidebar
- Subscription info card
- Tips/alerts section

**Key Features:**
- Status badges (Completed ✓, Processing ⏳, Failed ✗)
- Progress tracking for subscription usage
- Hover actions on table rows
- Organization member counts

### 3. **Analysis Workflow** (`components/analysis-workflow.tsx`)
A guided step-by-step form with:
- Progress indicator (4 steps)
- File upload with drag-and-drop
- Form inputs for analysis objectives
- Column mapping table
- Review & submit page
- Validation messages

**Key Features:**
- Multi-step form navigation
- File upload preview with metadata
- Data quality warnings
- Confirmation checkboxes

### 4. **Report Viewer** (`components/report-viewer.tsx`)
A comprehensive report display with:
- Report metadata (ID, generation time)
- Key metrics grid (statistics display)
- Expandable report sections
- Publication-quality visualizations
- Export/share options
- Data quality indicators

**Key Features:**
- Accordion-style expandable sections
- Chart visualizations with gradients
- Highlighted results boxes
- Multi-format export options

## 🎨 Color System

### Primary Palette
```css
Primary Blue:     #1e6cff (Clinical trust)
Accent Green:     #10b981 (Success/completion)
Secondary Amber:  #eab308 (Warnings/processing)
Error Red:        #dc2626 (Failures/critical)
```

### Neutral Palette
```css
White:          #ffffff
Light Gray:     #f3f4f6
Medium Gray:    #9ca3af
Dark Gray:      #4b5563
Dark Slate:     #0f172a
```

### Design Tokens
- `--primary`: Button actions, links, highlights
- `--accent`: Success states, checkmarks, positive outcomes
- `--secondary`: Warnings, loading states, attention needed
- `--destructive`: Errors, critical alerts, deletions
- `--border`: Dividers, card borders
- `--muted`: Hover states, secondary backgrounds

## 📐 Typography

### Font Family
- **Body**: Inter (system sans-serif fallback)
- **Headings**: Inter SemiBold/Bold

### Type Scale
| Level | Size | Weight | Usage |
|-------|------|--------|-------|
| H1 | 56px (3.5rem) | Bold | Page titles |
| H2 | 48px (3rem) | Bold | Section headers |
| H3 | 32px (2rem) | SemiBold | Subsection titles |
| H4 | 24px (1.5rem) | SemiBold | Card titles |
| Body | 16px (1rem) | Regular | Main content |
| Small | 14px (0.875rem) | Regular | Supporting text |
| XSmall | 12px (0.75rem) | Regular | Captions |

### Line Heights
- **Body**: `leading-6` (1.5) for readability
- **Headings**: `leading-tight` (1.25)
- **Labels**: `leading-relaxed` (1.625)

## 🏗️ Component Patterns

### Buttons
```jsx
<Button>Primary Action</Button>
<Button variant="outline">Secondary</Button>
<Button variant="ghost">Tertiary</Button>
<Button size="lg">Large</Button>
<Button size="sm">Small</Button>
<Button disabled>Disabled</Button>
```

### Cards
```jsx
<div className="rounded-lg border border-border bg-card p-6 
                hover:shadow-md transition-shadow">
  Content here
</div>
```

### Status Badges
```jsx
// Completed
<span className="bg-accent-100 text-accent-800 px-3 py-1 rounded-full text-xs font-semibold">
  Completed
</span>

// Processing
<span className="bg-secondary-100 text-secondary-800 px-3 py-1 rounded-full text-xs font-semibold">
  Processing
</span>

// Failed
<span className="bg-destructive/10 text-destructive px-3 py-1 rounded-full text-xs font-semibold">
  Failed
</span>
```

### Forms
```jsx
<div>
  <label className="block text-sm font-semibold text-foreground mb-2">
    Field Label
  </label>
  <input 
    className="w-full p-3 border border-border rounded-lg bg-background 
                focus:outline-none focus:ring-2 focus:ring-primary"
  />
  <p className="text-xs text-muted-foreground mt-1">Helper text</p>
</div>
```

## 📱 Responsive Design

### Breakpoints
- **Mobile**: 0 - 767px (single column, full width)
- **Tablet**: 768px - 1023px (2-3 columns)
- **Desktop**: 1024px+ (4+ columns)

### Responsive Utilities
```jsx
// Grid scaling
<div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">

// Text scaling
<h1 className="text-3xl md:text-4xl lg:text-5xl">

// Visibility
<div className="hidden md:block">Visible on tablet+</div>
<div className="md:hidden">Visible on mobile only</div>
```

## ♿ Accessibility Features

### WCAG 2.1 AA Compliance
- ✅ Minimum 4.5:1 contrast ratio for text
- ✅ 3:1 for large text (18.66px+)
- ✅ Semantic HTML (`<button>`, `<label>`, `<nav>`)
- ✅ Keyboard navigation (Tab, Enter, Escape)
- ✅ Focus indicators (ring-2 ring-primary)
- ✅ ARIA labels for screen readers

### Color Accessibility
- Never rely on color alone
- Use icons + badges together
- Status indicators include text labels
- High contrast for dark mode

### Interactive Elements
- Minimum 44x44px touch targets
- Visible focus states
- Labeled form inputs (not just placeholders)
- Error messages linked to inputs

## 🌙 Dark Mode

All components support dark mode via CSS variables:

```css
:root { /* Light mode */ }
.dark { /* Dark mode */ }

/* Automatic via prefers-color-scheme */
@media (prefers-color-scheme: dark) { }
```

## 📦 File Structure

```
/vercel/share/v0-project/
├── app/
│   ├── page.tsx                 # Landing page
│   ├── layout.tsx               # Root layout
│   └── globals.css              # Design tokens & styles
├── components/
│   ├── dashboard.tsx            # Dashboard component
│   ├── analysis-workflow.tsx    # Analysis form workflow
│   ├── report-viewer.tsx        # Report display
│   ├── ui-showcase.tsx          # Component showcase
│   └── ui/
│       └── button.tsx           # Base button component
├── DESIGN_SYSTEM.md             # Comprehensive guide
└── README.md                    # This file
```

## 🎨 Using the Design System

### Landing Page
```bash
# Visit the landing page
http://localhost:3000/
```

### Dashboard
```jsx
import Dashboard from '@/components/dashboard'
export default function Page() {
  return <Dashboard />
}
```

### Analysis Workflow
```jsx
import AnalysisWorkflow from '@/components/analysis-workflow'
export default function Page() {
  return <AnalysisWorkflow />
}
```

### Report Viewer
```jsx
import ReportViewer from '@/components/report-viewer'
export default function Page() {
  return <ReportViewer />
}
```

### UI Showcase (All Components)
```jsx
import UIShowcase from '@/components/ui-showcase'
export default function Page() {
  return <UIShowcase />
}
```

## 🔧 Customization

### Changing Primary Colors
Edit `app/globals.css`:
```css
:root {
  --primary: #your-color-here;
  --accent: #your-color-here;
  --secondary: #your-color-here;
}
```

### Modifying Typography
Edit `globals.css`:
```css
html {
  font-family: 'Your Font', sans-serif;
}
```

### Adjusting Spacing
All spacing uses Tailwind's scale (gap-4 = 1rem):
```jsx
<div className="gap-8">  {/* 2rem */}
<div className="p-6">   {/* 1.5rem padding */}
```

## 📊 Component Statistics

| Component | Sections | Responsive | Accessible |
|-----------|----------|-----------|------------|
| Landing | 7 (hero, features, workflow, pricing, CTA, footer) | ✅ | ✅ |
| Dashboard | 4 (stats, analyses, actions, subscription) | ✅ | ✅ |
| Workflow | 4 steps (upload, describe, map, review) | ✅ | ✅ |
| Report | 5+ sections (metrics, content, viz) | ✅ | ✅ |

## 🚀 Performance

- Lightweight design tokens (CSS variables)
- Minimal JavaScript (mostly UI state)
- Optimized images and SVGs
- CSS Grid/Flexbox (hardware acceleration)
- No heavy animation libraries

## 🔐 Security Considerations

- No sensitive data in demo components
- Form inputs validated
- CSRF protection ready
- XSS prevention via React
- HIPAA-ready architecture

## 📚 Additional Resources

- **Figma File**: Share DoctorStats design system (to be created)
- **Component Library**: shadcn/ui components used
- **Icons**: Lucide React icons
- **Tailwind CSS**: v4 with custom theme

## 🐛 Browser Support

- ✅ Chrome/Edge: Latest 2 versions
- ✅ Firefox: Latest 2 versions
- ✅ Safari: Latest 2 versions
- ✅ Mobile: iOS 14+, Android Chrome 90+

## 📝 Development Notes

### Adding New Components
1. Create component file in `components/`
2. Use existing design tokens
3. Follow color/spacing patterns
4. Ensure responsive design
5. Add accessibility features

### Maintaining Consistency
- Use `gap-4`, `p-6`, `text-lg` (not arbitrary values)
- Reference color tokens (not hex values)
- Follow typography scale
- Use existing component patterns

## 🎓 Learning Resources

- **Tailwind Docs**: https://tailwindcss.com
- **Shadcn/ui**: https://ui.shadcn.com
- **WCAG Guidelines**: https://www.w3.org/WAI/WCAG21/quickref/
- **Design System**: See `DESIGN_SYSTEM.md` for comprehensive guide

## 📞 Support

For questions about:
- **Design System**: See `DESIGN_SYSTEM.md`
- **Components**: Check component files for inline comments
- **Accessibility**: Review WCAG guidelines
- **Responsive Design**: Test on multiple viewport sizes

---

**Version**: 1.0  
**Last Updated**: June 2026  
**License**: MIT  
**Maintained by**: DoctorStats Design Team
