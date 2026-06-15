# DoctorStats UI Design System - Visual Summary

## 🎨 Complete Design System Overview

This document provides a quick reference for all UI components, colors, and patterns in the DoctorStats design system.

---

## Color Palette Quick Reference

### Status Colors
```
✅ Success/Completed    → #10b981 (Green)     Accent color
⏳ Processing           → #eab308 (Amber)     Secondary color
⏱️  Pending             → #eab308 (Amber)     Secondary color
❌ Failed/Error         → #dc2626 (Red)       Destructive color
ℹ️  Info/Tips           → #1e6cff (Blue)      Primary color
```

### UI Colors
```
Primary (Links, CTA)    → #1e6cff Blue
Borders                 → #e2e8f0 Gray
Hover/Muted BG          → #f3f4f6 Light Gray
Text Primary            → #0f172a Dark Slate
Text Secondary          → #64748b Medium Gray
```

---

## Component Quick Guide

### 1. Buttons

**Primary Button** (Main actions)
- Background: #1e6cff (Primary Blue)
- Text: White
- Padding: 12px 16px (md), 16px 24px (lg)
- Border Radius: 8px
- Hover: Darker blue, shadow

**Outline Button** (Secondary actions)
- Background: Transparent
- Border: 1px #1e6cff
- Text: #1e6cff
- Hover: Light blue background

**Ghost Button** (Tertiary/minimal)
- Background: Transparent
- Text: #1e6cff
- Border: None
- Hover: Light gray background

### 2. Cards
- Border: 1px solid #e2e8f0
- Background: #ffffff
- Padding: 24px (p-6)
- Border Radius: 8px
- Box Shadow: hover → shadow-md
- Transition: All 300ms

### 3. Status Badges
```
Completed:    [✓ Completed]  bg-#10b981-100 text-#10b981-800
Processing:   [⏳ Processing] bg-#eab308-100 text-#eab308-800
Pending:      [⏱️ Pending]    bg-#eab308-100 text-#eab308-800
Failed:       [✗ Failed]     bg-#dc2626-100 text-#dc2626-800
```

### 4. Form Elements
- Input Background: #f8fafc
- Input Border: 1px #e2e8f0
- Focus: Ring 2px #1e6cff
- Label: Bold, #0f172a
- Helper Text: Small, #64748b
- Padding: 12px

### 5. Tables
- Header Background: #f3f4f6
- Row Height: 56px
- Border Bottom: 1px #e2e8f0
- Hover: bg-#f3f4f6
- First Column: Icon or status indicator

---

## Component Showcase

### Landing Page Sections

**1. Navigation Header**
```
┌─────────────────────────────────────────────────┐
│ [DS] DoctorStats    Home About Pricing    SignIn [GetStarted] │
└─────────────────────────────────────────────────┘
```

**2. Hero Section**
```
┌────────────────────────────────────┐
│  [🚀 New: AI-Powered]              │
│                                    │
│  Statistical Analysis, Simplified  │
│  Upload clinical data...           │
│                                    │
│  [Start Free] [Watch Demo]         │
│                                    │
│                    ┌──────────────┐│
│                    │ Chart Visual ││
│                    │   95% ✓      ││
│                    └──────────────┘│
└────────────────────────────────────┘
```

**3. Features Grid (3x2)**
```
┌──────────┐  ┌──────────┐  ┌──────────┐
│ 🎯       │  │ 💡       │  │ 📊       │
│ Feature1 │  │ Feature2 │  │ Feature3 │
└──────────┘  └──────────┘  └──────────┘

┌──────────┐  ┌──────────┐  ┌──────────┐
│ 🔒       │  │ 👥       │  │ 📈       │
│ Feature4 │  │ Feature5 │  │ Feature6 │
└──────────┘  └──────────┘  └──────────┘
```

**4. Pricing Cards**
```
┌──────────┐  ┌──────────────┐  ┌──────────┐
│ Starter  │  │ Professional │  │ Enterprise
│ $0       │  │ $99/month    │  │ Custom
│          │  │              │  │
│ 3 tests  │  │ Unlimited ✨  │  │ Everything
│ Basic    │  │ Priority ✨   │  │ Dedicated ✨
└──────────┘  └──────────────┘  └──────────┘
              (Highlighted, scaled up)
```

