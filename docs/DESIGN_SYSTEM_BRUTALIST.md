# Design System - Brutalist SCSS Architecture

## 📐 DFII Score: 13/15
- **Aesthetic Impact**: 4/5 — Brutalist, memorable, distinct
- **Context Fit**: 4/5 — Perfect for data-heavy CRM, raw/transparent
- **Implementation**: 4/5 — Clean modular structure, maintainable
- **Performance**: 3/5 — CSS size manageable, no heavy animations
- **Consistency Risk**: -2 — Low, clear component system

## 🎨 Aesthetic Direction

**Editorial Brutalism** — Raw, bold, data-first design with:
- Heavy borders (3-4px solid black)
- Uppercase typography with tight spacing
- Accent color: `#FF6B35` (Orange)
- No border-radius, sharp corners
- Purposeful color for activity types

## 📁 Architecture Overview

```
resources/scss/
├── _base.scss                     # Global resets, typography
├── _theme.scss                    # CSS variables (colors, spacing)
├── _variables.scss                # SCSS variables
├── _mixins.scss                   # SCSS mixins
│
├── _data-brutalism.scss           # ⭐ Core components (buttons, cards, badges, inputs)
│
├── _forms-brutalist.scss          # 🆕 Form elements (.form-select, .form-group)
├── _layout-brutalist.scss         # 🆕 Page layout (.page-header, .filters-card, .stats-grid)
├── _components-brutalist.scss     # 🆕 Reusable UI (.timeline, .pagination, .empty-state)
├── _utilities-brutalist.scss      # 🆕 Helpers (.text-brutalist, .border-brutalist, .btn-icon)
│
├── _activities.scss               # Activities page-specific styles
├── _pipelines.scss                # Pipelines page-specific styles
│
└── app.scss                       # Entry point (imports all)
```

## 🧩 Component Categories

### 1. **Forms** (`_forms-brutalist.scss`)
Reusable form elements with brutalist styling.

| Class | Purpose | Example |
|-------|---------|---------|
| `.form-select` | Dropdown select | Used in filters |
| `.form-group` | Label + input wrapper | Form layouts |
| `.form-textarea` | Multiline text input | Notes, descriptions |
| `.filter-item` | Filter label + field | Search/filter sections |
| `.modal-form` | Modal form layout | Creation modals |

**Usage:**
```html
<div class="filter-item">
  <label>Type</label>
  <select class="form-select">
    <option>All</option>
  </select>
</div>
```

---

### 2. **Layout** (`_layout-brutalist.scss`)
Page structure and grid systems.

| Class | Purpose | Example |
|-------|---------|---------|
| `.page-header` | Page title section | All pages |
| `.filters-card` | Filter section container | Index pages |
| `.stats-grid` | Metrics card grid | Dashboard metrics |
| `.content-card` | Generic card wrapper | Content sections |

**Usage:**
```html
<div class="page-header">
  <h1 class="page-title">Activities</h1>
</div>

<div class="filters-card">
  <div class="filters-grid">
    <!-- filter items -->
  </div>
</div>

<div class="stats-grid">
  <StatCard />
  <StatCard />
</div>
```

---

### 3. **Components** (`_components-brutalist.scss`)
Complex reusable UI patterns.

| Class | Purpose | Example |
|-------|---------|---------|
| `.timeline` | Event timeline | Activities page |
| `.timeline-group` | Date grouping | Timeline sections |
| `.timeline-item` | Individual event | Activity card |
| `.timeline-marker` | Event icon | Type indicator |
| `.pagination` | Page navigation | List pages |
| `.pagination-btn` | Page number button | Pagination controls |
| `.timeline-empty` | No results state | Empty timeline |

**Usage:**
```html
<div class="timeline">
  <div class="timeline-group">
    <div class="timeline-date">
      <i class="fa fa-calendar"></i> Today
    </div>
    <div class="timeline-items">
      <div class="timeline-item">
        <div class="timeline-marker marker-call">
          <i class="fa fa-phone"></i>
        </div>
        <div class="timeline-content">
          <!-- content -->
        </div>
      </div>
    </div>
  </div>
</div>

<div class="pagination">
  <div class="pagination-info">Showing 1-20 of 100</div>
  <div class="pagination-buttons">
    <button class="pagination-btn active">1</button>
    <button class="pagination-btn">2</button>
  </div>
</div>
```

---

### 4. **Utilities** (`_utilities-brutalist.scss`)
Helper classes for common patterns.

| Class | Purpose | Example |
|-------|---------|---------|
| `.text-brutalist` | Uppercase bold text | Headers, labels |
| `.text-danger` | Error text | Validation messages |
| `.text-muted` | Secondary text | Hints, metadata |
| `.border-brutalist` | 3px black border | Custom borders |
| `.border-brutalist-thick` | 4px black border | Emphasis |
| `.btn-icon` | Icon-only button | Actions (36×36px) |
| `.btn-icon-sm` | Small icon button | Inline actions (30×30px) |
| `.hover-lift` | Lift on hover | Interactive cards |
| `.hover-border-accent` | Accent border on hover | Links, buttons |

