(function () {
    'use strict';

    window.initSwaggerSpec = function (spec) {
        if (!spec || typeof SwaggerUIBundle === 'undefined') return;

        SwaggerUIBundle({
            spec: spec,
            dom_id: '#swagger-ui',
            deepLinking: true,
            presets: [
                SwaggerUIBundle.presets.apis,
                SwaggerUIStandalonePreset,
            ],
            plugins: [
                SwaggerUIBundle.plugins.DownloadUrl,
            ],
            layout: 'StandaloneLayout',
            defaultModelsExpandDepth: 2,
            defaultModelExpandDepth: 2,
            docExpansion: 'list',
            filter: false,
            tryItOutEnabled: true,
            persistAuthorization: true,
            displayRequestDuration: true,
            syntaxHighlight: {
                activate: true,
                theme: 'agate',
            },
        });
    };
})();
