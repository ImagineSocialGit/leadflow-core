@php
    $sharedContent = config('webinars.content', []);
    $sharedStyle = config('webinars.style', []);

    $brand = $sharedContent['brand'] ?? [];
    $layout = $sharedStyle['layout'] ?? [];

    $header = $layout['header'] ?? [];
    $footer = $layout['footer'] ?? [];
    $primaryLink = $header['primary_link'] ?? [];

    $brandName = $brand['name'] ?? config('app.name');

    $primaryLinkLabel = $primaryLink['label'] ?? 'Webinars';

    $primaryLinkHref = isset($primaryLink['route'])
        ? route($primaryLink['route'])
        : url('/');
@endphp

<x-layouts.app :title="$title ?? $brandName" :meta-description="$metaDescription ?? null">
    <div class="{{ $layout['body'] ?? 'min-h-screen flex flex-col bg-white text-slate-900' }}">
        <header class="{{ $header['wrap'] ?? 'border-b border-slate-200 bg-white' }}">
            <div class="{{ $header['inner'] ?? 'mx-auto flex w-full max-w-7xl items-center justify-between px-6 py-4' }}">

                @if ($brand['logo'] ?? null)
                <a href="{{ $primaryLinkHref }}" class="{{ $header['brand_image'] ?? 'max-w-20 max-h-20 w-full h-full' }}">
                    <x-ui.image
                        :path="$brand['logo']"
                        :alt="$brand['image_alt'] ?? 'Logo'"
                        :sizes="$brand['image_sizes'] ?? '(min-width:1024px) 40vw,100vw'"
                        class="{{ $style['instructor']['image_class'] ?? 'w-full rounded-3xl object-cover' }}"
                        :placeholder="false"
                    />
                </a>
                @else
                <a href="{{ $primaryLinkHref }}" class="{{ $header['brand'] ?? 'text-lg font-semibold tracking-tight' }}">
                    {{ $brandName }}
                </a>
                @endif

                <nav class="{{ $header['nav'] ?? 'hidden items-center gap-6 text-sm font-medium md:flex' }}">
                    <a href="{{ $primaryLinkHref }}" class="{{ $header['nav_link'] ?? 'transition hover:opacity-70' }}">
                        {{ $primaryLinkLabel }}
                    </a>
                </nav>
            </div>
        </header>

        <main class="{{ $layout['main'] ?? 'flex-1' }}">
            {{ $slot }}
        </main>

        <footer class="{{ $footer['wrap'] ?? 'border-t border-slate-200 bg-white' }}">
            @if($sharedContent['footer']['compliance_identity']['enabled'] ?? false)
                <div class="{{ $sharedStyle['footer']['compliance_identity']['wrapper'] ?? 'mt-6 text-center' }}">
                    @foreach(($sharedContent['footer']['compliance_identity']['lines'] ?? []) as $line)
                        <span class="{{ $sharedStyle['footer']['compliance_identity']['line'] ?? 'block text-xs leading-6 text-white/90' }}">
                            {{ $line }}
                        </span>
                    @endforeach
                </div>
            @endif
            <div class="{{ $footer['inner'] ?? 'mx-auto w-full max-w-7xl px-6 py-6 text-sm text-slate-500' }}">
                {{ $footer['text'] ?? $brandName }}
            </div>
        </footer>
    </div>
</x-layouts.app>