@props(['subtitle' => null, 'size' => 'md'])
<div {{ $attributes->merge(['class' => 'flex items-center gap-3']) }}>
    <div class="{{ $size === 'sm' ? 'size-8 text-sm' : 'size-10 text-base' }} rounded-lg bg-primary flex items-center justify-center text-primary-foreground font-bold" aria-hidden="true">DS</div>
    <div>
        <div class="font-semibold text-foreground leading-tight">DoctorStats</div>
        @if($subtitle)
            <div class="text-xs text-muted-foreground leading-tight">{{ $subtitle }}</div>
        @endif
    </div>
</div>
