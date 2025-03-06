window.onload = function() {
    window.ui = SwaggerUIBundle({
        url: window.location.origin + "/v2/documentation/api-docs.yaml",
        dom_id: '#swagger-ui',
        docExpansion: 'none',
        deepLinking: true,
        persistAuthorization: true,
        filter: true,
        defaultModelsExpandDepth: 0,
        docExpansion: 'none',
        tagsSorter: 'alpha',
        presets: [
            SwaggerUIBundle.presets.apis,
            SwaggerUIStandalonePreset,
        ],
        plugins: [
            // SwaggerUIBundle.plugins.DownloadUrl
        ],
        layout: 'StandaloneLayout',
    });
};
