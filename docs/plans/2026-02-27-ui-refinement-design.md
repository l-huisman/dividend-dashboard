# UI Refinement Design

Date: 2026-02-27

## Overview

Seven changes to bring the Vue frontend closer to the original JSX functionality and improve UX.

## 1. Sidebar Navigation

Replace the top NavBar with a collapsible left sidebar.

- **Collapsed (default):** 64px wide, icons only, brand shows "DF"
- **Expanded:** 224px wide, icon + label, brand shows "DividendFlow"
- **Toggle:** Chevron button at sidebar bottom
- **Icons (Heroicons 24/outline):** HomeIcon, BriefcaseIcon, ArrowTrendingUpIcon, CalendarIcon, ArrowUpTrayIcon, UsersIcon
- **Bottom section:** Theme toggle, username, logout
- **Mobile:** Hidden by default, slide-in overlay with backdrop via hamburger button
- **AppLayout:** Horizontal flex layout, main content offset by sidebar width with transition

**Files:** New `SideBar.vue`, modified `AppLayout.vue`

## 2. Income Timeline (Custom SVG)

Replace the Chart.js bar chart with a custom horizontally scrollable SVG timeline spanning the full projection period.

### Data computation (client-side)

- Take 12-month base income pattern from `portfolio.calendar.monthly_income`
- Apply growth multipliers month-by-month using projection store data
- Generate `projYears * 12` data points starting from current month

### SVG structure

- Container: `overflow-x: auto` with native scroll, `scrollbar-width: thin`
- Floating Y-axis: Absolute left overlay with gradient fade, 5 tick marks, animated via `requestAnimationFrame`
- SVG width: `numBars * 48px`, each bar 28px wide with 4px border-radius
- Color gradient: Green `rgb(16,185,129)` to blue `rgb(37,99,235)` based on `year / maxYear`
- Value labels on every bar above the bar
- X-axis labels: Month abbreviations, January shows full year number in bold
- Year separators: Dotted vertical lines at each January
- Click handler: `scrollTo({ left: idx * 48, behavior: 'smooth' })`
- Native `<title>` elements for hover tooltips
- Current month: Amber highlight with "NOW" indicator
- Selected bar: Blue highlight

### Scroll event

On scroll, emit `timelineViewStart` index to parent DashboardView for dynamic summary cards.

**Files:** Rewrite `IncomeTimeline.vue`, modify `DashboardView.vue`

## 3. Dynamic Summary Cards

Summary cards update based on timeline scroll position.

- New props: `timelineIncome`, `timelineViewStart`, `projectionData`, `projYears`
- 5 cards (up from 4):
  1. Annual Dividends (with year label when scrolled)
  2. Monthly Income (with year label)
  3. Daily Income (with year label)
  4. Portfolio value or Total Invested (contextual)
  5. Final projection (always shows end-state)
- Grid: `grid-cols-2 lg:grid-cols-5`
- Values animate with `transition: all 0.3s ease`

**Files:** Modified `SummaryCards.vue`, modified `DashboardView.vue`

## 4. Portfolio Table Pagination

- `perPage` ref (default 10), `currentPage` ref (default 1)
- Computed `paginatedRows` slices `sortedRows`
- Pagination bar: "Showing X-Y of Z", prev/next buttons, page numbers, per-page dropdown (10/25/50)
- Reset page to 1 on search or sort change

**Files:** Modified `HoldingsTable.vue`

## 5. Explanation Texts

Add subtitle/description text to all cards and sections, ported from JSX:

| Location | Text |
|----------|------|
| IncomeTimeline | "Scroll through your dividend income from today to Year {X}" |
| SectorPieChart | "Portfolio value distribution (EUR)" |
| TopEarners | "Your highest-paying holdings (EUR)" |
| BestBuyWindows | "Buy before these dates to capture upcoming dividends" + T+1 settlement note |
| ProjectionView | "How this projection works" info box |
| GrowthChart | Subtitle with contribution/growth params |
| DividendGrowthChart | Subtitle with dividend growth rate |
| UpcomingEvents | "Next ex-dividend and payment dates for your holdings" |
| MonthlyOverview | "Which stocks pay dividends in each month" |

**Files:** Multiple component files

## 6. Upcoming Dividends Filter

Filter out events where `days_until_ex < 0` in `UpcomingEvents.vue`.

**Files:** Modified `UpcomingEvents.vue`

## 7. Manual Stock Entry (Combobox)

Replace `<select>` with autocomplete text input.

- Type to search existing stocks
- If no match, show "Add new stock" option
- Selecting "Add new stock" expands form with: ticker, name, price, dividend_per_share, sector
- Submit creates stock via `POST /stocks` first, then creates holding

**Files:** Modified `ManualAddForm.vue`