**Usage:**
```html
<h3 class="text-brutalist">Section Title</h3>
<p class="text-muted">Optional description</p>

<div class="border-brutalist-thick">
  <!-- content -->
</div>

<button class="btn-icon">
  <i class="fa fa-edit"></i>
</button>
```

---

### 5. **Data Brutalism** (`_data-brutalism.scss`)
Core design system components.

| Component | Variants | Purpose |
|-----------|----------|---------|
| `.btn` | `--secondary`, `--small`, `--large` | Primary actions |
| `.card` | `__header`, `__body`, `__footer` | Content containers |
| `.stat-card` | `__icon`, `__value`, `__label` | Metrics display |
| `.badge` | `--accent`, `--success`, `--warning`, `--danger` | Status labels |
| `.badge` | `--type-call`, `--type-meeting`, etc | Activity types |
| `.input` | `--error` | Text inputs |
| `.textarea` | — | Multiline inputs |
| `.select` | — | Dropdowns |
| `.table` | `thead`, `tbody` | Data tables |

**Badge Activity Types:**
```html
<span class="badge badge--type-call">Call</span>
<span class="badge badge--type-meeting">Meeting</span>
<span class="badge badge--type-email">Email</span>
<span class="badge badge--type-note">Note</span>
<span class="badge badge--type-task">Task</span>
```

---

## 🎯 Page-Specific Styles

### When to use page-specific SCSS:
- ✅ Unique layout patterns (e.g., timeline markers with colors)
- ✅ Page-specific component variants
- ✅ Complex nested structures

### When to use common modules:
- ✅ Filters, forms, pagination
- ✅ Cards, buttons, badges
- ✅ Grid layouts, headers

### Example: Activities Page

**`_activities.scss`** (page-specific):
- `.timeline-marker.marker-call` — Activity type colors
- `.activity-header` — Activity card header layout
- `.activity-notes` — Activity notes styling

**Common modules used:**
- `.page-header` from `_layout-brutalist`
- `.filters-card` from `_layout-brutalist`
- `.timeline` from `_components-brutalist`
- `.pagination` from `_components-brutalist`

---

## 🚀 Usage Guidelines

### ✅ DO:
- Use semantic class names (`.timeline-item`, not `.flex-col`)
- Extend existing components via page-specific files
- Keep common patterns in brutalist modules
- Use CSS variables from `_theme.scss` for colors

### ❌ DON'T:
- Duplicate styles across page files
- Create utility classes in page files
- Override brutalist module styles directly (extend instead)
- Mix design patterns (stay brutalist)

---

## 🔧 Adding New Components

1. **Identify category**: Form, layout, component, or utility?
2. **Check existing modules**: Can you extend existing classes?
3. **Add to appropriate file**:
   - Shared → brutalist module
   - Page-specific → page SCSS
4. **Document in this file**

---

## 📊 File Size Breakdown

| File | Purpose | Lines | Priority |
|------|---------|-------|----------|
| `_data-brutalism.scss` | Core components | ~390 | Critical |
| `_forms-brutalist.scss` | Form elements | ~130 | High |
| `_layout-brutalist.scss` | Page layouts | ~100 | High |
| `_components-brutalist.scss` | UI patterns | ~210 | High |
| `_utilities-brutalist.scss` | Helpers | ~100 | Medium |
| `_activities.scss` | Activities page | ~230 | Page |
| `_pipelines.scss` | Pipelines page | ~540 | Page |

**Total Brutalist System:** ~930 lines  
**Page-specific:** ~770 lines

---

## 🎨 Color System

```scss
// Accent
$accent: #FF6B35;

// Activity Types
.badge--type-call     // #2196F3 (Blue)
.badge--type-meeting  // #4CAF50 (Green)
.badge--type-email    // #9C27B0 (Purple)
.badge--type-note     // #FFEB3B (Yellow)
.badge--type-task     // #FF6B35 (Orange - accent)

// Status
.badge--success  // Green
.badge--warning  // Yellow
.badge--danger   // Red
.badge--accent   // Orange
```

---

## 📱 Responsive Strategy

All brutalist modules include responsive breakpoints:
- **Mobile**: `max-width: 768px`
- **Small mobile**: `max-width: 480px`

**Responsive patterns:**
- Grids → single column
- Horizontal → vertical stacks
- Reduce font sizes
- Adjust spacing

---

## 🔄 Migration from Old Structure

**Before:**
```scss
// _activities.scss (570 lines)
.activities-index {
  .page-header { ... }
  .filters-card { ... }
  .timeline { ... }
  .pagination { ... }
}
```

**After:**
```scss
// _layout-brutalist.scss
.page-header { ... }
.filters-card { ... }

// _components-brutalist.scss
.timeline { ... }
.pagination { ... }

// _activities.scss (230 lines)
.activities-index {
  .timeline-marker.marker-call { ... } // page-specific
  .activity-header { ... } // page-specific
}
```

**Result:** 60% reduction in duplication across pages

---

## 📚 References

- **Design Philosophy**: Editorial Brutalism (data-first, transparent, bold)
- **Typography**: Space Grotesk (display), Inter (body)
- **Inspiration**: Swiss Design, Data Visualization dashboards
- **Accessibility**: WCAG AA compliant (contrast, focus states)

---

**Last Updated:** 2026-02-06  
**Version:** 2.0 (Modular Brutalist Architecture)
