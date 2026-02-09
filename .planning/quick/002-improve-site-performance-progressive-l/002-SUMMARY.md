---
quick: 002
title: Improve Site Performance - Progressive Loading
subsystem: frontend-performance
tags: [performance, ux, loading-states, video]
dependency-graph:
  requires: []
  provides: [skeleton-loading, first-frame-poster]
  affects: [properties-page, homepage-hero]
tech-stack:
  added: []
  patterns: [skeleton-ui, progressive-loading]
key-files:
  created: []
  modified:
    - public/images/carousel/hero-poster.jpg
    - public/assets/index-DGSIg7Di.js
decisions: []
metrics:
  duration: ~2 minutes
  completed: 2026-02-09T14:18:06Z
---

# Quick Task 002: Improve Site Performance - Progressive Loading

Skeleton loading state with spinner and "Loading properties..." text for properties page; first-frame video poster extracted via ffmpeg.

## One-Liner

Progressive loading with 6 skeleton cards and spinner text replaces blocking spinner; hero poster now shows actual first video frame eliminating black flash.

## What Was Done

### Task 1: Extract true first frame as video poster
- Backed up existing poster to `hero-poster-backup.jpg`
- Used ffmpeg to extract frame 0 from `background-video.mp4`
- New poster is 12KB (optimized JPEG quality 2)
- Eliminates black flash before video playback

### Task 2 & 3: Add skeleton loading state with text indicator
- Replaced blocking `h0` spinner with skeleton card grid
- Added "Loading properties..." text with `Kw` spinner component
- 6 skeleton cards with `animate-pulse` animation
- Skeleton layout matches actual property card grid (1/2/3 columns responsive)
- Each skeleton card shows: image placeholder (h-48), title line, two detail lines

## Commits

| Hash | Message |
|------|---------|
| 91e4a44 | perf(quick-002): extract true first frame as video poster |
| caede9f | perf(quick-002): add skeleton loading state with text indicator |

## Files Modified

| File | Change |
|------|--------|
| public/images/carousel/hero-poster.jpg | Replaced with first frame from video |
| public/assets/index-DGSIg7Di.js | Added skeleton loading UI with spinner text |

## Deviations from Plan

None - plan executed exactly as written.

## Verification

1. **Video poster:** `hero-poster.jpg` now contains first frame (12KB, recent timestamp)
2. **Bundle validity:** `node --check` passes with no syntax errors
3. **Loading text:** "Loading properties..." string present in bundle
4. **Skeleton animation:** `animate-pulse` class added to bundle

## Self-Check: PASSED

- [x] `public/images/carousel/hero-poster.jpg` exists (12461 bytes)
- [x] `public/images/carousel/hero-poster-backup.jpg` backup exists
- [x] `public/assets/index-DGSIg7Di.js` contains "Loading properties..."
- [x] Bundle syntax valid (node --check passes)
- [x] Commit 91e4a44 exists
- [x] Commit caede9f exists
