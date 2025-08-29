// Home Ads Tracking Implementation
// File: public/js/ads-tracker.js

class AdsTracker {
    constructor(apiKey, baseUrl = '/api/v1') {
        this.apiKey = apiKey;
        this.baseUrl = baseUrl;
        this.viewedAds = new Set(); // Track yang sudah di-view untuk mencegah duplikasi
        this.clickDebounce = new Map(); // Debounce untuk click tracking
        
        this.init();
    }
    
    init() {
        // Setup Intersection Observer untuk track views
        this.setupViewTracking();
        
        // Setup click tracking untuk semua ads
        this.setupClickTracking();
    }
    
    /**
     * Setup automatic view tracking menggunakan Intersection Observer
     */
    setupViewTracking() {
        const options = {
            root: null,
            rootMargin: '0px',
            threshold: 0.5 // Trigger ketika 50% iklan visible
        };
        
        const observer = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    const adsId = entry.target.dataset.adsId;
                    
                    // Cek apakah sudah pernah di-track
                    if (!this.viewedAds.has(adsId)) {
                        this.trackView(adsId);
                        this.viewedAds.add(adsId);
                    }
                }
            });
        }, options);
        
        // Observe semua elemen ads
        document.querySelectorAll('[data-ads-id]').forEach(adsElement => {
            observer.observe(adsElement);
        });
    }
    
    /**
     * Setup click tracking dengan debounce
     */
    setupClickTracking() {
        document.addEventListener('click', (event) => {
            const adsElement = event.target.closest('[data-ads-id]');
            if (adsElement) {
                event.preventDefault(); // Prevent immediate navigation
                
                const adsId = adsElement.dataset.adsId;
                const clickableLink = adsElement.dataset.adsLink || adsElement.href;
                
                // Debounce click untuk mencegah double-click
                if (!this.clickDebounce.has(adsId)) {
                    this.clickDebounce.set(adsId, true);
                    
                    this.trackClick(adsId, clickableLink);
                    
                    // Clear debounce setelah 1 detik
                    setTimeout(() => {
                        this.clickDebounce.delete(adsId);
                    }, 1000);
                }
            }
        });
    }
    
    /**
     * Track view dengan API call
     */
    async trackView(adsId) {
        try {
            const response = await fetch(`${this.baseUrl}/home-ads/${adsId}/view`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-API-KEY': this.apiKey,
                    'X-Requested-With': 'XMLHttpRequest'
                }
            });
            
            if (!response.ok) {
                console.warn(`Failed to track view for ads ${adsId}:`, response.status);
            } else {
                console.debug(`View tracked for ads ${adsId}`);
            }
            
        } catch (error) {
            console.error(`Error tracking view for ads ${adsId}:`, error);
        }
    }
    
    /**
     * Track click dengan API call dan redirect
     */
    async trackClick(adsId, redirectUrl = null) {
        try {
            const response = await fetch(`${this.baseUrl}/home-ads/${adsId}/click`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-API-KEY': this.apiKey,
                    'X-Requested-With': 'XMLHttpRequest'
                }
            });
            
            if (response.ok) {
                const data = await response.json();
                console.debug(`Click tracked for ads ${adsId}`);
                
                // Redirect ke URL yang dikembalikan API atau URL yang disediakan
                const finalUrl = data.data?.redirect_url || redirectUrl;
                if (finalUrl) {
                    // Delay sedikit untuk memastikan tracking tercatat
                    setTimeout(() => {
                        window.open(finalUrl, '_blank');
                    }, 100);
                }
            } else {
                console.warn(`Failed to track click for ads ${adsId}:`, response.status);
                
                // Tetap redirect meski tracking gagal
                if (redirectUrl) {
                    window.open(redirectUrl, '_blank');
                }
            }
            
        } catch (error) {
            console.error(`Error tracking click for ads ${adsId}:`, error);
            
            // Tetap redirect meski ada error
            if (redirectUrl) {
                window.open(redirectUrl, '_blank');
            }
        }
    }
    
    /**
     * Manual tracking methods untuk use case khusus
     */
    manualTrackView(adsId) {
        if (!this.viewedAds.has(adsId)) {
            this.trackView(adsId);
            this.viewedAds.add(adsId);
        }
    }
    
    manualTrackClick(adsId, redirectUrl = null) {
        this.trackClick(adsId, redirectUrl);
    }
    
    /**
     * Get statistics untuk ads tertentu
     */
    async getStatistics(adsId) {
        try {
            const response = await fetch(`${this.baseUrl}/home-ads/${adsId}/statistics`, {
                headers: {
                    'X-API-KEY': this.apiKey,
                    'X-Requested-With': 'XMLHttpRequest'
                }
            });
            
            if (response.ok) {
                return await response.json();
            } else {
                throw new Error(`HTTP ${response.status}`);
            }
            
        } catch (error) {
            console.error(`Error getting statistics for ads ${adsId}:`, error);
            return null;
        }
    }
}

// Export untuk module systems
if (typeof module !== 'undefined' && module.exports) {
    module.exports = AdsTracker;
}

// Auto-initialize jika ada konfigurasi global
if (typeof window !== 'undefined' && window.ADS_TRACKER_CONFIG) {
    window.adsTracker = new AdsTracker(
        window.ADS_TRACKER_CONFIG.apiKey,
        window.ADS_TRACKER_CONFIG.baseUrl
    );
}
