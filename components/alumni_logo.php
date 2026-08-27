<?php
// Alumni Logo Component - Reusable across all alumni pages
?>
<div class="alumni-logo-component">
    <svg class="alumni-logo-small" viewBox="0 0 400 400" xmlns="http://www.w3.org/2000/svg">
        <!-- Background Circle -->
        <defs>
            <linearGradient id="logoGradientSmall" x1="0%" y1="0%" x2="100%" y2="100%">
                <stop offset="0%" style="stop-color:#667eea;stop-opacity:1" />
                <stop offset="100%" style="stop-color:#764ba2;stop-opacity:1" />
            </linearGradient>
            <linearGradient id="accentGradientSmall" x1="0%" y1="0%" x2="100%" y2="100%">
                <stop offset="0%" style="stop-color:#f093fb;stop-opacity:1" />
                <stop offset="100%" style="stop-color:#f5576c;stop-opacity:1" />
            </linearGradient>
            <filter id="glowSmall">
                <feGaussianBlur stdDeviation="2" result="coloredBlur"/>
                <feMerge> 
                    <feMergeNode in="coloredBlur"/>
                    <feMergeNode in="SourceGraphic"/>
                </feMerge>
            </filter>
        </defs>
        
        <!-- Main Circle Background -->
        <circle cx="200" cy="200" r="180" fill="url(#logoGradientSmall)" opacity="0.1"/>
        <circle cx="200" cy="200" r="160" fill="url(#logoGradientSmall)" opacity="0.2"/>
        <circle cx="200" cy="200" r="140" fill="url(#logoGradientSmall)" opacity="0.3"/>
        
        <!-- Graduation Cap -->
        <g transform="translate(200,150)">
            <!-- Cap Base -->
            <rect x="-50" y="-10" width="100" height="8" fill="url(#logoGradientSmall)" rx="4"/>
            <!-- Cap Top -->
            <rect x="-60" y="-18" width="120" height="4" fill="url(#logoGradientSmall)" rx="2"/>
            <!-- Tassel -->
            <line x1="45" y1="-14" x2="65" y2="10" stroke="url(#accentGradientSmall)" stroke-width="3" stroke-linecap="round"/>
            <circle cx="65" cy="10" r="4" fill="url(#accentGradientSmall)"/>
        </g>
        
        <!-- Book/Knowledge Symbol -->
        <g transform="translate(200,220)">
            <rect x="-30" y="-15" width="60" height="30" fill="url(#logoGradientSmall)" rx="3" opacity="0.8"/>
            <rect x="-25" y="-10" width="50" height="20" fill="white" rx="2" opacity="0.9"/>
            <line x1="-15" y1="-5" x2="15" y2="-5" stroke="url(#logoGradientSmall)" stroke-width="2"/>
            <line x1="-15" y1="0" x2="15" y2="0" stroke="url(#logoGradientSmall)" stroke-width="2"/>
            <line x1="-15" y1="5" x2="10" y2="5" stroke="url(#logoGradientSmall)" stroke-width="2"/>
        </g>
        
        <!-- Network Connections -->
        <g opacity="0.6">
            <circle cx="120" cy="120" r="6" fill="url(#accentGradientSmall)"/>
            <circle cx="280" cy="120" r="6" fill="url(#accentGradientSmall)"/>
            <circle cx="120" cy="280" r="6" fill="url(#accentGradientSmall)"/>
            <circle cx="280" cy="280" r="6" fill="url(#accentGradientSmall)"/>
            
            <line x1="120" y1="120" x2="200" y2="150" stroke="url(#accentGradientSmall)" stroke-width="2" opacity="0.5"/>
            <line x1="280" y1="120" x2="200" y2="150" stroke="url(#accentGradientSmall)" stroke-width="2" opacity="0.5"/>
            <line x1="120" y1="280" x2="200" y2="220" stroke="url(#accentGradientSmall)" stroke-width="2" opacity="0.5"/>
            <line x1="280" y1="280" x2="200" y2="220" stroke="url(#accentGradientSmall)" stroke-width="2" opacity="0.5"/>
        </g>
        
        <!-- Alumni Text -->
        <text x="200" y="320" text-anchor="middle" fill="url(#logoGradientSmall)" font-family="Poppins, sans-serif" font-size="20" font-weight="700">ALUMNI</text>
        <text x="200" y="340" text-anchor="middle" fill="url(#logoGradientSmall)" font-family="Inter, sans-serif" font-size="12" font-weight="500" opacity="0.8">NETWORK</text>
    </svg>
</div>

<style>
.alumni-logo-component {
    display: flex;
    justify-content: center;
    align-items: center;
    margin: 1rem 0;
}

.alumni-logo-small {
    width: 120px;
    height: 120px;
    filter: drop-shadow(0 5px 15px rgba(102, 126, 234, 0.2));
    animation: logoFloatSmall 4s ease-in-out infinite;
    transition: all 0.3s ease;
}

.alumni-logo-small:hover {
    transform: scale(1.1);
    filter: drop-shadow(0 8px 25px rgba(102, 126, 234, 0.3));
}

@keyframes logoFloatSmall {
    0%, 100% { transform: translateY(0px) rotate(0deg); }
    50% { transform: translateY(-5px) rotate(1deg); }
}

/* Responsive sizing */
@media (max-width: 768px) {
    .alumni-logo-small {
        width: 80px;
        height: 80px;
    }
}
</style>