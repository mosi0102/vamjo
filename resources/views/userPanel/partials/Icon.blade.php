{{-- آیکون SVG | ورودی: $name --}}
@php
    $icons = [
      'dashboard' => '<path d="M12 3l7.5 4.3v9.4L12 21l-7.5-4.3V7.3z"/><path d="M9.3 12.6c.7 1 1.6 1.5 2.7 1.5s2-.5 2.7-1.5"/>',
      'book'      => '<path d="M12 6c-1.8-1.3-4.3-2-8-2v13c3.7 0 6.2.7 8 2 1.8-1.3 4.3-2 8-2V4c-3.7 0-6.2.7-8 2zM12 6v13"/>',
      'doc'       => '<path d="M14 3H7a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h10a2 2 0 0 0 2-2V8z"/><path d="M14 3v5h5M9 15h6"/>',
      'card'      => '<rect x="3" y="5" width="18" height="14" rx="2.5"/><path d="M3 10h18M7 15h4"/>',
      'bookmark'  => '<path d="M6 4h12a1 1 0 0 1 1 1v15l-7-4-7 4V5a1 1 0 0 1 1-1z"/>',
      'radar'     => '<circle cx="12" cy="12" r="9"/><circle cx="12" cy="12" r="4.5"/><path d="M12 12l5-5"/>',
      'chat'      => '<path d="M20 12a8 8 0 0 1-11.6 7.1L4 20l1-4A8 8 0 1 1 20 12z"/><path d="M9 11h6M9 14h4"/>',
      'idcard'    => '<rect x="3" y="5" width="18" height="14" rx="2.5"/><circle cx="9" cy="11" r="2"/><path d="M6 16c.6-1.8 5.4-1.8 6 0M14 10h4M14 14h3"/>',
      'power'     => '<path d="M12 3v8"/><path d="M6.3 6.8a8 8 0 1 0 11.4 0"/>',
      'users'     => '<circle cx="9" cy="8" r="3.5"/><path d="M2.5 20a6.5 6.5 0 0 1 13 0M16 4.5a3.5 3.5 0 0 1 0 7M18 14.5a6.5 6.5 0 0 1 3.5 5.5"/>',
      'shield'    => '<path d="M12 3l8 3v6c0 4.5-3.3 8.3-8 9-4.7-.7-8-4.5-8-9V6z"/><path d="M9 12l2 2 4-4"/>',
      'settings'  => '<circle cx="12" cy="12" r="3"/><path d="M12 2v3M12 19v3M2 12h3M19 12h3M4.9 4.9l2.1 2.1M17 17l2.1 2.1M4.9 19.1L7 17M17 7l2.1-2.1"/>',
      'menu'      => '<path d="M4 7h16M4 12h16M4 17h10"/>',
      'arrow'     => '<path d="M5 12h14M13 6l6 6-6 6"/>',
      'clipboard' => '<rect x="6" y="4" width="12" height="17" rx="2"/><path d="M9 4h6v3H9zM9 12h6M9 16h4"/>',
      'wallet'    => '<path d="M3 7a2 2 0 0 1 2-2h12v4"/><path d="M3 7v11a2 2 0 0 0 2 2h14a1 1 0 0 0 1-1v-9a1 1 0 0 0-1-1H5a2 2 0 0 1-2-2z"/><circle cx="16.5" cy="14.5" r="1.2"/>',
      'bag'       => '<path d="M8 8l-2 4a5 5 0 0 0 0 7h12a5 5 0 0 0 0-7l-2-4"/><path d="M9 4h6l-1 4h-4zM12 12v4"/>',
      'user'      => '<circle cx="12" cy="8" r="4"/><path d="M4 21a8 8 0 0 1 16 0"/>',
      'close'     => '<path d="M6 6l12 12M18 6L6 18"/>',
    ];
@endphp
<svg viewBox="0 0 24 24" class="pn-ic" aria-hidden="true">{!! $icons[$name] ?? '' !!}</svg>
