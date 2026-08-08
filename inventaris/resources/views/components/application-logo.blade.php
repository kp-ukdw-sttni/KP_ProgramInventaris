<div {{ $attributes->merge(['class' => 'flex items-center space-x-2']) }}>
    <!-- STTNI logo image -->
    <img src="{{ asset('images/logo-sttni.png') }}" 
         alt="Logo STTNI" 
         class="h-10 w-auto flex-shrink-0" 
         id="sttniLogoImg"
         onerror="this.style.display='none'; document.getElementById('logo-fallback-text').style.display='inline-flex';" />
         
    <!-- Fallback Text emblem in case the image file is missing -->
    <span id="logo-fallback-text" class="font-extrabold text-lg tracking-wider text-gray-900 whitespace-nowrap flex items-center space-x-2">
        <svg class="w-8 h-8 text-blue-600 flex-shrink-0" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
            <path stroke-linecap="round" stroke-linejoin="round" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
        </svg>
        <span>
            <span class="text-blue-600">INVENTARIS</span> STTNI
        </span>
    </span>
</div>

<script>
    // Ensure the fallback text is hidden if the image successfully loads
    document.getElementById('sttniLogoImg').addEventListener('load', function() {
        document.getElementById('logo-fallback-text').style.display = 'none';
    });
</script>
