(function () {
    'use strict';

    const TYPE_FIELD_IDS = [
        'rest-fields',
        'soap-fields',
        'graphql-fields',
        'grpc-fields',
        'websocket-fields',
        'sse-fields',
        'socketio-fields',
        'ftps-fields',
        'sftp-fields',
        'zabbix-fields',
        'siem-fields',
        'splunk-fields',
    ];

    const NON_API_TYPES = ['ftps', 'sftp', 'zabbix', 'siem', 'splunk'];

    document.addEventListener('DOMContentLoaded', function () {
        const typeSelect = document.getElementById('api-type');
        const vendorFilter = document.getElementById('vendor-filter');
        const ownerSelect = document.getElementById('owner-system-id');
        const additionalSelect = document.getElementById('additional-system-ids');
        const apiFormatFields = document.getElementById('api-format-fields');
        const endpointLabel = document.getElementById('endpoint-label');
        const endpointHint = document.getElementById('endpoint-hint');
        const endpointInput = document.getElementById('endpoint-url');

        if (typeSelect) {
            function toggleFields() {
                const type = typeSelect.value;
                const isNonApi = NON_API_TYPES.includes(type);

                TYPE_FIELD_IDS.forEach(function (id) {
                    const el = document.getElementById(id);
                    if (el) {
                        el.style.display = id === type + '-fields' ? '' : 'none';
                    }
                });

                if (apiFormatFields) {
                    apiFormatFields.style.display = isNonApi ? 'none' : '';
                }

                if (endpointLabel) {
                    endpointLabel.textContent = isNonApi ? 'Connection / Host' : 'Endpoint URL';
                }

                if (endpointHint) {
                    endpointHint.textContent = isNonApi
                        ? 'Host, server, or collector address — no HTTP API endpoint required.'
                        : 'Full URL for API protocols; host or connection string for file transfer / monitoring integrations.';
                }

                if (endpointInput) {
                    endpointInput.placeholder = isNonApi
                        ? 'e.g. sftp.partner.com or splunk-hec.internal:8088'
                        : 'https://api.example.com/v1 or host:port';
                }
            }

            typeSelect.addEventListener('change', toggleFields);
            toggleFields();
        }

        function syncAdditionalSystems() {
            if (!ownerSelect || !additionalSelect) return;

            const ownerId = ownerSelect.value;

            Array.from(additionalSelect.options).forEach(function (option) {
                const isOwner = ownerId && option.value === ownerId;
                option.disabled = isOwner;
                if (isOwner) {
                    option.selected = false;
                }
            });
        }

        function filterOwnerSystems() {
            if (!ownerSelect || !vendorFilter) return;

            const vendorId = vendorFilter.value;
            let hasVisibleSelection = false;

            Array.from(ownerSelect.options).forEach(function (option) {
                if (!option.value) {
                    option.hidden = false;
                    return;
                }

                const matches = !vendorId || option.dataset.vendorId === vendorId;
                option.hidden = !matches;

                if (matches && option.selected) {
                    hasVisibleSelection = true;
                }
            });

            if (vendorId && !hasVisibleSelection) {
                ownerSelect.value = '';
            }

            syncAdditionalSystems();
        }

        if (ownerSelect && additionalSelect) {
            ownerSelect.addEventListener('change', syncAdditionalSystems);
            syncAdditionalSystems();
        }

        if (vendorFilter && ownerSelect) {
            vendorFilter.addEventListener('change', filterOwnerSystems);
            filterOwnerSystems();
        }
    });
})();
