@switch($icon)
    @case('washer')
        <svg viewBox="0 0 48 48" aria-hidden="true"><rect x="8" y="4" width="32" height="40" rx="5"/><path d="M8 14h32M14 9h.1M20 9h.1"/><circle cx="24" cy="29" r="10"/><path d="M16 29c2-2 4 2 8 0s6 2 8 0"/></svg>
        @break
    @case('fold')
        <svg viewBox="0 0 48 48" aria-hidden="true"><path d="M7 18h34v25H7zM11 18l5-8h16l5 8M17 25h14M17 31h14"/><path d="M20 11h8"/></svg>
        @break
    @case('shirt')
        <svg viewBox="0 0 48 48" aria-hidden="true"><path d="m17 6-11 6 5 11 7-3v22h20V20l7 3 5-11-11-6-6 6h-10z"/><path d="M17 6c0 5 3 9 7 9s7-4 7-9"/></svg>
        @break
    @case('iron')
        <svg viewBox="0 0 48 48" aria-hidden="true"><path d="M5 34h33a5 5 0 0 0 5-5v-3a8 8 0 0 0-8-8H21l-4-8H13v11l-5 3a9 9 0 0 0-3 10Z"/><path d="M5 40h38M25 18l3-8h7"/></svg>
        @break
    @case('dryer')
        <svg viewBox="0 0 48 48" aria-hidden="true"><path d="M6 25h28a7 7 0 0 0 7-7V9h-8v7H17a11 11 0 0 0-11 9Z"/><path d="M6 29h36M10 35h30M16 41h20"/><circle cx="15" cy="18" r="4"/></svg>
        @break
    @case('baby')
        <svg viewBox="0 0 48 48" aria-hidden="true"><path d="m15 8-10 6 5 10 6-3v21h18V21l6 3 5-10-10-6-5 5h-10z"/><path d="M17 8c0 4 3 7 7 7s7-3 7-7M20 28h.1M28 28h.1M20 34c2 2 6 2 8 0"/></svg>
        @break
    @case('hanger')
        <svg viewBox="0 0 48 48" aria-hidden="true"><path d="M22 14a4 4 0 1 1 4 4c-2 0-3 1-3 3v2M5 34l17-12a4 4 0 0 1 4 0l17 12a3 3 0 0 1-2 5H7a3 3 0 0 1-2-5Z"/></svg>
        @break
    @case('express')
        <svg viewBox="0 0 48 48" aria-hidden="true"><circle cx="28" cy="27" r="14"/><path d="M28 17v10l7 4M22 5h12M28 5v7M8 18h7M4 27h10M8 36h7M42 10l3-3"/></svg>
        @break
    @case('treatment')
        <svg viewBox="0 0 48 48" aria-hidden="true"><path d="m24 4 19 6v13c0 12-8 19-19 22C13 42 5 35 5 23V10z"/><path d="M24 15v18M15 24h18"/></svg>
@endswitch
