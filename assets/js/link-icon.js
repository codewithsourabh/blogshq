/**
 * Link Icon JavaScript for TOC
 *
 * @package BlogsHQ
 * @since 1.0.0
 */
(function() {
    'use strict';

    var settings = blogshqLinkIcon || {};
    var headingSelectors = settings.headings || ['h2'];
    var iconColor = settings.iconColor || '#2E62E9';
    var copiedText = settings.copiedText || 'The link has been copied to your clipboard.';
    var copyLabel = settings.copyLabel || 'Copy link to this section';

    /**
     * Show popup notification
     */
    function showPopup() {
        var popup = document.getElementById('blogshq-link-popup');
        
        if (!popup) {
            popup = document.createElement('div');
            popup.id = 'blogshq-link-popup';
            popup.innerHTML = '<span>' + copiedText + '</span>';
            popup.style.cssText = 'display:none;position:fixed;bottom:-40px;left:50%;transform:translateX(-50%);padding:14px 20px;border-radius:5px;color:white;font-weight:500;font-size:16px;background-color:' + iconColor + ';box-shadow:0 4px 24px rgba(0,0,0,0.07);transition:bottom 0.5s,opacity 0.5s;z-index:9999;';
            document.body.appendChild(popup);
        }

        var isMobile = window.innerWidth <= 768;
        var bottomPosition = isMobile ? '60px' : '40px';
        
        popup.style.display = 'block';
        
        setTimeout(function() {
            popup.style.bottom = bottomPosition;
            popup.style.opacity = '1';
        }, 10);
        
        setTimeout(function() {
            popup.style.bottom = '-40px';
            popup.style.opacity = '0';
            setTimeout(function() {
                popup.style.display = 'none';
            }, 500);
        }, 3000);
    }

    /**
     * Copy link to clipboard
     */
    function copyLinkToClipboard(headingId) {
        var link = window.location.origin + window.location.pathname + '#' + headingId;
        
        if (navigator.clipboard && navigator.clipboard.writeText) {
            navigator.clipboard.writeText(link)
                .then(function() {
                    showPopup();
                })
                .catch(function(err) {
                    console.error('Failed to copy:', err);
                });
        } else {
            // Fallback
            var textArea = document.createElement('textarea');
            textArea.value = link;
            textArea.style.position = 'fixed';
            textArea.style.opacity = '0';
            document.body.appendChild(textArea);
            textArea.select();
            
            try {
                document.execCommand('copy');
                showPopup();
            } catch (err) {
                console.error('Fallback copy failed:', err);
            }
            
            document.body.removeChild(textArea);
        }
    }

    /**
     * Create link icon SVG
     */
    function createLinkIcon() {
        var icon = document.createElement('button');
        icon.className = 'link-icon';
        icon.type = 'button';
        icon.setAttribute('role', 'button');
        icon.setAttribute('tabindex', '0');
        icon.setAttribute('aria-label', copyLabel);
        icon.style.cssText = 'display:inline-flex;align-items:center;opacity:0;transition:opacity 0.15s;margin-left:8px;vertical-align:middle;cursor:pointer;text-decoration:none;background:none;border:none;padding:0;';
        
        icon.innerHTML = '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 640 640" width="20" height="20" style="fill:' + iconColor + ';vertical-align:middle;display:block;"><path d="M451.5 160C434.9 160 418.8 164.5 404.7 172.7C388.9 156.7 370.5 143.3 350.2 133.2C378.4 109.2 414.3 96 451.5 96C537.9 96 608 166 608 252.5C608 294 591.5 333.8 562.2 363.1L491.1 434.2C461.8 463.5 422 480 380.5 480C294.1 480 224 410 224 323.5C224 322 224 320.5 224.1 319C224.6 301.3 239.3 287.4 257 287.9C274.7 288.4 288.6 303.1 288.1 320.8C288.1 321.7 288.1 322.6 288.1 323.4C288.1 374.5 329.5 415.9 380.6 415.9C405.1 415.9 428.6 406.2 446 388.8L517.1 317.7C534.4 300.4 544.2 276.8 544.2 252.3C544.2 201.2 502.8 159.8 451.7 159.8zM307.2 237.3C305.3 236.5 303.4 235.4 301.7 234.2C289.1 227.7 274.7 224 259.6 224C235.1 224 211.6 233.7 194.2 251.1L123.1 322.2C105.8 339.5 96 363.1 96 387.6C96 438.7 137.4 480.1 188.5 480.1C205 480.1 221.1 475.7 235.2 467.5C251 483.5 269.4 496.9 289.8 507C261.6 530.9 225.8 544.2 188.5 544.2C102.1 544.2 32 474.2 32 387.7C32 346.2 48.5 306.4 77.8 277.1L148.9 206C178.2 176.7 218 160.2 259.5 160.2C346.1 160.2 416 230.8 416 317.1C416 318.4 416 319.7 416 321C415.6 338.7 400.9 352.6 383.2 352.2C365.5 351.8 351.6 337.1 352 319.4C352 318.6 352 317.9 352 317.1C352 283.4 334 253.8 307.2 237.5z"/></svg>';
        
        return icon;
    }

    /**
     * Add icons to headings
     */
    function addIconsToHeadings() {
        var contentArea = document.querySelector('.entry-content');
        
        if (!contentArea) {
            return;
        }

        // Add CSS for hover effect
        var style = document.createElement('style');
        style.textContent = headingSelectors.map(function(tag) {
            return '.entry-content ' + tag + ':hover .link-icon { opacity: 1 !important; }';
        }).join('\n');
        document.head.appendChild(style);

        // Add icons to each selected heading
        headingSelectors.forEach(function(tag) {
            var headings = contentArea.querySelectorAll(tag + '[id]');
            
            headings.forEach(function(heading) {
                if (heading.querySelector('.link-icon')) {
                    return; // Already has icon
                }

                var icon = createLinkIcon();
                heading.appendChild(icon);

                // Click handler
                icon.addEventListener('click', function(e) {
                    e.preventDefault();
                    copyLinkToClipboard(heading.id);
                });

                // Keyboard handler
                icon.addEventListener('keypress', function(e) {
                    if (e.key === 'Enter' || e.key === ' ') {
                        e.preventDefault();
                        copyLinkToClipboard(heading.id);
                    }
                });
            });
        });
    }

    /**
     * Add smooth scroll to TOC links
     */
    function addSmoothScrollToTOC() {
        var tocLinks = document.querySelectorAll('.blogshq-toc a[href^="#"]');
        
        tocLinks.forEach(function(link) {
            link.addEventListener('click', function(e) {
                e.preventDefault();
                var targetId = this.getAttribute('href').substring(1);
                var target = document.getElementById(targetId);
                
                if (target) {
                    target.scrollIntoView({
                        behavior: 'smooth',
                        block: 'start'
                    });
                }
            });
        });
    }

    /**
     * Initialize on DOM ready
     */
    function init() {
        addIconsToHeadings();
        addSmoothScrollToTOC();
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', init);
    } else {
        init();
    }
})();