---

### Dashboard Sections

**1. Quick Stats (1x4 grid)**
```
┌──────────────┬──────────────┬──────────────┬──────────────┐
│ 📊 Total     │ ⭐ Plan      │ 👥 Members   │ 📄 Reports   │
│ 24           │ Professional │ 5            │ 18           │
└──────────────┴──────────────┴──────────────┴──────────────┘
```

**2. Recent Analyses Table**
```
┌──────────────────────────────────────────────────┐
│ Recent Analyses                          [View All] │
├──────────────────────────────────────────────────┤
│ ✓ Cardiovascular Study Q1      2 hours ago       │
│ ⏳ Diabetes Risk Factors         30 mins ago      │
│ ⏱️ Medication Efficacy           15 mins ago      │
│ ✗ Clinical Trial Data           Yesterday        │
│ ✓ Biomarker Correlation         2 days ago       │
└──────────────────────────────────────────────────┘
```

**3. Subscription Card**
```
┌─────────────────────────────┐
│ Your Plan          [Active] │
│                             │
│ Professional Plan           │
│ $99/month                   │
│                             │
│ ████████████░░░░░░░░░░      │
│ 18 of 24 analyses used      │
│                             │
│ [Upgrade Plan]              │
└─────────────────────────────┘
```

---

### Analysis Workflow (4 Steps)

**Step Progress Indicator**
```
  1 ✓    2 ─── 3 ───── 4
 Upload  Describe  Map  Review
```

**Step 1: Upload**
```
┌────────────────────────────────────┐
│ Upload Your Data                   │
│                                    │
│ ┌────────────────────────────────┐ │
│ │   📤 Drag and drop here        │ │
│ │   or click to browse           │ │
│ │        [Select File]           │ │
│ └────────────────────────────────┘ │
└────────────────────────────────────┘
```

**Step 2: Describe**
```
┌────────────────────────────────────┐
│ Describe Your Analysis             │
│                                    │
│ Analysis Objectives               │
│ ┌────────────────────────────────┐ │
│ │ [Text area - 50+ characters]   │ │
│ └────────────────────────────────┘ │
│                                    │
│ Study Type: [Dropdown ▼]          │
└────────────────────────────────────┘
```

**Step 3: Column Mapping**
```
Column Name | Data Type | Variable Role | Notes
─────────────────────────────────────────────
Patient_ID  │ [Text]    │ Identifier    │ -
Age         │ [Numeric] │ Independent   │ -
BP          │ [Numeric] │ Dependent     │ -
Gender      │ [Category]│ Control       │ -

⚠️ 2 missing values detected in BP column
```

**Step 4: Review**
```
┌────────────────────────────────────┐
│ Review & Submit                    │
│                                    │
│ File:        patient_data.xlsx     │
│ Rows:        1,250                 │
│ Columns:     12                    │
│ Analysis:    Correlational         │
│                                    │
│ ✅ All set! Ready to process      │
│                                    │
│ ☐ Share with organization         │
│                                    │
│ [Back]              [Submit]       │
└────────────────────────────────────┘
```

---

### Report Viewer Sections

**Key Metrics (1x4)**
```
┌──────────────┬──────────────┬──────────────┬──────────────┐
│ Correlation  │ Effect Size  │ Sample Size  │ Confidence   │
│ 0.542        │ 29.4%        │ 1,250        │ 95%          │
│ p < 0.001    │ variance     │ observations │ CI           │
└──────────────┴──────────────┴──────────────┴──────────────┘
```

**Expandable Sections**
```
▼ Executive Summary
  [Section content revealed]
  
► Data Overview
  [Hidden - click to expand]
  
► Methodology
  [Hidden - click to expand]

► Results
  [Hidden - click to expand]
  
► AI Interpretation
  [Hidden - click to expand]
```

