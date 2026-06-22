
    (function(window, document) {
        function LetterAvatar(name = '', size = 60) {
            const colors = [
                "#1abc9c", "#2ecc71", "#3498db", "#9b59b6", "#34495e", "#16a085", "#27ae60", "#2980b9", "#8e44ad", "#2c3e50",
                "#f1c40f", "#e67e22", "#e74c3c", "#f39c12", "#d35400", "#c0392b"
            ];

            const nameParts = name.trim().toUpperCase().split(/\s+/).filter(Boolean);
            let initials = '?';
            if (nameParts.length === 1) {
                initials = nameParts[0].charAt(0);
            } else if (nameParts.length > 1) {
                initials = nameParts[0].charAt(0) + nameParts[nameParts.length - 1].charAt(0);
            }

            if (window.devicePixelRatio) {
                size *= window.devicePixelRatio;
            }

            const charIndex = initials === '?' ? 72 : initials.charCodeAt(0) - 64;
            const colorIndex = charIndex % colors.length;

            const canvas = document.createElement('canvas');
            canvas.width = size;
            canvas.height = size;

            const context = canvas.getContext("2d");
            context.fillStyle = colors[colorIndex];
            context.fillRect(0, 0, canvas.width, canvas.height);

            context.font = `${Math.round(canvas.width / 2)}px Arial`;
            context.textAlign = "center";
            context.fillStyle = "#FFF";
            context.fillText(initials, size / 2, size / 1.5);

            const dataURI = canvas.toDataURL();
            return dataURI;
        }

        LetterAvatar.transform = function() {
            const avatarImages = document.querySelectorAll('img[avatar]');
            avatarImages.forEach(img => {
                const name = img.getAttribute('avatar');
                const width = parseInt(img.getAttribute('width'), 10) || 60; // Default to 60 if width is not valid
                img.src = LetterAvatar(name, width);
                img.removeAttribute('avatar');
                img.setAttribute('alt', name);
            });
        };

        if (typeof define === 'function' && define.amd) {
            define(() => LetterAvatar);
        } else if (typeof exports !== 'undefined') {
            if (typeof module !== 'undefined' && module.exports) {
                exports = module.exports = LetterAvatar;
            }
            exports.LetterAvatar = LetterAvatar;
        } else {
            window.LetterAvatar = LetterAvatar;
            document.addEventListener('DOMContentLoaded', () => {
                LetterAvatar.transform();
            });
            
            // Livewire 3: Re-initialize avatars after each component update
            document.addEventListener('livewire:initialized', () => {
                Livewire.hook('morph.updated', () => {
                    LetterAvatar.transform();
                });
                
                // Also transform on initial load
                LetterAvatar.transform();
            });
        }
    })(window, document);
