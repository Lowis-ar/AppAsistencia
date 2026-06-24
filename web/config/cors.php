<?php

/*
|--------------------------------------------------------------------------
| Cross-Origin Resource Sharing (CORS) Configuration
|--------------------------------------------------------------------------
|
| Aquí puede configurar sus ajustes de CORS. La app móvil necesita
| poder hacer peticiones cross-origin a la API.
|
| En producción, cambia 'allowed_origins' a los dominios específicos.
| En desarrollo, '*' permite peticiones desde cualquier origen.
|
*/

return [

    /*
     * Los paths de la API que estarán sujetos a CORS.
     */
    'paths' => ['api/*', 'sanctum/csrf-cookie'],

    /*
     * Métodos HTTP permitidos.
     */
    'allowed_methods' => ['*'],

    /*
     * Orígenes permitidos.
     * En producción reemplaza '*' con el dominio de tu app móvil.
     * Ejemplo: ['https://mi-app.com', 'capacitor://localhost']
     */
    'allowed_origins' => ['*'],

    /*
     * Patrones de orígenes permitidos (expresiones regulares).
     */
    'allowed_origins_patterns' => [],

    /*
     * Headers permitidos en las peticiones entrantes.
     */
    'allowed_headers' => ['*'],

    /*
     * Headers expuestos en la respuesta.
     */
    'exposed_headers' => [],

    /*
     * Tiempo máximo (en segundos) que el navegador puede cachear
     * la respuesta de pre-flight OPTIONS.
     */
    'max_age' => 0,

    /*
     * Indica si la solicitud puede incluir credenciales de usuario
     * (cookies, authorization headers, TLS client certificates).
     */
    'supports_credentials' => false,

];