**Visualizations (2 side-by-side)**
```
┌──────────────────────────┐  ┌──────────────────────────┐
│ Scatter Plot             │  │ Distribution             │
│ Age vs Blood Pressure    │  │ Blood Pressure           │
│                          │  │                          │
│  ▄▄                      │  │  ▁▂▄█▆▄▂▁                │
│   ▅▅▆▅▄                 │  │  
│    ▇██                   │  │
│                          │  │
│ r = 0.542, p < 0.001    │  │ Mean = 128.4, SD = 17.3  │
└──────────────────────────┘  └──────────────────────────┘
```

---

## Responsive Breakpoints

### Mobile (< 768px)
- Single column layout
- Full-width buttons
- Stacked cards
- Hamburger navigation
- Larger touch targets (44x44px)

### Tablet (768px - 1024px)
- 2-3 column grids
- Side-by-side cards
- Tab navigation
- Compact forms

### Desktop (> 1024px)
- 4+ column grids
- Multi-column layouts
- Full navigation bar
- Complex tables

---

## Accessibility Features

### Color Contrast
- ✅ 4.5:1 for body text (WCAG AA)
- ✅ 3:1 for large text (18px+)
- ✅ Icons + labels (not color alone)

### Keyboard Navigation
- ✅ Tab through interactive elements
- ✅ Enter/Space for buttons
- ✅ Escape for modals/dropdowns
- ✅ Arrow keys for selects

### Screen Readers
- ✅ Semantic HTML (`<button>`, `<label>`, `<nav>`)
- ✅ ARIA labels for icons
- ✅ Form labels linked to inputs
- ✅ Skip links for navigation

---

## Animation & Transitions

### Hover Effects
```
Cards:       border → primary, shadow elevation
Buttons:     opacity, color change (150ms)
Tables:      row background (100ms)
Navigation:  underline, color (200ms)
```

### Loading States
```
Uploading:   Progress bar (0-100%)
Processing:  Spinner icon (animate-spin)
Loading:     Skeleton placeholder
```

### Interactions
```
Expand:      Chevron rotates 180°
Success:     Green checkmark slides in
Error:       Red message fades in
```

---

## Typography Hierarchy

```
H1 (56px, Bold)      → Page titles
H2 (48px, Bold)      → Major sections
H3 (32px, SemiBold)  → Subsections
H4 (24px, SemiBold)  → Card titles
Body (16px, Regular) → Main content
Small (14px)         → Supporting text
XSmall (12px)        → Labels, captions
```

---

## Quick Copy-Paste Components

### Feature Card
```jsx
<div className="p-6 rounded-lg border border-border hover:border-primary 
                hover:shadow-lg transition-all bg-card hover:bg-primary-50">
  <div className="w-12 h-12 rounded-lg bg-primary-100 flex items-center justify-center text-primary mb-4">
    <Icon className="w-6 h-6" />
  </div>
  <h3 className="text-lg font-semibold text-foreground mb-2">Title</h3>
  <p className="text-muted-foreground">Description</p>
</div>
```

### Status Badge
```jsx
<span className={`text-xs font-medium px-3 py-1 rounded-full ${
  status === 'completed' ? 'bg-accent-100 text-accent-800' :
  status === 'processing' ? 'bg-secondary-100 text-secondary-800' :
  'bg-destructive/10 text-destructive'
}`}>
  {status}
</span>
```

### Stat Card
```jsx
<div className="rounded-lg border border-border bg-card p-6 hover:shadow-md transition-shadow">
  <p className="text-sm text-muted-foreground mb-2">Label</p>
  <p className="text-3xl font-bold text-foreground">Value</p>
</div>
```

---

## Design System Statistics

- **Total Colors**: 5 primary + 8 neutrals
- **Breakpoints**: 3 (mobile, tablet, desktop)
- **Component Types**: 15+ (buttons, cards, tables, forms, badges, etc.)
- **Pages Included**: 4 (landing, dashboard, workflow, report)
- **Responsive Scales**: 100% coverage
- **Accessibility**: WCAG 2.1 AA compliant
- **Dark Mode**: Full support with CSS variables

---

**Last Updated**: June 2026  
**Design System Version**: 1.0  
**Tailwind CSS**: v4  
**React**: 19+
