<div {{ $attributes->merge(['class' => 'flex items-center space-x-2']) }}>
    <!-- STTNI logo image -->
    <img src="{{ asset('images/logo-sttni.png') }}" 
         alt="Logo STTNI" 
         class="h-10 w-auto flex-shrink-0" 
         id="sttniLogoImg"
         onerror="this.style.display='none';" />
         
    <span class="font-extrabold text-lg tracking-wider text-gray-900 whitespace-nowrap">
        <span class="text-blue-600">INVENTARIS</span> STTNI
    </span>
</div>
