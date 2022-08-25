<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Foundation\Application;
class Localization
{
     /**
     * Localization constructor.
     *
     * @param \Illuminate\Foundation\Application $app
     */
    public function __construct(Application $app)
    {
        $this->app = $app;
    }

    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        // read the language from the request header
        $locale = $request->header('Content-Language');

        // if the header is missed
        if(!$locale){
            // take the default local language
            $locale = $this->app->config->get('app.locale');
        }

        $supported_langs = $this->app->config->get('app.supported_languages');
        // check the languages defined is supported
        if (!array_key_exists($locale, $supported_langs)) { 
            // respond with error
            return response()->json([
                'success' => 'false',
                'message' => 'Language not supported.',
                'err_code' => 403
            ]);
            // return abort(403, 'Language not supported.');
        }  

        // set the local language
        $this->app->setLocale($locale);

        // get the response after the request is done
        $response = $next($request);

        // set Content Languages header in the response
        $response->headers->set('Content-Language', $locale);

        // return the response
        return $response;
        // return $next($request);
    }
}
