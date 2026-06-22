

<script src="{{ asset('assets/js/toastr.js') }}"></script>
<script src="{{ asset('assets/js/sweet-alert.js') }}"></script>
<script>
    // Livewire 3 event listeners
    document.addEventListener('livewire:initialized', () => {
        // SweetAlert modal handler
        Livewire.on('swal:modal', (payload) => {
            const detail = Array.isArray(payload) ? payload[0] : payload;
            Swal.fire({
                title: detail.title,
                text: detail.text,
                icon: detail.type,
            });
        });

        // Toastr alert handler
        Livewire.on('alert', (payload) => {
            const detail = Array.isArray(payload) ? payload[0] : payload;
            const allowedTypes = ['success', 'error', 'info', 'warning'];
            const type = allowedTypes.includes(detail.type) ? detail.type : 'info';
            const message = detail.message ?? '';
            toastr.options = {
                "positionClass": "toast-top-right",
                "closeButton": true,
                "progressBar": true,
                "toastClass": `toastr custom-status-bar toastr-${type}`
            };
            if (typeof toastr[type] === 'function') {
                toastr[type](message, detail.title ?? '');
            }
        });

        // Custom CSS for status bar (only add once)
        if (!document.getElementById('toastr-custom-styles')) {
            const style = document.createElement('style');
            style.id = 'toastr-custom-styles';
            style.innerHTML = `
                .custom-status-bar { position: relative; overflow: hidden; }
                .custom-status-bar:before {
                    content: '';
                    display: block;
                    position: absolute;
                    left: 0; top: 0; bottom: 0;
                    width: 6px;
                    border-radius: 4px 0 0 4px;
                }
                .toastr-success.custom-status-bar:before { background: #28a745; }
                .toastr-error.custom-status-bar:before { background: #dc3545; }
                .toastr-info.custom-status-bar:before { background: #17a2b8; }
                .toastr-warning.custom-status-bar:before { background: #ffc107; }
            `;
            document.head.appendChild(style);
        }
    });
</script>
