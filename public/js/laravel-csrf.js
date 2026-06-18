(function (global) {
    'use strict';

    function readXsrfCookie() {
        const match = document.cookie.match(/(?:^|;\s*)XSRF-TOKEN=([^;]*)/);
        return match ? decodeURIComponent(match[1]) : '';
    }

    function token() {
        const meta = document.querySelector('meta[name="csrf-token"]');
        if (meta?.content) {
            return meta.content;
        }
        if (global.c4DiagramConfig?.csrf) {
            return global.c4DiagramConfig.csrf;
        }
        const input = document.querySelector('input[name="_token"]');
        if (input?.value) {
            return input.value;
        }

        return readXsrfCookie();
    }

    function headers(extra) {
        const t = token();
        const base = {
            Accept: 'application/json',
            'X-Requested-With': 'XMLHttpRequest',
        };
        if (t) {
            base['X-CSRF-TOKEN'] = t;
            base['X-XSRF-TOKEN'] = t;
        }

        return Object.assign(base, extra || {});
    }

    function appendToken(formData) {
        if (formData && !formData.has('_token')) {
            const t = token();
            if (t) {
                formData.append('_token', t);
            }
        }

        return formData;
    }

    async function csrfFetch(url, options) {
        const opts = options || {};
        const method = (opts.method || 'GET').toUpperCase();
        const hdrs = headers(opts.headers);
        let body = opts.body;

        if (body !== undefined && body !== null && typeof body === 'object' && !(body instanceof FormData)) {
            hdrs['Content-Type'] = 'application/json';
            body = JSON.stringify(body);
        } else if (body instanceof FormData) {
            appendToken(body);
        }

        return fetch(url, {
            credentials: opts.credentials || 'same-origin',
            ...opts,
            method,
            headers: hdrs,
            body,
        });
    }

    global.LaravelCsrf = {
        token,
        headers,
        appendToken,
        fetch: csrfFetch,
    };
})(window);
