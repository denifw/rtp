<?php

namespace App\Http\Middleware;

use App\Frame\Formatter\Trans;
use Closure;

class CheckApiToken
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        if (empty($request->get('us_api_token')) === true || empty($request->get('ss_id')) === true) {
            return response()->json([
                'success' => false,
                'response_code' => '404',
                'message' => Trans::getWord('404', 'api_response'),
                'results' => []
            ]);
        }
        return $next($request);
    }
}
